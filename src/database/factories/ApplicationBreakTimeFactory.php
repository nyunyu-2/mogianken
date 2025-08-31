<?php

namespace Database\Factories;

use App\Models\ApplicationBreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationBreakTimeFactory extends Factory
{
    protected $model = ApplicationBreakTime::class;

    public function definition()
    {
        return [
            'application_id' => null,
            'break_in_time' => now(),
            'break_out_time' => null,
        ];
    }
}
