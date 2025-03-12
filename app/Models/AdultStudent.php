<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultStudent extends Model
{
    protected $fillable = [
        'student_id',
        'emergency_responder',
        'phone_number',
        'emergency_responder_phone_number',
        'class_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
