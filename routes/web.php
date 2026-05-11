redirect()->route('dashboard.index'));

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Import
Route::get ('/import',                          [ImportController::class, 'index']      )->name('import.index');
Route::post('/import',                          [ImportController::class, 'store']      )->name('import.store');
Route::get ('/import/logs',                     [ImportController::class, 'logs']       )->name('import.logs');
Route::get ('/import/logs/{log}/failed-rows',   [ImportController::class, 'failedRows'] )->name('import.failed-rows');

// Campaigns
Route::get('/campaigns',            [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'] )->name('campaigns.show');

// Ad Sets
Route::get('/ad-sets',          [AdSetController::class, 'index'])->name('adsets.index');
Route::get('/ad-sets/{adSet}',  [AdSetController::class, 'show'] )->name('adsets.show');

// Ads
Route::get('/ads',       [AdController::class, 'index'])->name('ads.index');
Route::get('/ads/{ad}',  [AdController::class, 'show'] )->name('ads.show');

// Reports
Route::get('/reports',         [ReportController::class, 'index'] )->name('reports.index');
Route::get('/reports/export',  [ReportController::class, 'export'])->name('reports.export');