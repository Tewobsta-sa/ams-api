<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ✅ FIXED: Capital "I"
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'student_type'];

    // Define relationship with Young Students
    public function youngStudents()
    {
        return $this->hasMany(YoungStudent::class, 'class_id');
    }

    // Define relationship with Adult Students
    public function adultStudents()
    {
        return $this->hasMany(AdultStudent::class, 'class_id');
    }
}
