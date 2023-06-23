@extends('frontend.layouts.app' . config('theme_layout'))
@section('title', trans('labels.frontend.course.bundles') . ' | ' . app_name())

@section('content')

    <!-- start banner
                =============================== -->

    <div class="banner">
        <div>
            <div class="image-banner">
            </div>
        </div>
    </div>

    <!-- Start of course section
                        ============================================= -->
    <section id="course-page" class="course-page-section">
        <div class="container">
            <div class="genius-post-item">
                <div class="tab-container">
                    <div id="tab1" class="tab-content-1 pt35">
                        <div class="best-course-area best-course-v2">
                            <div class="row-vitrine">
                                @if (count($bundles) > 0)
                                    @foreach ($bundles as $bundle)
                                        <div class="category-item">
                                            <div class="best-course-pic-text-vitrine relative-position-vitrine">
                                                <a href="{{ route('bundles.show', [$bundle->slug]) }}">
                                                    <div class="best-course-pic-vitrine relative-position-vitrine"
                                                        @if ($bundle->course_image != '') style="background-image: url('{{ asset('storage/uploads/' . $bundle->course_image) }}')" @endif>
                                                        <div class="blakish-overlay"></div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

                                        @if(count($bundles) > 0 )
                                        @foreach($bundles as $bundle)
                                     @endforeach
                                        @endif

    <!-- End of course section
                        ============================================= -->
                        @endsection