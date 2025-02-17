<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'required|before:clock_out',
            'clock_out' => 'required|after:clock_in',

            // 休憩開始と終了時間のバリデーションを配列として処理
            'break_start.*' => 'nullable|after_or_equal:clock_in|before_or_equal:clock_out',
            'break_end.*' => 'nullable|after_or_equal:clock_in|before_or_equal:clock_out',

            'remarks' => 'required'
        ];
    }

    public function messages()
    {
        return [
            // 入力必須
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'remarks.required' => '備考を記入してください',
            // 退勤時間より前
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            // 出勤時間より後
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            // 出勤時間以降
            'break_start.after_or_equal' => '休憩時間が勤務時間外です',
            'break_end.after_or_equal' => '休憩時間が勤務時間外です',
            // 退勤時間以前
            'break_start.before_or_equal' => '休憩時間が勤務時間外です',
            'break_end.before_or_equal' => '休憩時間が勤務時間外です',
        ];
    }
}
