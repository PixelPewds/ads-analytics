validate([
            'report_id'  => ['required', 'integer', 'exists:reports,id'],
            'date_start' => ['nullable', 'date'],
            'date_end'   => ['nullable', 'date'],
            'campaign'   => ['nullable', 'string'],
            'adset'      => ['nullable', 'string'],
            'ad'         => ['nullable', 'string'],
        ]);

        $filters = $request->only(['date_start', 'date_end', 'campaign', 'adset', 'ad']);

        return response()->json([
            'kpis'      => $this->analytics->getKpis($request->report_id, $filters),
            'timeline'  => $this->analytics->getTimeline($request->report_id, $filters),
            'campaigns' => $this->analytics->getCampaignBreakdown($request->report_id, $filters),
        ]);
    }
}