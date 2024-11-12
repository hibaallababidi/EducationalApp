<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'lesson_date',
        'price',
        'is_confirmed',
        'rate',
        'meet_link'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
