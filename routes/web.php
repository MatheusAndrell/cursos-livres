<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\v2\InstamojoController;
use App\Http\Controllers\v2\PaypalPaymentController;
use App\Http\Controllers\v2\PayUMoneyController;
use App\Models\Auth\User;
use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Course;
use App\Models\Reason;
use App\Models\Sponsor;
use App\Models\Testimonial;

/*
 * Global Routes
 * Routes that are used between both frontend and backend.
 */


// Switch between the included languages
Route::get('lang/{lang}', [LanguageController::class, 'swap']);


Route::get('/sitemap-' . str_slug(config('app.name')) . '/{file?}', 'SitemapController@index');


//============ Remove this  while creating zip for Envato ===========//

/*This command is useful in demo site you can go to https://demo.neonlms.com/reset-demo and it will refresh site from this URL. */

Route::get('reset-demo', function () {
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 1000);
    try {
        \Illuminate\Support\Facades\Artisan::call('refresh:site');
        return 'Refresh successful!';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
//===================================================================//


/*
 * Frontend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    include_route_files(__DIR__ . '/frontend/');
});

/*
 * Backend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    /*
     * These routes need view-backend permission
     * (good if you want to allow more than one group in the backend,
     * then limit the backend features by different roles or permissions)
     *
     * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
     * These routes can not be hit if the password is expired
     */
    include_route_files(__DIR__ . '/backend/');
});

Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'auth'], function () {

//==== Messages Routes =====//
    Route::get('messages', ['uses' => 'MessagesController@index', 'as' => 'messages']);
    Route::post('messages/unread', ['uses' => 'MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
    Route::post('messages/send', ['uses' => 'MessagesController@send', 'as' => 'messages.send']);
    Route::post('messages/reply', ['uses' => 'MessagesController@reply', 'as' => 'messages.reply']);
});


Route::get('category/{category}/blogs', 'BlogController@getByCategory')->name('blogs.category');
Route::get('tag/{tag}/blogs', 'BlogController@getByTag')->name('blogs.tag');
Route::get('blog/{slug?}', 'BlogController@getIndex')->name('blogs.index');
Route::post('blog/{id}/comment', 'BlogController@storeComment')->name('blogs.comment');
Route::get('blog/comment/delete/{id}', 'BlogController@deleteComment')->name('blogs.comment.delete');

Route::get('teachers', 'Frontend\HomeController@getTeachers')->name('teachers.index');
Route::get('teachers/{id}/show', 'Frontend\HomeController@showTeacher')->name('teachers.show');


Route::post('newsletter/subscribe', 'Frontend\HomeController@subscribe')->name('subscribe');

//============Course Routes=================//
Route::get('courses', ['uses' => 'CoursesController@all', 'as' => 'courses.all']);
Route::get('course/{slug}', ['uses' => 'CoursesController@show', 'as' => 'courses.show'])->middleware('subscribed');
//Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
Route::post('course/{course_id}/rating', ['uses' => 'CoursesController@rating', 'as' => 'courses.rating']);
Route::get('category/{category}/courses', ['uses' => 'CoursesController@getByCategory', 'as' => 'courses.category']);
Route::post('courses/{id}/review', ['uses' => 'CoursesController@addReview', 'as' => 'courses.review']);
Route::get('courses/review/{id}/edit', ['uses' => 'CoursesController@editReview', 'as' => 'courses.review.edit']);
Route::post('courses/review/{id}/edit', ['uses' => 'CoursesController@updateReview', 'as' => 'courses.review.update']);
Route::get('courses/review/{id}/delete', ['uses' => 'CoursesController@deleteReview', 'as' => 'courses.review.delete']);


//============Bundle Routes=================//
Route::get('bundles', ['uses' => 'BundlesController@all', 'as' => 'bundles.all']);
Route::get('bundle/{slug}', ['uses' => 'BundlesController@show', 'as' => 'bundles.show']);
//Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
Route::post('bundle/{bundle_id}/rating', ['uses' => 'BundlesController@rating', 'as' => 'bundles.rating']);
Route::get('category/{category}/bundles', ['uses' => 'BundlesController@getByCategory', 'as' => 'bundles.category']);
Route::post('bundles/{id}/review', ['uses' => 'BundlesController@addReview', 'as' => 'bundles.review']);
Route::get('bundles/review/{id}/edit', ['uses' => 'BundlesController@editReview', 'as' => 'bundles.review.edit']);
Route::post('bundles/review/{id}/edit', ['uses' => 'BundlesController@updateReview', 'as' => 'bundles.review.update']);
Route::get('bundles/review/{id}/delete', ['uses' => 'BundlesController@deleteReview', 'as' => 'bundles.review.delete']);


Route::group(['middleware' => 'auth'], function () {
    Route::get('lesson/{course_id}/{slug}/', ['uses' => 'LessonsController@show', 'as' => 'lessons.show']);
    Route::post('lesson/{slug}/test', ['uses' => 'LessonsController@test', 'as' => 'lessons.test']);
    Route::post('lesson/{slug}/retest', ['uses' => 'LessonsController@retest', 'as' => 'lessons.retest']);
    Route::post('video/progress', 'LessonsController@videoProgress')->name('update.videos.progress');
    Route::post('lesson/progress', 'LessonsController@courseProgress')->name('update.course.progress');
    Route::post('lesson/book-slot', 'LessonsController@bookSlot')->name('lessons.course.book-slot');
});

Route::get('/search', [HomeController::class, 'searchCourse'])->name('search');
Route::get('/search-course', [HomeController::class, 'searchCourse'])->name('search-course');
Route::get('/search-bundle', [HomeController::class, 'searchBundle'])->name('search-bundle');
Route::get('/search-blog', [HomeController::class, 'searchBlog'])->name('blogs.search');


Route::get('/faqs', 'Frontend\HomeController@getFaqs')->name('faqs');


/*=============== Theme blades routes ends ===================*/


Route::get('contact', 'Frontend\ContactController@index')->name('contact');
Route::post('contact/send', 'Frontend\ContactController@send')->name('contact.send');


Route::get('download', ['uses' => 'Frontend\HomeController@getDownload', 'as' => 'download']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('cart/checkout', ['uses' => 'CartController@checkout', 'as' => 'cart.checkout']);
    Route::post('cart/add', ['uses' => 'CartController@addToCart', 'as' => 'cart.addToCart']);
    Route::get('cart', ['uses' => 'CartController@index', 'as' => 'cart.index']);
    Route::get('cart/clear', ['uses' => 'CartController@clear', 'as' => 'cart.clear']);
    Route::get('cart/remove', ['uses' => 'CartController@remove', 'as' => 'cart.remove']);
    Route::post('cart/apply-coupon', ['uses' => 'CartController@applyCoupon', 'as' => 'cart.applyCoupon']);
    Route::post('cart/remove-coupon', ['uses' => 'CartController@removeCoupon', 'as' => 'cart.removeCoupon']);
    Route::post('cart/stripe-payment', ['uses' => 'CartController@stripePayment', 'as' => 'cart.stripe.payment']);
    Route::post('cart/paypal-payment', ['uses' => 'CartController@paypalPayment', 'as' => 'cart.paypal.payment']);
    Route::get('cart/paypal-payment/status', ['uses' => 'CartController@getPaymentStatus'])->name('cart.paypal.status');

    Route::post('cart/instamojo-payment', ['uses' => 'CartController@instamojoPayment', 'as' => 'cart.instamojo.payment']);
    Route::get('cart/instamojo-payment/status', ['uses' => 'CartController@getInstamojoStatus'])->name('cart.instamojo.status');

    Route::post('cart/razorpay-payment', ['uses' => 'CartController@razorpayPayment', 'as' => 'cart.razorpay.payment']);
    Route::post('cart/razorpay-payment/status', ['uses' => 'CartController@getRazorpayStatus'])->name('cart.razorpay.status');

    Route::post('cart/cashfree-payment', ['uses' => 'CartController@cashfreeFreePayment', 'as' => 'cart.cashfree.payment']);
    Route::post('cart/cashfree-payment/status', ['uses' => 'CartController@getCashFreeStatus'])->name('cart.cashfree.status');

    Route::post('cart/payu-payment', ['uses' => 'CartController@payuPayment', 'as' => 'cart.payu.payment']);
    Route::post('cart/payu-payment/status', ['uses' => 'CartController@getPayUStatus'])->name('cart.pauy.status');

    Route::match(['GET', 'POST'], 'cart/flutter-payment', ['uses' => 'CartController@flatterPayment', 'as' => 'cart.flutter.payment']);
    Route::get('cart/flutter-payment/status', ['uses' => 'CartController@getFlatterStatus', 'as' => 'cart.flutter.status']);

    Route::get('status', function () {
        return view('frontend.cart.status');
    })->name('status');
    Route::post('cart/offline-payment', ['uses' => 'CartController@offlinePayment', 'as' => 'cart.offline.payment']);
    Route::post('cart/getnow', ['uses' => 'CartController@getNow', 'as' => 'cart.getnow']);
});

