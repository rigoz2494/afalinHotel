<?php

namespace App\Http\Controllers;

use App\Services\RoomService;
use Illuminate\Http\Response;

/**
 * A plain XML sitemap for Google/Yandex, built fresh on every request from
 * the current active rooms rather than a static file, so a newly added or
 * deactivated room is reflected immediately with no rebuild step.
 */
class SitemapController extends Controller
{
    public function __invoke(RoomService $rooms): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
        ])->merge(
            $rooms->listActive()->map(fn ($room): array => [
                'loc' => route('rooms.show', $room),
                'priority' => '0.8',
            ])
        );

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
