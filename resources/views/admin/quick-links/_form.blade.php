{{-- Shared by create and edit. $link is null when creating. --}}
@php
    $link = $link ?? null;
    $page = $link?->customPage;
    $mode = old('link_type', $link && $link->ownsItsPage() ? 'page' : 'url');
@endphp

@if($errors->any())
    <div class="alert alert-danger" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;margin-bottom:18px;padding:12px;border-radius:8px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="title">Link Title <span class="text-danger">*</span></label>
    <input type="text" id="title" name="title" class="form-control" required
           value="{{ old('title', $link->title ?? '') }}" placeholder="e.g., Government Schemes">
</div>

<div class="form-group">
    <label>What does this link open?</label>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:6px;">
        <label class="link-mode-option" style="flex:1;min-width:230px;cursor:pointer;border:2px solid {{ $mode === 'url' ? '#1a365d' : '#e2e8f0' }};border-radius:10px;padding:14px;display:flex;gap:10px;align-items:flex-start;">
            <input type="radio" name="link_type" value="url" {{ $mode === 'url' ? 'checked' : '' }} style="margin-top:3px;">
            <span>
                <strong style="display:block;">An existing address</strong>
                <small style="color:#64748b;">A page that already exists, or another website.</small>
            </span>
        </label>
        <label class="link-mode-option" style="flex:1;min-width:230px;cursor:pointer;border:2px solid {{ $mode === 'page' ? '#1a365d' : '#e2e8f0' }};border-radius:10px;padding:14px;display:flex;gap:10px;align-items:flex-start;">
            <input type="radio" name="link_type" value="page" {{ $mode === 'page' ? 'checked' : '' }} style="margin-top:3px;">
            <span>
                <strong style="display:block;">A page I write here</strong>
                <small style="color:#64748b;">Type the content below. The address is created for you.</small>
            </span>
        </label>
    </div>
</div>

<div class="form-group" id="url-field" style="display: {{ $mode === 'url' ? 'block' : 'none' }};">
    <label for="url">URL <span class="text-danger">*</span></label>
    <input type="text" id="url" name="url" class="form-control"
           value="{{ old('url', $link && !$link->ownsItsPage() ? $link->url : '') }}"
           placeholder="e.g., /digital-services or https://example.com">
    <small class="text-muted">Check the address works before saving. A wrong path shows citizens a 404.</small>
</div>

<div class="form-group" id="page-field" style="display: {{ $mode === 'page' ? 'block' : 'none' }};">
    <label for="page-editor">Page Content <span class="text-danger">*</span></label>
    @if($page)
        <p style="font-size:12px;color:#64748b;margin:0 0 8px;">
            Published at <code>/page/{{ $page->slug }}</code> ·
            <a href="{{ $page->url }}" target="_blank" rel="noopener">view</a>
        </p>
    @endif
    <div id="page-editor" class="quick-link-editor">{!! old('page_content', $page->content ?? '') !!}</div>
    <textarea name="page_content" id="page-content-source" class="quick-link-editor-source" hidden>{{ old('page_content', $page->content ?? '') }}</textarea>
    <small class="text-muted">
        Formatting and links only. Scripts and embedded frames are removed when saved.
    </small>
</div>

<div class="form-group">
    <label for="icon">Icon (Font Awesome class)</label>
    <input type="text" id="icon" name="icon" class="form-control"
           value="{{ old('icon', $link->icon ?? '') }}" placeholder="e.g., fa-file-alt">
    <small class="text-muted">See <a href="https://fontawesome.com/icons" target="_blank" rel="noopener">Font Awesome Icons</a></small>
</div>

<div class="form-group">
    <label for="location">Location <span class="text-danger">*</span></label>
    <select id="location" name="location" class="form-control" required>
        @php $loc = old('location', $link->location ?? 'footer'); @endphp
        <option value="footer" {{ $loc === 'footer' ? 'selected' : '' }}>Footer</option>
        <option value="header" {{ $loc === 'header' ? 'selected' : '' }}>Header</option>
    </select>
</div>

<div class="form-group">
    <label for="order">Display Order</label>
    <input type="number" id="order" name="order" class="form-control" min="0"
           value="{{ old('order', $link->order ?? 0) }}">
</div>

<div class="form-group">
    <label class="form-check">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $link->is_active ?? true) ? 'checked' : '' }}>
        <span>Active</span>
    </label>
</div>

<div class="form-group">
    <label class="form-check">
        <input type="checkbox" name="open_new_tab" value="1" {{ old('open_new_tab', $link->open_new_tab ?? false) ? 'checked' : '' }}>
        <span>Open in new tab</span>
    </label>
</div>

<div class="d-flex gap-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ $link ? 'Update Link' : 'Save Link' }}
    </button>
    <a href="{{ route('admin.quick-links.index') }}" class="btn btn-outline">Cancel</a>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<style>
    .quick-link-editor { background:#fff; border-radius:8px; }
    .quick-link-editor .ql-editor { min-height:260px; font-size:15px; line-height:1.7; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    (function () {
        var form = document.getElementById('quick-link-form');
        var editorEl = document.getElementById('page-editor');
        var source = document.getElementById('page-content-source');
        var urlField = document.getElementById('url-field');
        var pageField = document.getElementById('page-field');
        var urlInput = document.getElementById('url');
        var radios = document.querySelectorAll('input[name="link_type"]');

        if (!form) {
            return;
        }

        function applyMode(mode) {
            urlField.style.display = mode === 'url' ? 'block' : 'none';
            pageField.style.display = mode === 'page' ? 'block' : 'none';

            // Only the visible field is required, so the browser never blocks
            // submission on a hidden input.
            urlInput.required = mode === 'url';

            document.querySelectorAll('.link-mode-option').forEach(function (label) {
                var radio = label.querySelector('input[type=radio]');
                label.style.borderColor = radio && radio.value === mode ? '#1a365d' : '#e2e8f0';
            });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () { applyMode(this.value); });
        });

        applyMode(document.querySelector('input[name="link_type"]:checked').value);

        if (!editorEl || !source) {
            return;
        }

        // Fall back to a plain textarea if the CDN is unreachable, rather than
        // leaving no way to enter content.
        if (typeof Quill === 'undefined') {
            source.hidden = false;
            source.rows = 14;
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
            source.value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
        });
    })();
</script>
@endpush