//============= Menu  Manager Routes ===============//
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'middleware' => config('menu.middleware')], function () {
//     Route::get('wmenuindex', array('uses'=>'\Harimayco\Menu\Controllers\MenuController@wmenuindex'));
//    Route::post('add-custom-menu', 'MenuController@addcustommenu')->name('haddcustommenu');
//    Route::post('delete-item-menu', 'MenuController@deleteitemmenu')->name('hdeleteitemmenu');
//    Route::post('delete-menug', 'MenuController@deletemenug')->name('hdeletemenug');
//    Route::post('create-new-menu', 'MenuController@createnewmenu')->name('hcreatenewmenu');
//    Route::post('generate-menu-control', 'MenuController@generatemenucontrol')->name('hgeneratemenucontrol');
//    Route::post('update-item', 'MenuController@updateitem')->name('hupdateitem');
//    Route::post('save-custom-menu', 'MenuController@saveCustomMenu')->name('hcustomitem');
//    Route::post('change-location', 'MenuController@updateLocation')->name('update-location');
});

Route::get('certificate-verification', 'Backend\CertificateController@getVerificationForm')->name('frontend.certificates.getVerificationForm');
Route::post('certificate-verification', 'Backend\CertificateController@verifyCertificate')->name('frontend.certificates.verify');
Route::get('certificates/download', ['uses' => 'Backend\CertificateController@download', 'as' => 'certificates.download']);


