<?php

namespace Oxygencms\OxyNova\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Oxygencms\OxyNova\Models\Page;

class LocaleController extends Controller
{
    /**
     * @param         $locale
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale($locale, Request $request)
    {
        $locales = array_keys(config('oxygen.locales'));

        Validator::make(
            ['locale' => $locale],
            ['locale' => "required|string|in:". implode(',', $locales)]
        )->validate();

        $current_locale = session()->get('app_locale') ?: app()->getLocale();

        session()->put('app_locale', $locale);
        $previousUrl = null;

        if (session()->has('_previous.url')) {
            $prefix = $request->getSchemeAndHttpHost() . '/';
            $slug = str_replace($prefix, '',  session()->get('_previous.url'));
            $page = Page::bySlug($slug, $current_locale)->first();
            $previousUrl = session()->get('_previous.url');
        }

        if (empty($page) && $previousUrl !== null && !str_contains($previousUrl, 'set-locale')) {
            // here we need to check if he is coming from a translated page not managed by oxynova
            $previousRequest = Request::create($previousUrl);
            $refererNamedRouteData = Route::getRoutes()->match($previousRequest);
            $refererNamedRoute = $refererNamedRouteData->getName();
            $routeRefererLocale = substr($refererNamedRoute, 0, 3);
            $routeToRedirect = $refererNamedRoute;

            switch ($routeRefererLocale) {
                case 'en.':
                    $routeRefererLocale = 'en';
                    $refererNamedRoute = str_replace('en.', '', $refererNamedRoute);
                    break;
                case 'es.':
                    $routeRefererLocale = 'es';
                    $refererNamedRoute = str_replace('es.', '', $refererNamedRoute);
                    break;
                default:
                    $routeRefererLocale = 'fr';
            }

            if ($routeRefererLocale !== $locale) {
                $routeToRedirect = __('routes.' . $refererNamedRoute, [], $locale);

                if (false !== strpos($routeToRedirect, 'routes.')) {
                    $routeToRedirect = $refererNamedRoute;
                }
            }

            if ($routeToRedirect === null) {
                $routeToRedirect = 'home';
            }

            session()->forget('_previous.url');
            session()->save();
            return redirect()->route($routeToRedirect, $refererNamedRouteData->parameters(), 301);
        }

        if (empty($page)) {
            return redirect()->route('home');
        }

        return redirect()->to($page->getTranslation('slug', $locale));
    }
}
