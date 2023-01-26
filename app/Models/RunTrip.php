<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RunTrip extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = ['tripData_id', 'admin_id', 'driver_id', 'bus_id', 'host_id', 'type', 'active',
                            'startDate', 'startTime', 'notes', 'driverTips', 'hostTips','trip_distance'];




    /*** start relations ***/

    public function admin()
    {
        return $this->belongsTo(Admin::class,'admin_id');
    }


    public function tripData()
    {
        return $this->belongsTo(TripData::class,'tripData_id');
    }


    public function driver()
    {
        return $this->belongsTo(Driver::class,'driver_id');
    }


    public function bus()
    {
        return $this->belongsTo(Bus::class,'bus_id');
    }


    public function host()
    {
        return $this->belongsTo(Admin::class,'host_id');
    }


    public function ReservationBookingRequests()
    {
        return $this->hasMany(ReservationBookingRequest::class,'runTrip_id');
    }


    public function bookingSeats()
    {
        return $this->hasMany(BookingSeat::class,'runTrip_id');
    }


    public function shippings()
    {
        return $this->hasMany(Shipping::class,'run_trip_id');
    }

    /*** end relations ***/


} //end of class
