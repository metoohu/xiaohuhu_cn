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
                xhr.upload.onprogress = function(e) {
                    progress(e.loaded / e.total * 100);
                };
                xhr.onload = function() {
                    if (xhr.status === 403 || xhr.status === 419) {
                        reject('CSRF 验证失败，请刷新页面重试');
                        return;
                    }
                    if (xhr.status !== 200) {
                        reject('上传失败: ' + xhr.status);
                        return;
                    }
                    var json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        reject('响应解析失败');
                        return;
                    }
                    if (!json || typeof json.location !== 'string') {
                        reject('无效的响应格式');
                        return;
                    }
                    resolve(json.location);
                };
                xhr.onerror = function() { reject('网络错误'); };
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
