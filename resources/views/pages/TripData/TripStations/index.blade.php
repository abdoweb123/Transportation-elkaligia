@extends('layouts.master')
@section('css')
@section('title')
    محطات الرحلات
@stop
@endsection
@section('page-header')
    <!-- breadcrumb -->
@section('PageTitle')
  قائمة محطات الرحلات
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


                        <h6 class="text-center"><span style="background-color:#84ba3f; color:white; border-radius: 5px; padding:5px"> رحلة {{ $tripData->name }} --> المحطات </span></h6>


                    @foreach(['danger','warning','success','info'] as $msg)
                        @if(Session::has('alert-'.$msg))
                            <div class="alert alert-{{$msg}} messages mt-2">
                                {{Session::get('alert-'.$msg)}}
                            </div>
                        @endif
                    @endforeach


                        {{--  button of add_modal_station  --}}
                    <button type="button" class="button x-small" data-toggle="modal" data-target="#exampleModal">
                        إضافة محطة للرحلة
                    </button>
                    <br><br>

                    <div class="table-responsive">
                        <table id="datatable" class="table  table-hover table-sm table-bordered p-0" data-page-length="50"
                               style="text-align: center">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>ترتيب المحطة</th>
                                <th>اسم المحطة</th>
                                <th>نوع المحطة</th>
                                <th>الوقت المستغرق للوصول لهذه المحطة</th>
                                <th>المسافة المقطوعة للوصول لهذه المحطة</th>
                                <th>عدد مرات طباعة اللوحة</th>
                                <th>مدخل البيانات</th>
                                <th>العمليات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($tripStations as $item)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>{{$item->rank}}</td>
                                    <td>@isset($item->station->name) {{ $item->station->name }} @else لا يوجد @endisset</td>
                                    <td>@if($item->type == 1 ) صعود @elseif($item->type == 2 ) نزول @elseif($item->type == 3 ) صعود ونزول @endif</td>
                                    <td>{{$item->timeInMinutes}} &nbsp; <span> د </span></td>
                                    <td>{{$item->distance}} &nbsp; <span> كم </span> </td>
                                    <td>{{$item->printTimes}}</td>
                                    <td>@isset($item->admin->name)  {{ $item->admin->name }} @else لا يوجد @endisset</td>
                                    <td>
                                        <div class="dropdown show">
                                            <a class="btn btn-success btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                العمليات
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <a type="button" class="dropdown-item" style="cursor:pointer" data-toggle="modal"
                                                   data-target="#edit{{ $item->id }}" title="{{ trans('main_trans.edit') }}">
                                                   <i style="color:#a3a373" class="fa fa-edit"></i>&nbsp; تعديل</a>

                                                <a type="button" class="dropdown-item" style="cursor:pointer" data-toggle="modal"
                                                   data-target="#delete{{ $item->id }}" title="{{ trans('main_trans.delete') }}">
                                                   <i style="color:red" class="fa fa-trash"></i>&nbsp; حذف</a>

                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!--  page of edit_modal_station -->
                                @include('pages.TripData.TripStations.edit')

                                <!--  page of delete_modal_station -->
                                @include('pages.TripData.TripStations.delete')

                            @endforeach
                        </table>

                        <div> {{$tripStations->links('pagination::bootstrap-4')}}</div>

                    </div>

                        <form action="{{route('createLinesOfTrip')}}" method="POST">
                            @csrf
                            <input type="hidden" name="tripData_id" value="{{$tripData->id}}">
                            @foreach($tripStations as $item)
                                <input type="hidden" name="tripStations[]" value="{{$item->id}}">
                            @endforeach
                            <button type="submit" class="btn btn-success">حفظ</button>
                        </form>

                </div>
            </div>
        </div>


       <!--  page of add_modal_station -->
       @include('pages.TripData.TripStations.create')
    </div>



@endsection
@section('js')
    @toastr_js
    @toastr_render

    <script>
        $(document).ready(function(){
            $(".messages").delay(5000).slideUp(300);
        });
    </script>
@endsection



