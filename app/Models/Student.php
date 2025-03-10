<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'christian_name', 
        'birth_date',
        'age',
        'educational_level',
        'discrit',
        'special_place',
        'house_no',
        'student_type',
    ];

    public function youngStudent()
    {
        return $this->hasOne(YoungStudent::class);
    }

    public function adultStudent()
    {
        return $this->hasOne(AdultStudent::class);
    }
}
