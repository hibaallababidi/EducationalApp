<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable=[
        'reporter_id',
        'reported_at_id',
        'reporter_type',
        'reported_at_type',
        'message'
    ];
}
