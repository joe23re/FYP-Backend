<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DtcLibrary extends Model
{
    protected $table = 'dtc_library';
    protected $fillable = ['code', 'description', 'vag_specific_info', 'severity', 'possible_causes'];

     protected $casts = [
    'possible_causes' => 'array', // Crucial for PostgreSQL JSONB
    ];
}
