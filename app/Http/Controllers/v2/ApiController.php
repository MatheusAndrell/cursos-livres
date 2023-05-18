<?php

namespace App\Http\Controllers\v2;

use App\Helpers\General\EarningHelper;
use App\Helpers\Payments\InstamojoWrapper;
use App\Helpers\Payments\PayuMoneyWrapper;
use App\Helpers\Payments\RazorpayWrapper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Frontend\User\UpdatePasswordRequest;
use App\Http\Requests\Frontend\User\UpdateProfileRequest;
use App\Http\Resources\API\SubscribedResource;
use App\Mail\Frontend\AdminOrederMail;
use App\Mail\Frontend\Contact\SendContact;
use App\Mail\Frontend\LiveLesson\StudentMeetingSlotMail;
use App\Mail\OfflineOrderMail;
use App\Models\Auth\SocialAccount;
use App\Models\Auth\Traits\SendUserPasswordReset;
use App\Models\Auth\User;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Config;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Lesson;
use App\Models\LessonSlotBooking;
use App\Models\LiveLessonSlot;
use App\Models\Locale;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionsOption;
use App\Models\Reason;
use App\Models\Review;
use App\Models\Sponsor;
use App\Models\Stripe\StripePlan;
use App\Models\Tag;
use App\Models\Tax;
use App\Models\Test;
use App\Models\Testimonial;
use App\Models\TestsResult;
use App\Models\TestsResultsAnswer;
use App\Models\VideoProgress;
use App\Models\WishList;
use App\Repositories\Frontend\Api\OrderRepository;
use App\Repositories\Frontend\Auth\UserRepository;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Carbon\Carbon;
use Darryldecode\Cart\CartCondition;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Exception;
use Harimayco\Menu\Models\MenuItems;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Cart;
use Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Lexx\ChatMessenger\Models\Message;
use Lexx\ChatMessenger\Models\Participant;
use Lexx\ChatMessenger\Models\Thread;
use Omnipay\Omnipay;
use Purifier;

//use Messenger;
use Newsletter;
use DevDojo\Chatter\Events\ChatterAfterNewDiscussion;
use DevDojo\Chatter\Events\ChatterAfterNewResponse;
use DevDojo\Chatter\Events\ChatterBeforeNewDiscussion;
use DevDojo\Chatter\Events\ChatterBeforeNewResponse;
use DevDojo\Chatter\Mail\ChatterDiscussionUpdated;
use DevDojo\Chatter\Models\Models;

