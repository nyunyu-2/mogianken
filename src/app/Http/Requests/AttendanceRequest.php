<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in_time' => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i'],
            'reason' => ['required'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in_time.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_time.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'breaks.*.start.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->clock_in_time ? Carbon::createFromFormat('H:i', $this->clock_in_time) : null;
            $clockOut = $this->clock_out_time ? Carbon::createFromFormat('H:i', $this->clock_out_time) : null;

            // 1. 出勤 > 退勤 or 退勤 < 出勤
            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $validator->errors()->add('clock_in_time', '出勤時間もしくは退勤時間が不適切な値です');
                $validator->errors()->add('clock_out_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩のバリデーション
            if ($this->breaks) {
                foreach ($this->breaks as $i => $break) {
                    $breakStart = isset($break['start']) ? Carbon::createFromFormat('H:i', $break['start']) : null;
                    $breakEnd   = isset($break['end']) ? Carbon::createFromFormat('H:i', $break['end']) : null;

                    // 2. 休憩開始が出勤前 or 退勤後
                    if ($breakStart && $clockIn && $breakStart->lt($clockIn)) {
                        $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                    }
                    if ($breakStart && $clockOut && $breakStart->gt($clockOut)) {
                        $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                    }

                    // 3. 休憩終了 > 退勤
                    if ($breakEnd && $clockOut && $breakEnd->gt($clockOut)) {
                        $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
