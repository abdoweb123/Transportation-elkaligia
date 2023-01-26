@extends('layouts.master')
@section('css')
@section('title')
    قائمة أساطيل الحافلات
@stop
@endsection
@section('page-header')
    <!-- breadcrumb -->
@section('PageTitle')
    قائمة أساطيل الحافلات
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
                            @foreach ($errors->all() as $error)
                                <div>
                                  {{ $error }}
                                </div>
                            @endforeach
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
                    <button type="button" class="button x-small" data-toggle="modal" data-target="#exampleModal">
                        إضافة أسطول
                    </button>
                    <br><br>

                    <div class="table-responsive">
                        <table id="datatable" class="table  table-hover table-sm table-bordered p-0" data-page-length="50"
                               style="text-align: center">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الأسطول</th>
                                <th>طول الحافلة</th>
                                <th>عرض الحافلة</th>
                                <th>عدد المقاعد</th>
                                <th>مدخل البيانات</th>
                                <th>العمليات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($busTypes as $item)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->length }}</td>
                                    <td>{{ $item->width }}</td>
                                    <td>{{ $item->slug }}</td>
                                    <td>@isset($item->admin->name)  {{ $item->admin->name }} @else لا يوجد @endisset</td>
                                    <td>
                                        <div class="dropdown show">
                                            <a class="btn btn-success btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                العمليات
                                            </a>


                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">

                                                <a href="{{route('show.busType.seats',$item->id)}}" class="dropdown-item" style="cursor:pointer">
                                                    <i style="color:green" class="fa fa-eye"></i>&nbsp; عرض التصميم</a>

                                                <a type="button" class="dropdown-item" style="cursor:pointer" data-toggle="modal"
                                                   data-target="#edit{{ $item->id }}" title="تعديل">
                                                   <i style="color:#a3a373" class="fa fa-edit"></i>&nbsp; تعديل</a>

                                                <a type="button" class="dropdown-item" style="cursor:pointer" data-toggle="modal"
                                                   data-target="#delete{{ $item->id }}" title="حذف">
                                                   <i style="color:red" class="fa fa-trash"></i>&nbsp; حذف</a>


                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!--  page of edit_modal_BusTypes -->
                                @include('pages.BusTypes.edit')

                                <!--  page of delete_modal_BusTypes -->
                                @include('pages.BusTypes.delete')


                                <!--  page of show_modal_BusTypes -->
                                @include('pages.BusTypes.show')

                            @endforeach
                        </table>

                        <div> {{$busTypes->links('pagination::bootstrap-4')}}</div>
                    </div>
                </div>
            </div>
        </div>


       <!--  page of add_modal_city -->
       @include('pages.BusTypes.create')
    </div>



@endsection
@section('js')
    @toastr_js
    @toastr_render

    <script>
        $(document).ready(function(){
            $(".alert").delay(5000).slideUp(300);
        });
    </script>
@endsection



