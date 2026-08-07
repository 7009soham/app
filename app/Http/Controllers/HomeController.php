<?php

namespace App\Http\Controllers;

use App\Models\QuickLink;
use App\Models\SiteSetting;
use App\Models\Slider;
use App\Models\TaxType;

class HomeController extends Controller
{
    protected function getCommonData()
    {
        $settings = [
            'site_name' => SiteSetting::get('site_name', 'Gram Panchayat'),
            'site_tagline' => SiteSetting::get('site_tagline', 'Serving Our Community'),
            'site_description' => SiteSetting::get('site_description', ''),
            'contact_email' => SiteSetting::get('contact_email', ''),
            'contact_phone' => SiteSetting::get('contact_phone', ''),
            'address' => SiteSetting::get('address', ''),
            'facebook_url' => SiteSetting::get('facebook_url', ''),
            'twitter_url' => SiteSetting::get('twitter_url', ''),
            'instagram_url' => SiteSetting::get('instagram_url', ''),
            'youtube_url' => SiteSetting::get('youtube_url', ''),
        ];

        $quickLinks = QuickLink::active()->footer()->ordered()->get();

        return compact('settings', 'quickLinks');
    }

    public function index()
    {
        $data = $this->getHomepageData();

        return view('home', $data);
    }

    public function designExplorationIndex()
    {
        $data = $this->getHomepageData();
        return view('design-exploration.home.index', $data);
    }

    public function designExplorationVariationOne()
    {
        $data = $this->getHomepageData();
        return view('design-exploration.home.variation-1', $data);
    }

    public function designExplorationVariationTwo()
    {
        $data = $this->getHomepageData();
        return view('design-exploration.home.variation-2', $data);
    }

    public function designExplorationVariationThree()
    {
        $data = $this->getHomepageData();
        return view('design-exploration.home.variation-3', $data);
    }

    public function about()
    {
        $data = $this->getCommonData();
        return view('pages.about', $data);
    }

    public function contact()
    {
        $data = $this->getCommonData();
        return view('pages.contact', $data);
    }

    public function privacyPolicy()
    {
        $data = $this->getCommonData();
        return view('pages.privacy-policy', $data);
    }

    public function termsConditions()
    {
        $data = $this->getCommonData();
        return view('pages.terms-conditions', $data);
    }

    public function refundPolicy()
    {
        $data = $this->getCommonData();
        return view('pages.refund-policy', $data);
    }

    public function customPage(string $slug)
    {
        $page = \App\Models\CustomPage::published()->where('slug', $slug)->firstOrFail();

        $data = $this->getCommonData();
        $data['page'] = $page;

        return view('pages.custom', $data);
    }

    public function disclaimer()
    {
        $data = $this->getCommonData();
        return view('pages.disclaimer', $data);
    }

    public function accessibilityStatement()
    {
        $data = $this->getCommonData();
        return view('pages.accessibility-statement', $data);
    }

    public function copyrightPolicy()
    {
        $data = $this->getCommonData();
        return view('pages.copyright-policy', $data);
    }

    public function hyperlinkingPolicy()
    {
        $data = $this->getCommonData();
        return view('pages.hyperlinking-policy', $data);
    }

    public function digitalServices()
    {
        $data = $this->getCommonData();
        return view('pages.digital-services', $data);
    }

    protected function getHomepageData(): array
    {
        $data = $this->getCommonData();
        $data['sliders'] = Slider::active()->ordered()->get();
        $data['taxTypes'] = TaxType::active()->get();

        return $data;
    }
}
