<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calc_bookingRequest;
use App\Http\Requests\EditBookingRequest;
use App\Http\Requests\PrintTablohRequest;
use App\Http\Requests\SaveDataRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\Admin;
use App\Models\BookingSeat;
use App\Models\Bus;
use App\Models\BusType;
use App\Models\Coupon;
use App\Models\CouponTrip;
use App\Models\Driver;
use App\Models\Les;
use App\Models\Line;
use App\Models\ReservationBookingRequest;
use App\Models\RunTrip;
use App\Models\Shipping;
use App\Models\Station;
use App\Models\TripData;
use App\Models\TripSeat;
use App\Models\TripStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Whoops\Run;

class ReservationBookingRequestController extends Controller
{


    public function searchLines(Request $request,$old_ticket_id = null)
    {



        // for create new booking
        if ($request->has('date')) {

            $dataAll = DB::table('trip_data')
                ->join('lines', 'trip_data.id', 'lines.tripData_id')
                ->join('run_trips', 'trip_data.id', 'run_trips.tripData_id')
                ->where('run_trips.startDate', $request->date)
                ->where('lines.from_id', $request->stationFrom_id)
                ->where('lines.to_id', $request->stationTo_id)
                ->select('trip_data.*','lines.from_id','lines.to_id','run_trips.startDate','run_trips.startTime','run_trips.id as runTrip_id')->distinct()->latest()->paginate(100);

            $stationFrom_id = Station::where('id',$request->stationFrom_id)->select('name')->get();
            $stationTo_id = Station::where('id',$request->stationTo_id)->select('name')->get();
            $stations = Station::select('id','name')->get();




            return view('pages.ReservationBookingRequests.searchLines',compact('dataAll','stations','request','stationFrom_id','stationTo_id'));

        }

        // for edit new booking
        if ($request->has('edit_date')) {

            $dataAll = DB::table('trip_data')
                ->join('lines', 'trip_data.id', 'lines.tripData_id')
                ->join('run_trips', 'trip_data.id', 'run_trips.tripData_id')
                ->where('run_trips.startDate', $request->edit_date)
                ->where('lines.from_id', $request->stationFrom_id)
                ->where('lines.to_id', $request->stationTo_id)
                ->select('trip_data.*','lines.from_id','lines.to_id','run_trips.startDate','run_trips.id as runTrip_id')->distinct()->latest()->paginate(100);

            $stationFrom_id = Station::where('id',$request->stationFrom_id)->select('name')->get();
            $stationTo_id = Station::where('id',$request->stationTo_id)->select('name')->get();
            $stations = Station::select('id','name')->get();
            $reservationBookingRequest = ReservationBookingRequest::find($request->reservationBookingRequest_id);

            return view('pages.ReservationBookingRequests.editPage',compact('dataAll','stations','request','stationFrom_id','stationTo_id','reservationBookingRequest'));
        }


        $stations = Station::select('id','name')->get();

        if ($old_ticket_id != null) // جاي من تصميم التذكرة يعمل تذكرة العودة
        {
            return view('pages.ReservationBookingRequests.searchLines',compact('stations','request','old_ticket_id'));
        }
        else{    // رايح يعمل حجز جديد عادي
            return view('pages.ReservationBookingRequests.searchLines',compact('stations','request'));
        }

    }



    /*** bookingPage ***/
    public function bookingPage(Request $request)
    {
        $tripData = TripData::findOrFail($request->tripData_id);
        $tripDegrees = $tripData->tripDegrees;
        $tripSeats = TripSeat::where('tripData_id',$request->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        $old_request = $request;
        $reservationBookings = ReservationBookingRequest::where('trip_id',$tripData->id)->get();

        // for printing tabloh
        $drivers = Driver::select('id','name')->get();
        $buses = Bus::select('id','code')->get();
        $hosts = Admin::where('type',3)->select('id','name')->get();

        $shippings = DB::table('shippings')
            ->join('run_trips','run_trips.id','=','shippings.run_trip_id')
            ->join('trip_data','trip_data.id','=','run_trips.tripData_id')
            ->where('shippings.deleted_at','=',null)
            ->where('trip_data.id',$tripData->id)->select('shippings.*','shippings.id as shipping_id')->get();

        return view('pages.ReservationBookingRequests.bookingPage',compact('old_request','tripSeats','tripDegrees','tripData','busType','shippings','reservationBookings','drivers','buses','hosts'));
    }



    /*** searchUserPhone function ***/
    public function searchUserPhone(Request $newRequest)
    {
        $tripData = TripData::findOrFail($newRequest->tripData_id);
        $tripDegrees = $tripData->tripDegrees;
        $linesOfTrip = Line::where('tripData_id',$newRequest->tripData_id)->where('from_id',$newRequest->stationFrom_id)->where('to_id',$newRequest->stationTo_id)->get();
        $tripSeats = TripSeat::where('tripData_id',$newRequest->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        $user = User::where('mobile',$newRequest->searchUserPhone)->select('id','name','wallet')->first();

        $coupons = DB::table('coupon_trips')
            ->join('coupons','coupon_trips.coupon_id','coupons.id')
            ->where('coupon_trips.tripData_id',$newRequest->tripData_id)
            ->where('coupons.endDate','>=',Carbon::today())
            ->select('coupons.id','coupons.code','coupons.endDate','coupons.startDate')->get();

        return view('pages.ReservationBookingRequests.searchPhoneUser',compact('user','newRequest','tripDegrees','tripData','tripSeats','busType','coupons','linesOfTrip'));
    }



    /*** create new client function ***/
    public function createNewUser(UserStoreRequest $newRequest)
    {
        $newUser = new User();
        $newUser->name = $newRequest['name'];
        $newUser->email = $newRequest['email'];
        $newUser->mobile = $newRequest['mobile'];
        $newUser->nationalId = $newRequest['nationalId'];
        $newUser->admin_id = auth('admin')->id();
        $newUser->password = Hash::make($newRequest['password']);
        $newUser->save();

        $tripData = TripData::findOrFail($newRequest->tripData_id);
        $tripDegrees = $tripData->tripDegrees;
        $linesOfTrip = Line::where('tripData_id',$newRequest->tripData_id)->where('from_id',$newRequest->stationFrom_id)->where('to_id',$newRequest->stationTo_id)->get();
        $tripSeats = TripSeat::where('tripData_id',$newRequest->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        $coupons = DB::table('coupon_trips')
            ->join('coupons','coupon_trips.coupon_id','coupons.id')
            ->where('coupon_trips.tripData_id',$newRequest->tripData_id)
            ->select('coupons.id','coupons.code')->get();

        return view('pages.ReservationBookingRequests.searchPhoneUser',compact('newUser','newRequest','tripData','tripDegrees','tripSeats','busType','coupons','linesOfTrip'));
    }



    /*** calculate Booking Request ***/
    public function calc_booking(Calc_bookingRequest $request)
    {


        if ($request->trip_type == 1)  // GO
        {
            if ($request->passenger_type == 1) // egyptian ==>  priceGo
            {
                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
                    $arr[] = "$getPriceGoOfDegree";
                    $degrees[] = $getPriceGoOfDegree;
                }

                $newArr = array_count_values($arr);

                foreach ($newArr as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiply[] = $multiply;
                }

                $total = array_sum($arrMultiply);

                $wallet = 0;


            }

            else{ // foreign ==>  priceForeignerGo

                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceForeignerGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerGo;
                    $arr[] = "$getPriceForeignerGoOfDegree";
                    $degrees[] = $getPriceForeignerGoOfDegree;
                }

                $newArr = array_count_values($arr);

                foreach ($newArr as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiply[] = $multiply;
                }

                $total = array_sum($arrMultiply);

                $wallet = 0;
            }
        }
        else{   // BACK

            if ($request->passenger_type == 1) // egyptian ==>  priceBack
            {
                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceBack;
                    $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
                    $arrPriceBack[] = "$getPriceBackOfDegree";
                    $arrPriceGo[] = "$getPriceGoOfDegree";
                    $degrees[] = $getPriceBackOfDegree;
                }

                $newArrPriceBack = array_count_values($arrPriceBack);
                foreach ($newArrPriceBack as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceBack[] = $multiply;
                }
                $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                $newArrPriceGo = array_count_values($arrPriceGo);
                foreach ($newArrPriceGo as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceGo[] = $multiply;
                }
                $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                $total = $totalPriceBack;
                $totalGo = $totalPriceGo;

                $wallet = $total - $totalGo;  // wallet


            }
            else{ // foreign ==>  priceForeignerBack

                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceForeignerBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerBack;
                    $getPriceForeignerGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerGo;
                    $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                    $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                    $degrees[] = $getPriceForeignerBackOfDegree;
                }


                $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                foreach ($newArrPriceForeignerBack as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceForeignerBack[] = $multiply;
                }
                $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                foreach ($newArrPriceForeignerGo as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceForeignerGo[] = $multiply;
                }
                $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                $total = $totalPriceForeignerBack;
                $totalGo = $totalPriceForeignerGo;

                $wallet = $total - $totalGo;  // wallet

            }

        }


