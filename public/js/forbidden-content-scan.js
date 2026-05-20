/**
 * 违禁词实时扫描（debounce）与高亮预览。
 * 纯文本字段：标题下方展示 mark 高亮；TinyMCE 正文：标签旁显示违规计数。
 */
(function (global) {
    'use strict';

    var DEFAULT_DEBOUNCE = 500;

    function debounce(fn, ms) {
        var timer;
        return function () {
            var ctx = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, ms);
        };
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /**
     * 按 hit 位置在纯文本上插入 mark 标签（从后往前避免 offset 漂移）。
     */
    function buildHighlightedHtml(text, hits) {
        if (!text || !hits || hits.length === 0) {
            return '';
        }
        var fieldHits = hits.filter(function (h) {
            return h && typeof h.start === 'number' && typeof h.end === 'number';
        });
        if (fieldHits.length === 0) {
            return escapeHtml(text);
        }
        fieldHits.sort(function (a, b) {
            return b.start - a.start;
        });
        var result = text;
        fieldHits.forEach(function (hit) {
            var start = Math.max(0, hit.start);
            var end = Math.min(result.length, hit.end);
            if (start >= end) {
                return;
            }
            var before = result.slice(0, start);
            var mid = result.slice(start, end);
            var after = result.slice(end);
            var title = (hit.category_name || hit.category_slug || '违规') +
                (hit.suggestion ? '：' + hit.suggestion : '');
            result = before +
                '<mark class="bg-red-200 text-red-900 rounded px-0.5" title="' + escapeHtml(title) + '">' +
                escapeHtml(mid) +
                '</mark>' +
                after;
        });
        return result;
    }

    function categoryLabel(slug) {
        if (slug === 'compliance_redline') {
            return '合规红线';
        }
        if (slug === 'tone_violation') {
            return '调性违规';
        }
        return slug || '违规';
    }

    function ForbiddenContentScan() {}

    ForbiddenContentScan.prototype.init = function (options) {
        var self = this;
        this.scanUrl = options.scanUrl;
        this.context = options.context || 'article';
        this.debounceMs = options.debounceMs || DEFAULT_DEBOUNCE;
        this.fields = options.fields || [];
        this.panel = options.panelSelector
            ? document.querySelector(options.panelSelector)
            : document.getElementById('forbidden-live-scan-panel');
        this.pending = false;

        this.fields.forEach(function (field) {
            self.bindField(field);
        });

        this.scheduleScan = debounce(function () {
            self.runScan();
        }, this.debounceMs);

        // 初次加载也扫描一次（如 validation 回显后）
        setTimeout(function () {
            self.runScan();
        }, 300);
    };

    ForbiddenContentScan.prototype.bindField = function (field) {
        var self = this;
        var el = document.querySelector(field.selector);
        if (!el) {
            return;
        }

        if (field.tinymce) {
            var onBodyChange = function () {
                self.scheduleScan();
            };
            document.addEventListener('forbidden-content:body-changed', onBodyChange);
            return;
        }

        el.addEventListener('input', function () {
            self.scheduleScan();
        });
    };

    ForbiddenContentScan.prototype.collectFields = function () {
        var payload = {};
        var self = this;

        this.fields.forEach(function (field) {
            var el = document.querySelector(field.selector);
            if (!el) {
                return;
            }
            var value = '';
            if (field.tinymce && typeof tinymce !== 'undefined') {
                var ed = tinymce.get(el.id);
                value = ed ? ed.getContent() : (el.value || '');
            } else {
                value = el.value || '';
            }
            payload[field.key] = value;
        });

        return payload;
    };

    ForbiddenContentScan.prototype.runScan = function () {
        var self = this;
        if (!this.scanUrl || this.pending) {
            return;
        }

        var fields = this.collectFields();
        var hasContent = Object.keys(fields).some(function (k) {
            return String(fields[k] || '').trim() !== '';
        });
        if (!hasContent) {
            this.renderPanel([], null);
            this.updateFieldUi([], fields);
            return;
        }

        this.pending = true;
        this.setLoading(true);

        fetch(this.scanUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                context: this.context,
                fields: fields,
            }),
        })
            .then(function (res) {
                if (res.status === 401 || res.status === 403) {
                    return null;
                }
                return res.json();
            })
            .then(function (data) {
                if (!data) {
                    return;
                }
                var hits = Array.isArray(data.hits) ? data.hits : [];
                self.renderPanel(hits, data);
                self.updateFieldUi(hits, fields);
            })
            .catch(function () {
                /* 静默失败，不打扰输入 */
            })
            .finally(function () {
                self.pending = false;
                self.setLoading(false);
            });
    };

    ForbiddenContentScan.prototype.setLoading = function (loading) {
        if (!this.panel) {
            return;
        }
        if (loading) {
            this.panel.setAttribute('data-scan-loading', '1');
        } else {
            this.panel.removeAttribute('data-scan-loading');
        }
    };

    ForbiddenContentScan.prototype.renderPanel = function (hits, data) {
        if (!this.panel) {
            return;
        }

        if (!hits.length) {
            this.panel.classList.add('hidden');
            this.panel.innerHTML = '';
            return;
        }

        this.panel.classList.remove('hidden');
        var messages = (data && data.messages) ? data.messages : [];
        var html = '<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-900">';
        html += '<p class="font-medium mb-2">实时校验：发现 ' + hits.length + ' 处疑似违规</p>';
        if (messages.length) {
            html += '<ul class="list-disc list-inside mb-2 space-y-0.5">';
            messages.forEach(function (m) {
                html += '<li>' + escapeHtml(m) + '</li>';
            });
            html += '</ul>';
        }
        html += '<ul class="space-y-1 text-xs">';
        hits.forEach(function (h) {
            html += '<li><span class="font-medium">' + escapeHtml(h.field || '') + '</span>：';
            html += '「' + escapeHtml(h.word || '') + '」';
            html += ' <span class="text-red-700">(' + escapeHtml(categoryLabel(h.category_slug)) + ')</span></li>';
        });
        html += '</ul><p class="text-xs text-red-700/80 mt-2">提交时仍以服务端校验为准。</p></div>';
        this.panel.innerHTML = html;
    };

    ForbiddenContentScan.prototype.updateFieldUi = function (hits, fieldValues) {
        var self = this;
        this.fields.forEach(function (field) {
            var el = document.querySelector(field.selector);
            if (!el) {
                return;
            }
            var fieldHits = hits.filter(function (h) {
                return h.field === field.key;
            });

            if (field.previewSelector) {
                var preview = document.querySelector(field.previewSelector);
                if (preview) {
                    if (fieldHits.length && fieldValues[field.key]) {
                        preview.innerHTML = buildHighlightedHtml(fieldValues[field.key], fieldHits);
                        preview.classList.remove('hidden');
                    } else {
                        preview.innerHTML = '';
                        preview.classList.add('hidden');
                    }
                }
            }

            if (field.badgeSelector) {
                var badge = document.querySelector(field.badgeSelector);
                if (badge) {
                    if (fieldHits.length) {
                        badge.textContent = '违规 ' + fieldHits.length + ' 处';
                        badge.classList.remove('hidden');
                    } else {
                        badge.textContent = '';
                        badge.classList.add('hidden');
                    }
                }
            }

            if (fieldHits.length) {
                el.classList.add('ring-2', 'ring-red-300', 'border-red-400');
            } else {
                el.classList.remove('ring-2', 'ring-red-300', 'border-red-400');
            }
        });
    };

    global.ForbiddenContentScan = {
        init: function (options) {
            var instance = new ForbiddenContentScan();
            instance.init(options);
            return instance;
        },
    };
})(window);