if (config('show_offers') == 1) {
    Route::get('offers', ['uses' => 'CartController@getOffers', 'as' => 'frontend.offers']);
}

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth', 'role:teacher|administrator']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::group(['prefix' => 'subscription'], function () {
    Route::get('plans', 'SubscriptionController@plans')->name('subscription.plans');
    Route::get('/{plan}/{name}', 'SubscriptionController@showForm')->name('subscription.form');
    Route::post('subscribe/{plan}', 'SubscriptionController@subscribe')->name('subscription.subscribe');
    Route::post('update/{plan}', 'SubscriptionController@updateSubscription')->name('subscription.update');
    Route::get('status', 'SubscriptionController@status')->name('subscription.status');
    Route::post('subscribe', 'SubscriptionController@courseSubscribed')->name('subscription.course_subscribe');
});

Route::get('payu-handle-payment/{order_id}', [PayUMoneyController::class, 'handlePayment'])->name('payu-handle-payment');
Route::post('payu-transaction/success', [PayUMoneyController::class, 'paymentStatus'])->name('payu-payment.success');
Route::post('payu-transaction/declined', [PayUMoneyController::class, 'paymentStatus'])->name('payu-payment.declined');

Route::get('paypal-handle-payment/{order_id}', [PaypalPaymentController::class, 'paypalHandlePayment'])->name('paypal-handle-payment');
Route::get('paypal-transaction/success', [PaypalPaymentController::class, 'getPaymentStatus'])->name('paypal-payment.success');
Route::get('paypal-transaction/declined', [PaypalPaymentController::class, 'getPaymentStatus'])->name('paypal-payment.declined');

Route::get('instamojo-handle-payment/{order_id}', [InstamojoController::class, 'instamojoPayment'])->name('instamojo-handle-payment');
Route::get('instamojo-transaction/status', [InstamojoController::class, 'getInstamojoStatus'])->name('instamojo-payment.status');
Route::get('instamojo-transaction/success', [InstamojoController::class, 'status'])->name('instamojo-payment.success');
Route::get('instamojo-transaction/declined', [InstamojoController::class, 'status'])->name('instamojo-payment.declined');

// wishlist
Route::post('add-to-wishlist', 'Backend\WishlistController@store')->name('add-to-wishlist');

Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::get('/{page?}', [HomeController::class, 'index'])->name('index');

});

// vitrine 

Route::get('/leaf-package/courses', function () {
    
    $type = config('theme_layout');
    $sections = Config::where('key', '=', 'layout_' . $type)->first();
    $sections = json_decode($sections->value);

    $popular_courses = Course::withoutGlobalScope('filter')->canDisableCourse()
        ->whereHas('category')
        ->where('published', '=', 1)
        ->where('popular', '=', 1)->take(6)->get();

    $featured_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', '=', 1)
        ->whereHas('category')
        ->where('featured', '=', 1)->take(8)->get();

     $course_categories = Category::with('courses')->where('icon', '!=', "")->take(12)->get();

    $trending_courses = Course::withoutGlobalScope('filter')->canDisableCourse()
        ->whereHas('category')
        ->where('published', '=', 1)
        ->where('trending', '=', 1)->take(2)->get();

    $teachers = User::role('teacher')->with('courses')->where('active', '=', 1)->take(7)->get();

    $sponsors = Sponsor::where('status', '=', 1)->get();

    $news = Blog::orderBy('created_at', 'desc')->take(2)->get();

    $faqs = Category::with('faqs')->get()->take(6);

    $testimonials = Testimonial::where('status', '=', 1)->orderBy('created_at', 'desc')->get();

    $reasons = Reason::where('status', '=', 1)->orderBy('created_at', 'desc')->get();

    if ((int)config('counter') == 1) {
        $total_students = config('total_students');
        $total_courses = config('total_courses');
        $total_teachers = config('total_teachers');
    } else {
        $total_course = Course::where('published', '=', 1)->canDisableCourse()->get()->count();
        $total_bundle = Bundle::where('published', '=', 1)->canDisableBundle()->get()->count();
        $total_students = User::role('student')->get()->count();
        $total_courses = $total_course + $total_bundle;
        $total_teachers = User::role('teacher')->get()->count();
    }

    $categories = Category::get();
    return view($this->path . '.index-' . config('theme_layout'), compact('popular_courses', 'featured_courses', 'sponsors', 'total_students', 'total_courses', 'total_teachers', 'testimonials', 'news', 'trending_courses', 'teachers', 'faqs', 'course_categories', 'reasons', 'sections','categories'));
});

Route::get('/obtenha-business', 'FrontendController@obtenhaBusiness')->name('obtenha.business');
Route::get('/saiba-mais', 'FrontendController@saibaMais')->name('saiba.mais');
Route::get('/seja-instrutor', 'FrontendController@saibaMais')->name('seja.instrutor');