        if (!$request->coupon_id) // no coupon
        {
            $total_discount_booking = 0; // no coupons
            $sub_total_booking = $total; // no coupons

            for ($i=0; $i<count($request->seatId); $i++)
            {
                $sub_total_seat[] = $degrees[$i];
            }
        }
        else
        {
            $coupon = Coupon::where('id',$request->coupon_id)->first();
            if ($coupon->used_by > 0 ){

                if($coupon->percent == 1) // pound
                {
                    $total_discount_booking =  $coupon->amount * count($request->seatId);

                    if ($total <= $total_discount_booking)
                    {
                        $total_discount_booking = $total;
                    }

                    $sub_total_booking = $total - $total_discount_booking;

                    for ($i=0; $i<count($request->seatId); $i++)
                    {
                        $sub_total_seat[] = $degrees[$i] - $coupon->amount;
                    }


                }
                else{  // percentage %

                    for ($i=0; $i<count($request->seatId); $i++)
                    {
                        $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id;

                        $discount_seat = ($coupon->amount / 100) * $degrees[$i];

                        if ($discount_seat < $coupon->max_amount)
                        {
                            $getSumDegrees[] = $discount_seat;
                        }
                        else{
                            $getSumDegrees[] = $coupon->max_amount;
                        }

                        $sub_total_seat[] = $degrees[$i] - $getSumDegrees[$i];
                    }


                    $total_discount_booking = array_sum($getSumDegrees);
                    $sub_total_booking = $total - $total_discount_booking;
                }

            }
        }

//        return $total;
//        return $total_discount_booking;
//        return $sub_total_booking;

        $tripData = TripData::findOrFail($request->tripData_id);
        $newTripSeats = $request->seatId;
        $tripDegrees = $tripData->tripDegrees;
        $linesOfTrip = Line::where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->get();
        $tripSeats = TripSeat::where('tripData_id',$request->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        $user = User::findOrFail($request->user_id);
        $coupon = Coupon::find($request->coupon_id);

        return view('pages.ReservationBookingRequests.calc_searchPhoneUser',compact('request','tripData','tripDegrees','tripSeats','busType','coupon','linesOfTrip','total','total_discount_booking','sub_total_booking','user','newTripSeats'));

    }



