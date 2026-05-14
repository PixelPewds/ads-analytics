<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report' => [
                'required',
                'file',
                'max:20480', // 20 MB
                'mimes:csv,xlsx,xls',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'report.required' => 'Please select a file to upload.',
            'report.mimes'    => 'Only CSV and Excel (XLSX/XLS) files are accepted.',
            'report.max'      => 'The file must not exceed 20 MB.',
        ];
    }
}