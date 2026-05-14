[
                'required',
                'file',
                'max:20480', // 20MB
                'mimes:csv,xlsx,xls',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'report.required' => 'Please select a file to upload.',
            'report.mimes'    => 'Only CSV and Excel (XLSX/XLS) files are accepted.',
            'report.max'      => 'The file must not exceed 20MB.',
        ];
    }
}