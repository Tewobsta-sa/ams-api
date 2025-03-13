<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $youngClasses = ['htsane_kirkos', 'bietel', 'gelila', 'efriata', 'yordanos', 'keraniyo'];
        $adultClasses = ['kedamay', 'kaleay', 'salsay', 'rabeay', 'hamsay', 'sadsay'];

        foreach ($youngClasses as $class) {
            Classes::create(['name' => $class, 'student_type' => 'young']);
        }

        foreach ($adultClasses as $class) {
            Classes::create(['name' => $class, 'student_type' => 'adult']);
        }
    }
}
