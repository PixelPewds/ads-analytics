@extends('layouts.app')

@section('content')
{{-- ══════════════════════════════════════════
     HEADER + REPORT SELECTOR
══════════════════════════════════════════ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#264653]" style="font-family:'Barlow',sans-serif">
            Performance Dashboard
        </h2>
        @if($activeReport)
            <p class="text-sm text-[#6B7C8D] mt-0.5">
                {{ $activeReport->original_filename }}
                &nbsp;·&nbsp;
                <span class="text-[#2A9D8F] font-medium">{{ $activeReport->date_range_label }}</span>
            </p>
        @else
            <p class="text-sm text-[#6B7C8D] mt-0.5">Upload your first Meta Ads CSV to get started.</p>
        @endif
    </div>

    @if($reports->isNotEmpty())
    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2 items-center">
        {{-- Report picker --}}
        <select name="report_id" onchange="this.form.submit()"
                class="text-sm border border-[#D6E1EA] rounded-lg px-3 py-2 bg-white text-[#264653] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40">
            @foreach($reports as $r)
                <option value="{{ $r->id }}" {{ $activeReport?->id == $r->id ? 'selected' : '' }}>
                    {{ Str::limit($r->original_filename, 32) }}
                    @if($r->date_range_start) ({{ $r->date_range_start->format('M d') }} – {{ $r->date_range_end?->format('M d, Y') }}) @endif
                </option>
            @endforeach
        </select>

        {{-- Date range filters --}}
        <input type="date" name="date_start" value="{{ $activeFilters['date_start'] ?? '' }}"
               class="text-sm border border-[#D6E1EA] rounded-lg px-3 py-2 bg-white text-[#264653] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40">
        <input type="date" name="date_end" value="{{ $activeFilters['date_end'] ?? '' }}"
               class="text-sm border border-[#D6E1EA] rounded-lg px-3 py-2 bg-white text-[#264653] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40">

        {{-- Campaign filter --}}
        @if(!empty($filters['campaigns']))
        <select name="campaign"
                class="text-sm border border-[#D6E1EA] rounded-lg px-3 py-2 bg-white text-[#264653] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40">
            <option value="">All Campaigns</option>
            @foreach($filters['campaigns'] as $c)
                <option value="{{ $c }}" {{ ($activeFilters['campaign'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        @endif

        <button type="submit" class="btn-primary text-xs py-2 px-4">Apply</button>
        <a href="{{ route('dashboard', ['report_id' => $activeReport?->id]) }}" class="text-xs text-[#6B7C8D] hover:text-[#264653] underline">Reset</a>
    </form>
    @endif
</div>

{{-- ── No data state ────────────────────────────────────────────── --}}
@if(!$activeReport)
    <div class="chart-card flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-16 h-16 text-[#D6E1EA] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-lg font-bold text-[#264653] mb-2">No Reports Yet</h3>
        <p class="text-[#6B7C8D] mb-6 max-w-sm">Upload your Meta Ads CSV or Excel report to see KPIs, charts, and AI-powered recommendations.</p>
        <a href="{{ route('upload.index') }}" class="btn-coral">Upload Your First Report</a>
    </div>
@else

{{-- ══════════════════════════════════════════
     KPI CARDS
══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    <x-kpi-card
        label="Total Spend"
        :value="'$' . number_format($kpis['total_spend'] ?? 0, 2)"
        icon="currency-dollar"
        color="teal"
    />
    <x-kpi-card
        label="Impressions"
        :value="number_format($kpis['total_impressions'] ?? 0)"
        icon="eye"
        color="blue"
    />
    <x-kpi-card
        label="Clicks"
        :value="number_format($kpis['total_clicks'] ?? 0)"
        icon="cursor-click"
        color="slate"
    />
    <x-kpi-card
        label="CTR"
        :value="($kpis['ctr'] ?? 0) . '%'"
        icon="trending-up"
        color="coral"
    />
    <x-kpi-card
        label="CPC"
        :value="'$' . number_format($kpis['cpc'] ?? 0, 2)"
        icon="tag"
        color="teal"
    />
    <x-kpi-card
        label="ROAS"
        :value="($kpis['roas'] ?? 0) . '×'"
        icon="chart-bar"
        color="coral"
    />

</div>

{{-- Second row KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <x-kpi-card
        label="Conversions"
        :value="number_format($kpis['total_conversions'] ?? 0, 1)"
        icon="check-circle"
        color="slate"
    />
    <x-kpi-card
        label="CAC"
        :value="'$' . number_format($kpis['cac'] ?? 0, 2)"
        icon="user"
        color="blue"
    />
    <x-kpi-card
        label="Conversations"
        :value="number_format($kpis['total_conversations'] ?? 0, 0)"
        icon="chat"
        color="teal"
    />
    <x-kpi-card
        label="Cost / Conv."
        :value="'$' . number_format($kpis['cost_per_conversation'] ?? 0, 2)"
        icon="receipt"
        color="coral"
    />
</div>

{{-- ══════════════════════════════════════════
     TIMELINE CHART
══════════════════════════════════════════ --}}
<div class="chart-card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-[#264653]" style="font-family:'Barlow',sans-serif">
            Spend · CTR · CPC Timeline
        </h3>
        <span class="text-xs text-[#6B7C8D]">{{ count($timeline['labels'] ?? []) }} data points</span>
    </div>
    @if(!empty($timeline['labels']))
        <div class="relative h-72">
            <canvas id="timelineChart"></canvas>
        </div>
    @else
        <div class="flex items-center justify-center h-48 text-[#6B7C8D] text-sm">No timeline data for selected filters.</div>
    @endif
</div>

{{-- ══════════════════════════════════════════
     CAMPAIGN BREAKDOWN + CHART
══════════════════════════════════════════ --}}
@if(!empty($campaignData))
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- Campaign bar chart --}}
    <div class="chart-card">
        <h3 class="text-base font-bold text-[#264653] mb-4" style="font-family:'Barlow',sans-serif">
            Campaign Performance
        </h3>
        <div class="relative h-64">
            <canvas id="campaignChart"></canvas>
        </div>
    </div>

    {{-- Campaign table --}}
    <div class="chart-card overflow-x-auto">
        <h3 class="text-base font-bold text-[#264653] mb-4" style="font-family:'Barlow',sans-serif">
            Campaign Breakdown
        </h3>
        <table class="analytics-table w-full text-left">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th class="text-right">Spend</th>
                    <th class="text-right">CTR</th>
                    <th class="text-right">CPC</th>
                    <th class="text-right">ROAS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaignData as $row)
                <tr>
                    <td class="font-medium text-[#264653] max-w-xs">
                        <span class="block truncate" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                    </td>
                    <td class="text-right text-[#2A9D8F] font-semibold">${{ number_format($row['spend'], 2) }}</td>
                    <td class="text-right">{{ $row['ctr'] }}%</td>
                    <td class="text-right">${{ $row['cpc'] }}</td>
                    <td class="text-right font-semibold {{ $row['roas'] >= 2 ? 'text-green-600' : ($row['roas'] >= 1 ? 'text-yellow-600' : 'text-red-500') }}">
                        {{ $row['roas'] }}×
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endif

{{-- ══════════════════════════════════════════
     AI RECOMMENDATIONS
══════════════════════════════════════════ --}}
@if($recommendations->isNotEmpty())
    <x-recommendation-panel :recommendations="$recommendations" :report="$activeReport" />
@else
    <div class="chart-card flex items-center gap-4 p-5 mb-6">
        <div class="w-10 h-10 rounded-xl bg-[#EDE9FE] flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-[#264653]">No AI recommendations yet</p>
            <p class="text-sm text-[#6B7C8D]">
                Set <code class="bg-[#E8EEF4] px-1 rounded">OPENAI_API_KEY</code> in your <code class="bg-[#E8EEF4] px-1 rounded">.env</code> and click regenerate.
            </p>
        </div>
        <form method="POST" action="{{ route('reports.regenerate', $activeReport) }}" class="ml-auto flex-shrink-0">
            @csrf
            <button class="btn-primary text-xs py-2">Generate</button>
        </form>
    </div>
@endif

@endif {{-- end activeReport --}}

{{-- ══════════════════════════════════════════
     CHART INIT SCRIPTS
══════════════════════════════════════════ --}}
@if($activeReport && !empty($timeline['labels']))
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.initTimelineChart('timelineChart', @json($timeline));

    @if(!empty($campaignData))
    window.initCampaignChart('campaignChart', @json($campaignData));
    @endif
});
</script>
@endif

@endsection