    /*** Make Booking Request ***/
    public function saveData(SaveDataRequest $request)
    {

//       return $request;
        $user = User::find($request->user_id);
      if (!$request->seatId){
          return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
      }

      if (!$request->user_id){
          return redirect()->back()->with('alert-danger','برجاء اختيار مستخدم');
      }


      if ($request->trip_type == 1)  // GO
      {
          if ($request->passenger_type == 1) // egyptian ==>  priceGo
          {
              for ($i=0; $i<count($request->seatId); $i++)
              {
                  $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                  $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
                  $arr[] = "$getPriceGoOfDegree";
                  $degrees[] = $getPriceGoOfDegree;
              }

              $newArr = array_count_values($arr);

              foreach ($newArr as $key=>$value)
              {
                  $multiply = $key * $value;

                  $arrMultiply[] = $multiply;
              }

              $total = array_sum($arrMultiply);

              $wallet = 0;


          }

          else{ // foreign ==>  priceForeignerGo

              for ($i=0; $i<count($request->seatId); $i++)
              {
                  $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                  $getPriceForeignerGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerGo;
                  $arr[] = "$getPriceForeignerGoOfDegree";
                  $degrees[] = $getPriceForeignerGoOfDegree;
              }

              $newArr = array_count_values($arr);

              foreach ($newArr as $key=>$value)
              {
                  $multiply = $key * $value;

                  $arrMultiply[] = $multiply;
              }

              $total = array_sum($arrMultiply);

              $wallet = 0;
          }
      }
      else{   // BACK

          if ($request->passenger_type == 1) // egyptian ==>  priceBack
          {
              for ($i=0; $i<count($request->seatId); $i++)
              {
                  $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                  $getPriceBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceBack;
                  $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
                  $arrPriceBack[] = "$getPriceBackOfDegree";
                  $arrPriceGo[] = "$getPriceGoOfDegree";
                  $degrees[] = $getPriceBackOfDegree;
              }

                 $newArrPriceBack = array_count_values($arrPriceBack);
                 foreach ($newArrPriceBack as $key=>$value)
                 {
                    $multiply = $key * $value;

                    $arrMultiplyPriceBack[] = $multiply;
                 }
                 $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                 $newArrPriceGo = array_count_values($arrPriceGo);
                 foreach ($newArrPriceGo as $key=>$value)
                 {
                    $multiply = $key * $value;

                    $arrMultiplyPriceGo[] = $multiply;
                 }
                 $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                   $total = $totalPriceBack;
                   $totalGo = $totalPriceGo;

                  $wallet = $total - $totalGo;  // wallet


          }
          else{ // foreign ==>  priceForeignerBack

              for ($i=0; $i<count($request->seatId); $i++)
              {
                  $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                  $getPriceForeignerBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerBack;
                  $getPriceForeignerGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerGo;
                  $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                  $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                  $degrees[] = $getPriceForeignerBackOfDegree;
              }


              $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
              foreach ($newArrPriceForeignerBack as $key=>$value)
              {
                  $multiply = $key * $value;

                  $arrMultiplyPriceForeignerBack[] = $multiply;
              }
              $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



              $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
              foreach ($newArrPriceForeignerGo as $key=>$value)
              {
                  $multiply = $key * $value;

                  $arrMultiplyPriceForeignerGo[] = $multiply;
              }
              $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


              $total = $totalPriceForeignerBack;
              $totalGo = $totalPriceForeignerGo;

              $wallet = $total - $totalGo;  // wallet

          }

      }


        if (!$request->coupon_id) // no coupon
        {
            $total_discount_booking = 0; // no coupons
            $sub_total_booking = $total; // no coupons

            for ($i=0; $i<count($request->seatId); $i++)
            {
                $sub_total_seat[] = $degrees[$i];
            }
        }
        else
         {
             $coupon = Coupon::where('id',$request->coupon_id)->first();
             if ($coupon->used_by > 0 ){

                 if($coupon->percent == 1) // pound
                 {
                     $total_discount_booking =  $coupon->amount * count($request->seatId);

                     if ($total <= $total_discount_booking)
                     {
                         $total_discount_booking = $total;
                     }

                     $sub_total_booking = $total - $total_discount_booking;

                     for ($i=0; $i<count($request->seatId); $i++)
                     {
                         $sub_total_seat[] = $degrees[$i] - $coupon->amount;
                     }


                 }
                 else{  // percentage %

                     for ($i=0; $i<count($request->seatId); $i++)
                     {
                         $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id;

                         $discount_seat = ($coupon->amount / 100) * $degrees[$i];

                         if ($discount_seat < $coupon->max_amount)
                         {
                             $getSumDegrees[] = $discount_seat;
                         }
                         else{
                             $getSumDegrees[] = $coupon->max_amount;
                         }

                         $sub_total_seat[] = $degrees[$i] - $getSumDegrees[$i];
                     }


                     $total_discount_booking = array_sum($getSumDegrees);
                     $sub_total_booking = $total - $total_discount_booking;
                 }

                 $coupon->used_by = $coupon->used_by - 1;
                 $coupon->used_count = $coupon->used_count + 1;
                 $coupon->update();
             }
         }



        $reservationBookingRequest = new ReservationBookingRequest();
        $reservationBookingRequest->runTrip_id = $request['runTrip_id'];
        $reservationBookingRequest->trip_id = $request['tripData_id'];
        $reservationBookingRequest->user_id = $request['user_id'];
        $reservationBookingRequest->stationFrom_id = $request['stationFrom_id'];
        $reservationBookingRequest->stationTo_id = $request['stationTo_id'];
        $reservationBookingRequest->coupon_id = $request['coupon_id'];
        $reservationBookingRequest->address = $request['address'];
        $reservationBookingRequest->total = $total;
        $reservationBookingRequest->discount = $total_discount_booking;


        if ($request->trip_type == 2) // go and back
        {
            $old_ticket_subTotal = $sub_total_booking / 2;
//            $user_wallet = $sub_total_booking / 2;
            $reservationBookingRequest->sub_total = $old_ticket_subTotal;


//            $user->wallet = $user->wallet + $user_wallet;
//            $user->update();
        }
        else{
            $reservationBookingRequest->sub_total = $sub_total_booking;
        }

        $reservationBookingRequest->wallet = $wallet;
        $reservationBookingRequest->type = $request['trip_type'];
        $reservationBookingRequest->passenger_type = $request['passenger_type'];
        $reservationBookingRequest->admin_id = auth('admin')->id();
        $reservationBookingRequest->active = 1;
        $reservationBookingRequest->save();




        for ($i=0; $i<count($request->seatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id;

            $bookingSeats = new BookingSeat();
            $bookingSeats->booking_id = $reservationBookingRequest->id;
            $bookingSeats->runTrip_id = $request['runTrip_id'];
            $bookingSeats->seat_id = $request->seatId[$i];
            $bookingSeats->degree_id = $getDegree;
            $bookingSeats->office_id =  auth('admin')->user()->office_id;
            $bookingSeats->city_id = auth('admin')->user()->office->station->city_id;
            $bookingSeats->total =  $degrees[$i];
            $bookingSeats->sub_total =  $sub_total_seat[$i];
            $bookingSeats->admin_id = auth('admin')->id();
            $bookingSeats->active = 1;
            $bookingSeats->save();
        }


        // create trigger
        $les = new Les();
        $les->type = 1;
        $les->ticket_id = $reservationBookingRequest->id;

        if ($request->payment_method == 1) //cash
        {
            $les->amount = $sub_total_booking;
            $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $sub_total_booking . ' جنيها ';
        }
        else{ // wallet
            if ($user->wallet > $sub_total_booking)
            {
                $user->wallet = $user->wallet - $request->paid_from_wallet;
                $user->update();

                $les->amount = 0;
                $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $request->paid_from_wallet . ' جنيها '. 'من محفظته';
            }
            elseif($user->wallet == $sub_total_booking)
            {
                $user->wallet = $user->wallet - $sub_total_booking;
                $user->update();

                $les->amount = 0;
                $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $request->paid_from_wallet . ' جنيها '. 'من محفظته';
            }
            elseif($user->wallet < $sub_total_booking)
            {
                $user->wallet = 0;
                $user->update();

                $les->amount = $request->paid_cash;
                $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $request->paid_from_wallet . ' جنيها '. ' من محفظته ' . ' وقام بدفع '. $request->paid_cash . ' نقديا ';
            }
        }

        $les->admin_id = auth('admin')->id();
        $les->active = 1;
        $les->save();

        if ($request->trip_type == 2) // go and back
        {
            $user->wallet = $user->wallet + $sub_total_booking / 2;
            $user->update();
        }




        $reservationBookingRequest_id = $reservationBookingRequest->id;

        return redirect()->route('reservationBookingRequests.getTicketDesign',compact('reservationBookingRequest_id'));
    }



    /*** edit page ***/
    public function editPage(Request $request)
    {
        if ($request->has('search_Booking'))
        {
            $reservationBookingRequest = ReservationBookingRequest::find($request->search_Booking);
            return view('pages.ReservationBookingRequests.editPage',compact('reservationBookingRequest'));
        }

        return view('pages.ReservationBookingRequests.editPage');
    }




    /*** change seats ***/
    public function changeSeats(EditBookingRequest $request)
    {

        if (isset($request->reservationBookingRequest_id))  // عشان اعرف هو جاي من changeSeats.blade ولا من changeSeatsNewTrip.blade
        {
            $reservationBookingRequest = ReservationBookingRequest::find($request->reservationBookingRequest_id);
        }
        else{
            $reservationBookingRequest = ReservationBookingRequest::find($request->newReservationBookingRequest_id);
        }


        $time_of_now = Carbon::now();
        $get_line = Line::where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first();

        // Time_to_edit
        $time_to_edit = $get_line->time_to_edit;
        $time_to_edit_add_minutes = $reservationBookingRequest->created_at->addMinutes($time_to_edit);

        // Time_to_edit_without_fee
        $time_to_edit_without_fee = $get_line->time_to_edit_without_fee;
        $time_to_edit_without_fee_add_minutes = $reservationBookingRequest->created_at->addMinutes($time_to_edit_without_fee);


        if ( $time_of_now > $time_to_edit_add_minutes)  // هل الوقت مسموح فيه التعديل
        {
            return redirect()->route('reservationBookingRequests.editPage')->with('alert-danger','لقد تخطيت الوقت المسموح به لتعديل التذكرة');

        }
        else{

            if ($request->cancelFee == 'on')
            {

                $tripData = TripData::find($reservationBookingRequest->trip_id);
                $tripDegrees = $tripData->tripDegrees;
                $tripSeats = TripSeat::where('tripData_id',$tripData->id)->get();
                $busType = BusType::findOrFail($tripData->busType->id);



                // START To get sub_total of selected seats
                $bookingSeats = $request->seatId;
                $totalCount=0;
                for ($i=0; $i<count($bookingSeats); $i++)
                {
                    $bookingSeat_subTotal = BookingSeat::find($bookingSeats[$i])->sub_total;
                    $totalCount = $totalCount + $bookingSeat_subTotal;
                }
                // END To get sub_total of selected seats


                // START To get sub_total of discount
                $linesOfBooking = DB::table('lines')
                    ->join('trip_data','trip_data.id','=','lines.tripData_id')
                    ->join('reservation_booking_requests','trip_data.id','=','reservation_booking_requests.trip_id')
                    ->join('booking_seats','reservation_booking_requests.id','=','booking_seats.booking_id')
                    ->where('lines.stationFrom_id','=',$reservationBookingRequest->stationFrom_id)
                    ->where('lines.stationTo_id','=',$reservationBookingRequest->stationTo_id)
                    ->select('lines.*')->distinct()->get();



                if ($time_of_now > $time_to_edit_without_fee_add_minutes)  // هل هيدفع غرامة تأخير
                {
                    // Start For selected seats
                    $bookingSeats = $request->seatId;
                    $totalDiscount=0;
                    for ($i=0; $i<count($bookingSeats); $i++)
                    {
                        $bookingSeat_degree_id = BookingSeat::find($bookingSeats[$i])->degree_id; //1
                        foreach ($linesOfBooking as $line)
                        {
                            if ($line->degree_id == $bookingSeat_degree_id)
                            {
                                $totalDiscount = $totalDiscount + $line->cancelFee;
                            }
                        }
                    }
                    // End For selected seats


                    // Start For the whole Booking
                    $allBookingSeats = $reservationBookingRequest->bookingSeats;
                    foreach ($allBookingSeats as $item)
                    {
                        $allBookingSeatsIds[] = $item->id;
                    }

                    $totalDiscountForAllSeats = 0;
                    for ($i=0; $i<count($allBookingSeatsIds); $i++)
                    {
                        $bookingSeat_all_degree_id = BookingSeat::find($allBookingSeatsIds[$i])->degree_id; //1
                        foreach ($linesOfBooking as $line)
                        {
                            if ($line->degree_id == $bookingSeat_all_degree_id)
                            {
                                $totalDiscountForAllSeats = $totalDiscountForAllSeats + $line->cancelFee;
                            }
                        }
                    }

                    // End For the whole Booking

                }
                else{
                    $totalDiscount=0;
                    $totalDiscountForAllSeats=0;
                    $line = Line::where('tripData_id',$reservationBookingRequest->tripData_id)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first();
                }


                // END To get sub_total of discount


                return view('pages.ReservationBookingRequests.cancelBooking',compact('reservationBookingRequest','tripData','tripDegrees','tripSeats','busType','request','totalCount','totalDiscount','line','totalDiscountForAllSeats'));
            }


            if (!$request->trip_type)
            {
                return redirect()->back()->with('alert-danger','برجاء تحديد نوع الرحلة');
            }



            if ($request->trip_type == 1) // The same trip
            {

                $tripData = TripData::find($reservationBookingRequest->trip_id);
                $tripDegrees = $tripData->tripDegrees;
                $tripSeats = TripSeat::where('tripData_id',$tripData->id)->get();
                $busType = BusType::findOrFail($tripData->busType->id);


                // START Make sure of process
                if ($request->has('old_paid'))
                {
//                return $request;
                    if (!$request->NewSeatId){
                        return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
                    }



                    if ($reservationBookingRequest->type == 1)  // GO
                    {
                        if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceGo
                        {
                            for ($i=0; $i<count($request->NewSeatId); $i++)
                            {
                                $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                $getPriceGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceGo;
                                $arr[] = "$getPriceGoOfDegree";
                                $degrees[] = $getPriceGoOfDegree;
                            }

                            $newArr = array_count_values($arr);

                            foreach ($newArr as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiply[] = $multiply;
                            }

                            $total = array_sum($arrMultiply);

                            $wallet = 0;


                        }

                        else{ // foreign ==>  priceForeignerGo

                            for ($i=0; $i<count($request->NewSeatId); $i++)
                            {
                                $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerGo;
                                $arr[] = "$getPriceForeignerGoOfDegree";
                                $degrees[] = $getPriceForeignerGoOfDegree;
                            }

                            $newArr = array_count_values($arr);

                            foreach ($newArr as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiply[] = $multiply;
                            }

                            $total = array_sum($arrMultiply);

                            $wallet = 0;
                        }
                    }
                    else{   // BACK

                        if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceBack
                        {
                            for ($i=0; $i<count($request->NewSeatId); $i++)
                            {
                                $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                $getPriceBackOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceBack;
                                $getPriceGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceGo;
                                $arrPriceBack[] = "$getPriceBackOfDegree";
                                $arrPriceGo[] = "$getPriceGoOfDegree";
                                $degrees[] = $getPriceBackOfDegree;
                            }

                            $newArrPriceBack = array_count_values($arrPriceBack);
                            foreach ($newArrPriceBack as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiplyPriceBack[] = $multiply;
                            }
                            $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                            $newArrPriceGo = array_count_values($arrPriceGo);
                            foreach ($newArrPriceGo as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiplyPriceGo[] = $multiply;
                            }
                            $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                            $total = $totalPriceBack;
                            $totalGo = $totalPriceGo;

                            $wallet = $total - $totalGo;  // wallet


                        }
                        else{ // foreign ==>  priceForeignerBack

                            for ($i=0; $i<count($request->NewSeatId); $i++)
                            {
                                $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                $getPriceForeignerBackOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerBack;
                                $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerGo;
                                $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                                $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                                $degrees[] = $getPriceForeignerBackOfDegree;
                            }


                            $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                            foreach ($newArrPriceForeignerBack as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiplyPriceForeignerBack[] = $multiply;
                            }
                            $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                            $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                            foreach ($newArrPriceForeignerGo as $key=>$value)
                            {
                                $multiply = $key * $value;

                                $arrMultiplyPriceForeignerGo[] = $multiply;
                            }
                            $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                            $total = $totalPriceForeignerBack;
                            $totalGo = $totalPriceForeignerGo;

                            $wallet = $total - $totalGo;  // wallet

                        }

                    }


                    $totalOfNewBooking = $total;
                    $totalCount = $request->totalCount;
                    $totalDiscount = $request->totalDiscount;
                    $newTripSeats = $request->NewSeatId;
                    $line = Line::find($request->line_id);

                    return view('pages.ReservationBookingRequests.changeSeats',compact('reservationBookingRequest','tripData','tripDegrees', 'newTripSeats','tripSeats','busType','request','totalCount','totalDiscount','line','totalOfNewBooking'));
                }
                // END Make sure of process




                // START To get sub_total of selected seats
                $bookingSeats = $request->seatId;
                $totalCount=0;
                for ($i=0; $i<count($bookingSeats); $i++)
                {
                    $bookingSeat_subTotal = BookingSeat::find($bookingSeats[$i])->sub_total;
                    $totalCount = $totalCount + $bookingSeat_subTotal;
                }
                // END To get sub_total of selected seats


                // START To get sub_total of discount
                $linesOfBooking = DB::table('lines')
                    ->join('trip_data','trip_data.id','=','lines.tripData_id')
                    ->join('reservation_booking_requests','trip_data.id','=','reservation_booking_requests.trip_id')
                    ->join('booking_seats','reservation_booking_requests.id','=','booking_seats.booking_id')
                    ->where('lines.stationFrom_id','=',$reservationBookingRequest->stationFrom_id)
                    ->where('lines.stationTo_id','=',$reservationBookingRequest->stationTo_id)
                    ->select('lines.*')->distinct()->get();



                if ($time_of_now > $time_to_edit_without_fee_add_minutes)
                {

                    $bookingSeats = $request->seatId;
                    $totalDiscount=0;
                    for ($i=0; $i<count($bookingSeats); $i++)
                    {
                        $bookingSeat_degree_id = BookingSeat::find($bookingSeats[$i])->degree_id; //1
                        foreach ($linesOfBooking as $line)
                        {
                            if ($line->degree_id == $bookingSeat_degree_id)
                            {
                                $totalDiscount = $totalDiscount + $line->editFee;
                            }
                        }
                    }
                }
                else{
                    $totalDiscount=0;
                    $line = Line::where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first();
                }


                // END To get sub_total of discount


                return view('pages.ReservationBookingRequests.changeSeats',compact('reservationBookingRequest','tripData','tripDegrees','tripSeats','busType','request','totalCount','totalDiscount','line'));
            }
            else if ($request->trip_type == 2){ // Other Trip


                if ($request->has('newReservationBookingRequest_id'))
                {

                    $old_tripData = TripData::find($reservationBookingRequest->trip_id);
                    $old_tripDegrees = $old_tripData->tripDegrees;
                    $old_tripSeats = TripSeat::where('tripData_id',$old_tripData->id)->get();
                    $old_busType = BusType::findOrFail($old_tripData->busType->id);


                    $new_tripData = TripData::find($request->new_tripData_id);
                    $new_tripDegrees = $new_tripData->tripDegrees;
                    $new_tripSeats = TripSeat::where('tripData_id',$new_tripData->id)->get();
                    $new_busType = BusType::findOrFail($new_tripData->busType->id);


                    // START Make sure of process
                    if ($request->has('old_paid'))
                    {
//                        return $request;
                        if (!$request->NewSeatId){
                            return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
                        }


//                        return $request;
                        if ($reservationBookingRequest->type == 1)  // GO
                        {
                            if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceGo
                            {

                                for ($i=0; $i<count($request->NewSeatId); $i++)
                                {
                                    $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                    $getPriceGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceGo;
                                    $arr[] = "$getPriceGoOfDegree";
                                    $degrees[] = $getPriceGoOfDegree;
                                }

                                $newArr = array_count_values($arr);

                                foreach ($newArr as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiply[] = $multiply;
                                }

                                $total = array_sum($arrMultiply);

                                $wallet = 0;


                            }

                            else{ // foreign ==>  priceForeignerGo

                                for ($i=0; $i<count($request->NewSeatId); $i++)
                                {
                                    $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                    $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerGo;
                                    $arr[] = "$getPriceForeignerGoOfDegree";
                                    $degrees[] = $getPriceForeignerGoOfDegree;
                                }

                                $newArr = array_count_values($arr);

                                foreach ($newArr as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiply[] = $multiply;
                                }

                                $total = array_sum($arrMultiply);

                                $wallet = 0;
                            }
                        }
                        else{   // BACK

                            if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceBack
                            {
                                for ($i=0; $i<count($request->NewSeatId); $i++)
                                {
                                    $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                    $getPriceBackOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceBack;
                                    $getPriceGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceGo;
                                    $arrPriceBack[] = "$getPriceBackOfDegree";
                                    $arrPriceGo[] = "$getPriceGoOfDegree";
                                    $degrees[] = $getPriceBackOfDegree;
                                }

                                $newArrPriceBack = array_count_values($arrPriceBack);
                                foreach ($newArrPriceBack as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiplyPriceBack[] = $multiply;
                                }
                                $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                                $newArrPriceGo = array_count_values($arrPriceGo);
                                foreach ($newArrPriceGo as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiplyPriceGo[] = $multiply;
                                }
                                $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                                $total = $totalPriceBack;
                                $totalGo = $totalPriceGo;

                                $wallet = $total - $totalGo;  // wallet


                            }
                            else{ // foreign ==>  priceForeignerBack

                                for ($i=0; $i<count($request->NewSeatId); $i++)
                                {
                                    $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                                    $getPriceForeignerBackOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerBack;
                                    $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerGo;
                                    $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                                    $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                                    $degrees[] = $getPriceForeignerBackOfDegree;
                                }


                                $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                                foreach ($newArrPriceForeignerBack as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiplyPriceForeignerBack[] = $multiply;
                                }
                                $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                                $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                                foreach ($newArrPriceForeignerGo as $key=>$value)
                                {
                                    $multiply = $key * $value;

                                    $arrMultiplyPriceForeignerGo[] = $multiply;
                                }
                                $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                                $total = $totalPriceForeignerBack;
                                $totalGo = $totalPriceForeignerGo;

                                $wallet = $total - $totalGo;  // wallet

                            }

                        }



                        $totalOfNewBooking = $total;
                        $totalCount = $request->totalCount;
                        $totalDiscount = $request->totalDiscount;
                        $newTripSeats = $request->NewSeatId;
                        $line = Line::find($request->line_id);

//                        return $request;
                        return view('pages.ReservationBookingRequests.changeSeatsNewTrip',compact('reservationBookingRequest','old_tripData','old_tripDegrees', 'newTripSeats','old_tripSeats','old_busType','request','totalCount','totalDiscount','line','totalOfNewBooking','new_tripData','new_tripDegrees','new_tripSeats','new_busType'));
                    }
                    // END Make sure of process



                    // START To get sub_total of selected seats
                    $bookingSeats = $request->seatId;
                    $totalCount=0;
                    for ($i=0; $i<count($bookingSeats); $i++)
                    {
                        $bookingSeat_subTotal = BookingSeat::find($bookingSeats[$i])->sub_total;
                        $totalCount = $totalCount + $bookingSeat_subTotal;
                    }
                    // END To get sub_total of selected seats


                    // START To get sub_total of fee
                    $linesOfBooking = DB::table('lines')
                        ->join('trip_data','trip_data.id','=','lines.tripData_id')
                        ->join('reservation_booking_requests','trip_data.id','=','reservation_booking_requests.trip_id')
                        ->join('booking_seats','reservation_booking_requests.id','=','booking_seats.booking_id')
                        ->where('lines.stationFrom_id','=',$reservationBookingRequest->stationFrom_id)
                        ->where('lines.stationTo_id','=',$reservationBookingRequest->stationTo_id)
                        ->select('lines.*')->distinct()->get();

//                    return $request;
                    if ($time_of_now > $time_to_edit_without_fee_add_minutes)
                    {
                        $bookingSeats = $request->seatId;
                        $totalDiscount=0;
                        for ($i=0; $i<count($bookingSeats); $i++)
                        {
                            $bookingSeat_degree_id = BookingSeat::find($bookingSeats[$i])->degree_id; //1
                            foreach ($linesOfBooking as $line)
                            {
                                if ($line->degree_id == $bookingSeat_degree_id)
                                {
                                    $totalDiscount = $totalDiscount + $line->editFee;
                                }
                            }
                        }
                    }
                    else{

                        $totalDiscount=0;
                        $line = Line::where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first();
                    }




                    // END To get sub_total of fee

                    return view('pages.ReservationBookingRequests.changeSeatsNewTrip',compact('reservationBookingRequest','old_tripData','old_tripDegrees','old_tripSeats','old_busType','request','totalCount','totalDiscount','line','new_busType','new_tripData','new_tripDegrees','new_tripSeats'));
                }

//            $stations = Station::select('id','name')->get();

//            return view('pages.ReservationBookingRequests.editPage',compact('stations','reservationBookingRequest','request'));
            }
        }



        $stations = Station::select('id','name')->get();

        return view('pages.ReservationBookingRequests.editPage',compact('stations','reservationBookingRequest','request'));

    }



    /*** Make new Booking Request ***/
    public function newBookingAfterChangeSeats(Request $request)
    {

        $reservationBookingRequest = ReservationBookingRequest::find($request->reservationBookingRequest_id);

         $runTrip = RunTrip::find($reservationBookingRequest->runTrip_id);

        if (!$request->NewSeatId){
            return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
        }


        if ($request->totalOfNewBooking != 0) // To prevent him from click sure before calc
        {
            if ($reservationBookingRequest->type == 1)  // GO
            {
                if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceGo
                {
                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceGo;
                        $arr[] = "$getPriceGoOfDegree";
                        $degrees[] = $getPriceGoOfDegree;
                    }

                    $newArr = array_count_values($arr);

                    foreach ($newArr as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiply[] = $multiply;
                    }

                    $total = array_sum($arrMultiply);

                    $wallet = 0;

                }

                else{ // foreign ==>  priceForeignerGo

                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerGo;
                        $arr[] = "$getPriceForeignerGoOfDegree";
                        $degrees[] = $getPriceForeignerGoOfDegree;
                    }

                    $newArr = array_count_values($arr);

                    foreach ($newArr as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiply[] = $multiply;
                    }

                    $total = array_sum($arrMultiply);

                    $wallet = 0;
                }
            }
            else{   // BACK

                if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceBack
                {
                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceBackOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceBack;
                        $getPriceGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceGo;
                        $arrPriceBack[] = "$getPriceBackOfDegree";
                        $arrPriceGo[] = "$getPriceGoOfDegree";
                        $degrees[] = $getPriceBackOfDegree;
                    }

                    $newArrPriceBack = array_count_values($arrPriceBack);
                    foreach ($newArrPriceBack as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceBack[] = $multiply;
                    }
                    $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                    $newArrPriceGo = array_count_values($arrPriceGo);
                    foreach ($newArrPriceGo as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceGo[] = $multiply;
                    }
                    $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                    $total = $totalPriceBack;
                    $totalGo = $totalPriceGo;

                    $wallet = $total - $totalGo;  // wallet


                }
                else{ // foreign ==>  priceForeignerBack

                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceForeignerBackOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerBack;
                        $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$reservationBookingRequest->trip_id)->where('degree_id',$getDegree)->where('from_id',$reservationBookingRequest->stationFrom_id)->where('to_id',$reservationBookingRequest->stationTo_id)->first()->priceForeignerGo;
                        $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                        $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                        $degrees[] = $getPriceForeignerBackOfDegree;
                    }


