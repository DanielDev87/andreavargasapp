<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CourseContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ValidationRequestController as AdminValidationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ValidationRequestController;
use App\Http\Controllers\WebhookController;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $courses = Course::all()->filter->isPubliclyVisible()->values();

    return view('welcome', compact('courses'));
});

Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Registration
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Password reset
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard')->middleware('can:admin');

    // Student profile
    Route::get('/perfil', [ProfileController::class, 'index'])->name('profile');

    // Validation request
    Route::get('/validacion', [ValidationRequestController::class, 'create'])->name('validation.create');
    Route::post('/validacion', [ValidationRequestController::class, 'store'])->name('validation.store');

    // Purchases
    Route::get('/cursos/{course}/comprar', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases/{course}', [PurchaseController::class, 'initiate'])->name('purchases.initiate');

    // Course comments (student <-> instructor)
    Route::post('/cursos/{course}/comentarios', [CommentController::class, 'store'])->name('comments.store');

    // Lesson viewing and completion
    Route::get('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete'])->name('lessons.complete');
    Route::get('/lessons/{lesson}/embed', [LessonController::class, 'embed'])->name('lessons.embed');

    // Admin validation review
    Route::prefix('admin')->middleware(['auth', 'can:admin'])->group(function () {
        Route::get('validations', [AdminValidationController::class, 'index'])->name('admin.validations.index');
        Route::get('validations/{validationRequest}', [AdminValidationController::class, 'show'])->name('admin.validations.show');
        Route::post('validations/{validationRequest}/review', [AdminValidationController::class, 'review'])->name('admin.validations.review');

        // Course management
        Route::get('courses', [App\Http\Controllers\Admin\CourseController::class, 'index'])->name('admin.courses.index');
        Route::get('courses/create', [App\Http\Controllers\Admin\CourseController::class, 'create'])->name('admin.courses.create');
        Route::post('courses', [App\Http\Controllers\Admin\CourseController::class, 'store'])->name('admin.courses.store');
        Route::get('courses/{course}/edit', [App\Http\Controllers\Admin\CourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('courses/{course}', [App\Http\Controllers\Admin\CourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('courses/{course}', [App\Http\Controllers\Admin\CourseController::class, 'destroy'])->name('admin.courses.destroy');
        Route::post('courses/{course}/toggle-visibility', [App\Http\Controllers\Admin\CourseController::class, 'toggleVisibility'])->name('admin.courses.toggle-visibility');

        // Attendance (presencial events: check-in codes/QR)
        Route::get('courses/{course}/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::post('courses/{course}/attendance/scan', [AttendanceController::class, 'scan'])->name('admin.attendance.scan');
        Route::post('courses/{course}/attendance/{purchase}/check-in', [AttendanceController::class, 'checkIn'])->name('admin.attendance.check-in');

        // Course content: modules & lessons
        Route::get('courses/{course}/content', [CourseContentController::class, 'index'])->name('admin.courses.content');
        Route::post('courses/{course}/modules', [CourseContentController::class, 'storeModule'])->name('admin.modules.store');
        Route::put('courses/{course}/modules/{module}', [CourseContentController::class, 'updateModule'])->name('admin.modules.update');
        Route::delete('courses/{course}/modules/{module}', [CourseContentController::class, 'destroyModule'])->name('admin.modules.destroy');
        Route::post('courses/{course}/modules/{module}/lessons', [CourseContentController::class, 'storeLesson'])->name('admin.lessons.store');
        Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [CourseContentController::class, 'updateLesson'])->name('admin.lessons.update');
        Route::delete('courses/{course}/modules/{module}/lessons/{lesson}', [CourseContentController::class, 'destroyLesson'])->name('admin.lessons.destroy');

        // Purchases (transfers manual approval)
        Route::get('purchases', [App\Http\Controllers\Admin\PurchaseController::class, 'index'])->name('admin.purchases.index');
        Route::post('purchases/{purchase}/approve', [App\Http\Controllers\Admin\PurchaseController::class, 'approve'])->name('admin.purchases.approve');

        // Comments notification panel (student <-> instructor Q&A)
        Route::get('comments', [App\Http\Controllers\Admin\CommentController::class, 'index'])->name('admin.comments.index');
        Route::post('comments/{comment}/reply', [App\Http\Controllers\Admin\CommentController::class, 'reply'])->name('admin.comments.reply');
    });
});

// Webhooks (no auth). GET is only reachable via BoldService's local "simulate" redirect when no
// real Bold credentials are configured; the real Bold webhook always POSTs.
Route::match(['get', 'post'], '/webhooks/bold', [WebhookController::class, 'bold'])->name('webhooks.bold');

// Badges
Route::middleware('auth')->group(function () {
    Route::get('/badges/{badge}', [BadgeController::class, 'show'])->name('badges.show');
    Route::get('/badges/{badge}/share', [BadgeController::class, 'share'])->name('badges.share');
});
