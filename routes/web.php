<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\JournalController as AdminJournalController;
use App\Http\Controllers\Admin\LocalAdminSetupController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\TourDateController as AdminTourDateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityBookingController;
use App\Http\Controllers\ActivityController as PublicActivityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController as PublicDestinationController;
use App\Http\Controllers\FaqController as PublicFaqController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\FlightBookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController as PublicHotelController;
use App\Http\Controllers\HotelBookingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\JournalController as PublicJournalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TourController as PublicTourController;
use App\Http\Controllers\TripPlannerController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', SearchController::class)->name('search');

Route::get('/destination', [PublicDestinationController::class, 'index'])->name('destination');
Route::get('/destination/{destination:slug}', [PublicDestinationController::class, 'show'])->name('destination.show');
Route::get('/tour', [PublicTourController::class, 'index'])->name('tour');
Route::get('/tour/{tour:slug}', [PublicTourController::class, 'show'])->name('tour.show');
Route::get('/booking', [BookingController::class, 'create'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->middleware('auth')->name('booking.store');
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');
Route::middleware('auth')->group(function () {
    Route::get('/payment', [PaymentController::class, 'show'])->name('payment');
    Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/payment/esewa/success', [PaymentController::class, 'esewaSuccess'])->name('esewa.success');
    Route::get('/payment/esewa/failure', [PaymentController::class, 'esewaFailure'])->name('esewa.failure');
});
Route::get('/invoice', [InvoiceController::class, 'show'])->middleware('auth')->name('invoice');
Route::get('/invoice/{reference}/pdf', [InvoiceController::class, 'download'])->middleware('auth')->name('invoice.download');
Route::get('/planner', [TripPlannerController::class, 'index'])->name('planner');
Route::post('/planner', [TripPlannerController::class, 'store'])->middleware('auth')->name('planner.store');
Route::get('/planner/{tripPlan}', [TripPlannerController::class, 'show'])->middleware('auth')->name('planner.show');
Route::patch('/planner/{tripPlan}/days/{day}', [TripPlannerController::class, 'updateDay'])->middleware('auth')->name('planner.days.update');
Route::post('/planner/{tripPlan}/days/{day}/items', [TripPlannerController::class, 'addItem'])->middleware('auth')->name('planner.items.store');
Route::patch('/planner/{tripPlan}/items/{item}', [TripPlannerController::class, 'moveItem'])->middleware('auth')->name('planner.items.move');
Route::delete('/planner/{tripPlan}/items/{item}', [TripPlannerController::class, 'removeItem'])->middleware('auth')->name('planner.items.destroy');
Route::delete('/planner/{tripPlan}', [TripPlannerController::class, 'destroy'])->middleware('auth')->name('planner.destroy');
Route::get('/account', [BookingController::class, 'index'])->middleware('auth')->name('account');
Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('auth')->name('booking.cancel');
Route::get('/journal', [PublicJournalController::class, 'index'])->name('journal');
Route::get('/journal/{slug}', [PublicJournalController::class, 'show'])->name('journal.article');
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin'])->name('admin');
Route::get('/hotels', [PublicHotelController::class, 'index'])->name('hotels');
Route::get('/hotels/{hotel:slug}', [PublicHotelController::class, 'show'])->name('hotel.show');
Route::get('/hotels/{hotel:slug}/booking', [HotelBookingController::class, 'create'])->name('hotel.booking');
Route::post('/hotels/{hotel:slug}/booking', [HotelBookingController::class, 'store'])->middleware('auth')->name('hotel.booking.store');
Route::view('/flights', 'pages.flights')->name('flights');
Route::get('/flights/search', [FlightController::class, 'search'])->name('flights.search');
Route::get('/flights/{flight}/booking', [FlightBookingController::class, 'create'])->name('flight.booking');
Route::post('/flights/{flight}/booking', [FlightBookingController::class, 'store'])->middleware('auth')->name('flight.booking.store');
Route::view('/profile', 'pages.profile')->middleware('auth')->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth')->name('profile.password');
Route::get('/activities', [PublicActivityController::class, 'index'])->name('activities');
Route::get('/activities/{activity:slug}', [PublicActivityController::class, 'show'])->name('activities.show');
Route::get('/activities/{activity:slug}/booking', [ActivityBookingController::class, 'create'])->name('activities.booking');
Route::post('/activities/{activity:slug}/booking', [ActivityBookingController::class, 'store'])->middleware('auth')->name('activities.booking.store');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter/subscribe', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');
Route::get('/faq', [PublicFaqController::class, 'index'])->name('faq');
Route::view('/photo-credits', 'pages.photo-credits')->name('photo-credits');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::get('/wishlist', [WishlistController::class, 'index'])->middleware('auth')->name('wishlist');
Route::post('/wishlist', [WishlistController::class, 'store'])->middleware('auth')->name('wishlist.store');
Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->middleware('auth')->name('wishlist.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/admin/setup', [LocalAdminSetupController::class, 'create'])->name('admin.setup');
Route::post('/admin/setup', [LocalAdminSetupController::class, 'store'])->name('admin.setup.store');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tours', TourController::class)->except(['show']);
    Route::get('/tours/{tour}/dates', [AdminTourDateController::class, 'index'])->name('tours.dates.index');
    Route::post('/tours/{tour}/dates', [AdminTourDateController::class, 'store'])->name('tours.dates.store');
    Route::patch('/tours/{tour}/dates/{tourDate}', [AdminTourDateController::class, 'update'])->name('tours.dates.update');
    Route::resource('coupons', CouponController::class)->except(['show']);
    Route::resource('hotels', AdminHotelController::class)->except(['show']);
    Route::get('/hotels/{hotel}/rooms', [\App\Http\Controllers\Admin\HotelRoomController::class, 'index'])->name('hotels.rooms.index');
    Route::post('/hotels/{hotel}/rooms', [\App\Http\Controllers\Admin\HotelRoomController::class, 'store'])->name('hotels.rooms.store');
    Route::put('/hotels/{hotel}/rooms/{room}', [\App\Http\Controllers\Admin\HotelRoomController::class, 'update'])->name('hotels.rooms.update');
    Route::delete('/hotels/{hotel}/rooms/{room}', [\App\Http\Controllers\Admin\HotelRoomController::class, 'destroy'])->name('hotels.rooms.destroy');
    Route::resource('journal', AdminJournalController::class)->except(['show']);
    Route::resource('faqs', AdminFaqController::class)->except(['show']);

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings');
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::patch('/contact-messages/{contactMessage}/status', [AdminContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
    Route::get('/newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
    Route::patch('/newsletter-subscribers/{subscriber}/status', [AdminNewsletterSubscriberController::class, 'updateStatus'])->name('newsletter-subscribers.status');
    Route::delete('/newsletter-subscribers/{subscriber}', [AdminNewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::patch('/bookings/{booking}/refunds/{refund}', [AdminBookingController::class, 'processRefund'])->name('bookings.refunds.process');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews');
    Route::patch('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.status');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::patch('/users/{user}/admin', [AdminUserController::class, 'updateAdminRole'])->name('users.admin');
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
    Route::post('/settings', [AdminSettingController::class, 'store'])->name('settings.store');

    Route::resource('destinations', DestinationController::class)->except(['show']);

});
