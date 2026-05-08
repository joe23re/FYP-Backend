<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['user_id','brand','model','color','year','plate_number','vin','engine_type','current_mileage'];

     public function user() {

       return $this->belongsTo(User::class);

     }

     public function diagnosticScans() {

      return $this->hasMany(DiagnosticScan::class);

     }

    public function maintenanceLogs() {

      return $this->hasMany(MaintenanceLog::class);
      
     }
}
