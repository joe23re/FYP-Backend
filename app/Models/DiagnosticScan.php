<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticScan extends Model
{
    protected $fillable = ['vehicle_id', 'dtc_id', 'mileage_at_scan', 'status'];

    public function vehicle() {

    return $this->belongsTo(Vehicle::class);

   }

    public function dtc() {

    return $this->belongsTo(DtcLibrary::class, 'dtc_id');
    
   }
}