                    $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                    foreach ($newArrPriceForeignerBack as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceForeignerBack[] = $multiply;
                    }
                    $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                    $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                    foreach ($newArrPriceForeignerGo as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceForeignerGo[] = $multiply;
                    }
                    $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                    $total = $totalPriceForeignerBack;
                    $totalGo = $totalPriceForeignerGo;

                    $wallet = $total - $totalGo;  // wallet

                }

            }
        }
        else{
            return redirect()->back()->with('alert-danger','برجاء حساب الحجز أولا');
        }




        // Create New Booking Request
        $newReservationBookingRequest = new ReservationBookingRequest();
        $newReservationBookingRequest->runTrip_id = $reservationBookingRequest->runTrip_id;
        $newReservationBookingRequest->trip_id = $reservationBookingRequest->trip_id;
        $newReservationBookingRequest->user_id = $reservationBookingRequest->user_id;
        $newReservationBookingRequest->stationFrom_id = $reservationBookingRequest->stationFrom_id;
        $newReservationBookingRequest->stationTo_id = $reservationBookingRequest->stationTo_id;
        $newReservationBookingRequest->address = $reservationBookingRequest->address;
        $newReservationBookingRequest->total = $total;
        $newReservationBookingRequest->discount = 0;
        $newReservationBookingRequest->sub_total = $total;
        $newReservationBookingRequest->wallet = $wallet;
        $newReservationBookingRequest->type = $reservationBookingRequest->type;
        $newReservationBookingRequest->passenger_type = $reservationBookingRequest->passenger_type;
        $newReservationBookingRequest->admin_id = auth('admin')->id();
        $newReservationBookingRequest->active = 1;
        $newReservationBookingRequest->save();




        // Create New Seats in Booking Request
        for ($i=0; $i<count($request->NewSeatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id;

            $bookingSeats = new BookingSeat();
            $bookingSeats->booking_id = $newReservationBookingRequest->id;
            $bookingSeats->runTrip_id = $newReservationBookingRequest->runTrip_id;
            $bookingSeats->seat_id = $request->NewSeatId[$i];
            $bookingSeats->degree_id = $getDegree;
            $bookingSeats->office_id =  auth('admin')->user()->office_id;
            $bookingSeats->city_id = auth('admin')->user()->office->station->city_id;
            $bookingSeats->total =  $degrees[$i];
            $bookingSeats->sub_total =  $degrees[$i];
            $bookingSeats->admin_id = auth('admin')->id();
            $bookingSeats->active = 1;
            $bookingSeats->save();
        }



        // create trigger
        $les = new Les();
        $les->amount = $request->totalRemain;
        $les->type = 1;
        $les->ticket_id = $newReservationBookingRequest->id;
        $les->action =      ' تم إلغاء عدد مقاعد ' . count($request->seatId) . ' التابعة للتذكرة رقم '. $reservationBookingRequest->id  . ' وتم حجز عدد مقاعد '. count($request->NewSeatId) .' التابعة للتذكرة رقم '. $newReservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $request->totalRemain . ' جنيها ';
        $les->admin_id = auth('admin')->id();
        $les->active = 1;
        $les->save();


        // Delete old Seats in Booking Request
        $sub_total_of_old_booking_seats = 0;
        for ($i=0; $i<count($request->seatId); $i++)
        {
            $bookingSeats = BookingSeat::find($request->seatId[$i]);
            $sub_total_of_old_booking_seats += $bookingSeats->sub_total;
            $bookingSeats->delete();
        }


        // Minus sub_total of deleted seats from its ticket
        $reservationBookingRequest->sub_total -= $sub_total_of_old_booking_seats;
        $reservationBookingRequest->update();


        return redirect()->route('reservationBookingRequests.editPage')->with('alert-success','تم تحديث البيانات بنجاح');
    }



    /*** Make new Booking Request new trip ***/
    public function newBookingAfterChangeSeatsNewTrip(Request $request)
    {

        $reservationBookingRequest = ReservationBookingRequest::find($request->reservationBookingRequest_id);
        $run_trip = RunTrip::find($request->new_runTrip_id);

        if (!$request->NewSeatId){
            return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
        }

//        return $request;

        if ($request->totalOfNewBooking != 0) // To prevent him from click sure before calc
        {
            if ($reservationBookingRequest->type == 1)  // GO
            {
                if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceGo
                {
//                            return $request->NewSeatId;
                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceGo;
                        $arr[] = "$getPriceGoOfDegree";
                        $degrees[] = $getPriceGoOfDegree;
                    }

                    $newArr = array_count_values($arr);

                    foreach ($newArr as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiply[] = $multiply;
                    }

                    $total = array_sum($arrMultiply);

                    $wallet = 0;


                }

                else{ // foreign ==>  priceForeignerGo

                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerGo;
                        $arr[] = "$getPriceForeignerGoOfDegree";
                        $degrees[] = $getPriceForeignerGoOfDegree;
                    }

                    $newArr = array_count_values($arr);

                    foreach ($newArr as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiply[] = $multiply;
                    }

                    $total = array_sum($arrMultiply);

                    $wallet = 0;
                }
            }
            else{   // BACK

                if ($reservationBookingRequest->passenger_type == 1) // egyptian ==>  priceBack
                {
                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceBackOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceBack;
                        $getPriceGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceGo;
                        $arrPriceBack[] = "$getPriceBackOfDegree";
                        $arrPriceGo[] = "$getPriceGoOfDegree";
                        $degrees[] = $getPriceBackOfDegree;
                    }

                    $newArrPriceBack = array_count_values($arrPriceBack);
                    foreach ($newArrPriceBack as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceBack[] = $multiply;
                    }
                    $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                    $newArrPriceGo = array_count_values($arrPriceGo);
                    foreach ($newArrPriceGo as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceGo[] = $multiply;
                    }
                    $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                    $total = $totalPriceBack;
                    $totalGo = $totalPriceGo;

                    $wallet = $total - $totalGo;  // wallet


                }
                else{ // foreign ==>  priceForeignerBack

                    for ($i=0; $i<count($request->NewSeatId); $i++)
                    {
                        $getDegree = TripSeat::where('tripData_id',$request->new_tripData_id)->where('id',$request->NewSeatId[$i])->first()->degree->id; //1
                        $getPriceForeignerBackOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerBack;
                        $getPriceForeignerGoOfDegree  = Line::where('tripData_id',$request->new_tripData_id)->where('degree_id',$getDegree)->where('from_id',$request->new_stationFrom_id)->where('to_id',$request->new_stationTo_id)->first()->priceForeignerGo;
                        $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                        $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                        $degrees[] = $getPriceForeignerBackOfDegree;
                    }


                    $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                    foreach ($newArrPriceForeignerBack as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceForeignerBack[] = $multiply;
                    }
                    $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                    $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                    foreach ($newArrPriceForeignerGo as $key=>$value)
                    {
                        $multiply = $key * $value;

                        $arrMultiplyPriceForeignerGo[] = $multiply;
                    }
                    $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                    $total = $totalPriceForeignerBack;
                    $totalGo = $totalPriceForeignerGo;

                    $wallet = $total - $totalGo;  // wallet

                }

            }

        }
        else{
            return redirect()->back()->with('alert-danger','برجاء حساب الحجز أولا');
        }




        // Create New Booking Request
        $newReservationBookingRequest = new ReservationBookingRequest();
        $newReservationBookingRequest->runTrip_id = $request->new_runTrip_id;
        $newReservationBookingRequest->trip_id = $request->new_tripData_id;
        $newReservationBookingRequest->user_id = $reservationBookingRequest->user_id;
        $newReservationBookingRequest->stationFrom_id = $request->new_stationFrom_id;
        $newReservationBookingRequest->stationTo_id = $request->new_stationTo_id;
        $newReservationBookingRequest->address = $reservationBookingRequest->address;
        $newReservationBookingRequest->total = $total;
        $newReservationBookingRequest->discount = 0;
        $newReservationBookingRequest->sub_total = $total;
        $newReservationBookingRequest->wallet = $wallet;
        $newReservationBookingRequest->type = $reservationBookingRequest->type;
        $newReservationBookingRequest->passenger_type = $reservationBookingRequest->passenger_type;
        $newReservationBookingRequest->admin_id = auth('admin')->id();
        $newReservationBookingRequest->active = 1;
        $newReservationBookingRequest->save();




        // Create New Seats in Booking Request
        for ($i=0; $i<count($request->NewSeatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->NewSeatId[$i])->first()->degree->id;

            $bookingSeats = new BookingSeat();
            $bookingSeats->booking_id = $newReservationBookingRequest->id;
            $bookingSeats->runTrip_id = $newReservationBookingRequest->runTrip_id;
            $bookingSeats->seat_id = $request->NewSeatId[$i];
            $bookingSeats->degree_id = $getDegree;
            $bookingSeats->office_id =  auth('admin')->user()->office_id;
            $bookingSeats->city_id = auth('admin')->user()->office->station->city_id;
            $bookingSeats->total =  $degrees[$i];
            $bookingSeats->sub_total =  $degrees[$i];
            $bookingSeats->admin_id = auth('admin')->id();
            $bookingSeats->active = 1;
            $bookingSeats->save();
        }


        // create trigger
        $les = new Les();
        $les->amount = $request->totalRemain;
        $les->type = 1;
        $les->ticket_id = $newReservationBookingRequest->id;
        $les->action =      ' تم إلغاء عدد مقاعد ' . count($request->seatId) . ' التابعة للتذكرة رقم '. $reservationBookingRequest->id  . ' وتم حجز عدد مقاعد '. count($request->NewSeatId) .' التابعة للتذكرة رقم '. $newReservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $request->totalRemain . ' جنيها ';
        $les->admin_id = auth('admin')->id();
        $les->active = 1;
        $les->save();


        // Delete old Seats in Booking Request
        $sub_total_of_old_booking_seats = 0;
        for ($i=0; $i<count($request->seatId); $i++)
        {
            $bookingSeats = BookingSeat::find($request->seatId[$i]);
            $sub_total_of_old_booking_seats += $bookingSeats->sub_total;
            $bookingSeats->delete();
        }


        // Minus sub_total of deleted seats from its ticket
        $reservationBookingRequest->sub_total -= $sub_total_of_old_booking_seats;
        $reservationBookingRequest->update();




        return redirect()->route('reservationBookingRequests.editPage')->with('alert-success','تم تحديث البيانات بنجاح');
    }



    /*** Cancel Booking Request new trip ***/
    public function cancelBooking(Request $request)
    {

        $reservationBookingRequest = ReservationBookingRequest::find($request->reservationBookingRequest_id);


        if ($request->has('totalCount'))  // Delete only old seats
        {
            // create trigger
            $les = new Les();
            $les->amount = $request->totalRemain;
            $les->type = 2;
            $les->ticket_id = null;
            $les->action =  ' تم إلغاء عدد مقاعد ' . count($request->seatId) . ' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل باسترداد مبلغ قدره ' . $request->totalRemain . ' جنيها ';
            $les->admin_id = auth('admin')->id();
            $les->active = 1;
            $les->save();


            // Delete old Seats in Booking Request
            $sub_total_of_old_booking_seats = 0;
            for ($i=0; $i<count($request->seatId); $i++)
            {
                $bookingSeats = BookingSeat::find($request->seatId[$i]);
                $sub_total_of_old_booking_seats += $bookingSeats->sub_total;
                $bookingSeats->delete();
            }


            // Minus sub_total of deleted seats from its ticket
            $reservationBookingRequest->sub_total -= $sub_total_of_old_booking_seats;
            $reservationBookingRequest->update();

        }
         else{ // Delete the whole Ticket


             // related seats of the ticket
             $booking_seats = $reservationBookingRequest->bookingSeats;

             // create trigger
             $les = new Les();
             $les->amount = $request->totalRemain;
             $les->type = 2;
             $les->ticket_id = null;
             $les->action =  ' تم إلغاء التذكرة رقم ' . $reservationBookingRequest->id . ' والتي تحتوي علي عدد مقاعد '.  count($booking_seats)  .' وقام العميل باسترداد مبلغ قدره ' . $request->totalRemain . ' جنيها ';
             $les->admin_id = auth('admin')->id();
             $les->active = 1;
             $les->save();


             // Delete old Seats in Booking Request
             $sub_total_of_old_booking_seats = 0;
             foreach ($booking_seats as $seat)
             {

                 $sub_total_of_old_booking_seats += $seat->sub_total;
                 $seat->delete();
             }

             // Delete the ticket
             $reservationBookingRequest->delete();
         }




        return redirect()->route('reservationBookingRequests.editPage')->with('alert-success','تم تحديث البيانات بنجاح');
    }



    /*** get ticket design ***/
    public function getTicketDesign($reservationBookingRequest_id, $paid_user = null)
    {
        $reservationBookingRequest = ReservationBookingRequest::find($reservationBookingRequest_id);

        $company_arabic_name = $this->company_arabic_name;
        $company_english_name = $this->company_english_name;

        if ($paid_user != null)
        {
            return view('pages.ReservationBookingRequests.ticketDesign',compact('reservationBookingRequest','paid_user','company_arabic_name','company_english_name'));
        }
        return view('pages.ReservationBookingRequests.ticketDesign',compact('reservationBookingRequest','company_arabic_name','company_english_name'));
    }



    /*** ticket_back page ***/
    public function ticket_back(Request $request)
    {
        $tripData = TripData::findOrFail($request->tripData_id);
        $tripDegrees = $tripData->tripDegrees;
        $linesOfTrip = Line::where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->get();
        $tripSeats = TripSeat::where('tripData_id',$request->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        return view('pages.ReservationBookingRequests.ticketBack.ticketBack',compact('request','tripDegrees','tripData','tripSeats','busType','linesOfTrip'));
    }



    /*** ticket_road page ***/
    public function ticket_road(Request $request)
    {
        $tripData = TripData::findOrFail($request->tripData_id);
        $tripDegrees = $tripData->tripDegrees;
        $linesOfTrip = Line::where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->get();
        $tripSeats = TripSeat::where('tripData_id',$request->tripData_id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        return view('pages.ReservationBookingRequests.ticketRoad.ticketRoad',compact('request','tripDegrees','tripData','tripSeats','busType','linesOfTrip'));
    }



    /*** save_ticket_back ***/
    public function save_ticket_back(Request $request)
    {
//        return $request;
        $old_ticket = ReservationBookingRequest::find($request->old_ticket_id);
        $user = User::findOrFail($old_ticket->user_id);

        if (!$request->seatId){
            return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
        }


        if ($old_ticket->type == 2){   // BACK

            if ($old_ticket->passenger_type == 1) // egyptian ==>  priceBack
            {
                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceBack;
                    $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
                    $arrPriceBack[] = "$getPriceBackOfDegree";
                    $arrPriceGo[] = "$getPriceGoOfDegree";
                    $degrees[] = $getPriceBackOfDegree;
                }

                $newArrPriceBack = array_count_values($arrPriceBack);
                foreach ($newArrPriceBack as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceBack[] = $multiply;
                }
                $totalPriceBack = array_sum($arrMultiplyPriceBack); // total of priceBack



                $newArrPriceGo = array_count_values($arrPriceGo);
                foreach ($newArrPriceGo as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceGo[] = $multiply;
                }
                $totalPriceGo = array_sum($arrMultiplyPriceGo); // total of priceGo


                $total = $totalPriceBack;
                $totalGo = $totalPriceGo;

                $wallet = $total - $totalGo;  // wallet


            }
            else{ // foreign ==>  priceForeignerBack

                for ($i=0; $i<count($request->seatId); $i++)
                {
                    $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id; //1
                    $getPriceForeignerBackOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerBack;
                    $getPriceForeignerGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceForeignerGo;
                    $arrPriceForeignerBack[] = "$getPriceForeignerBackOfDegree";
                    $arrPriceForeignerGo[] = "$getPriceForeignerGoOfDegree";
                    $degrees[] = $getPriceForeignerBackOfDegree;
                }


                $newArrPriceForeignerBack = array_count_values($arrPriceForeignerBack);
                foreach ($newArrPriceForeignerBack as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceForeignerBack[] = $multiply;
                }
                $totalPriceForeignerBack = array_sum($arrMultiplyPriceForeignerBack);  // total of priceBack



                $newArrPriceForeignerGo = array_count_values($arrPriceForeignerGo);
                foreach ($newArrPriceForeignerGo as $key=>$value)
                {
                    $multiply = $key * $value;

                    $arrMultiplyPriceForeignerGo[] = $multiply;
                }
                $totalPriceForeignerGo = array_sum($arrMultiplyPriceForeignerGo);  // total of priceBack


                $total = $totalPriceForeignerBack;
                $totalGo = $totalPriceForeignerGo;

                $wallet = $total - $totalGo;  // wallet

            }

        }


        // no coupons
        $total_discount_booking = 0; // no coupons
        $sub_total_booking = $total; // no coupons

        for ($i=0; $i<count($request->seatId); $i++)
        {
            $sub_total_seat[] = $degrees[$i];
        }




        // make new ticket
        $reservationBookingRequest = new ReservationBookingRequest();
        $reservationBookingRequest->runTrip_id = $request['runTrip_id'];
        $reservationBookingRequest->trip_id = $request['tripData_id'];
        $reservationBookingRequest->go_ticket_id = $old_ticket->id;
        $reservationBookingRequest->user_id = $old_ticket->user_id;
        $reservationBookingRequest->stationFrom_id = $request['stationFrom_id'];
        $reservationBookingRequest->stationTo_id = $request['stationTo_id'];
        $reservationBookingRequest->total = $total;
        $reservationBookingRequest->discount = $total_discount_booking;
        $reservationBookingRequest->sub_total = $sub_total_booking;
        $reservationBookingRequest->wallet = $wallet;
        $reservationBookingRequest->type = $old_ticket->type;
        $reservationBookingRequest->passenger_type = $old_ticket->passenger_type;
        $reservationBookingRequest->admin_id = auth('admin')->id();
        $reservationBookingRequest->active = 1;
        $reservationBookingRequest->save();


        // update old ticket
        $old_ticket->go_ticket_id = $reservationBookingRequest->id;
        $old_ticket->update();




        // create booking seats
        for ($i=0; $i<count($request->seatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id;

            $bookingSeats = new BookingSeat();
            $bookingSeats->booking_id = $reservationBookingRequest->id;
            $bookingSeats->runTrip_id = $request['runTrip_id'];
            $bookingSeats->seat_id = $request->seatId[$i];
            $bookingSeats->degree_id = $getDegree;
            $bookingSeats->office_id =  auth('admin')->user()->office_id;
            $bookingSeats->city_id = auth('admin')->user()->office->station->city_id;
            $bookingSeats->total =  $degrees[$i];
            $bookingSeats->sub_total =  $sub_total_seat[$i];
            $bookingSeats->admin_id = auth('admin')->id();
            $bookingSeats->active = 1;
            $bookingSeats->save();
        }


        // create trigger
        $les = new Les();
        if ($user->wallet < $sub_total_booking)
        {
            $les->amount = $sub_total_booking - $user->wallet;
            $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $les->amount . ' جنيها ';
        }
        else{
            $les->amount = 0;
            $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وتم تصفية حساب العميل ';
        }
        $les->type = 1;
        $les->ticket_id = $reservationBookingRequest->id;
        $les->admin_id = auth('admin')->id();
        $les->active = 1;
        $les->save();


        // update user_wallet
        $user->wallet = $user->wallet - $old_ticket->sub_total;
        $user->update();


        $reservationBookingRequest_id = $reservationBookingRequest->id;
        $paid_user = $les->amount;

        return redirect()->route('reservationBookingRequests.getTicketDesign',[$reservationBookingRequest_id,$paid_user]);
    }



    /*** save_ticket_road ***/
    public function save_ticket_road(Request $request)
    {
//        return $request;
        if (!$request->seatId){
            return redirect()->back()->with('alert-danger','برجاء اختيار مقعد');
        }



        $trip_type = 3; // road booking
        $passenger_type = 1;  // egyptian

        for ($i=0; $i<count($request->seatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->seatId[$i])->where('tripData_id',$request->tripData_id)->first()->degree->id; //1
            $getPriceGoOfDegree  = Line::where('degree_id',$getDegree)->where('tripData_id',$request->tripData_id)->where('from_id',$request->stationFrom_id)->where('to_id',$request->stationTo_id)->first()->priceGo;
            $arr[] = "$getPriceGoOfDegree";
            $degrees[] = $getPriceGoOfDegree;
        }

        $newArr = array_count_values($arr);

        foreach ($newArr as $key=>$value)
        {
            $multiply = $key * $value;

            $arrMultiply[] = $multiply;
        }
        $total = array_sum($arrMultiply);
        $wallet = 0;




        // no coupons
        $total_discount_booking = 0; // no coupons
        $sub_total_booking = $request->save_ticket_road ;
        $sub_total_seat =($sub_total_booking / count($request->seatId));




        // make new ticket
        $reservationBookingRequest = new ReservationBookingRequest();
        $reservationBookingRequest->runTrip_id = $request['runTrip_id'];
        $reservationBookingRequest->trip_id = $request['tripData_id'];
        $reservationBookingRequest->stationFrom_id = $request['stationFrom_id'];
        $reservationBookingRequest->stationTo_id = $request['stationTo_id'];
        $reservationBookingRequest->total = $total;
        $reservationBookingRequest->discount = $total_discount_booking;
        $reservationBookingRequest->sub_total = $sub_total_booking;
        $reservationBookingRequest->wallet = $wallet;
        $reservationBookingRequest->type = $trip_type;
        $reservationBookingRequest->passenger_type = $passenger_type;
        $reservationBookingRequest->admin_id = auth('admin')->id();
        $reservationBookingRequest->active = 1;
        $reservationBookingRequest->save();





        // create booking seats
        for ($i=0; $i<count($request->seatId); $i++)
        {
            $getDegree = TripSeat::where('id',$request->seatId[$i])->first()->degree->id;

            $bookingSeats = new BookingSeat();
            $bookingSeats->booking_id = $reservationBookingRequest->id;
            $bookingSeats->runTrip_id = $request['runTrip_id'];
            $bookingSeats->seat_id = $request->seatId[$i];
            $bookingSeats->degree_id = $getDegree;
            $bookingSeats->office_id =  auth('admin')->user()->office_id;
            $bookingSeats->city_id = auth('admin')->user()->office->station->city_id;
            $bookingSeats->total =  $degrees[$i];
            $bookingSeats->sub_total =  $sub_total_seat;
            $bookingSeats->admin_id = auth('admin')->id();
            $bookingSeats->active = 1;
            $bookingSeats->save();
        }


        // create trigger
        $les = new Les();
        $les->amount = $sub_total_booking;
        $les->type = 1;
        $les->ticket_id = $reservationBookingRequest->id;
        $les->action =  ' تم حجز عدد مقاعد '. count($request->seatId) .' التابعة للتذكرة رقم '. $reservationBookingRequest->id  .' وقام العميل بدفع مبلغ قدره ' . $sub_total_booking . ' جنيها ';
        $les->admin_id = auth('admin')->id();
        $les->active = 1;
        $les->save();



        return redirect()->back()->with('alert-success','تم حفظ البيانات بنجاح');

//        $reservationBookingRequest_id = $reservationBookingRequest->id;

//        return redirect()->route('reservationBookingRequests.getTicketDesign',$reservationBookingRequest_id);
    }



//    /*** print_tabloh_page ***/
//    public function print_tabloh_page(Request $request)
//    {
//        $drivers = Driver::select('id','name')->get();
//        $buses = Bus::select('id','code')->get();
//        $hosts = Admin::where('type',3)->select('id','name')->get();
//        return view('pages.ReservationBookingRequests.print_tabloh',compact('request','drivers','buses','hosts'));
//    }



    /*** print_tabloh ***/
    public function print_tabloh(Request $request)
    {
        // all trip_seats
        $trip_seats = TripSeat::where('tripData_id',$request->tripData_id)->get();

        // only booked seats
        $get_booking_seats = BookingSeat::whereHas('reservationBooking',function($q) use ($request){
              $q->where('stationFrom_id','=',$request->stationFrom_id)->where('stationTo_id','=',$request->stationTo_id)
                ->where('runTrip_id','=',$request->runTrip_id)->where('trip_id','=',$request->tripData_id);
        });


        // get only booked seats
        $booking_seats = $get_booking_seats->get();

        // sub_total of booked seats
        $booking_seats_sub_total = $get_booking_seats->sum('sub_total');

        $run_trip = RunTrip::find($request->runTrip_id);

        $trip_data = TripData::find($request->tripData_id);

        $station_from = Station::find($request->stationFrom_id);

        $station_to = Station::find($request->stationTo_id);

        $bus = Bus::find($request->bus_id);

        $driver = Driver::find($request->driver_id);

        $extra_driver = Driver::find($request->extra_driver_id);

        $host = Admin::find($request->host_id);


        $company_arabic_name = $this->company_arabic_name;
        $company_english_name = $this->company_english_name;

        return view('pages.ReservationBookingRequests.print_tabloh_page',compact('booking_seats','trip_seats','booking_seats_sub_total','run_trip','trip_data','station_from','station_to','bus','driver','extra_driver','host','request','company_arabic_name','company_english_name'));

    }



    /*** print_noulon ***/
    public function print_noulon(Request $request)
    {
//        return $request;


    $get_shippings = Shipping::whereHas('runTrip',function($q1) use ($request){

                $q1->where('id',$request->runTrip_id)->whereHas('tripData',function($q2) use($request){

                    $q2->where('id',$request->tripData_id);
                });
          })->where('from_station_id',$request->stationFrom_id)->where('to_station_id',$request->stationTo_id);


//     return   $get_shippings = DB::table('shippings')
//            ->join('run_trips','run_trips.id','=','shippings.run_trip_id')
//            ->join('trip_data','trip_data.id','=','run_trips.tripData_id')
//            ->where('shippings.deleted_at','=',null)
//            ->where('trip_data.id',$request->tripData_id)
//            ->where('run_trips.id',$request->runTrip_id)
//            ->where('shippings.from_station_id',$request->stationFrom_id)
//            ->where('shippings.to_station_id',$request->stationTo_id)
//            ->select('shippings.*','shippings.id as shipping_id')->get();


        $shippings = $get_shippings->get();

        // sub_total of shippings
        $shippings_sub_total = $get_shippings->sum('price');

        $run_trip = RunTrip::find($request->runTrip_id);

        $trip_data = TripData::find($request->tripData_id);

        $station_from = Station::find($request->stationFrom_id);

        $station_to = Station::find($request->stationTo_id);

        $bus = Bus::find($request->bus_id);

        $driver = Driver::find($request->driver_id);



        return view('pages.ReservationBookingRequests.print_noloun_page',compact('shippings','shippings_sub_total','run_trip','trip_data','station_from','station_to','bus','driver','request'));

    }

} //end of class
