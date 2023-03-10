<?php

use App\Http\Controllers\AutomatedProcessController;
use App\Http\Controllers\ClosedAlertsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrchestratorConnectionController;
use App\Http\Controllers\PendingAlertsController;
use App\Http\Controllers\PropertyKeyController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UiPathController;
use App\Http\Resources\OrchestratorConnectionResource;
use App\Models\OrchestratorConnection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('locale/{locale}', function ($locale) {
    //session()->put('locale', $locale);
    Cookie::queue(Cookie::forever('locale', $locale));

    return redirect()->back();
})->name('locale');

// orchestrator connections tenants webhook handler
Route::post(
    'configuration/orchestrator-connections/tenants/webhook-handler/{uuid}',
    [OrchestratorConnectionController::class, 'tenantsWebhookHandler']
)->name('configuration.orchestrator-connections.tenants.webhook-handler');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::name('pending-alerts.')->prefix('pending-alerts')->group(function () {
        Route::get('/', [PendingAlertsController::class, 'index'])->name('index');
        Route::get('/{alert}/edit', [PendingAlertsController::class, 'edit'])->name('edit');
        Route::post('/{alert}/read', [PendingAlertsController::class, 'read'])->name('read');
        Route::post('/{alert}/lock', [PendingAlertsController::class, 'lock'])->name('lock');
        Route::post('/{alert}/unlock', [PendingAlertsController::class, 'unlock'])->name('unlock');
        Route::post('/bulk-read', [PendingAlertsController::class, 'bulkRead'])->name('bulk-read');
        Route::post('/bulk-lock', [PendingAlertsController::class, 'bulkLock'])->name('bulk-lock');
        Route::post('/bulk-unlock', [PendingAlertsController::class, 'bulkUnlock'])->name('bulk-unlock');
    });
    
    Route::name('closed-alerts.')->prefix('closed-alerts')->group(function () {
        Route::get('/', [ClosedAlertsController::class, 'index'])->name('index');
    });

    Route::get('/tags', [TagController::class, 'getTags'])
        ->name('tags');

    Route::get(
        '/uipath/folders/{orchestrator_connection}/{orchestrator_connection_tenant}/{with_releases}/{with_machines}/{with_queue_definitions}',
        [UiPathController::class, 'folders'])
        ->name('uipath.folders');

    Route::name('configuration.')->prefix('configuration')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Configuration/Index');
        })->name('index');
        Route::post(
            'generate/code',
            function () {
                return response()->json([
                    'code' => getAcronym(request('name'))
                ]);
            }
        )->name('generate-code');

        // orchestrator connections
        Route::resource('orchestrator-connections', OrchestratorConnectionController::class);
        Route::name('orchestrator-connections.')->prefix('orchestrator-connections')->group(function () {
            Route::post('/bulk-destroy', [OrchestratorConnectionController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('/verify/{orchestrator_connection?}', [OrchestratorConnectionController::class, 'verify'])->name('verify');
        });

        // automated processes
        Route::resource('automated-processes', AutomatedProcessController::class);
        Route::name('automated-processes.')->prefix('automated-processes')->group(function () {
            Route::post('/bulk-destroy', [AutomatedProcessController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        // property keys
        Route::resource('property-keys', PropertyKeyController::class);
        Route::post(
            'property-keys/bulk-destroy',
            [PropertyKeyController::class, 'bulkDestroy']
        )->name('property-keys.bulk-destroy');
    });
});
