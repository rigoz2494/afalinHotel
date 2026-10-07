<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', LandingPageController::class)->name('home');

// A clean, indexable URL per room, for search engines — not a separate page;
// it renders the exact same landing experience already opened to that room.
// See LandingPageController and sitemap.xml, which links every active one.
Route::get('/rooms/{room:slug}', LandingPageController::class)->name('rooms.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Every page is server-rendered through Inertia, so there is no client-side
// router to catch an unmatched URL. This fallback must stay the last route in
// the file: it only runs when nothing else, including routes/api.php and the
// Filament panel, has matched the request.
Route::fallback(function (Request $request) {
    if ($request->is('api/*') || $request->expectsJson()) {
        return response()->json(['message' => 'Not found.'], 404);
    }

    // ->toResponse() sets the `X-Inertia` header, which is what tells the
    // Inertia client to render this as a normal page instead of an error
    // overlay, regardless of the 404 status code below.
    return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
})->name('fallback');
