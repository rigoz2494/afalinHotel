<?php

namespace App\Http\Middleware;

use App\Models\HotelSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the admin panel's own PHP locale (every `__()` call, and any
 * locale-aware date formatting like the Bookings chart's month labels) to
 * match the Global Language Mode when it's locked to one language —
 * instead of always following the server's static APP_LOCALE regardless
 * of what the Master Switch says. Multilingual mode leaves the app's own
 * configured locale alone, since there's no single "right" admin language
 * to force in that case.
 */
class SetAdminPanelLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($locale = HotelSetting::restrictedLocale()) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
