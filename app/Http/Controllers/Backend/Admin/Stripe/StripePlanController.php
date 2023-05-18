<?php

namespace App\Http\Controllers\Backend\Admin\Stripe;

use App\Helpers\Payments\Stripe\StripeWrapper;
use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Course;
use App\Models\Stripe\StripePlan;
use App\Models\Stripe\SubscribeCourse;
use App\Models\Stripe\SubscribeBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class StripePlanController extends Controller
{
    private $stripeWrapper;

    /**
     * StripePlanController constructor.
     */
    public function __construct()
    {
        $this->stripeWrapper = new StripeWrapper();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('stripe_plan_access')) {
            return abort(401);
        }
        return view('backend.stripe.plan.index');
    }

    /**
     * Display a listing of Lessons via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $stripePlans = StripePlan::query();

        if ($request->show_deleted == 1) {
            if (!Gate::allows('stripe_plan_delete')) {
                return abort(401);
            }
            $stripePlans = StripePlan::query()->onlyTrashed();
        }


        if (auth()->user()->can('stripe_plan_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('stripe_plan_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('stripe_plan_delete')) {
            $has_delete = true;
        }

        return DataTables::of($stripePlans)
            ->addIndexColumn()
            ->addColumn('actions', function ($stripePlan) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-restore')->with(['route_label' => 'admin.stripe.plans', 'label' => 'id', 'value' => $stripePlan->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.stripe.plans.show', ['plan' => $stripePlan->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.stripe.plans.edit', ['plan' => $stripePlan->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.stripe.plans.destroy', ['plan' => $stripePlan->id])])
                        ->render();
                    $view .= $delete;
                }

                return $view;
            })
            ->addColumn('expire', function ($q) {
                return $q->expire;
            })
            ->rawColumns(['actions','expire'])
            ->make();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('stripe_plan_create')) {
            return abort(401);
        }
        if(!config('services.stripe.secret')) {
            return redirect()->route('admin.stripe.plans.index')->withFlashDanger(__('alerts.backend.stripe_plan.stripe_credentials'));
        }
        $courseArr=[];
        $bundleArr=[];
        $courses = Course::ofTeacher()->pluck('title', 'id');
        $bundles = Bundle::ofTeacher()->pluck('title','id');
        return view('backend.stripe.plan.create',compact('courses','bundles','courseArr','bundleArr'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('stripe_plan_create')) {
            return abort(401);
        }

        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'amount' => 'required',
            'currency' => 'required',
            'interval' => 'required',
        ]);
        $stripeProduct = $this->stripeWrapper->createProduct($request->only('name'));
        $request->request->add(['product' => $stripeProduct->id]);
        $stripePlan = $this->stripeWrapper->createPlan($request->only('amount', 'currency', 'interval', 'product'));
        $request->request->add(['plan_id' => $stripePlan->id]);
        $plan = StripePlan::create($request->all());

        //strip plan course and bundle add to database
        if($request->courses_selected){
            foreach($request->courses_selected as $course)
            {
                $subscribeCourse = new SubscribeCourse();
                $subscribeCourse->stripe_id = $plan->id;
                $subscribeCourse->course_id = $course;
                $subscribeCourse->created_at = date('Y-m-d h:i:s');
                $subscribeCourse->save();
            }
        }
        if($request->bundle_selected){
            foreach($request->bundle_selected as $bundle)
            {
                $subscribeBundle = new SubscribeBundle();
                $subscribeBundle->stripe_id = $plan->id;
                $subscribeBundle->bundle_id = $bundle;
                $subscribeBundle->created_at = date('Y-m-d h:i:s');
                $subscribeBundle->save();
            }
        }
        return redirect()->route('admin.stripe.plans.index')->withFlashSuccess(__('alerts.backend.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Stripe\StripePlan $plan
     * @return \Illuminate\Http\Response
     */
    public function show(StripePlan $plan)
    {
        if (!Gate::allows('stripe_plan_view')) {
            return abort(401);
        }
        return view('backend.stripe.plan.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Stripe\StripePlan $plan
     * @return \Illuminate\Http\Response
     */
    public function edit(StripePlan $plan)
    {
        if (!Gate::allows('stripe_plan_edit')) {
            return abort(401);
        }

        $courses = Course::ofTeacher()->pluck('title', 'id');
        $bundles = Bundle::ofTeacher()->pluck('title','id');
        $courseArr = [];
        if(count($plan->subcribeCourses)>0)
        {
            foreach ($plan->subcribeCourses as $selectedCourse)
            {
                $courseArr[]=$selectedCourse['course_id'];
            }
        }
        $bundleArr = [];
        if(count($plan->subcribeBundle)>0)
        {
            foreach ($plan->subcribeBundle as $selectedbundle)
            {
                $bundleArr[]=$selectedbundle['bundle_id'];
            }
        }
        return view('backend.stripe.plan.edit',compact('plan','courses','bundles','courseArr','bundleArr'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Stripe\StripePlan $plan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StripePlan $plan)
    {

        if (!Gate::allows('stripe_plan_edit')) {
            return abort(401);
        }
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'amount' => 'required',
            'currency' => 'required',
            'interval' => 'required',
        ]);
        $this->stripeWrapper->updateProduct($plan->product,$request->only('name'));
        $plan->update($request->all());
        //strip plan course and bundle add to database
        if($request->courses_selected){
            $subscribeCourseDelete = SubscribeCourse::where('stripe_id',$plan->id);
            $subscribeCourseDelete->delete();
            foreach($request->courses_selected as $course)
            {
                $subscribeCourse = new SubscribeCourse();
                $subscribeCourse->stripe_id = $plan->id;
                $subscribeCourse->course_id = $course;
                $subscribeCourse->created_at = date('Y-m-d h:i:s');
                $subscribeCourse->save();
            }
        }
        if($request->bundle_selected){
            $subscribeBundleDelete = SubscribeBundle::where('stripe_id',$plan->id);
            $subscribeBundleDelete->delete();
            foreach($request->bundle_selected as $bundle)
            {
                $subscribeBundle = new SubscribeBundle();
                $subscribeBundle->stripe_id = $plan->id;
                $subscribeBundle->bundle_id = $bundle;
                $subscribeBundle->created_at = date('Y-m-d h:i:s');
                $subscribeBundle->save();
            }
        }
        return redirect()->route('admin.stripe.plans.index')->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Stripe\StripePlan $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(StripePlan $plan)
    {
        if (!Gate::allows('stripe_plan_delete')) {
            return abort(401);
        }
        $plan->delete();
        return redirect()->route('admin.stripe.plans.index')->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    /**
     * Restore Course from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('stripe_plan_restore')) {
            return abort(401);
        }
        $plan = StripePlan::onlyTrashed()->findOrFail($id);
        $plan->restore();

        return redirect()->route('admin.stripe.plans.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Course from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function permanent($id)
    {
        if (!Gate::allows('stripe_plan_delete')) {
            return abort(401);
        }
        $plan = StripePlan::onlyTrashed()->findOrFail($id);
        $this->stripeWrapper->deletePlan($plan->plan_id);
        $this->stripeWrapper->deleteProduct($plan->product);
        $plan->forceDelete();

        return redirect()->route('admin.stripe.plans.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}


