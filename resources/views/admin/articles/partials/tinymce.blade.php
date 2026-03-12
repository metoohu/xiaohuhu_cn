@php
    $useCloud = config('editor.tinymce_source') === 'cloud' && config('editor.tinymce_api_key');
@endphp
@if ($useCloud)
<script src="https://cdn.tiny.cloud/1/{{ config('editor.tinymce_api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@else
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    var formId = '{{ $formId ?? "article-create-form" }}';
    var form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('article-submit-btn');
            if (typeof tinymce !== 'undefined') {
                var editor = tinymce.get('article-content');
                if (editor) {
                    editor.save();
                } else {
                    tinymce.triggerSave();
                }
            }
            if (btn) {
                btn.disabled = true;
                btn.textContent = formId.indexOf('create') >= 0 ? '提交中...' : '更新中...';
            }
            requestAnimationFrame(function() {
                form.submit();
            });
        });
    }

    var initConfig = {
        selector: '#article-content',
        height: 400,
        menubar: false,
        plugins: 'lists link image code table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        content_style: 'body { font-family: sans-serif; font-size: 14px; }',
        images_upload_handler: function(blobInfo, progress) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '{{ route("admin.articles.upload-image") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) progress(e.loaded / e.total * 100);
                };
                xhr.onload = function() {
                    var json;
                    try {
                        json = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                    } catch (e) {
                        reject('服务器响应异常，请重试');
                        return;
                    }
                    if (xhr.status === 200 && json && typeof json.location === 'string') {
                        resolve(json.location);
                        return;
                    }
                    var errMsg = '图片上传失败';
                    if (json) {
                        if (json.errors && json.errors.file && json.errors.file[0]) {
                            errMsg = json.errors.file[0];
                        } else if (json.message) {
                            errMsg = json.message;
                        }
                    }
                    if (xhr.status === 403 || xhr.status === 419) errMsg = 'CSRF 验证失败，请刷新页面重试';
                    else if (xhr.status === 422) errMsg = errMsg || '图片格式不正确或文件过大（最大 2MB），请使用 jpg、png、gif 等格式';
                    else if (xhr.status === 413) errMsg = '文件过大，请选择小于 2MB 的图片';
                    else if (xhr.status >= 500) errMsg = '服务器错误，请稍后重试';
                    try {
                        if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.notificationManager) {
                            tinymce.activeEditor.notificationManager.open({ text: errMsg, type: 'error' });
                        }
                    } catch (n) {}
                    reject(errMsg);
                };
                xhr.onerror = function() {
                    var errMsg = '网络错误，请检查网络后重试';
                    try {
                        if (typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.notificationManager) {
                            tinymce.activeEditor.notificationManager.open({ text: errMsg, type: 'error' });
                        }
                    } catch (n) {}
                    reject(errMsg);
                };
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        }
    };
    @if (!$useCloud)
    initConfig.license_key = 'gpl';
    initConfig.base_url = 'https://cdn.jsdelivr.net/npm/tinymce@6/';
    initConfig.suffix = '.min';
    @endif
    tinymce.init(initConfig);
});
</script>
