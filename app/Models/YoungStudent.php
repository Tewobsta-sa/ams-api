<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoungStudent extends Model
{
    protected $fillable = [
        'student_id',
        'parent_name',
        'parent_phone_number',
        'school_name',
        'class_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
