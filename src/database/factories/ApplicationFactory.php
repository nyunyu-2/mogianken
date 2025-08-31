<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'status' => '承認待ち',
            'reason' => $this->faker->sentence,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ];
    }
}
