<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BreakTimeValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($clockIn, $clockOut)
    {
        // 秒に変換
        $this->clockIn = strtotime($clockIn);
        $this->clockOut = strtotime($clockOut);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // null の場合（休憩時間が入力されていない場合）、true を返してバリデーションを通す。
        if (!$value) {
            return true;
        }

        // 秒に変換
        $breakTime = strtotime($value);

        // 退勤時間が出勤時間より前（=日付を跨ぐ）の場合、翌日扱い
        if ($this->clockOut < $this->clockIn) {
            $this->clockOut += 86400; // 24時間 (60 * 60 * 24)
        }

        if ($breakTime < $this->clockIn) {
            $breakTime += 86400; // 24時間足して翌日扱い
        }

        // 休憩時間が出勤時間と退勤時間の間にあるかチェック
        return ($this->clockIn <= $breakTime && $breakTime <= $this->clockOut);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '休憩時間が勤務時間外です。';
    }
}
