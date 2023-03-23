<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tel_no',
        'queue_no',
        'status',
        'duration',
    ];
}
