/**
 * 违禁词实时扫描（M8）：debounce 调用 scan API；标题/标签纯文本高亮；TinyMCE 仅显示违规计数。
 */

function debounce(fn, ms) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), ms);
    };
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/** 从后往前拼接 <mark>，避免偏移错乱 */
function highlightPlainText(text, hits) {
    if (!hits || hits.length === 0) {
        return escapeHtml(text);
    }
    const sorted = [...hits].sort((a, b) => (b.start ?? 0) - (a.start ?? 0));
    let tail = text.length;
    let html = '';
    sorted.forEach((h) => {
        const start = h.start ?? 0;
        const end = h.end ?? start;
        html =
            '<mark class="bg-red-200 rounded px-0.5">' +
            escapeHtml(text.slice(start, end)) +
            '</mark>' +
            escapeHtml(text.slice(end, tail)) +
            html;
        tail = start;
    });
    return escapeHtml(text.slice(0, tail)) + html;
}

function plainTextFromHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html || '';
    return (tmp.textContent || '').replace(/\s+/g, ' ').trim();
}

function getBodyPlainText(bodySelector, useTinymce) {
    const el = document.querySelector(bodySelector);
    if (!el) {
        return '';
    }
    const id = el.id || '';
    if (useTinymce && typeof tinymce !== 'undefined' && id) {
        const ed = tinymce.get(id);
        if (ed) {
            return ed.getContent({ format: 'text' });
        }
    }
    return plainTextFromHtml(el.value);
}

function collectFieldsFromLegacy(root) {
    const titleSel = root.dataset.titleSelector;
    const bodySel = root.dataset.bodySelector;
    const contentField = root.dataset.contentField || 'content';
    const useTinymce = root.dataset.bodyTinymce === '1';
    const tagsSel = root.dataset.tagsSelector;

    const fields = {};
    const titleEl = titleSel ? document.querySelector(titleSel) : null;
    if (titleEl) {
        fields.title = titleEl.value || '';
    }
    if (bodySel) {
        fields[contentField] = getBodyPlainText(bodySel, useTinymce);
    }
    if (tagsSel) {
        const tagsEl = document.querySelector(tagsSel);
        if (tagsEl && tagsEl.value) {
            fields.tags = tagsEl.value;
        }
    }
    return fields;
}

function collectFieldsFromComponent(root) {
    const titleName = root.dataset.titleName || 'title';
    const contentId = root.dataset.contentId || '';
    const contentField = root.dataset.contentField || 'content';
    const titleInput =
        root.closest('form')?.querySelector(`[name="${titleName}"]`) ||
        document.querySelector(`[name="${titleName}"]`);
    const fields = {};
    if (titleInput) {
        fields.title = titleInput.value || '';
    }
    if (contentId) {
        fields[contentField] = getBodyPlainText('#' + contentId, true);
    }
    return fields;
}

function renderLegacyHits(root, data) {
    const titlePreview = document.getElementById('forbidden-title-preview');
    const bodyBadge = document.getElementById('forbidden-body-badge');
    const tagsPreview = document.getElementById('forbidden-tags-preview');
    const titleSel = root.dataset.titleSelector;
    const titleEl = titleSel ? document.querySelector(titleSel) : null;
    const titleText = titleEl ? titleEl.value || '' : '';

    const titleHits = (data.hits || []).filter((h) => h.field === 'title');
    const bodyHits = (data.hits || []).filter((h) => h.field === 'content' || h.field === 'content_pending' || h.field === 'content_public');
    const tagHits = (data.hits || []).filter((h) => h.field === 'tags');
    const total = (data.hits || []).length;

    if (titlePreview) {
        if (titleHits.length > 0 && titleText) {
            titlePreview.innerHTML = highlightPlainText(titleText, titleHits);
            titlePreview.classList.remove('hidden');
        } else {
            titlePreview.innerHTML = '';
            titlePreview.classList.add('hidden');
        }
    }

    if (tagsPreview) {
        const tagsSel = root.dataset.tagsSelector;
        const tagsEl = tagsSel ? document.querySelector(tagsSel) : null;
        const tagsText = tagsEl ? tagsEl.value || '' : '';
        if (tagHits.length > 0 && tagsText) {
            tagsPreview.innerHTML = highlightPlainText(tagsText, tagHits);
            tagsPreview.classList.remove('hidden');
        } else {
            tagsPreview.innerHTML = '';
            tagsPreview.classList.add('hidden');
        }
    }

    if (bodyBadge) {
        const bodyCount = bodyHits.length;
        if (total > 0) {
            bodyBadge.textContent = bodyCount > 0 ? `违规 ${bodyCount} 处` : `违规 ${total} 处`;
            bodyBadge.classList.remove('hidden');
        } else {
            bodyBadge.textContent = '';
            bodyBadge.classList.add('hidden');
        }
    }
}

