@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="page-header">
    <h1>General Settings</h1>
    <p>Configure your website settings</p>
</div>

<div class="settings-layout">
    @include('admin.settings.partials.nav', ['active' => 'general'])

    <div class="settings-content">
        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

                <div id="general">
                    <div class="card">
                        <div class="card-header">
                            <h3>General Settings</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $generalSettings = $settings->get('general', collect());
                                $logoPath = $generalSettings->firstWhere('key', 'site_logo')?->value;
                            @endphp

                            <div class="form-group">
                                <label for="site_logo">Logo / Emblem</label>
                                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                                    <div style="width:96px;height:96px;border:1px dashed #cbd5e1;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f8fafc;flex-shrink:0;">
                                        @if($logoPath)
                                            <img src="{{ asset('storage/' . $logoPath) }}" alt="Current logo"
                                                 style="max-width:88px;max-height:88px;object-fit:contain;">
                                        @else
                                            <i class="fas fa-landmark" style="font-size:34px;color:#94a3b8;"></i>
                                        @endif
                                    </div>
                                    <div style="flex:1;min-width:240px;">
                                        <input type="file" id="site_logo" name="site_logo" class="form-control"
                                               accept="image/png,image/jpeg,image/webp">
                                        <small style="color:#64748b;display:block;margin-top:6px;">
                                            PNG, JPG or WebP up to 1&nbsp;MB, max 1200&times;400px. A transparent PNG works
                                            best. Leave empty to keep the current image.
                                            @unless($logoPath)
                                                Until one is uploaded, the header shows a generic building icon.
                                            @endunless
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="site_name">Site Name</label>
                                <input type="text" id="site_name" name="site_name" class="form-control"
                                       value="{{ $generalSettings->firstWhere('key', 'site_name')?->value ?? '' }}"
                                       placeholder="e.g. Neral Gram Panchayat">
                                <small style="color:#64748b;">Shown in the header, footer, page titles and emails.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_tagline">Tagline</label>
                                <input type="text" id="site_tagline" name="site_tagline" class="form-control" 
                                       value="{{ $generalSettings->firstWhere('key', 'site_tagline')?->value ?? '' }}">
                            </div>
                            
                            <div class="form-group">
                                <label for="site_description">Site Description</label>
                                <textarea id="site_description" name="site_description" class="form-control" rows="3">{{ $generalSettings->firstWhere('key', 'site_description')?->value ?? '' }}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_email">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                       value="{{ $generalSettings->firstWhere('key', 'contact_email')?->value ?? '' }}">
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_phone">Contact Phone</label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control" 
                                       value="{{ $generalSettings->firstWhere('key', 'contact_phone')?->value ?? '' }}">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="2">{{ $generalSettings->firstWhere('key', 'address')?->value ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="d-flex gap-3 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@include('admin.settings.partials.styles')
@endpush
