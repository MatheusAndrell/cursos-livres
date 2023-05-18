<section id="course-category" class="course-category-section">
    <div class="container">
        @if ($course_categories)
            <div class="category-item">
                <div class="row">
                    @php
                        $icon_classes = ['first-icon', 'second-icon', 'third-icon', 'fourth-icon', 'fifth-icon', 'sixth-icon'];
                    @endphp
                    @foreach ($course_categories->take(6) as $index => $category)
                        <div class="col-md-4 category-card-{{ $index }}">
                            <a href="{{ route('courses.category', ['category' => $category->slug]) }}">
                                <div class="category-icon-title text-center category-icon-title-{{ $index }}">

                                </div>
                            </a>
                        </div>
                    @endforeach

                    <!-- /category -->
                </div>
            </div>
        @endif
    </div>
</section>