function renderComponentHits(root, data) {
    const titlePreview = root.querySelector('[data-forbidden-title-preview]');
    const badge = root.querySelector('[data-forbidden-badge]');
    const badgeCount = root.querySelector('[data-forbidden-count]');
    const titleName = root.dataset.titleName || 'title';
    const titleInput =
        root.closest('form')?.querySelector(`[name="${titleName}"]`) ||
        document.querySelector(`[name="${titleName}"]`);
    const titleText = titleInput ? titleInput.value || '' : '';
    const titleHits = (data.hits || []).filter((h) => h.field === 'title');
    const total = (data.hits || []).length;

    if (titlePreview && titleInput) {
        if (titleHits.length > 0 && titleText) {
            titlePreview.innerHTML = highlightPlainText(titleText, titleHits);
            titlePreview.classList.remove('hidden');
        } else {
            titlePreview.innerHTML = '';
            titlePreview.classList.add('hidden');
        }
    }
    if (badge && badgeCount) {
        if (total > 0) {
            badgeCount.textContent = String(total);
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
            badgeCount.textContent = '0';
        }
    }
}

async function postScan(root, collectFn, renderFn) {
    const url = root.dataset.scanUrl;
    const context = root.dataset.context || 'article';
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!url || !token) {
        return;
    }
    const fields = collectFn(root);
    if (Object.keys(fields).length === 0) {
        return;
    }
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ fields, context }),
        });
        if (!res.ok) {
            return;
        }
        const data = await res.json();
        renderFn(root, data);
    } catch (e) {
        // 静默失败，不打断输入
    }
}

function bindLegacy(root) {
    const run = debounce(() => postScan(root, collectFieldsFromLegacy, renderLegacyHits), 500);
    const titleSel = root.dataset.titleSelector;
    const bodySel = root.dataset.bodySelector;
    const tagsSel = root.dataset.tagsSelector;
    const useTinymce = root.dataset.bodyTinymce === '1';

    if (titleSel) {
        const el = document.querySelector(titleSel);
        if (el) {
            el.addEventListener('input', run);
        }
    }
    if (bodySel) {
        const el = document.querySelector(bodySel);
        if (el) {
            el.addEventListener('input', run);
        }
    }
    if (tagsSel) {
        const el = document.querySelector(tagsSel);
        if (el) {
            el.addEventListener('input', run);
        }
    }
    if (useTinymce && bodySel) {
        const id = (document.querySelector(bodySel) || {}).id;
        const hook = () => {
            if (typeof tinymce === 'undefined' || !id) {
                return;
            }
            const ed = tinymce.get(id);
            if (ed && !ed._forbiddenScanBound) {
                ed._forbiddenScanBound = true;
                ed.on('input change keyup Undo Redo', run);
            }
        };
        setTimeout(hook, 800);
        setTimeout(hook, 2000);
    }
    run();
}

function bindComponent(root) {
    const run = debounce(() => postScan(root, collectFieldsFromComponent, renderComponentHits), 500);
    const titleName = root.dataset.titleName || 'title';
    const contentId = root.dataset.contentId || '';
    const titleInput =
        root.closest('form')?.querySelector(`[name="${titleName}"]`) ||
        document.querySelector(`[name="${titleName}"]`);
    if (titleInput) {
        titleInput.addEventListener('input', run);
    }
    const textarea = contentId ? document.getElementById(contentId) : null;
    if (textarea) {
        textarea.addEventListener('input', run);
    }
    if (typeof tinymce !== 'undefined' && contentId) {
        const hook = () => {
            const ed = tinymce.get(contentId);
            if (ed && !ed._forbiddenScanBound) {
                ed._forbiddenScanBound = true;
                ed.on('input change keyup Undo Redo', run);
            }
        };
        setTimeout(hook, 800);
        setTimeout(hook, 2000);
    }
    run();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-forbidden-legacy-scan]').forEach(bindLegacy);
    document.querySelectorAll('[data-forbidden-scan]').forEach(bindComponent);
});
