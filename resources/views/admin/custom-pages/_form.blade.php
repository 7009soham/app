{{-- Shared create/edit fields. $page is null when creating. --}}
@php $page = $page ?? null; @endphp

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger" style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; margin-bottom: 18px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="title">Page Title *</label>
                <input type="text" id="title" name="title" class="form-control" required
                       value="{{ old('title', $page->title ?? '') }}"
                       placeholder="e.g. Government Schemes">
            </div>
            <div class="form-group">
                <label for="order">Display Order</label>
                <input type="number" id="order" name="order" class="form-control" min="0" max="9999"
                       value="{{ old('order', $page->order ?? 0) }}">
            </div>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="slug">URL Slug</label>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="color: #64748b; font-size: 13px; white-space: nowrap;">{{ url('/page') }}/</span>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="{{ old('slug', $page->slug ?? '') }}"
                           placeholder="leave blank to generate from the title">
                </div>
                <small style="color: #64748b;">
                    Changing this on a published page breaks any existing link to it.
                </small>
            </div>
            <div class="form-group">
                <label for="icon">Icon class</label>
                <input type="text" id="icon" name="icon" class="form-control"
                       value="{{ old('icon', $page->icon ?? '') }}"
                       placeholder="fas fa-file-alt">
                <small style="color: #64748b;">Any Font Awesome class.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="summary">Short Summary</label>
            <input type="text" id="summary" name="summary" class="form-control" maxlength="500"
                   value="{{ old('summary', $page->summary ?? '') }}"
                   placeholder="One line shown under the page heading">
        </div>

        <div class="form-group">
            <label for="content-editor">Page Content *</label>
            <div id="content-editor" class="page-content-editor">{!! old('content', $page->content ?? '') !!}</div>
            <textarea name="content" id="content-source" class="page-content-source" hidden>{{ old('content', $page->content ?? '') }}</textarea>
            <small style="color: #64748b;">
                Formatting and links only. Scripts, forms and embedded frames are removed when the page is saved.
            </small>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_published" value="1"
                    @if(old('is_published', $page->is_published ?? false)) checked @endif>
                <span style="font-weight: 600;">Published</span>
            </label>
            <small style="color: #64748b;">Unpublished pages return 404 to citizens.</small>
        </div>
    </div>
</div>

<div class="d-flex gap-3 mb-4" style="margin-top: 16px;">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ $page ? 'Update Page' : 'Create Page' }}
    </button>
    <a href="{{ route('admin.custom-pages.index') }}" class="btn btn-outline">Cancel</a>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<style>
    .page-content-editor {
        background: #fff;
        border-radius: 8px;
    }
    .page-content-editor .ql-editor {
        min-height: 320px;
        font-size: 15px;
        line-height: 1.7;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    (function () {
        var form = document.getElementById('custom-page-form');
        var editorEl = document.getElementById('content-editor');
        var source = document.getElementById('content-source');

        if (!form || !editorEl || !source) {
            return;
        }

        // If the CDN is unreachable, fall back to a plain textarea rather than
        // leaving the admin with no way to edit content at all.
        if (typeof Quill === 'undefined') {
            source.hidden = false;
            source.rows = 16;
            source.classList.add('form-control');
            editorEl.style.display = 'none';
            return;
        }

        var quill = new Quill(editorEl, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, 4, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'blockquote'],
                    ['clean']
                ]
            }
        });

        form.addEventListener('submit', function () {
            var html = quill.root.innerHTML;
            source.value = quill.getText().trim() === '' ? '' : html;
        });
    })();
</script>
@endpush
