@extends ('backend.layouts.app')
@php $currentUrl = url()->current();
@endphp
@section('title', __('labels.backend.menu-manager.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endpush
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="page-title mb-0">
                {{ __('labels.backend.menu-manager.title') }}
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('labels.backend.menu-manager.header_text') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ __('labels.backend.menu-manager.instruction_text') }}
                            <div class="sortable" id="menuAccordion">
                                @foreach ($menu_items as $item)
                                    <div class="card" data-id="{{ $item->id }}">
                                        <div class="card-header">
                                            <h5 class="page-title float-left mb-0">{{ $item->label }}</h5>
                                            <div class="float-right">
                                                <a class="btn btn-default btn-sm btn-rounded" data-toggle="collapse" data-target="#menu{{ $item->id }}"
                                                   data-action="collapse"><i class="fas fa-eye"></i> {{ __('labels.backend.menu-manager.button.view_details') }}</a>
                                            </div>
                                        </div>
                                        <div class="card-body collapsed collapse" id="menu{{ $item->id }}" data-parent="#menuAccordion">
                                            <form method="POST"
                                                  action="{{ route('admin.menu-manager.store') }}">
                                                @csrf
                                                <input type="hidden" name="menu_item_id"
                                                       value="{{ $item->id }}">
                                                <div class="form-group">
                                                    <label>{{ __('labels.backend.menu-manager.label.label') }}</label>
                                                    <span class="text-danger">*</span>
                                                    {!! Form::text('label', $item->label ?? old('label'), [
                                                        'class' => 'form-control',
                                                        'required'
                                                    ]) !!}
                                                </div>
                                                <div class="form-group">
                                                    <label>{{ __('labels.backend.menu-manager.label.url') }}</label>
                                                    <span class="text-danger">*</span>
                                                    {!! Form::text('link', $item->link ?? old('link'), [
                                                        'class' => 'form-control',
                                                        'required',
                                                    ]) !!}
                                                </div>
                                                <button type="submit" class="btn btn-success">{{ __('buttons.general.crud.update') }}
                                                </button>
                                                <a href="javascript:;" data-delete_url="{{ route('admin.menu-manager.delete', $item->id) }}" class="btn btn-danger delete_warning"  data-trans-button-cancel="{{__('buttons.general.cancel')}}"
                                                   data-trans-button-confirm="{{__('buttons.general.crud.delete')}}" data-trans-title="{{__('strings.backend.general.are_you_sure')}}">{{ __('buttons.general.crud.delete') }}</a>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <a href="javascript:;" id="updateSortOrder"
                                   class="btn btn-success">{{ __('labels.backend.menu-manager.button.update_sort_order') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Side Section -->
                <div class="col-xs-6 col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4 class="card-title">{{ __('labels.backend.menu-manager.custom_links_card_header_text') }}</h4>
                        </div>
                        <form method="POST" action="{{ route('admin.menu-manager.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>{{ __('labels.backend.menu-manager.label.label') }}</label>
                                    <span class="text-danger">*</span>
                                    {!! Form::text('label', old('label'), [
                                        'class' => 'form-control',
                                        'required'
                                    ]) !!}
                                </div>
                                <div class="form-group">
                                    <label>{{ __('labels.backend.menu-manager.label.url') }}</label>
                                    <span class="text-danger">*</span>
                                    {!! Form::text('link', old('link'), [
                                        'class' => 'form-control',
                                        'required'
                                    ]) !!}
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <button type="submit"
                                        class="btn btn-sm btn-success">{{ __('labels.backend.menu-manager.button.add_to_menu') }}</button>
                            </div>
                        </form>
                    </div>
                    @if (count($pages) > 0)
                        <div class="card my-3">
                            <div class="card-header bg-light">
                                <h4 class="card-title">{{ __('labels.backend.menu-manager.pages_card_header_text') }}</h4>
                            </div>
                            <form method="POST" action="{{ route('admin.menu-manager.store') }}">
                                @csrf
                                <div class="card-body">
                                    @foreach ($pages as $page)
                                        <div class="form-group @if ($loop->last) mb-0 @endif">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                       name="custom_pages[]" id="checkbox{{ $page->id }}"
                                                       value="{{ $page->id }}">
                                                <label class="custom-control-label"
                                                       for="checkbox{{ $page->id }}">{{ $page->title }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="card-footer bg-light">
                                    <button type="submit"
                                            class="btn btn-sm btn-success">{{ __('labels.backend.menu-manager.button.add_to_menu') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
@push('after-scripts')
    <script src="{{url('/plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js')}}"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function () {
            $(".sortable").sortable({
                items: "> div",
                tolerance: "pointer",
                cursor: "move",
            });

            $("#updateSortOrder").on('click', function() {
                var sortMenuItems = $('.sortable').sortable('toArray', {
                    attribute: 'data-id'
                });
                $.ajax({
                    url: '{{ route('admin.menu-manager.update_sort') }}',
                    method: 'POST',
                    data: {
                        sortMenuItems: sortMenuItems
                    },
                    success: function(data) {
                        if (data.status) {

                        }
                    }
                });
            })

            $(document).on('click', '.delete_warning', function () {
                const link = $(this);
                const title = (link.attr('data-trans-title')) ? link.attr('data-trans-title') : 'Are you sure you want to do this?';
                const cancel = (link.attr('data-trans-button-cancel')) ? link.attr('data-trans-button-cancel') : 'Cancel';
                const confirm = (link.attr('data-trans-button-confirm')) ? link.attr('data-trans-button-confirm') : 'Continue';
                swal({
                    title: title,
                    showCancelButton: true,
                    confirmButtonText: confirm,
                    cancelButtonText: cancel,
                    type: 'info'
                }).then((result) => {
                    result.value && window.location.assign(link.data('delete_url'));
                });
            });
        });
    </script>
@endpush
