<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
 
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Share Links
Route::get('/share/{token}', [ShareController::class, 'show'])->name('share.show');
Route::post('/share/{token}/verify', [ShareController::class, 'verifyPassword'])->name('share.verify');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/quick', [SearchController::class, 'quick'])->name('search.quick');

    // Teams
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/{team}', [TeamController::class, 'show'])->name('show');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('/{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');
        Route::post('/{team}/switch', [TeamController::class, 'switch'])->name('switch');
        Route::get('/{team}/members', [TeamController::class, 'members'])->name('members');
        Route::post('/{team}/members', [TeamController::class, 'inviteMember'])->name('members.invite');
        Route::put('/{team}/members/{user}', [TeamController::class, 'updateMemberRole'])->name('members.update');
        Route::delete('/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('members.remove');
        Route::post('/{team}/leave', [TeamController::class, 'leave'])->name('leave');
    });

    // Collections
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [CollectionController::class, 'index'])->name('index');
        Route::get('/create', [CollectionController::class, 'create'])->name('create');
        Route::post('/', [CollectionController::class, 'store'])->name('store');
        Route::get('/tree', [CollectionController::class, 'tree'])->name('tree');
        Route::post('/reorder', [CollectionController::class, 'reorder'])->name('reorder');
        Route::get('/{collection}', [CollectionController::class, 'show'])->name('show');
        Route::get('/{collection}/edit', [CollectionController::class, 'edit'])->name('edit');
        Route::put('/{collection}', [CollectionController::class, 'update'])->name('update');
        Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('destroy');
    });

    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::post('/reorder', [DocumentController::class, 'reorder'])->name('reorder');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{document}/star', [DocumentController::class, 'star'])->name('star');
        Route::post('/{document}/duplicate', [DocumentController::class, 'duplicate'])->name('duplicate');
        Route::get('/{document}/versions', [DocumentController::class, 'versions'])->name('versions');
        Route::post('/{document}/versions/{versionId}/restore', [DocumentController::class, 'restoreVersion'])->name('versions.restore');
        Route::post('/{document}/publish', [DocumentController::class, 'publish'])->name('publish');
        Route::post('/{document}/unpublish', [DocumentController::class, 'unpublish'])->name('unpublish');
        Route::post('/{document}/move', [DocumentController::class, 'move'])->name('move');
        Route::get('/{document}/export/{format?}', [DocumentController::class, 'export'])->name('export');
        
        // Share
        Route::get('/{document}/share', [ShareController::class, 'create'])->name('share');
        Route::post('/{document}/share', [ShareController::class, 'store'])->name('share.store');
        
        // Attachments
        Route::post('/{document}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
        
        // Comments
        Route::post('/{document}/comments', [CommentController::class, 'store'])->name('comments.store');
    });

    // Attachments
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // Comments
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/resolve', [CommentController::class, 'resolve'])->name('comments.resolve');
    Route::post('/comments/{comment}/unresolve', [CommentController::class, 'unresolve'])->name('comments.unresolve');

    // Share Links
    Route::delete('/share-links/{shareLink}', [ShareController::class, 'destroy'])->name('share-links.destroy');

    // Tags
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{tag}', [TagController::class, 'show'])->name('show');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
    });
});