@extends('admin.layouts.app')

@section('title', 'Social Links')

@section('content')
<div class="page-header">
    <h1>Social Links</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    @include('admin.settings.partials.nav', ['active' => 'social'])

    <div class="settings-content">
        <form action="{{ route('admin.settings.social.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

                <div id="social">
                    <div class="card">
                        <div class="card-header">
                            <h3>Social Links</h3>
                        </div>
                        <div class="card-body">
                            @php $socialSettings = $settings->get('social', collect()); @endphp
                            
                            <div class="form-group">
                                <label for="facebook_url"><i class="fab fa-facebook"></i> Facebook URL</label>
                                <input type="url" id="facebook_url" name="facebook_url" class="form-control" 
                                       value="{{ $socialSettings->firstWhere('key', 'facebook_url')?->value ?? '' }}" 
                                       placeholder="https://facebook.com/yourpage">
                            </div>
                            
                            <div class="form-group">
                                <label for="twitter_url"><i class="fab fa-twitter"></i> Twitter URL</label>
                                <input type="url" id="twitter_url" name="twitter_url" class="form-control" 
                                       value="{{ $socialSettings->firstWhere('key', 'twitter_url')?->value ?? '' }}" 
                                       placeholder="https://twitter.com/yourhandle">
                            </div>
                            
                            <div class="form-group">
                                <label for="instagram_url"><i class="fab fa-instagram"></i> Instagram URL</label>
                                <input type="url" id="instagram_url" name="instagram_url" class="form-control" 
                                       value="{{ $socialSettings->firstWhere('key', 'instagram_url')?->value ?? '' }}" 
                                       placeholder="https://instagram.com/yourhandle">
                            </div>
                            
                            <div class="form-group">
                                <label for="youtube_url"><i class="fab fa-youtube"></i> YouTube URL</label>
                                <input type="url" id="youtube_url" name="youtube_url" class="form-control" 
                                       value="{{ $socialSettings->firstWhere('key', 'youtube_url')?->value ?? '' }}" 
                                       placeholder="https://youtube.com/yourchannel">
                            </div>
                        </div>
                    </div>
                </div>

            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Social Links
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@include('admin.settings.partials.styles')
@endpush
