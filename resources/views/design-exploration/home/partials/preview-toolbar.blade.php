<section class="preview-toolbar" aria-label="Design exploration toolbar">
    <div class="container toolbar-inner">
        <div class="preview-note">
            <i class="fas fa-flask"></i>
            <span>Preview only. Design exploration pages are isolated from production homepage.</span>
        </div>
        <div class="preview-actions">
            <a href="{{ route('home') }}" class="preview-chip">
                <i class="fas fa-home"></i>
                Live Homepage
            </a>
            <a href="{{ route('design-exploration.home.index') }}" class="preview-chip {{ ($active ?? '') === 'index' ? 'active' : '' }}">Overview</a>
            <a href="{{ route('design-exploration.home.variation-1') }}" class="preview-chip {{ ($active ?? '') === 'v1' ? 'active' : '' }}">Variation 1</a>
            <a href="{{ route('design-exploration.home.variation-2') }}" class="preview-chip {{ ($active ?? '') === 'v2' ? 'active' : '' }}">Variation 2</a>
            <a href="{{ route('design-exploration.home.variation-3') }}" class="preview-chip {{ ($active ?? '') === 'v3' ? 'active' : '' }}">Variation 3</a>
        </div>
    </div>
</section>