class ApiController extends Controller
{
    use FileUploadTrait;
    use SendsPasswordResetEmails;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function __invoke(Request $request)
    {
        try {
            //        $this->validateEmail($request);
            $validator = Validator::make($request->all(),
                [
                    'email' => 'required|string|email',
                ]);
            if ($validator->fails()) {
                return response()->json(['status' => 100,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $response = $this->broker()->sendResetLink(
                $request->only('email')
            );
            return $response == Password::RESET_LINK_SENT
                ? response()->json(['status' => 200, 'result' => null, 'message' => 'Reset link sent to your email.'])
                : response()->json(['status' => 100, 'result' => null, 'message' => 'Unable to send reset link. No Email found.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get the Signup Form
     *
     * @return [json] config object
     */
    public function signupForm()
    {
        $fields = [];
        if (config('registration_fields') != null) {
            $fields = json_decode(config('registration_fields'), true);
        }
//        if (config('access.captcha.registration') > 0) {
//            $fields[] = ['name' => 'g-recaptcha-response', 'type' => 'captcha'];
//        }
        return response()->json(['status' => 200, 'result' => $fields]);
    }

    public function signup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|string|email|unique:users',
//            'g-recaptcha-response' => (config('access.captcha.registration') ? ['required', new CaptchaRule()] : ''),
                ], [
//            'g-recaptcha-response.required' => __('validation.attributes.frontend.captcha'),
                ]);
            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }
            $user = new User([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            $user->dob = isset($request->dob) ? $request->dob : null;
            $user->phone = isset($request->phone) ? $request->phone : null;
            $user->gender = isset($request->gender) ? $request->gender : null;
            $user->address = isset($request->address) ? $request->address : null;
            $user->city = isset($request->city) ? $request->city : null;
            $user->pincode = isset($request->pincode) ? $request->pincode : null;
            $user->state = isset($request->state) ? $request->state : null;
            $user->country = isset($request->country) ? $request->country : null;
            $user->save();

            $userForRole = User::find($user->id);
            $userForRole->confirmed = 1;
            $userForRole->save();
            $userForRole->assignRole('student');
            $user->save();
            return response()->json(['status' => 200, 'result' => null, 'message' => 'Successfully created user!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] access_token
     */
    public function login(Request $request)
    {
        try {
            if ($request->grant_type == 'password') {
                $tokenResult = $this->loginViaPassword($request);
            } else {
                $tokenResult = $this->social($request);
            }
            return response()->json([
                'status' => 200,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @throws Exception
     */
    private function loginViaPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
            'remember_me' => 'sometimes|boolean'
        ]);
        if ($validator->fails()) {
            throw new Exception(implode(",", $validator->errors()->all()));
        }
        $credentials = request()->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            throw new Exception('Unauthorized');
        }
        $user = $request->user();
        return $user->token() ?? $user->createToken('socialLogin');
    }

    /**
     * @throws \Exception
     */
    protected function social(Request $request)
    {
        $user = User::query()->where(['email' => $request->email])->first();
        if ($user == null) {
            throw new \Exception('User Not Found with Provided Details');
        }

        SocialAccount::query()->updateOrCreate([
            'user_id' => $user->id,
            'provider_id' => $request->social_id,
            'provider' => $request->provider,
        ]);
        return $user->token() ?? $user->createToken('socialLogin');
    }


    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        try {
            auth()->user()->token()->revoke();
            return response()->json(['status' => 200, 'result' => null, 'message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        try {
            return response()->json($request->user());
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get the App Config
     *
     * @return [json] config object
     */
    public function getConfig(Request $request)
    {
        $data = ['font_color', 'contact_data', 'counter', 'total_students', 'total_courses', 'total_teachers', 'logo_b_image', 'logo_w_image', 'logo_white_image', 'contact_data', 'footer_data', 'app.locale', 'app.display_type', 'app.currency', 'app.name', 'app.url', 'access.captcha.registration', 'paypal.active', 'payment_offline_active'];
        $json_arr = [];
        $config = Config::whereIn('key', $data)->select('key', 'value')->get();
        foreach ($config as $data) {
            if ((array_first(explode('_', $data->key)) == 'logo') || (array_first(explode('_', $data->key)) == 'favicon')) {
                $data->value = asset('storage/logos/' . $data->value);
            }
            $json_arr[$data->key] = (is_null(json_decode($data->value, true))) ? $data->value : json_decode($data->value, true);
        }
        return response()->json(['status' => 200, 'result' => $json_arr]);
    }

    public function getTranslations(Request $request)
    {
        $requestData = $request->all();
        $returnTranslationList = [];
        $default_language_data = Locale::where('is_default', 1)->select('short_name')->first();
        $default_language = $default_language_data->short_name;
        $language_code = isset($requestData['language_code']) ? $requestData['language_code'] : $default_language;

        $locales = Locale::pluck('short_name')->toArray();
        $locale_translation_data = DB::table('ltm_translations')->where('group', 'menus')->whereRaw('`key` LIKE "language-picker.langs.%"')->where('locale', $language_code)->pluck('value', 'key')->toArray();
        $locale_default_translation_data = DB::table('ltm_translations')->where('group', 'menus')->whereRaw('`key` LIKE "language-picker.langs.%"')->where('locale', $default_language)->pluck('value', 'key')->toArray();
        foreach ($locales as $locale) {
            $returnTranslationList[$language_code]['languages'][$locale] = isset($locale_translation_data["language-picker.langs." . $locale]) ? $locale_translation_data["language-picker.langs." . $locale] : $locale_default_translation_data["language-picker.langs." . $locale];
        }

        $translations_data = DB::table('ltm_translations')->select('locale', 'group', 'value', 'key')->where('group', 'app')->where('locale', $language_code)->get();
        $default_translation_data = DB::table('ltm_translations')->select('locale', 'group', 'value', 'key')->where('group', 'app')->where('locale', $default_language)->get()->toArray();
        $current_lang_data = [];
        foreach ($translations_data as $value) {
            $current_lang_data[$value->group][$value->key] = $value->value;
        }
        foreach ($default_translation_data as $value) {
            $array = explode(".", $value->key);
            if (isset($array[1])) {
                $returnTranslationList[$language_code][$array[0]][$array[1]] = isset($current_lang_data[$value->group][$value->key]) ? $current_lang_data[$value->group][$value->key] : $value->value;
            } else {
                $returnTranslationList[$language_code][$array[0]] = isset($current_lang_data[$value->group][$value->key]) ? $current_lang_data[$value->group][$value->key] : $value->value;
            }
        }
        return response()->json(['status' => '200', "message" => "Translations List Sent Successfully.", 'result' => $returnTranslationList]);
    }

    /**
     * Get  courses
     *
     * @return [json] course object
     */
    public function getCourses(Request $request)
    {
        try {
            $types = ['popular', 'trending', 'featured'];
            $type = ($request->type) ? $request->type : null;
            if ($type != null) {
                if (in_array($type, $types)) {
                    $courses = Course::where('published', '=', 1)
                        ->where($type, '=', 1)
                        ->paginate(10);
                } else {
                    return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
                }
            } else {
                $courses = Course::where('published', '=', 1)
                    ->paginate(10);
            }
            return response()->json(['status' => 200, 'message' => 'Courses List Sent Successfully', 'type' => $type, 'result' => $courses]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Search Basic
     *
     * @return [json] Course / Bundle / Blog object
     */
    public function search(Request $request)
    {
        try {
            $courses = Course::query()->where('title', 'LIKE', '%' . $request->q . '%')
                ->orWhere('description', 'LIKE', '%' . $request->q . '%')
                ->where('published', '=', 1)
                ->with('teachers')
                ->get()->map(function ($q) {
                    $q->type = 'course';
                    return $q;
                });
            $totalBundles = 3 + (4 - count($courses));
            $bundles = Bundle::query()->where('title', 'LIKE', '%' . $request->q . '%')
                ->orWhere('description', 'LIKE', '%' . $request->q . '%')
                ->where('published', '=', 1)
                ->with('user')
                ->get()->map(function ($q) {
                    $q->type = 'bundle';
                    return $q;
                });
            $totalBlogs = 3 + (7 - (count($courses) + count($bundles)));
            $blogs = Blog::query()->where('title', 'LIKE', '%' . $request->q . '%')
                ->orWhere('content', 'LIKE', '%' . $request->q . '%')
                ->with('author')
                ->get()->map(function ($q) {
                    $q->type = 'blog';
                    return $q;
                });

            $result = $blogs->merge($bundles)->merge($courses)->paginate(10)->toArray();
            shuffle($result['data']);
            $result['data'] = array_values($result['data']);
            $type = $request->type;
            $q = $request->q;
            $result['q'] = $q;
            $result['type'] = $type;

            return response()->json(['status' => 200, 'message' => "Search result sent successfully.", 'q' => $q, 'type' => $type, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Latest News / Blog
     *
     * @return [json] Blog object
     */
    public function getLatestNews(Request $request)
    {
        try {
            $blog = Blog::orderBy('created_at', 'desc')
                ->select('id', 'category_id', 'user_id', 'title', 'slug', 'content', 'image')
                ->paginate(10);
            return response()->json(['status' => 200, 'message' => "Latest news sent successfully.", 'result' => $blog]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get Latest Testimonials
     *
     * @return [json] Testimonial object
     */
    public function getTestimonials(Request $request)
    {
        try {
            $testimonials = Testimonial::where('status', '=', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['status' => 200, 'message' => "Testimonials sent successfully.", 'result' => $testimonials]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Teachers
     *
     * @return [json] Teacher object
     */
    public function getTeachers(Request $request)
    {
        try {
            $teachers = User::role('teacher')->with('teacherProfile')->paginate(10);
            if ($teachers == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }
            return response()->json(['status' => 200, 'message' => "Teachers List sent successfully.", 'result' => $teachers]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Single Teacher
     *
     * @return [json] Teacher object
     */
    public function getSingleTeacher(Request $request)
    {
        try {
            $teacher = User::role('teacher')->find($request->teacher_id);
            if ($teacher == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }
            $courses = $teacher->courses->take(5);
            $bundles = $teacher->bundles->take(5);
            $profile = $teacher->teacherProfile->first();
            return response()->json(['status' => 200, 'message' => "Teacher details sent successfully.", 'result' => ['teacher' => $teacher, 'courses' => $courses, 'bundles' => $bundles, 'profile' => $profile]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    public function getSingleUser(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if ($user == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }
            return response()->json(['status' => 200, 'message' => "User details sent successfully.", 'result' => ['user' => $user]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Teacher Courses
     *
     * @return [json] Teacher Courses object
     */
    public function getTeacherCourses(Request $request)
    {
        try {
            $teacher = User::role('teacher')->find($request->teacher_id);
            if ($teacher == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }
            $courses = $teacher->courses()->paginate(10);
            return response()->json(['status' => 200, 'message' => "Teacher courses sent successfully.", 'result' => ['teacher' => $teacher, 'courses' => $courses]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Teacher Bundles
     *
     * @return [json] Teacher Bundles object
     */
    public function getTeacherBundles(Request $request)
    {
        try {
            $teacher = User::role('teacher')->find($request->teacher_id);
            if ($teacher == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }
            $bundles = $teacher->bundles()->paginate(10);
            return response()->json(['status' => 200, 'message' => "Teacher bundles sent successfully.", 'result' => ['teacher' => $teacher, 'bundles' => $bundles]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get FAQs
     *
     * @return [json] FAQs object
     */
    public function getFaqs()
    {
        try {
            $faqs = Faq::whereHas('category')
                ->where('status', '=', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['status' => 200, 'message' => "Faqs list sent successfully.", 'result' => $faqs]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get WHy Us Reasons
     *
     * @return [json] Reason object
     */
    public function getWhyUs()
    {
        try {
            $reasons = Reason::where('status', '=', 1)->paginate(10);
            return response()->json(['status' => 200, 'message' => "Why us api data sent successfully.", 'result' => $reasons]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Sponsors
     *
     * @return [json] Sponsors object
     */
    public function getSponsors()
    {
        try {
            $sponsors = Sponsor::where('status', '=', 1)->paginate(10);
            return response()->json(['status' => 200, 'message' => "Sponsors api data sent successfully.", 'result' => $sponsors]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }

    }

    /**
     * Save Contact Us Request
     *
     * @return [json] Success feedback
     */
    public function saveContactUs(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                    'message' => 'required'
                ]);
            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }

            $contact = new Contact();
            $contact->name = $request->name;
            $contact->number = $request->number;
            $contact->email = $request->email;
            $contact->message = $request->message;
            $contact->save();

            try {
                Mail::send(new SendContact($request));
            } catch (Exception $e) {
                \Log::error($e->getMessage());
            }
            return response()->json(['status' => 200, 'result' => null, 'message' => "Contact us data save successfully."]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Single Course
     *
     * @return [json] Success feedback
     */
    public function getSingleCourse(Request $request)
    {
        try {
            $continue_course = null;
            $course_timeline = null;
            $course = Course::withoutGlobalScope('filter')->with('teachers', 'category')->where('id', '=', $request->course_id)->with('publishedLessons')->first();
            if ($course == null) {
                return response()->json(['status' => 100, 'result' => null]);
            }

            $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
            $course_rating = 0;
            $total_ratings = 0;
            $completed_lessons = null;
            $is_reviewed = false;
            if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
                $is_reviewed = true;
            }
            if ($course->reviews->count() > 0) {
                $course_rating = $course->reviews->avg('rating');
                $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
            }
            $lessons = $course->courseTimeline()->orderby('sequence', 'asc')->get();
            if (\Auth::check()) {
                $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->whereNotIn('model_id', $completed_lessons)->first();
                if ($continue_course == null) {
                    $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->first();
                }
            }

            if ($course->courseTimeline) {
                $timeline = $course->courseTimeline()->orderBy('sequence')->get();
                foreach ($timeline as $item) {
                    $completed = false;
                    if (in_array($item->model_id, $completed_lessons)) {
                        $completed = true;
                    }
                    $type = $item->model->live_lesson ? 'live_lesson' : 'lesson';
                    $slots = [];
                    if ($item->model->live_lesson) {
                        if ($item->model->liveLessonSlots->count()) {
                            foreach ($item->model->liveLessonSlots as $slot) {
                                $slots[] = $slot;
                            }
                        }
                    }
                    $description = "";
                    if ($item->model_type == 'App\Models\Test') {
                        $type = 'test';
                        $description = $item->model->description;
                    } else {
                        $description = $item->model->short_text;
                    }
                    $course_timeline[] = [
                        'title' => $item->model->title,
                        'type' => $type,
                        'id' => $item->model_id,
                        'description' => $description,
                        'completed' => $completed,
                        'slots' => $slots,
                    ];
                }
            }
            $mediaVideo = (!$course->mediaVideo) ? null : $course->mediaVideo->toArray();
            if ($mediaVideo && $mediaVideo['type'] == 'embed') {
                preg_match('/src="([^"]+)"/', $mediaVideo['url'], $match);
                $url = $match[1];
                $mediaVideo['file_name'] = $url;
            }
            $result = [
                'course' => $course,
                'course_video' => $mediaVideo,
                'purchased_course' => $purchased_course,
                'course_rating' => $course_rating,
                'total_ratings' => $total_ratings,
                'is_reviewed' => $is_reviewed,
                'lessons' => $lessons,
                'course_timeline' => $course_timeline,
                'completed_lessons' => $completed_lessons,
                'continue_course' => $continue_course,
                'is_certified' => $course->isUserCertified(),
                'course_process' => $course->progress()
            ];
            return response()->json(['status' => 200, 'message' => "Single course data sent successfully", 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Submit review
     *
     * @return [json] Success message
     */
    public function submitReview(Request $request)
    {
        try {
            $reviewable_id = $request->item_id;
            if ($request->type == 'course') {
                $reviewable_type = Course::class;
                $item = Course::find($request->item_id);
            } else {
                $reviewable_type = Bundle::class;
                $item = Bundle::find($request->item_id);
            }
            if ($item != null) {
                $review = new Review();
                $review->user_id = auth()->user()->id;
                $review->reviewable_id = $reviewable_id;
                $review->reviewable_type = $reviewable_type;
                $review->rating = $request->rating;
                $review->content = $request->review;
                $review->save();
                return response()->json(['status' => 200, 'result' => null, "message" => "Review submitted successfully"]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update Review
     *
     * @return [json] Success message
     */
    public function updateReview(Request $request)
    {
        try {
            $review = Review::where('id', '=', $request->review_id)->where('user_id', '=', auth()->user()->id)->first();
            if ($review != null) {
                $review->rating = $request->rating;
                $review->content = $request->review;
                $review->save();

                return response()->json(['status' => 200, 'result' => null, "message" => "Review updated successfully"]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Lesson
     *
     * @return [json] Success message
     */
    public function getLesson(Request $request)
    {
        try {
            $lesson = Lesson::where('published', '=', 1)
                ->where('id', '=', $request->lesson_id)
                ->first();
            if ($lesson != null) {
                $course = $lesson->course;
                $previous_lesson = $lesson->course->courseTimeline()->where('sequence', '<', $lesson->courseTimeline->sequence)
                    ->orderBy('sequence', 'desc')
                    ->first();
                $next_lesson = $lesson->course->courseTimeline()->where('sequence', '>', $lesson->courseTimeline->sequence)
                    ->orderBy('sequence', 'asc')
                    ->first();

                $is_certified = $lesson->course->isUserCertified();
                $course_progress = $lesson->course->progress();

                $downloadable_media = $lesson->downloadable_media;

                $mediaVideo = (!$lesson->mediaVideo) ? null : $lesson->mediaVideo->toArray();
                if ($mediaVideo && $mediaVideo['type'] == 'embed') {
                    preg_match('/src="([^"]+)"/', $mediaVideo['url'], $match);
                    $url = $match[1];
                    $mediaVideo['file_name'] = $url;
                }

                $video = $mediaVideo;
                $pdf = $lesson->mediaPDF;
                $audio = $lesson->mediaAudio;
                $lesson_media = [
                    'downloadable_media' => $downloadable_media,
                    'video' => $video,
                    'pdf' => $pdf,
                    'audio' => $audio,
                ];
                return response()->json(['status' => 200, 'message' => "Lesson detail sent successfully", 'result' => ['lesson' => $lesson, 'lesson_media' => $lesson_media, 'previous_lesson' => $previous_lesson, 'next_lesson' => $next_lesson, 'is_certified' => $is_certified, 'course_progress' => $course_progress, 'course' => $course]]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get Test
     *
     * @return [json] Success message
     */

    public function getTest(Request $request)
    {
        try {
            $test = Test::where('published', '=', 1)
                ->where('id', '=', $request->test_id)
                ->where('course_id', '=', $request->course_id)
                ->first();
            $questions = [];
            $is_test_given = false;
            //If Retest is being taken
            if (isset($request->result_id)) {
                TestsResult::where('id', '=', $request->result_id)
                    ->where('user_id', '=', auth()->user()->id)
                    ->delete();
                $is_test_given = false;
            }

            $test_result = TestsResult::where('test_id', $test->id)
                ->where('user_id', '=', auth()->user()->id)
                ->first();
            $result_data = null;
            $test_result_answers = null;
            if ($test_result) {
                $test_result = $test_result->toArray();
                $test_result_answers = TestsResultsAnswer::where('tests_result_id', '=', $test_result['id'])
                    ->get()->groupBy('option_id')->toArray();
                $is_test_given = true;

                $total_questions = $test->questions->count();
                $percentage = $test_result['test_result'] / $total_questions * 100;
                $test_pass = ($percentage < $test->passing_score) ? "Failed" : "Pass";

                $data['test_score'] = $test_result['test_result'];
                $data['gained_percentage'] = number_format($percentage, 2);
                $data['result'] = $test_pass;
            }

            if ($test->questions && (count($test->questions) > 0)) {
                foreach ($test->questions as $question) {
                    $options = [];
                    if ($question->options) {
                        $question->options = $question->options->filter(function ($item) use ($test_result_answers) {
                            $item->user_selected = (isset($test_result_answers) && isset($test_result_answers[$item->id])) ? 1 : 0;
                            return $item;
                        });
                        $options = $question->options->toArray();
                    }

                    $question_data['question'] = $question->toArray();
                    $question_data['options'] = $options;

                    $questions[] = $question_data;
                }
            }

            $data['test'] = $test->toArray();
            $data['is_test_given'] = $is_test_given;
            $data['result_id'] = isset($test_result) ? $test_result['id'] : null;
            $data['score'] = isset($test_result) ? $test_result['test_result'] : null;
            return response()->json(['status' => 200, "message" => "Test details sent successfully", 'result' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Save Test
     *
     * @return [json] Success message
     */
    public function saveTest(Request $request)
    {
        try {
            $test = Test::where('id', $request->test_id)->firstOrFail();
            if (!$test) {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
            }
            $answers = [];
            $test_score = 0;
            foreach ($request->question_data as $item) {
                $question_id = $item['question_id'];
                $answer_id = $item['ans_id'];
                $question = Question::find($question_id);
                $correct = QuestionsOption::where('question_id', $question_id)
                        ->where('id', $answer_id)
                        ->where('correct', 1)->count() > 0;
                $answers[] = [
                    'question_id' => $question_id,
                    'option_id' => $answer_id,
                    'correct' => $correct
                ];
                if ($correct) {
                    if ($question->score) {
                        $test_score += $question->score;
                    }
                }
                /*
                 * Save the answer
                 * Check if it is correct and then add points
                 * Save all test result and show the points
                 */
            }
            $test_result = TestsResult::create([
                'test_id' => $test->id,
                'user_id' => \Auth::id(),
                'test_result' => $test_score,
            ]);
            $test_result->answers()->createMany($answers);


            if ($test->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
                $test->chapterStudents()->create([
                    'model_type' => $test->model_type,
                    'model_id' => $test->id,
                    'user_id' => auth()->user()->id,
                    'course_id' => $test->course->id
                ]);
            }

            $result = TestsResultsAnswer::where('tests_result_id', '=', $test_result->id)->get()->groupBy('option_id')->toArray();
            $questions = [];
            if ($test->questions && (count($test->questions) > 0)) {
                foreach ($test->questions as $question) {
                    $options = [];
                    if ($question->options) {
                        $question->options = $question->options->filter(function ($item) use ($result) {
                            $item->user_selected = (isset($result) && isset($result[$item->id])) ? 1 : 0;
                            return $item;
                        });
                        $options = $question->options->toArray();
                    }

                    $question_data['question'] = $question->toArray();
                    $question_data['options'] = $options;
                    $questions[] = $question_data;
                }
            }

            $total_questions = $test->questions->count();
            $percentage = $test_result['test_result'] / $total_questions * 100;
            $test_pass = ($percentage < $test->passing_score) ? "Failed" : "Pass";

            $data['test_score'] = $test_result['test_result'];
            $data['gained_percentage'] = number_format($percentage, 2);
            $data['result'] = $test_pass;

            $data['test'] = $test->toArray();
            $data['is_test_given'] = true;
            $data['result_id'] = $test_result->id;
            $data['score'] = $test_score;

            return response()->json(['status' => 200, 'result' => $data, "message" => "Test Save Successfully!"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Live Lesson Slot
     *
     * @return [json] Success message
     */
    public function getLiveLesson(Request $request)
    {
        try {
            $lesson = Lesson::where('published', '=', 1)
                ->where('id', '=', $request->lesson_id)
                ->first();
            if ($lesson != null) {
                $course = $lesson->course;
                $previous_lesson = $lesson->course->courseTimeline()->where('sequence', '<', $lesson->courseTimeline->sequence)
                    ->orderBy('sequence', 'desc')
                    ->first();
                $next_lesson = $lesson->course->courseTimeline()->where('sequence', '>', $lesson->courseTimeline->sequence)
                    ->orderBy('sequence', 'asc')
                    ->first();

                $is_certified = $lesson->course->isUserCertified();
                $course_progress = $lesson->course->progress();

                $downloadable_media = $lesson->downloadable_media;

                $mediaVideo = (!$lesson->mediaVideo) ? null : $lesson->mediaVideo->toArray();
                if ($mediaVideo && $mediaVideo['type'] == 'embed') {
                    preg_match('/src="([^"]+)"/', $mediaVideo['url'], $match);
                    $url = $match[1];
                    $mediaVideo['file_name'] = $url;
                }

                $video = $mediaVideo;
                $pdf = $lesson->mediaPDF;
                $audio = $lesson->mediaAudio;
                $lesson_media = [
                    'downloadable_media' => $downloadable_media,
                    'video' => $video,
                    'pdf' => $pdf,
                    'audio' => $audio,
                ];

                $is_available_slot = true;
                $slots = $lesson->liveLessonSlots->each(function ($slot) {
                    if ($slot->lessonSlotBookings->count() >= $slot->student_limit) {
                        $slot['is_slot_available'] = false;
                    } else {
                        $slot['is_slot_available'] = true;
                    }
                    return $slot->lesson_slot_bookings;
                });

                $slot_booked = false;
                $slot_booked_id = null;
                if ($lesson->lessonSlotBooking && $lesson->lessonSlotBooking->where('user_id', auth()->user()->id)->count()) {
                    $slot_booked = true;
                    $slot_booked_id = $lesson->lessonSlotBooking->where('user_id', auth()->user()->id)->first()->live_lesson_slot_id;
                }
                return response()->json(['status' => 200, 'message' => "Live lesson detail sent successfully.", 'result' => ['lesson' => $lesson, 'lesson_media' => $lesson_media, 'previous_lesson' => $previous_lesson, 'next_lesson' => $next_lesson, 'is_certified' => $is_certified, 'course_progress' => $course_progress, 'course' => $course, 'zoom_timezone' => \config('zoom.timezone'), 'slot_booked_id' => $slot_booked_id, 'slot_booked' => $slot_booked, 'slots' => $slots]]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Booked Live Lessons Slot
     *
     * @return [json] Success message
     */
    public function bookedSlot(Request $request)
    {
        try {
            $lesson_slot = LiveLessonSlot::find($request->slot_id);
            if (!$lesson_slot) {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
            }
            if (LessonSlotBooking::where('lesson_id', $request->lesson_id)->where('user_id', auth()->user()->id)->count() == 0) {
                LessonSlotBooking::create(
                    ['lesson_id' => $request->lesson_id, 'live_lesson_slot_id' => $request->slot_id, 'user_id' => auth()->user()->id]
                );
                \Mail::to(auth()->user()->email)->send(new StudentMeetingSlotMail($lesson_slot));
            }
            return response()->json(['status' => 200, 'result' => null, "message" => "Booked slot details sent successfully."]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Complete Lesson
     *
     * @return [json] Success message
     */
    public function courseProgress(Request $request)
    {
        try {
            if ($request->model_type == 'test') {
                $model_type = Test::class;
                $chapter = Test::find((int)$request->model_id);
            } else {
                $model_type = Lesson::class;
                $chapter = Lesson::find((int)$request->model_id);
            }
            if ($chapter != null) {
                if ($chapter->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
                    $chapter->chapterStudents()->create([
                        'model_type' => $model_type,
                        'model_id' => $request->model_id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $chapter->course->id
                    ]);
                    return response()->json(['status' => 200, 'result' => null]);
                }
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Save video progress for Lesson
     *
     * @return [json] Success message
     */
    public function videoProgress(Request $request)
    {
        try {
            $user = auth()->user();
            $video = Media::find($request->media_id);
            if ($video == null) {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
            }
            $video_progress = VideoProgress::where('user_id', '=', $user->id)
                ->where('media_id', '=', $video->id)->first() ?: new VideoProgress();
            $video_progress->media_id = $video->id;
            $video_progress->user_id = $user->id;
            $video_progress->duration = $video_progress->duration ?: round($request->duration, 2);
            $video_progress->progress = round($request->progress, 2);
            if ($video_progress->duration - $video_progress->progress < 5) {
                $video_progress->progress = $video_progress->duration;
                $video_progress->complete = 1;
            }
            $video_progress->save();
            return response()->json(['status' => 200, 'result' => null]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Generate course certificate
     *
     * @return [json] Success message
     */

    public function generateCertificate(Request $request)
    {
        try {
            $course = Course::whereHas('students', function ($query) {
                $query->where('id', \Auth::id());
            })
                ->where('id', '=', $request->course_id)->first();
            if (($course != null) && ($course->progress() == 100)) {
                $certificate = Certificate::firstOrCreate([
                    'user_id' => auth()->user()->id,
                    'course_id' => $request->course_id
                ]);

                $data = [
                    'name' => auth()->user()->name,
                    'course_name' => $course->title,
                    'date' => Carbon::now()->format('d M, Y'),
                ];
                $certificate_name = 'Certificate-' . $course->id . '-' . auth()->user()->id . '.pdf';
                $certificate->name = auth()->user()->id;
                $certificate->url = $certificate_name;
                $certificate->save();

                $pdf = \PDF::loadView('certificate.index', compact('data'))->setPaper('', 'landscape');

                $pdf->save(public_path('storage/certificates/' . $certificate_name));

                return response()->json(['status' => 200, 'result' => null, "message" => "Certificate generated successfully."]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get Bundles
     *
     * @return [json] Bundle Object
     */
    public function getBundles(Request $request)
    {
        try {
            $types = ['popular', 'trending', 'featured'];
            $type = ($request->type) ? $request->type : null;
            if ($type != null) {
                if (in_array($type, $types)) {
                    $bundles = Bundle::where('published', '=', 1)
                        ->where($type, '=', 1)
                        ->paginate(10);
                } else {
                    return response()->json(['status' => 200, 'message' => 'Bundles List', 'type' => $type, 'result' => $bundles ?? []]);
                }
            } else {
                $bundles = Bundle::where('published', '=', 1)
                    ->paginate(10);
            }
            return response()->json(['status' => 200, 'message' => "Bundles List Sent Successfully", 'type' => $type, 'result' => $bundles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Bundles
     *
     * @return [json] Bundle Object
     */
    public function getSingleBundle(Request $request)
    {
        try {
            $result['bundle'] = Bundle::where('published', '=', 1)
                ->where('id', '=', $request->bundle_id)
                ->first();
            $user = $request->user();
            $purchased_bundle = ($user != null) && $result['bundle']->students()->where('user_id', $user->id)->count() > 0;
            if ($result['bundle'] == null) {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'Invalid Request']);
            }
            $result['courses'] = $result['bundle']->courses;
            $result['purchased_bundle'] = $purchased_bundle;
            return response()->json(['status' => 200, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Add to cart
     *
     * @return [json] Return cart value
     */

    public function addToCart(Request $request)
    {
        try {
            $product = "";
            $teachers = "";
            $type = "";
            if ($request->type == 'course') {
                $product = Course::findOrFail($request->item_id);
                $teachers = $product->teachers->pluck('id', 'name');
                $type = 'course';
                $cart_item_id = 'course_' . $product->id;
            } elseif ($request->type == 'bundle') {
                $product = Bundle::findOrFail($request->item_id);
                $teachers = $product->user->name;
                $type = 'bundle';
                $cart_item_id = 'bundle_' . $product->id;
            }
            $cart_items = Cart::session(auth()->user()->id)->getContent()->keys()->toArray();
            if (!in_array($request->type . '_' . $product->id, $cart_items)) {
                Cart::session(auth()->user()->id)
                    ->add(
                        $cart_item_id, // $product->id,
                        $product->title,
                        $product->price,
                        1,
                        [
                            'user_id' => auth()->user()->id,
                            'description' => $product->description,
                            'image' => $product->course_image,
                            'product_id' => $product->id,
                            'type' => $type,
                            'teachers' => $teachers,
                            'rating' => $product->reviews->avg('rating'),
                        ]
                    );
            } else {
                throw new Exception('Item is Already exists in Cart');
            }
            $total = Cart::session(auth()->user()->id)->getTotal();
            $this->applyTax($total);
            $response = $this->getCartDetailArray();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get Free Course / Bundle
     *
     * @return [json] Success Message
     */
    public function getNow(Request $request)
    {
        try {
            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->reference_no = str_random(8);
            $order->amount = 0;
            $order->status = 1;
            $order->payment_type = 0;
            $order->save();
            //Getting and Adding items
            if ($request->course_id) {
                $type = Course::class;
                $id = $request->course_id;
            } else {
                $type = Bundle::class;
                $id = $request->bundle_id;
            }
            $order->items()->create([
                'item_id' => $id,
                'item_type' => $type,
                'price' => 0
            ]);

            foreach ($order->items as $orderItem) {
                //Bundle Entries
                if ($orderItem->item_type == Bundle::class) {
                    foreach ($orderItem->item->courses as $course) {
                        $course->students()->attach($order->user_id);
                    }
                }
                $orderItem->item->students()->attach($order->user_id);
            }

            return response()->json(['status' => 200, 'result' => null]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Remove from cart
     *
     * @return [json] Remove from cart
     */
    public function removeFromCart(Request $request)
    {
        try {
            foreach (Cart::session(auth()->user()->id)->getContent() as $cartItem) {
                if (($cartItem->attributes->type == $request->type) && ($cartItem->attributes->product_id == $request->item_id)) {
                    $cart_item_id = $request->type . '_' . $request->item_id;
                    Cart::session(auth()->user()->id)->remove($cart_item_id);
                }
            }
            $response = $this->getCartDetailArray();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Show Cart
     *
     * @return [json] Get Cart data
     */
    public function getCartData(Request $request)
    {
        try {
            $response = $this->getCartDetailArray();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @throws InvalidConditionException
     * @throws Exception
     */
    public function getCartDetailArray()
    {
        $course_ids = [];
        $bundle_ids = [];
        $couponArray = [];
        if (count(Cart::session(auth()->user()->id)->getContent()) > 0) {
            foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
                if ($item->attributes->type == 'bundle') {
                    $bundle_ids[] = $item->attributes->product_id;
                } else {
                    $course_ids[] = $item->attributes->product_id;
                }
            }
            $courses = Course::find($course_ids)->map(function ($q) {
                $q->rating = $q->reviews->avg('rating');
                return $q;
            });
            $bundles = Bundle::find($bundle_ids)->map(function ($q) {
                $q->rating = $q->reviews->avg('rating');
                return $q;
            });
            $subtotal = Cart::session(auth()->user()->id)->getSubTotal();

            if (count(Cart::session(request()->user()->id)->getConditionsByType('coupon')) > 0) {
                $coupon = Cart::session(request()->user()->id)->getConditionsByType('coupon')->first();
                $couponData = Coupon::where('code', '=', $coupon->getName())->first();
                Cart::session(auth()->user()->id)->clearCartConditions();
                $this->addCouponToCartSession($couponData->id);
                if ($couponData->type == 1) {
                    $discount = $subtotal * $couponData->amount / 100;
                } else {
                    $discount = $couponData->amount;
                }
                /*$couponArray = [
                    'name' => $couponData->name,
                    'code' => $couponData->code,
                    'type' => ($couponData->type == 1) ? trans('labels.backend.coupons.discount_rate') : trans('labels.backend.coupons.flat_rate'),
                    'value' => $coupon->getValue(),
                    'amount' => number_format($coupon->getCalculatedValue($total), 2)
                ];*/
                $couponArray = $couponData->toArray();
                $couponArray['total_coupon_discount'] = number_format($discount, 2, '.', '');
            } else {
                $couponArray = null;
            }
            $taxes = Tax::where('status', '=', 1)->get();
            $taxData = [];
            if ($taxes != null) {
                foreach ($taxes as $tax) {
                    $amount = $subtotal * $tax->rate / 100;
                    $taxData[] = ['name' => '+' . $tax->rate . '% ' . $tax->name, 'amount' => $amount];
                }
            }
            $total = Cart::session(auth()->user()->id)->getTotal();
            return [
                'status' => 200,
                'message' => "Cart data sent successfully",
                'result' => [
                    'courses' => $courses,
                    'bundles' => $bundles,
                    'count' => count($courses) + count($bundles),
                    'coupon' => $couponArray,
                    'tax' => $taxData,
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                    'total' => number_format($total, 2, '.', ''),
                    'final_total' => number_format($total, 2, '.', ''),
                ],
            ];
        }
        return ['status' => 200, 'result' => ['count' => 0], 'message' => "No Record Found"];
    }

    /**
     * Clear Cart
     *
     * @return [json] Success Message
     */
    public function clearCart()
    {
        try {
            Cart::session(auth()->user()->id)->clear();
            Cart::session(auth()->user()->id)->clearCartConditions();
            return response()->json(['status' => 200, 'result' => null]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    public function makeOnlinePayment(Request $request)
    {
        try {
            switch ($request->payment_mode) {
                case 1:
                    $response = $this->stripePayment($request);
                    break;
                case 2:
                    $response = [
                        'handle_payment_url' => route('paypal-handle-payment', $request->order_confirmation_id),
                        'success_url' => route('paypal-payment.success'),
                        'declined_url' => route('paypal-payment.declined'),
                        'order_confirmation_id' => $request->order_confirmation_id,
                        'payment_mode' => 2,
                    ];
                    break;
                case 4:
                    $total = Cart::session(auth()->user()->id)->getTotal();
                    if ($total <= 10)
                        throw new Exception('For Payment Via Instamojo, Order Amount Cannot be less then 10');
                    $response = [
                        'handle_payment_url' => route('instamojo-handle-payment', $request->order_confirmation_id),
                        'success_url' => route('instamojo-payment.success'),
                        'declined_url' => route('instamojo-payment.declined'),
                        'order_confirmation_id' => $request->order_confirmation_id,
                        'payment_mode' => 4,
                    ];
                    break;
                case 5:
                    $response = $this->razorpayPayment($request);
                    break;
                case 7:
                    $response = [
                        'handle_payment_url' => route('payu-handle-payment', $request->order_confirmation_id),
                        'success_url' => route('payu-payment.success'),
                        'declined_url' => route('payu-payment.declined'),
                        'order_confirmation_id' => $request->order_confirmation_id,
                        'payment_mode' => 7,
                    ];
                    break;
                default:
                    throw new Exception('Please Select on of the available online payment Modes.');
            }
            return response()->json(['status' => 200, 'message' => 'Payment Mode Details', 'result' => $response ?? []]);
        } catch (Exception $e) {
            return response()->json(['status' => 100, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /**
     * @throws Exception
     */
    public function razorpayPayment(Request $request)
    {
        $currency = getCurrency(config('app.currency'))['short_code'];
        if (!$request->total_amount)
            throw new Exception("Please Provide Cart Grand Total \"total_amount\"");
        $amount = $request->total_amount * 100;
        $razorWrapper = new RazorpayWrapper();
        $razorpay_order_id = $razorWrapper->order($currency, $amount);
        return [
            'order_id' => $razorpay_order_id,
            'amount' => $amount / 100,
            'currency' => $currency,
            'description' => $request->user()->name,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'payment_mode' => 5,
            'image' => asset('storage/logos/popup-logo.png'),
            'gateway_key' => config('services.razorpay.key'),
            'gateway_secret' => config('services.razorpay.secret'),
            'gateway_active' => config('services.razorpay.active'),
            'theme_color' => '#03386F',
        ];
    }


    public function getPaymentGatewayStatus(Request $request)
    {
        try {
            $payment_mode = $request->payment_mode ?? $request->payment_type;
            switch ($payment_mode) {
                case 1:
                case 5:
                    $userId = auth()->user()->id;
                    $orderRepository = new OrderRepository();
                    $response = $orderRepository->updateOrderStatus(
                        $request->order_confirmation_id,
                        $request->transaction_id,
                        $request->payment_type ?? $request->payment_mode,
                        $request->status,
                        $request->remarks ?? ''
                    );
                    clearCartById($userId);
                    break;
                default:
                    throw new Exception('Please Select on of the available online payment Modes.');
            }
            return response()->json(['status' => 200, 'message' => 'Payment Mode Details', 'result' => $response ?? []]);
        } catch (Exception $e) {
            return response()->json(['status' => 100, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function stripePayment(Request $request)
    {
        $order_confirmation_id = $request->order_confirmation_id;
        $order = Order::query()->findOrFail($order_confirmation_id);
        $amount = number_format($order->amount, 2, '.', '');
        $currency = getCurrency(config('app.currency'))['short_code'];
        return [
            'currency' => $currency,
            'payment_mode' => 1,
            'stripe_publishable_key' => config('services.stripe.key'),
            'secret_key' => config('services.stripe.secret'),
            'description' => 'Order Payment',
            'order_id' => $order_confirmation_id,
            'amount' => (int)$amount * 100,
        ];
    }

    /**
     * Payment Status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [json] Success Message
     */
    public function paymentStatus(Request $request)
    {
        try {
            $counter = 0;
            $items = [];
            $order = Order::where('id', '=', (int)$request->order_id)->where('status', '=', 0)->first();
            if ($order) {
                if ((int)$request->payment_type == 3) {
                    $status = 0;
                } else {
                    $status = ($request->status == 'success') ? 1 : 0;
                }
                $order->payment_type = $request->payment_type;
                $order->status = $status;
                $order->remarks = $request->remarks;
                $order->transaction_id = $request->transaction_id;
                $order->save();
                if ($order->status == 1) {
                    (new EarningHelper())->insert($order);
                }
                if ((int)$request->payment_type == 3) {
                    foreach ($order->items as $key => $cartItem) {
                        $counter++;
                        array_push($items, ['number' => $counter, 'name' => $cartItem->item->name, 'price' => $cartItem->item->price]);
                    }
                    $content['items'] = $items;
                    $content['total'] = $order->amount;
                    $content['reference_no'] = $order->reference_no;
                    try {
                        \Mail::to(auth()->user()->email)->send(new OfflineOrderMail($content));
                    } catch (\Exception $e) {
                        \Log::info($e->getMessage() . ' for order ' . $order->id);
                    }
                } else {
                    foreach ($order->items as $orderItem) {
                        //Bundle Entries
                        if ($orderItem->item_type == Bundle::class) {
                            foreach ($orderItem->item->courses as $course) {
                                $course->students()->attach($order->user_id);
                            }
                        }
                        $orderItem->item->students()->attach($order->user_id);
                    }
                    //Generating Invoice
                    generateInvoice($order);
                }
                clearCartById($order->user_id);
                return response()->json(['status' => 200, 'message' => 'Order Placed Successfully', 'result' => null]);
            } else {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'No order found']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create Order
     *
     * @return [json] Order
     */
    private function makeOrderOld()
    {
        $coupon = Cart::session(auth()->user()->id)->getConditionsByType('coupon')->first();
        if ($coupon != null) {
            $coupon = Coupon::where('code', '=', $coupon->getName())->first();
        }
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->reference_no = str_random(8);
        $order->amount = Cart::session(auth()->user()->id)->getTotal();
        $order->status = 1;
        $order->coupon_id = ($coupon == null) ? 0 : $coupon->id;
        $order->payment_type = 3;
        $order->save();
        //Getting and Adding items
        foreach (Cart::session(auth()->user()->id)->getContent() as $cartItem) {
            if ($cartItem->attributes->type == 'bundle') {
                $type = Bundle::class;
            } else {
                $type = Course::class;
            }
            $order->items()->create([
                'item_id' => $cartItem->id,
                'item_type' => $type,
                'price' => $cartItem->price
            ]);
        }
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
        return $order;
    }

    private function makeOrder($data)
    {
        $coupon = $data['coupon_data'];
        if ($coupon != false) {
            $coupon_id = $coupon['id'];
        } else {
            $coupon_id = 0;
        }
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->reference_no = str_random(8);
        $order->amount = $data['final_total'];
        $order->status = 0;
        $order->coupon_id = $coupon_id;
        $order->payment_type = 0;
        $order->save();
        //Getting and Adding items
        foreach ($data['data'] as $item) {
            if ($item['type'] == 'bundle') {
                $type = Bundle::class;
            } else {
                $type = Course::class;
            }
            $order->items()->create([
                'item_id' => $item['id'],
                'item_type' => $type,
                'price' => $item['price']
            ]);
        }
        return $order;
    }


    /**
     * Create Order
     *
     * @return [json] Order
     */
    public function getBlog(Request $request)
    {
        try {
            if ($request->blog_id != null) {
                $blog_id = $request->blog_id;
                $blog = Blog::with('comments', 'category', 'author')->find($blog_id);
                // get previous user id
                $previous_id = Blog::where('id', '<', $blog_id)->max('id');
                $previous = Blog::find($previous_id);

                // get next user id
                $next_id = Blog::where('id', '>', $blog_id)->min('id');
                $next = Blog::find($next_id);

                return response()->json(['status' => 200, 'result' => $blog, 'next' => $next_id, 'previous' => $previous_id]);
            }
            $blog = Blog::has('category')->with('comments')->OrderBy('created_at', 'desc')->paginate(10);
            return response()->json(['status' => 200, 'result' => $blog]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Blog By Category
     *
     * @return [json] Blog List
     */
    public function getBlogByCategory(Request $request)
    {
        try {
            $category = Category::find((int)$request->category_id);
            if ($category != null) {
                $blog = $category->blogs()->paginate(10);
                return response()->json(['status' => 200, 'result' => $blog]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => "Invalid request"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Blog By Tag
     *
     * @return [json] Blog List
     */
    public function getBlogByTag(Request $request)
    {
        try {
            $tag = Tag::find((int)$request->tag_id);
            if ($tag != "") {
                $blog = $tag->blogs()->paginate(10);
                return response()->json(['status' => 200, 'result' => $blog]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => "Invalid request"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Blog Store Comment
     *
     * @return [json] Success Message
     */
    public function addBlogComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['comment' => 'required|min:3']);
            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }
            $blog = Blog::find($request->blog_id);
            if ($blog != null) {
                $blogcooment = new BlogComment($request->all());
                $blogcooment->name = auth()->user()->full_name;
                $blogcooment->email = auth()->user()->email;
                $blogcooment->comment = $request->comment;
                $blogcooment->blog_id = $blog->id;
                $blogcooment->user_id = auth()->user()->id;
                $blogcooment->save();
                return response()->json(['status' => 200, 'result' => null]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => "Invalid request"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Blog Delete Comment
     *
     * @return [json] Success Message
     */
    public function deleteBlogComment(Request $request)
    {
        try {
            $comment = BlogComment::find((int)$request->comment_id);
            if (auth()->user()->id == $comment->user_id) {
                $comment->delete();
                return response()->json(['status' => 200, 'result' => null]);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => "Invalid request"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Forums home
     *
     * @return [json] forum object
     */

    public function getForum(Request $request)
    {
        try {
            $pagination_results = config('chatter.paginate.num_of_results');

            $discussions = Models::discussion()->with('user')->with(['post', 'post.user'])->with('postsCount')->with('category')->orderBy(config('chatter.order_by.discussions.order'), config('chatter.order_by.discussions.by'));
            if (isset($slug)) {
                $category = Models::category()->where('slug', '=', $slug)->first();

                if (isset($category->id)) {
                    $current_category_id = $category->id;
                    $discussions = $discussions->where('chatter_category_id', '=', $category->id);
                } else {
                    $current_category_id = null;
                }
            }

            $discussions = $discussions->paginate($pagination_results);

            $categories = Models::category()->get();

            $result = [
                'discussions' => $discussions,
                'categories' => $categories,
            ];

            return response()->json(['status' => 200, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create Discussion
     *
     * @return [json] success message
     */

    public function createDiscussion(Request $request)
    {
        try {
            $request->request->add(['body_content' => strip_tags($request->body)]);

            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:255',
                'body_content' => 'required|min:10',
                'chatter_category_id' => 'required',
            ], [
                'title.required' => trans('chatter::alert.danger.reason.title_required'),
                'title.min' => [
                    'string' => trans('chatter::alert.danger.reason.title_min'),
                ],
                'title.max' => [
                    'string' => trans('chatter::alert.danger.reason.title_max'),
                ],
                'body_content.required' => trans('chatter::alert.danger.reason.content_required'),
                'body_content.min' => trans('chatter::alert.danger.reason.content_min'),
                'chatter_category_id.required' => trans('chatter::alert.danger.reason.category_required'),
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }

            Event::dispatch(new ChatterBeforeNewDiscussion($request, $validator));
            if (function_exists('chatter_before_new_discussion')) {
                chatter_before_new_discussion($request, $validator);
            }

            $user_id = Auth::user()->id;

            if (config('chatter.security.limit_time_between_posts')) {
                if ($this->notEnoughTimeBetweenDiscussion()) {
                    $minutes = trans_choice('chatter::messages.words.minutes', config('chatter.security.time_between_posts'));

                    return response()->json(['status' => '100', 'result' => trans('chatter::alert.danger.reason.prevent_spam', [
                        'minutes' => $minutes,
                    ])]);
                }
            }

            // *** Let's gaurantee that we always have a generic slug *** //
            $slug = str_slug($request->title, '-');

            $discussion_exists = Models::discussion()->where('slug', '=', $slug)->withTrashed()->first();
            $incrementer = 1;
            $new_slug = $slug;
            while (isset($discussion_exists->id)) {
                $new_slug = $slug . '-' . $incrementer;
                $discussion_exists = Models::discussion()->where('slug', '=', $new_slug)->withTrashed()->first();
                $incrementer += 1;
            }

            if ($slug != $new_slug) {
                $slug = $new_slug;
            }

            $new_discussion = [
                'title' => $request->title,
                'chatter_category_id' => $request->chatter_category_id,
                'user_id' => $user_id,
                'slug' => $slug,
                'color' => '#0c0919',
            ];

            $category = Models::category()->find($request->chatter_category_id);
            if (!isset($category->slug)) {
                $category = Models::category()->first();
            }

            $discussion = Models::discussion()->create($new_discussion);

            $new_post = [
                'chatter_discussion_id' => $discussion->id,
                'user_id' => $user_id,
                'body' => $request->body,
            ];

            if (config('chatter.editor') == 'simplemde'):
                $new_post['markdown'] = 1;
            endif;

            // add the user to automatically be notified when new posts are submitted
            $discussion->users()->attach($user_id);

            $post = Models::post()->create($new_post);

            if ($post->id) {
                Event::dispatch(new ChatterAfterNewDiscussion($request, $discussion, $post));
                if (function_exists('chatter_after_new_discussion')) {
                    chatter_after_new_discussion($request);
                }

                return response()->json(['status' => 200, 'result' => null]);
            } else {
                return response()->json(['status' => 100, 'result' => 'Not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Create Response for Discussion
     *
     * @return [json] success message
     */
    public function storeResponse(Request $request)
    {
        try {
            $stripped_tags_body = ['body' => strip_tags($request->body)];
            $validator = Validator::make($stripped_tags_body, [
                'body' => 'required|min:10',
            ], [
                'body.required' => trans('chatter::alert.danger.reason.content_required'),
                'body.min' => trans('chatter::alert.danger.reason.content_min'),
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }

            Event::dispatch(new ChatterBeforeNewResponse($request, $validator));
            if (function_exists('chatter_before_new_response')) {
                chatter_before_new_response($request, $validator);
            }

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }


            $request->request->add(['user_id' => Auth::user()->id]);

            if (config('chatter.editor') == 'simplemde'):
                $request->request->add(['markdown' => 1]);
            endif;

            $new_post = Models::post()->create($request->all());

            $discussion = Models::discussion()->find($request->chatter_discussion_id);

            $category = Models::category()->find($discussion->chatter_category_id);
            if (!isset($category->slug)) {
                $category = Models::category()->first();
            }

            if ($new_post->id) {
                $discussion->last_reply_at = $discussion->freshTimestamp();
                $discussion->save();

                Event::dispatch(new ChatterAfterNewResponse($request, $new_post));
                if (function_exists('chatter_after_new_response')) {
                    chatter_after_new_response($request);
                }

                // if email notifications are enabled
                if (config('chatter.email.enabled')) {
                    // Send email notifications about new post
                    $this->sendEmailNotifications($new_post->discussion);
                }


                return response()->json(['status' => 200, 'result' => null, 'message' => trans('chatter::alert.success.reason.submitted_to_post')]);
            } else {
                return response()->json(['status' => 100, 'result' => null, 'message' => trans('chatter::alert.danger.reason.trouble')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Update the Response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return [json] success message
     */
    public function updateResponse(Request $request)
    {
        try {
            $id = $request->post_id;
            $stripped_tags_body = ['body' => strip_tags($request->body)];
            $validator = Validator::make($stripped_tags_body, [
                'body' => 'required|min:10',
            ], [
                'body.required' => trans('chatter::alert.danger.reason.content_required'),
                'body.min' => trans('chatter::alert.danger.reason.content_min'),
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 100, 'result' => null,
                    'message' => implode(",", $validator->errors()->all()),
                ]);
            }

            $post = Models::post()->find($id);
            if (!Auth::guest() && (Auth::user()->id == $post->user_id)) {
                if ($post->markdown) {
                    $post->body = strip_tags($request->body);
                } else {
                    $post->body = Purifier::clean($request->body);
                }
                $post->save();

                $discussion = Models::discussion()->find($post->chatter_discussion_id);

                $category = Models::category()->find($discussion->chatter_category_id);
                if (!isset($category->slug)) {
                    $category = Models::category()->first();
                }

                return response()->json(['status' => 200, 'result' => null, 'message' => trans('chatter::alert.success.reason.updated_post')]);
            } else {
                return response()->json(['status' => 100, 'result' => null, 'message' => trans('chatter::alert.danger.reason.update_post')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete Response.
     *
     * @param string $id
     * @param \Illuminate\Http\Request
     *
     * @return [json] success message
     */
    public function deleteResponse(Request $request)
    {
        try {
            $id = $request->post_id;
            $post = Models::post()->with('discussion')->findOrFail($id);
            if ($request->user()->id !== (int)$post->user_id) {
                return response()->json(['status' => 100, 'result' => null, 'message' => trans('chatter::alert.danger.reason.destroy_post')]);
            }
            if ($post->discussion->posts()->oldest()->first()->id === $post->id) {
                if (config('chatter.soft_deletes')) {
                    $post->discussion->posts()->delete();
                    $post->discussion()->delete();
                } else {
                    $post->discussion->posts()->forceDelete();
                    $post->discussion()->forceDelete();
                }
                return response()->json(['status' => 200, 'result' => null, 'message' => trans('chatter::alert.success.reason.destroy_post')]);
            }
            $post->delete();
            return response()->json(['status' => 200, 'result' => null, 'message' => trans('chatter::alert.success.reason.destroy_from_discussion')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get Conversations.
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] messages
     */

    public function getMessages(Request $request)
    {
        try {
            $thread = "";
            $teachers = User::role('teacher')->select('id', 'first_name', 'last_name', 'email', 'avatar_type', 'avatar_location')->get();
            auth()->user()->load('threads.messages.user');
            $threads = auth()->user()->threads;
            $threads = $threads->each(function ($item, $key) {
                $user = $item->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user;
                $item->participant_name = $user->name;
                $item->participant_image = $user->image;
                $item->messages->each(function ($item1, $key1) {
                    $item1->sender_id = $item1->user_id;
                    $item1->sender = $item1->user;
                    unset($item1->user_id);
                    unset($item1->user);
                });
            });
            if (request()->has('thread') && ($request->thread != null)) {
                if (request('thread')) {
                    $thread = auth()->user()->threads()
                        ->where('chat_threads.id', '=', $request->thread)
                        ->first();
                    if ($thread == "") {
                        return response()->json(['status' => 100, 'result' => null, 'message' => 'Not found']);
                    }
                    $thread->markAsRead(auth()->user()->id);
                }
            }
            return response()->json(['status' => 200, 'result' => $threads,
                'teachers' => $teachers,
                'thread' => $thread]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Create Message
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Success Message
     */
    public function composeMessage(Request $request)
    {
        try {
            $recipients = $request->data['recipients'];
            $thread = Thread::create();
            $message = Message::create([
                'thread_id' => $thread->id,
                'user_id' => auth()->user()->id,
                'body' => $request->data['message'],
            ]);
            $participant = Participant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => auth()->user()->id,
            ]);
            $participant->last_read = Carbon::now();
            $participant->save();
            if ($recipients) {
                $thread->addParticipant($recipients);
            }

            $thread = auth()->user()->threads()
                ->where('chat_threads.id', '=', $message->thread_id)
                ->first();
            $user = $thread->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user;
            $thread->participant_name = $user->name;
            $thread->participant_image = $user->image;
            $thread->messages->each(function ($item1, $key1) {
                $item1->sender_id = $item1->user_id;
                $item1->sender = $item1->user;
                unset($item1->user_id);
                unset($item1->user);
            });
            return response()->json(['status' => 200, 'result' => $thread]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Reply Message
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Success Message
     */
    public function replyMessage(Request $request)
    {
        try {
            $message = Message::create([
                'thread_id' => $request->thread_id,
                'user_id' => auth()->user()->id,
                'body' => $request->message,
            ]);

            $participant = Participant::firstOrCreate([
                'thread_id' => $request->thread_id,
                'user_id' => auth()->user()->id,
            ]);

            $participant->last_read = Carbon::now();
            $participant->save();

            $thread = auth()->user()->threads()
                ->where('chat_threads.id', '=', $request->thread_id)
                ->first();
            $user = $thread->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user;
            $thread->participant_name = $user->name;
            $thread->participant_image = $user->image;
            $thread->messages->each(function ($item1, $key1) {
                $item1->sender_id = $item1->user_id;
                $item1->sender = $item1->user;
                unset($item1->user_id);
                unset($item1->user);
            });
            return response()->json(['status' => 200, 'result' => $thread]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get Unread Messages
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Success Message
     */
    public function getUnreadMessages(Request $request)
    {
        try {
            $unreadMessageCount = auth()->user()->unreadMessagesCount();
            $unreadThreads = [];
            foreach (auth()->user()->threads as $item) {
                if ($item->userUnreadMessagesCount(auth()->user()->id)) {
                    $data = [
                        'thread_id' => $item->id,
                        'message' => str_limit($item->messages()->orderBy('id', 'desc')->first()->body, 35),
                        'unreadMessagesCount' => $item->userUnreadMessagesCount(auth()->user()->id),
                        'title' => $item->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user->name
                    ];
                    $unreadThreads[] = $data;
                }
            }
            return response()->json(['status' => 200, 'unreadMessageCount' => $unreadMessageCount, 'result' => $unreadThreads]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get My Certificates
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] certificates object
     */
    public function getMyCertificates()
    {
        try {
            $certificates = auth()->user()->certificates;
            return response()->json(['status' => 200, 'result' => $certificates]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get My Courses / Bundles / Purchases
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] certificates object
     */
    public function getMyPurchases()
    {
        try {
            $purchased_courses = auth()->user()->purchasedCourses();
            $purchased_bundles = auth()->user()->purchasedBundles();
            return response()->json(['status' => 200, 'result' => ['courses' => $purchased_courses, 'bundles' => $purchased_bundles]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Get My Account
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Loggedin user object
     */
    public function getMyAccount()
    {
        try {
            $user = auth()->user();
            return response()->json(['status' => 200, 'result' => $user]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Update My Account
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Update account
     */
    public function updateMyAccount(Request $request)
    {
        try {
            $fieldsList = [];
            if (config('registration_fields') != null) {
                $fields = json_decode(config('registration_fields'));

                foreach ($fields as $field) {
                    $fieldsList[] = '' . $field->name;
                }
            }
            $output = $this->userRepository->update(
                $request->user()->id,
                $request->only('first_name', 'last_name', 'dob', 'phone', 'gender', 'address', 'city', 'pincode', 'state', 'country', 'language_code', 'avatar_type', 'avatar_location'),
                $request->has('avatar_location') ? $request->avatar_location : false, true
            );

            // E-mail address was updated, user has to reconfirm
            if (is_array($output) && $output['email_changed']) {
                auth()->logout();
                return response()->json(['status' => 200, 'result' => null, 'message' => __('strings.frontend.user.email_changed_notice')]);
            }
            $user = $this->userRepository->getById($request->user()->id);

            return response()->json(['status' => 200, 'result' => ['user' => $user], 'message' => __('strings.frontend.user.profile_updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update Password
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Update password
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = auth()->user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->update(['password' => $request->password]);
            }
            return response()->json(['status' => 200, 'result' => null, 'message' => __('strings.frontend.user.password_updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Update Pages (About-us)
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] Update password
     */
    public function getPage()
    {
        try {
            $page = Page::where('slug', '=', request('page'))
                ->where('published', '=', 1)->first();
            if ($page != "") {
                return response()->json(['status' => 200, 'result' => $page]);
            }
            return response()->json(['status' => 100, 'result' => null]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Subscribe newsletter
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] response
     */

    public function subscribeNewsletter(Request $request)
    {
        if (config('mail_provider') != null && config('mail_provider') == "mailchimp") {
            try {
                if (!Newsletter::isSubscribed($request->email)) {
                    if (config('mailchimp_double_opt_in')) {
                        Newsletter::subscribePending($request->email);
                        $message = "We've sent you an email, Check your mailbox for further procedure.";
                    } else {
                        Newsletter::subscribe($request->email);
                        $message = "You've subscribed successfully";
                    }
                    return response()->json(['status' => 200, 'result' => null, 'message' => $message]);
                } else {
                    $message = "Email already exist in subscription list";
                    return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
                }
            } catch (\Exception $e) {
                \Log::info($e->getMessage());
                $message = "Something went wrong, Please try again Later";
                return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
            }
        } elseif (config('mail_provider') != null && config('mail_provider') == "sendgrid") {
            try {
                $apiKey = config('sendgrid_api_key');
                $sg = new \SendGrid($apiKey);
                $query_params = json_decode('{"page": 1, "page_size": 1}');
                $response = $sg->client->contactdb()->recipients()->get(null, $query_params);
                if ($response->statusCode() == 200) {
                    $users = json_decode($response->body());
                    $emails = [];
                    foreach ($users->recipients as $user) {
                        array_push($emails, $user->email);
                    }
                    if (in_array($request->email, $emails)) {
                        $message = "Email already exist in subscription list";
                        return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
                    } else {
                        $request_body = json_decode(
                            '[{
                             "email": "' . $request->email . '",
                             "first_name": "",
                             "last_name": ""
                              }]'
                        );
                        $response = $sg->client->contactdb()->recipients()->post($request_body);
                        if ($response->statusCode() != 201 || (json_decode($response->body())->new_count == 0)) {
                            $message = "Email already exist in subscription list";
                            return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
                        } else {
                            $recipient_id = json_decode($response->body())->persisted_recipients[0];
                            $list_id = config('sendgrid_list');
                            $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
                            if ($response->statusCode() == 201) {
                                return response()->json(['status' => 200, 'result' => null, 'message' => "You've subscribed successfully"]);
                            } else {
                                $message = "Check your email and try again";
                                return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::info($e->getMessage());
                $message = "Something went wrong, Please try again Later";
                return response()->json(['status' => 100, 'result' => null, 'message' => $message]);
            }
        }
        return response()->json(['status' => 100, 'result' => null, 'message' => 'Please setup mail provider in Admin dashboard on server']);
    }


    /**
     * Get Offers
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] response
     */
    public function getOffers()
    {
        try {
            $coupons = Coupon::where('status', '=', 1)->get();
            return ['status' => 200, 'result' => $coupons];
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Apply Coupon
     *
     * @param \Illuminate\Http\Request
     *
     * @return [json] response
     */
    public function applyCouponOld(Request $request)
    {
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');

        $coupon = $request->coupon;
        $coupon = Coupon::where('code', '=', $coupon)
            ->where('status', '=', 1)
            ->first();
        if ($coupon != null) {
            Cart::session(auth()->user()->id)->clearCartConditions();
            Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
            Cart::session(auth()->user()->id)->removeConditionsByType('tax');

            $ids = Cart::session(auth()->user()->id)->getContent()->keys();
            $course_ids = [];
            $bundle_ids = [];
            foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
                if ($item->attributes->type == 'bundle') {
                    $bundle_ids[] = $item->id;
                } else {
                    $course_ids[] = $item->id;
                }
            }
            $courses = new Collection(Course::find($course_ids));
            $bundles = Bundle::find($bundle_ids);
            $courses = $bundles->merge($courses);

            $total = $courses->sum('price');
            $isCouponValid = false;

            if ($coupon->per_user_limit > $coupon->useByUser()) {
                $isCouponValid = true;
                if (($coupon->min_price != null) && ($coupon->min_price > 0)) {
                    if ($total >= $coupon->min_price) {
                        $isCouponValid = true;
                    }
                } else {
                    $isCouponValid = true;
                }
            }

            if ($coupon->expires_at != null) {
                if (Carbon::parse($coupon->expires_at) >= Carbon::now()) {
                    $isCouponValid = true;
                } else {
                    $isCouponValid = false;
                }
            }

            if ($isCouponValid == true) {
                $type = null;
                if ($coupon->type == 1) {
                    $type = '-' . $coupon->amount . '%';
                } else {
                    $type = '-' . $coupon->amount;
                }

                $condition = new CartCondition(array(
                    'name' => $coupon->code,
                    'type' => 'coupon',
                    'target' => 'total', // this condition will be applied to cart's subtotal when getSubTotal() is called.
                    'value' => $type,
                    'order' => 1
                ));

                Cart::session(auth()->user()->id)->condition($condition);
                //Apply Tax
                $taxData = $this->applyTax('subtotal');
                return ['status' => 200, 'result' => null];
            }
        }
        return ['status' => 100, 'result' => null, 'message' => trans('labels.frontend.cart.invalid_coupon')];
    }

    /**
     * @throws Exception
     */
    protected function isCouponValid($couponId)
    {
        $coupon = Coupon::query()->find($couponId);
        if (is_null($coupon))
            throw new Exception('Invalid Coupon Code');
        if ($coupon->exires_at != null && Carbon::parse($coupon->expires_at) < Carbon::now())
            throw new Exception('Coupon is Expired');


    }

    public function applyCoupon(Request $request)
    {
        try {
            $this->removeCoupon($request);
            $data = [];
            $coupon = $request->coupon;
            $coupon = Coupon::where('code', '=', $coupon)
                ->where('status', '=', 1)
                ->first();
            $cart = Cart::session(auth()->user()->id);
            $isCouponValid = false;

            if ($coupon != null) {
                $total = $cart->getTotal();
                $total = (float)number_format($total, 2, '.', '');

                if ($coupon->per_user_limit > $coupon->useByUser()) {
                    $isCouponValid = true;
                    if (($coupon->min_price != null) && ($coupon->min_price > 0)) {
                        if ($total >= $coupon->min_price) {
                            $isCouponValid = true;
                        } else {
                            throw new Exception('Cart Total should be Greater than or equal to ' . $coupon->min_price . ' to Apply this Promo code.');
                        }
                    }
                }
                if ($coupon->expires_at != null) {
                    if (Carbon::parse($coupon->expires_at) >= Carbon::now()) {
                        $isCouponValid = true;
                    } else {
                        throw new Exception('Coupon is Expired');
                    }
                }
                if ($isCouponValid == false) {
                    throw new Exception('Coupon is Invalid');
                }

                if ($coupon->type == 1) {
                } else {
                    if ($coupon->amount >= $total) {
                        throw new Exception('Coupon Amount should be less then Cart Sub Total.');
                    }
                }
                $this->addCouponToCartSession($coupon->id);

                $res = $this->getCartDetailArray();
                $res['message'] = 'Coupon Applied Successfully';
                $res['result']['final_total'] = $res['result']['total']; // (float)number_format(($total - $discount) + $tax_amount, 2, '.', '');
                return $res;
            }
            return ['status' => 100, 'result' => null, 'message' => 'Please apply a valid coupon'];
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    public function orderConfirmation(Request $request)
    {
        try {
            $data = [];
            $items = [];
            $total = 0;

            $cartDetailArray = $this->getCartDetailArray()['result'];
            $cartObj = Cart::session(auth()->user()->id);
            if ($cartObj->getContent()->count() <= 0) {
                throw new Exception("You don't have any item in your cart.");
            }
            $dataItems = [];
            foreach ($cartDetailArray['courses'] as $course) {
                $dataItems[] = [
                    'type' => 'course',
                    'id' => $course['id'],
                    'price' => $course['price']
                ];
            }
            foreach ($cartDetailArray['bundles'] as $course) {
                $dataItems[] = [
                    'type' => 'bundle',
                    'id' => $course['id'],
                    'price' => $course['price']
                ];
            }
            $data = [
                'coupon_data' => $cartDetailArray['coupon'],
                'final_total' => $cartDetailArray['total'],
                'subtotal' => $cartDetailArray['subtotal'],
                'tax_data' => $cartDetailArray['tax'],
                'data' => $dataItems,
            ];
            $order = $this->makeOrder($data);
            $data['order'] = $order;
            return ['status' => 200, 'result' => $data];

            if (count($request->data) > 0) {
                foreach ($request->data as $item) {
                    $id = $item['id'];
                    $price = $item['price'];
                    if ($item['type'] == 'bundle') {
                        $status = false;
                        $bundle = Bundle::where('id', '=', $item['id'])
                            ->where('price', '=', $item['price'])
                            ->where('published', '=', 1)
                            ->first();
                        if ($bundle) {
                            $status = true;
                            $total = $total + $bundle->price;
                        }
                        $bundle = [
                            'id' => $id,
                            'type' => 'bundle',
                            'price' => $price,
                            'status' => $status
                        ];
                        array_push($items, $bundle);
                    } else {
                        $status = false;
                        $course = Course::where('id', '=', $id)
                            ->where('price', '=', $price)
                            ->where('published', '=', 1)
                            ->first();
                        if ($course) {
                            $status = true;
                            $total = $total + $course->price;
                        }
                        $course = [
                            'id' => $id,
                            'type' => 'course',
                            'price' => $price,
                            'status' => $status
                        ];
                        array_push($items, $course);
                    }
                }
                $data['data'] = $items;
                /*$a = (float)$request->total;
                $b = floatval($total);
                if (abs(($a - $b) / $b) < 0.00001) {*/

                if (floatNumber($request->total) == floatNumber($total)) {
                    $coupon = $request->coupon;
                    $discount = 0;
                    $tax_amount = 0;
                    $coupon = Coupon::where('code', '=', $coupon)
                        ->where('status', '=', 1)
                        ->first();

                    $type = null;
                    if ($coupon) {
                        if ($coupon->type == 1) {
                            $discount = $total * $coupon->amount / 100;
                        } else {
                            $discount = $coupon->amount;
                        }
                        //$data['discounted_total'] = (float)number_format($total - $discount,2);
                        $data['coupon_data'] = $coupon->toArray();
                        $data['coupon_data']['total_coupon_discount'] = (float)number_format($discount, 2, '.', '');
                        $discount = $data['coupon_data']['total_coupon_discount'];
                    } else {
                        $data['coupon_data'] = false;
                    }
                    $data['subtotal'] = number_format((float)$total, 2, '.', '');
                    $total = number_format($total - $discount, 2, '.', '');

                    //Apply Tax
                    $data['tax_data'] = $this->applyTax($total);
                    if ($data['tax_data'] != 0) {
                        $tax_amount = $data['tax_data']['total_tax'];
                    }
                    $data['final_total'] = number_format((float)$total + (float)$tax_amount, 2, '.', '');
                    $order = $this->makeOrder($data);
                    $data['order'] = $order;
                    return ['status' => 200, 'result' => $data];
                } else {
                    return ['status' => 100, 'message' => 'Total Mismatch', 'result' => $data];
                }
            }
            return ['status' => 100, 'result' => null, 'message' => 'Add Items to Cart before applying coupon'];
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    public function removeCoupon(Request $request)
    {
        //Obsolete
        try {
            Cart::session(auth()->user()->id)->clearCartConditions();
            Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
            Cart::session(auth()->user()->id)->removeConditionsByType('tax');

            $course_ids = [];
            $bundle_ids = [];
            foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
                if ($item->attributes->type == 'bundle') {
                    $bundle_ids[] = $item->id;
                } else {
                    $course_ids[] = $item->id;
                }
            }
            $courses = new Collection(Course::find($course_ids));
            $bundles = Bundle::find($bundle_ids);
            $courses = $bundles->merge($courses);

            //Apply Tax
            $this->applyTax('subtotal');

            // return ['status' => 200, 'result' => null];
            $res = $this->getCartDetailArray();
            $res['message'] = 'Coupon Removed Successfully';
            return $res;
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    private function notEnoughTimeBetweenDiscussion()
    {
        $user = Auth::user();

        $past = Carbon::now()->subMinutes(config('chatter.security.time_between_posts'));

        $last_discussion = Models::discussion()->where('user_id', '=', $user->id)->where('created_at', '>=', $past)->first();

        if (isset($last_discussion)) {
            return true;
        }

        return false;
    }

    private function sendEmailNotifications($discussion)
    {
        $users = $discussion->users->except(Auth::user()->id);
        foreach ($users as $user) {
            \Mail::to($user)->queue(new ChatterDiscussionUpdated($discussion));
        }
    }

    public function getPaymentModsList()
    {
        return [
            1 => [
                'name' => 'Stripe',
                'is_active' => true,
            ],
            2 => [
                'name' => 'Paypal',
                'is_active' => true,
            ],
            3 => [
                'name' => 'Offline',
                'is_active' => true,
            ],
            4 => [
                'name' => 'InstaMojo',
                'is_active' => true,
            ],
            5 => [
                'name' => 'Razorpay',
                'is_active' => true,
            ],
            6 => [
                'name' => 'CashFree',
                'is_active' => false,
            ],
            7 => [
                'name' => 'PayUMoney',
                'is_active' => true,
            ]
        ];
    }

    public function getConfigs()
    {

        $data = getCurrency(config('app.currency'));
        $user = User::query()->find(request()->user_id ?? null);
        if (!is_null($user)) {
            $data['default_language'] = $user->language_code['id'];
        } else {
            $data['default_language'] = 'en';
        }
        $data['languages_display_type'] = [];
        $locales = Locale::query()->select('name', 'display_type', 'short_name')->get();
        foreach ($locales as $locale) {
            $data['languages_display_type'][] = [
                'id' => $locale->short_name,
                'name' => $locale->name,
                'is_rtl' => $locale->display_type == "rtl",
            ];
        }
        $data['current_language'] = collect($data['languages_display_type'])->where('id', $data['default_language'])->first();
        if (!is_null($user)) {
            $data['user_default_language'] = $user->language_code;
        } else {
            $data['user_default_language'] = $data['current_language'];
        }
        $data['payment_modes'] = $this->getPaymentModsList();
        $data['social_platforms'] = Config::query()->whereIn('key', ['twitter', 'google', 'facebook'])->select('key', 'value')->get()->toArray();
        return response()->json(['status' => 200, 'result' => $data]);
    }

    private function applyTax($total)
    {
        //Apply Conditions on Cart
        $total = Cart::session(auth()->user()->id)->getTotal();

        $taxes = Tax::where('status', '=', 1)->get();
        if (count($taxes) > 0) {
            $taxData = [];
            $taxDetails = [];
            $amounts = [];
            foreach ($taxes as $tax) {

                $amount = $total * ((float)$tax->rate / 100);
                $amounts[] = $amount;
                $taxMeta = [
                    'name' => (float)$tax->rate . '% ' . $tax->name,
                    'amount' => (float)$amount
                ];
                array_push($taxDetails, $taxMeta);
            }
            $taxData['taxes'] = $taxDetails;
            $taxData['total_tax'] = array_sum($amounts);
            return $taxData;
        }
        return false;
    }

    /**
     * @throws InvalidConditionException
     * @throws Exception
     */
    public function addCouponToCartSession($coupon_id)
    {
        $coupon = Coupon::query()->findOrFail($coupon_id);
        $value = $coupon->type == 2 ? '-' . $coupon->amount : '-' . $coupon->amount . '%';
        $condition = new CartCondition(array(
            'name' => $coupon->code,
            'type' => 'coupon',
            'target' => 'total', // this condition will be applied to cart's subtotal when getSubTotal() is called.
            'value' => $value,
            'attributes' => array( // attributes field is optional
                'description' => $coupon->description,
                'coupon_id' => $coupon_id
            )
        ));
        Cart::session(auth()->user()->id)->condition($condition);
    }

    public function subscriptionsPlans()
    {
        try {
            $plans = StripePlan::orderBy('id', 'desc')->get();
            return response()->json(['status' => 200, 'result' => ['plans' => $plans]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    public function mySubscription()
    {
        try {
            $user = auth()->user();
            $activePlan = $user->subscribed('default') ? StripePlan::where('plan_id', $user->subscription()->stripe_plan)->first() ?? optional() : optional();
            $subscribed_courses = $user->subscribedCourse();
            $subscribed_bundles = $user->subscribedBundles();
            return response()->json(['status' => 200, 'result' => ['active_plan' => new SubscribedResource($activePlan), 'course' => $subscribed_courses, 'bundles' => $subscribed_bundles]]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    public function checkSubscription(Request $request)
    {
        $user = auth()->user();
        if ($user->subscription('default')) {
            if (!in_array($request->course_id, $user->getPurchasedCoursesIds())) {
                if (in_array($request->course_id, $user->getSubscribedCoursesIds())) {
                    if ($user->subscription()->ended()) {
//                        return redirect()->back()->withFlashDanger(trans('alerts.frontend.course.subscription_plan_expired'));
                        return response()->json(['status' => 100, 'result' => null, 'message' => 'You cann\'t access because you subscription plan ended.']);
                    }
                    if ($user->subscription()->cancelled() && !$user->subscription()->onGracePeriod()) {
                        return response()->json(['status' => 200, 'result' => null, 'message' => 'You cann\'t access because you subscription plan ended.']);
                    }
                }
            }
        }

    }

    public function subscribeBundleOrCourse(Request $request)
    {
        try {
            $user = auth()->user();
            if ($user->subscription('default')) {
                if ($user->subscription()->ended()) {
                    return response()->json(['status' => 100, 'result' => null, 'message' => 'You cann\'t subscribed because you subscription plan ended.']);
                }
                if ($user->subscription()->cancelled() && !$user->subscription()->onGracePeriod()) {
                    return response()->json(['status' => 100, 'result' => null, 'You cann\'t subscribed because you subscription plan cancelled.']);
                }

                if ($user->subscription()->active()) {
                    $plan = $this->getPlan($user->subscription()->stripe_plan);
                    if ($request->type == 'course') {
                        if ($plan->course == 99) {
                            return response()->json(['status' => 100, 'result' => null, 'message' => 'Your Subscription Plan Not Any Course Access.']);
                        }
                        if ($plan->course != 0 && $user->subscribedCourse()->count() >= $plan->course) {
                            return response()->json(['status' => 100, 'result' => null, 'message' => 'Your Subscription Plan Course Limit Over.']);
                        }
                    } else {
                        if ($plan->bundle == 99) {
                            return response()->json(['status' => 100, 'result' => null, 'message' => 'Your Subscription Plan Not Any Bundle Access.']);
                        }
                        if ($plan->bundle != 0 && $user->subscribedBundles()->count() >= $plan->bundle) {
                            return response()->json(['status' => 100, 'result' => null, 'message' => 'Your Subscription Plan Bundle Limit Over.']);
                        }
                    }
                }

                $order = new Order();
                $order->user_id = $user->id;
                $order->reference_no = str_random(8);
                $order->amount = 0;
                $order->status = 1;
                $order->payment_type = 0;
                $order->order_type = 1;
                $order->save();
                if ($request->type == 'course') {
                    $type = Course::class;
                    $id = $request->id;
                } else {
                    $type = Bundle::class;
                    $id = $request->id;

                }

                $order->items()->create([
                    'item_id' => $id,
                    'item_type' => $type,
                    'price' => 0,
                    'type' => 1
                ]);

                foreach ($order->items as $orderItem) {
                    //Bundle Entries
                    if ($orderItem->item_type == Bundle::class) {
                        foreach ($orderItem->item->courses as $course) {
                            $course->students()->attach($order->user_id);
                        }
                    }
                    $orderItem->item->students()->attach($order->user_id);
                }
                return response()->json(['status' => 200, 'result' => null, 'message' => 'Subscribe Successfully.']);
            }
            return response()->json(['status' => 100, 'result' => null, 'message' => 'You cann\'t subscribed any plan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @param $planId
     * @return mixed
     */
    private function getPlan($planId)
    {
        return StripePlan::where('plan_id', $planId)->firstorfail();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWishlist(Request $request)
    {
        try {
            if (!Wishlist::where('course_id', $request->course_id)->where('user_id', auth()->user()->id)->first()) {
                Wishlist::create([
                    'user_id' => auth()->user()->id,
                    'course_id' => $request->course_id,
                    'price' => $request->price
                ]);
                return response()->json(['status' => 200, 'result' => null, 'message' => trans('alerts.frontend.wishlist.added')]);
            } else {
                return response()->json(['status' => 100, 'result' => null, 'message' => trans('alerts.frontend.wishlist.exist')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function wishlist()
    {
        try {
            $wishlists = Wishlist::query()->with(['course'])->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate();
            return response()->json(['status' => 200, 'result' => $wishlists]);
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }
}
