<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Available languages
     */
    protected $languages = ['en', 'hi', 'mr'];

    /**
     * Switch the application language
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale', 'en');

        // Validate the locale
        if (in_array($locale, $this->languages)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        // Redirect back or to home
        return redirect()->back()->with('language_changed', true);
    }

    /**
     * Check if language has been selected
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        return response()->json([
            'selected' => Session::has('locale'),
            'locale' => Session::get('locale', 'en')
        ]);
    }
    /**
     * Switch the application language via parameter
     *
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchParam($locale)
    {
        // Validate the locale
        if (in_array($locale, $this->languages)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        // Redirect back or to home
        return redirect()->back()->with('language_changed', true);
    }
}
