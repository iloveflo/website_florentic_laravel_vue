<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date|before_or_equal:today',
            'end_date'   => 'nullable|date|after_or_equal:start_date|before_or_equal:today',
            'period'     => 'nullable|in:day,week,month',
            'days'       => 'nullable|integer|min:1|max:365',
            'limit'      => 'nullable|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.before_or_equal' => 'Ngày bắt đầu không được lớn hơn hôm nay',
            'end_date.after_or_equal'    => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu',
            'period.in'                  => 'Kỳ báo cáo không hợp lệ (chỉ hỗ trợ day, week, month)',
            'days.max'                   => 'Chỉ hỗ trợ tối đa 365 ngày',
        ];
    }
}