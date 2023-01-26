@extends('layouts.master')
@section('css')
@section('title')
   إضافة حجز
@stop


<style>
    .process{border:none; border-radius:3px; padding:3px 5px;}
     select{padding:10px !important;}
    .process
    {
        cursor:pointer;
        background-color:white;
        border-radius:3px;
        border: 1px solid #dddd;
        padding: 5px 3px 0 4px;
        margin-left: 2px;
    }
</style>

@endsection
@section('page-header')
    <!-- breadcrumb -->
@section('PageTitle')
    إضافة حجز
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


{{--                    <div class="line mb-3" style="border-bottom: 1px solid #e9ecef; padding-bottom:30px">--}}
{{--                    </div>--}}
                     <form action="{{route('reservationBookingRequests.searchLines')}}" method="get" enctype="multipart/form-data">
                        @csrf
                         @isset($request->old_ticket_id)
                         <input type="hidden" name="old_ticket_id" value="{{$request->old_ticket_id}}">
                         @endisset
                         <div class="row mb-5">
                            <div class="col">
                                <label for="name_ar" class="mr-sm-2">محطة الانطلاق :</label>
                                <select class="form-control mr-sm-2 p-2" name="stationFrom_id">
                                    <option class="custom-select mr-sm-2 p-2" value=" ">--- اختر ---</option>
                                    @foreach($stations as $station)
                                        <option value="{{$station->id}}" {{ $station->id == $request->stationFrom_id ? 'selected' : ''}}>{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label for="name_ar" class="mr-sm-2">محطة الوصول :</label>
                                <select class="form-control mr-sm-2 p-2" name="stationTo_id">
                                    <option class="custom-select mr-sm-2 p-2" value=" ">--- اختر ---</option>
                                    @foreach($stations as $station)
                                        <option value="{{$station->id}}" {{ $station->id == $request->stationTo_id ? 'selected' : ''}}>{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label for="name_ar" class="mr-sm-2">التاريخ :</label>
                                <input class="form-control" type="text" name="date" value="{{$request->date}}" id="datepicker-action" data-date-format="yyyy-mm-dd" style="padding:13px">
                            </div>
                            <div class="col">
                                <label for="name_ar" class="mr-sm-2"> </label>
                                <input type="submit" value="ابحث" class="btn btn-success form-control" style="background-color: #84ba3f; color: white; font-size: 16px; padding:12px; margin-top: 5px;">
                            </div>
                        </div>

                        </form>


                        <div class="table-responsive">
                        <table id="datatable" class="table  table-hover table-sm table-bordered p-0" data-page-length="50"
                               style="text-align: center">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الرحلة</th>
                                <th>التاريخ</th>
                                <th>الوقت</th>
                                <th>محطة الانطلاق</th>
                                <th>محطة الوصول</th>
                                <th>العمليات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @isset($dataAll)
                            @foreach ($dataAll as $item)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>@isset($item->name)  {{ $item->name }} @else _____ @endisset</td>
                                    <td>@isset($item->startDate)  {{ $item->startDate }} @else _____ @endisset</td>
                                    <td>@isset($item->startTime)  {{ $item->startTime }} @else _____ @endisset</td>
                                        @foreach($stationFrom_id as $station)
                                            <td>{{$station->getTranslation('name','ar')}}</td>
                                        @endforeach

                                    @foreach($stationTo_id as $station)
                                        <td>{{$station->getTranslation('name','ar')}}</td>
                                    @endforeach
                                    <td>
                                        @isset($request->old_ticket_id)
                                            <form action="{{route('reservationBookingRequests.ticket_back')}}" method="get" class="d-inline-block" enctype="multipart/form-data">
                                                <input type="hidden" name="old_ticket_id" value="{{$request->old_ticket_id}}">
                                        @else
                                            <form action="{{route('reservationBookingRequests.bookingPage')}}" method="get" class="d-inline-block" enctype="multipart/form-data">
                                        @endisset
                                                @csrf
                                            <input type="hidden" name="runTrip_id" value="{{$item->runTrip_id}}">
                                            <input type="hidden" name="tripData_id" value="{{$item->id}}">
                                            <input type="hidden" name="tripData_name" value="{{$item->name}}">
                                            <input type="hidden" name="stationFrom_id" value="{{$item->from_id}}">
                                            <input type="hidden" name="stationTo_id" value="{{$item->to_id}}">
                                            <input type="hidden" name="startDate" value="{{$item->startDate}}">

                                            <button type="submit" class="btn btn-success" style="background-color: #84ba3f">
                                                <i style="color:white; font-size:18px;" class="fa fa-ticket"></i>&nbsp; حجز </button>

                                            </form>
                                    </td>
                                </tr>

                                <!--  page of delete_modal_city -->
                                @include('pages.ReservationBookingRequests.delete')

                            @endforeach
                            @endisset
                        </table>
                        @isset($dataAll)
                        <div> {{$dataAll->links('pagination::bootstrap-4')}}</div>
                         @endisset
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
        });
    </script>
@endsection




