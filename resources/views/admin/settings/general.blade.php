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
                            @php $generalSettings = $settings->get('general', collect()); @endphp
                            
                            <div class="form-group">
                                <label for="site_name">Site Name</label>
                                <input type="text" id="site_name" name="site_name" class="form-control" 
                                       value="{{ $generalSettings->firstWhere('key', 'site_name')?->value ?? '' }}">
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
