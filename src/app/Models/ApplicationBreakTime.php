<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationBreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['application_id', 'break_in_time', 'break_out_time'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
