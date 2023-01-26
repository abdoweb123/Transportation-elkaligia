<?php

namespace App\Http\Controllers;

use App\Models\BusType;
use App\Models\Seat;
use App\Models\TripData;
use App\Models\TripDegree;
use App\Models\TripSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TripSeatController extends Controller
{


    /***  showBusTypeSeats  ***/
    public function showBusTypeSeatsOfTrip($id)
    {
        $tripData = TripData::findOrFail($id);
        $tripDegrees = $tripData->tripDegrees;
        $tripSeats = TripSeat::where('tripData_id',$id)->get();
        $busType = BusType::findOrFail($tripData->busType->id);
        $seats = Seat::where('busType_id',$tripData->busType->id)->get();
        return view('pages.TripData.TripSeats.showSeatsBusTypeOfTrip',compact('busType','seats','tripDegrees','tripData','tripSeats'));
    }



    /***  createTripSeats  ***/
    public function createTripSeats(Request $request)
    {

        $inputs = $request->input('type');
        foreach ($inputs as $tripSeat_id => $value)
        {

//            $arrValues[] = $value;
//
//            if ($value == null) {
//                $Value = $request->initial_degree;


                    TripSeat::create([
                        'tripData_id'=>$request->tripData_id,
                        'admin_id'=>auth('admin')->id(),
                        'seat_id'=>$tripSeat_id,
                        'degree_id'=>$value,
                    ]);

//                }
//                 else
//                 {
//                     TripSeat::create([
//                         'tripData_id'=>$request->tripData_id,
//                         'admin_id'=>auth('admin')->id(),
//                         'seat_id'=>$tripSeat_id,
//                         'degree_id'=>$value,
//                     ]);
//                 }

            }



        return redirect()->back()->with('alert-success','تم حفظ البيانات بنجاح');
        }



    /***  updateTripSeats  ***/
    public function updateTripSeats(Request $request)
    {

//        return $request;

        $inputs = $request->input('type');
        foreach ($inputs as $tripSeat_id => $value)
        {

            $tripSeat = TripSeat::where('id',$tripSeat_id)->where('tripData_id',$request->tripData_id)->first();

//            $arrValues[] = $value;
//
//            if ($value == null) {
//                $value = $request->initial_degree;

                $tripSeat->update([
                        'tripData_id'=>$request->tripData_id,
                        'admin_id'=>auth('admin')->id(),
                        'degree_id'=>$value,
                    ]);

//                }
//                 else
//                 {
//                     $tripSeat->update([
//                         'tripData_id'=>$request->tripData_id,
//                         'admin_id'=>auth('admin')->id(),
//                         'degree_id'=>$value,
//                     ]);
//                 }

            }



        return redirect()->back()->with('alert-success','تم حفظ البيانات بنجاح');
        }





} // end of class
