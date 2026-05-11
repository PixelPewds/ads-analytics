[
                'required',
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:51200', // 50 MB
            ],
            'account_name' => ['required', 'string', 'max:255'],
            'account_id'   => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a CSV or Excel file to upload.',
            'file.mimes'    => 'Only CSV and Excel (.xlsx/.xls) files are accepted.',
            'file.max'      => 'File size must not exceed 50 MB.',
        ];
    }
}