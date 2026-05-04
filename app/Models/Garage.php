<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garage extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude', 'specialization', 'rating_avg'];

    public function reviews(){

    return $this->hasMany(Review::class);
   }


   public function getAverageRatingAttribute() {

    return $this->reviews()->avg('rating');
  } 
}
