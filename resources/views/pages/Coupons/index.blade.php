@extends('layouts.master')
@section('css')
@section('title')
    قائمة الكوبونات
@stop

<style>
    .tripName:not(:last-child)::after{content: ' - ' }
</style>

@endsection
@section('page-header')
    <!-- breadcrumb -->
@section('PageTitle')
    قائمة الكوبونات
@stop
<!-- breadcrumb -->
@endsection
@section('content')
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 mb-30">
            <div class="card card-statistics h-100">
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    @foreach(['danger','warning','success','info'] as $msg)
                        @if(Session::has('alert-'.$msg))
                            <div class="alert alert-{{$msg}}">
                                {{Session::get('alert-'.$msg)}}
                            </div>
                        @endif
                    @endforeach

                    {{--  button of add_modal_city  --}}
                    <a href="{{route('coupons.create')}}" class="button x-small">
                        إضافة كوبون
                    </a>
                    <br><br>

                    <div class="table-responsive">
                        <table id="datatable" class="table  table-hover table-sm table-bordered p-0" data-page-length="50"
                               style="text-align: center">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>الكود</th>
                                <th>الرحلة</th>
                                <th>الخصم</th>
                                <th>أكبر قيمة للخصم</th>
                                <th>تاريخ البداية</th>
                                <th>تاريخ النهاية</th>
                                <th>العدد الكلي</th>
                                <th>العدد المتبقي</th>
                                <th>عدد مستخدمي الكوبون</th>
                                <th>العدد الأقصى لكل مستخدم</th>
                                <th>نوع العميل</th>
                                <th>الحالة</th>
                                <th>الملاحظات</th>
                                <th>مدخل البيانات</th>
                                <th>{{ trans('main_trans.processes') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($coupons as $item)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>{{ $item->code  }}</td>
                                    <td>
                                        @foreach($item->trips as $trip)
                                           <span class="tripName">{{$trip->name}}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $item->amount }}  &nbsp; {{$item->percent == 0 ? '%' : 'جنيه'}} </td>
                                    <td>{{ $item->max_amount == null ? 'لا يوجد' : $item->max_amount  }}</td>
                                    <td>{{ $item->startDate  }}</td>
                                    <td>{{ $item->endDate  }}</td>
                                    <td>{{ $item->max_users  }}</td>
                                    <td>{{ $item->used_by  }}</td>
                                    <td>{{ $item->used_count  }}</td>
                                    <td>{{ $item->max_per_user  }}</td>
                                    <td>@isset($item->customerType->name)  {{ $item->customerType->name }} @else لا يوجد @endisset</td>
                                    <td>{{$item->active == 1 ? 'نشط' : 'غير نشط'}}</td>
                                    <td>{{ $item->notes == null ? 'لا يوجد' : $item->notes }}</td>
                                    <td>@isset($item->admin->name)  {{ $item->admin->name }} @else لا يوجد @endisset</td>
                                    <td>
                                        <div class="dropdown show">
                                            <a class="btn btn-success btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{ trans('main_trans.processes') }}
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <a class="dropdown-item" href="{{route('coupons.edit', $item->id)}}" style="cursor:pointer">
                                                   <i style="color:#a3a373" class="fa fa-edit"></i>&nbsp; {{trans('cities_trans.edit')}}</a>

                                                <a type="button" class="dropdown-item" style="cursor:pointer" data-toggle="modal"
                                                   data-target="#delete{{ $item->id }}" title="{{ trans('cities_trans.delete') }}">
                                                   <i style="color:red" class="fa fa-trash"></i>&nbsp; {{trans('main_trans.delete')}}</a>

                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!--  page of edit_modal_city -->
{{--                                @include('pages.Coupons.edit')--}}

                                <!--  page of delete_modal_city -->
                                @include('pages.Coupons.delete')


                            @endforeach
                        </table>

                        <div> {{$coupons->links('pagination::bootstrap-4')}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
@section('js')
    @toastr_js
    @toastr_render

    <script>
        $(document).ready(function(){
            $(".alert").delay(5000).slideUp(300);


            // To hide div max_amount when ...
            $('.percent').change(function (){
                if ( $($(this).val() == '1') ){      // جنيه
                    $('.max_amount').slideToggle();            // اخفيه
                }
                else {       // %
                    $('.max_amount').slideToggle();           // اظهره
                }

            });

        });




    </script>
@endsection



