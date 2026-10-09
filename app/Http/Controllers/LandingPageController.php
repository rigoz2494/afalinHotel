<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdditionalServiceResource;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\HotelSettingsResource;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\AdditionalServiceService;
use App\Services\CurrencyService;
use App\Services\FaqService;
use App\Services\HotelSettingService;
use App\Services\PricingService;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LandingPageController extends Controller
{
    /**
     * Renders the same single-page landing experience for both the root URL
     * and a room's own clean URL (`/rooms/{slug}`), so each active room gets
     * a distinct, indexable, crawlable address — sharing all of the page's
     * data and markup rather than duplicating it on a separate view.
     */
    public function __invoke(Request $request, HotelSettingService $settings, RoomService $rooms, PricingService $pricing, CurrencyService $currencies, FaqService $faqs, AdditionalServiceService $additionalServices, ?Room $room = null): Response
    {
        if ($room !== null && ! $room->is_active) {
            throw new NotFoundHttpException;
        }

        $table = $pricing->table();

        return Inertia::render('Landing', [
            'hotel' => HotelSettingsResource::make($settings->all())->resolve(),
            'currencies' => CurrencyResource::collection($currencies->selectable())->resolve(),
            'rooms' => RoomResource::collection($rooms->listActive()),
            'faqs' => FaqResource::collection($faqs->listActive()),
            'additionalServices' => AdditionalServiceResource::collection($additionalServices->listActive()),
            'pricing' => [
                'columns' => $table['columns'],
                // Rows are already plain, computed arrays (see PricingService),
                // not Eloquent models, so there is no resource to wrap them with.
                'rows' => ['data' => $table['rows']],
            ],
            // Tells the page which room to open on load and build its
            // per-room <title>/meta description/og:image around, for SEO.
            'focusRoomSlug' => $room?->slug,
            'canonicalUrl' => $request->url(),
        ]);
    }
}
