<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api/legacy.php';

Route::prefix('v2')->group(function () {
    require __DIR__.'/api/v2/public.php';

    Route::middleware(['auth:sanctum'])->group(function () {
        require __DIR__.'/api/v2/session.php';
        require __DIR__.'/api/v2/admin.php';
        require __DIR__.'/api/v2/modules.php';
        require __DIR__.'/api/v2/datatables.php';
        require __DIR__.'/api/v2/lookups.php';
        require __DIR__.'/api/v2/reports.php';
    });

    require __DIR__.'/api/v2/portal.php';
});

foreach (Route::getRoutes()->getRoutes() as $route) {
    if (str_starts_with($route->uri(), 'api/v2/') && $route->getName() !== null) {
        $action = $route->getAction();
        $action['as'] = 'api.v2.'.$route->getName();
        $route->setAction($action);
    }
}

Route::getRoutes()->refreshNameLookups();
