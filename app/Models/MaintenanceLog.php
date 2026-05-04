<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = ['vehicle_id', 'service_type', 'cost', 'date_performed', 'mileage_at_service'];

    public function vehicle() {

    return $this->belongsTo(Vehicle::class);
    
   }
}
