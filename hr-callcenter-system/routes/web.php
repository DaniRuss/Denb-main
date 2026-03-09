<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ComplaintController;
use App\Http\Controllers\Public\TipController;
use App\Http\Controllers\Public\AnnouncementController;
use App\Http\Controllers\Public\FaqController;

/*
|--------------------------------------------------------------------------
| Public Portal Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Announcements
Route::prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::get('/{id}', [AnnouncementController::class, 'show'])->name('show');
});

// FAQ
Route::get('/faq', [FaqController::class, 'index'])->name('faq');

// Complaints - FULL ROUTES
Route::prefix('complaint')->name('complaint.')->group(function () {
    Route::get('/submit', [ComplaintController::class, 'create'])->name('create');
    Route::post('/submit', [ComplaintController::class, 'store'])->name('store');
    Route::get('/track', [ComplaintController::class, 'track'])->name('track');
    Route::post('/track', [ComplaintController::class, 'checkStatus'])->name('check');
    Route::get('/status/{ticket}', [ComplaintController::class, 'showStatus'])->name('status');
});

// Tips (Anonymous Reporting) - FULL ROUTES
Route::prefix('tip')->name('tip.')->group(function () {
    Route::get('/submit', [TipController::class, 'create'])->name('create');
    Route::post('/submit', [TipController::class, 'store'])->name('store');
    Route::get('/track/{token}', [TipController::class, 'trackAnonymous'])->name('track.anonymous');
});

// Contact
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

/*
|--------------------------------------------------------------------------
| Filament Admin Routes
|--------------------------------------------------------------------------
*/
// Filament handles these automatically at /admin
// No need to define them here

/*
|--------------------------------------------------------------------------
| Health Check & Debug Routes
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
        'version' => '1.0.0'
    ]);
});

// Only in local environment
if (app()->environment('local')) {
    Route::get('/test-email', function () {
        try {
            Mail::raw('This is a test email from the HR system', function ($message) {
                $message->to('test@example.com')
                    ->subject('Test Email');
            });
            return 'Email sent successfully';
        } catch (\Exception $e) {
            return 'Email failed: ' . $e->getMessage();
        }
    });
}
