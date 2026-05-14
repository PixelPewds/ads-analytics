<x-layouts.app :title="$title ?? 'Dashboard'">

@push('head')
<style>
    .level-btn.active { background-color: #2F5061; color: white; }
</style>
@endpush

{{-- Report selector bar --}}
@if($reports->isNotEmpty())
<div class="mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('dashboard') }}" id="reportForm" class="flex flex-wrap items-center gap-3 w-full">

        {{-- Report selector --}}
        <div class="relative flex-1 min-w-48">
            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">Report</label>
            <select
                name="report_id"
                onchange="document.getElementById('reportForm').submit()"
                class="w-full pl-3 pr-8 py-2 rounded-xl border border-gray-200 bg-white text-sm text-[#2F5061] font-medium focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0] appearance-none cursor-pointer"
            >
                @foreach($reports as $report)
                    <option value="{{ $report->id }}" {{ ($activeReport && $report->id === $activeReport->id) ? 'selected' : '' }}>
                        {{ $report->original_filename }} — {{ $report->date_range_label }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($activeReport)
            {{-- Date start --}}
            <div class="min-w-36">
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">From</label>
                <input type="date" name="date_start" value="{{ $activeFilters['date_start'] ?? '' }}"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0]">
            </div>

            {{-- Date end --}}
            <div class="min-w-36">
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">To</label>
                <input type="date" name="date_end" value="{{ $activeFilters['date_end'] ?? '' }}"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0]">
            </div>

            {{-- Campaign filter --}}
            @if(!empty($filters['campaigns']))
            <div class="min-w-44">
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">Campaign</label>
                <select name="campaign"
                    class="w-full pl-3 pr-8 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0] appearance-none">
                    <option value="">All campaigns</option>
                    @foreach($filters['campaigns'] as $c)
                        <option value="{{ $c }}" {{ ($activeFilters['campaign'] ?? '') === $c ? 'selected' : '' }}>
                            {{ Str::limit($c, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Level selector --}}
            <div class="min-w-44">
                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider block mb-1">View Level</label>
                <select name="level"
                    class="w-full pl-3 pr-8 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0] appearance-none">
                    <option value="campaign" {{ request('level', 'campaign') === 'campaign' ? 'selected' : '' }}>Campaign Level</option>
                    <option value="adset" {{ request('level') === 'adset' ? 'selected' : '' }}>Adset Level</option>
                    <option value="ad" {{ request('level') === 'ad' ? 'selected' : '' }}>Ad Level</option>
                    <option value="all" {{ request('level') === 'all' ? 'selected' : '' }}>All Levels</option>
                </select>
            </div>

            <div class="flex gap-2 self-end">
                <button type="submit" class="px-4 py-2 bg-[#4297A0] hover:bg-[#3a8a93] text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Apply
                </button>
                <a href="{{ route('dashboard', ['report_id' => $activeReport->id]) }}" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        @endif
    </form>
</div>
@endif

{{-- No reports state --}}
@if($reports->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
        <div class="w-20 h-20 rounded-2xl bg-[#2F5061]/10 flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-[#2F5061]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-[#2F5061] font-[Barlow] mb-2">No reports yet</h2>
        <p class="text-gray-500 text-sm mb-6 max-w-sm">Upload your first Meta Ads CSV or XLSX report to start analyzing campaign performance.</p>
        <a href="{{ route('upload.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#E57F84] hover:bg-[#d96e73] text-white font-semibold text-sm rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload First Report
        </a>
    </div>
@else

{{-- ═══ KPI CARDS ═══ --}}
<section class="mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

        <x-kpi-card
            label="Total Spend"
            prefix="$"
            :value="number_format($kpis['total_spend'] ?? 0, 2)"
            color="coral"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 8v1m0-8c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />

        <x-kpi-card
            label="Impressions"
            :value="number_format($kpis['total_impressions'] ?? 0)"
            color="misty"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
        />

        <x-kpi-card
            label="Total Clicks"
            :value="number_format($kpis['total_clicks'] ?? 0)"
            color="teal"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>'
        />

        <x-kpi-card
            label="CTR"
            :value="$kpis['ctr'] ?? 0"
            suffix="%"
            color="teal"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'
        />

        <x-kpi-card
            label="CPC"
            prefix="$"
            :value="number_format($kpis['cpc'] ?? 0, 2)"
            color="amber"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
        />

        <x-kpi-card
            label="Conversions"
            :value="number_format($kpis['total_conversions'] ?? 0)"
            color="emerald"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />

        <x-kpi-card
            label="CAC"
            prefix="$"
            :value="number_format($kpis['cac'] ?? 0, 2)"
            sublabel="Cost per Conversion"
            color="coral"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>'
        />

        <x-kpi-card
            label="Conversations"
            :value="number_format($kpis['total_conversations'] ?? 0)"
            color="misty"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'
        />

        <x-kpi-card
            label="Cost / Conv."
            prefix="$"
            :value="number_format($kpis['cost_per_conversation'] ?? 0, 2)"
            sublabel="per conversation"
            color="teal"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>'
        />

        <x-kpi-card
            label="ROAS"
            :value="number_format($kpis['roas'] ?? 0, 2)"
            suffix="x"
            color="emerald"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 8v1m0-8c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        />
    </div>
</section>

{{-- ═══ CHARTS ═══ --}}
@if(!empty($timeline['labels']))
<section class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">

    <x-chart-card title="Daily Spend" subtitle="Spend over reporting period" id="spendChart" height="260">
        @slot('actions')
            <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">{{ $activeReport->date_range_label }}</span>
        @endslot
    </x-chart-card>

    <x-chart-card title="Conversions & Conversations" subtitle="Daily volume trend" id="convChart" height="260"/>

    <x-chart-card title="CTR Trend" subtitle="Click-through rate daily" id="ctrChart" height="220"/>

    <x-chart-card title="CPC Trend" subtitle="Cost per click daily" id="cpcChart" height="220"/>

</section>
@endif

{{-- ═══ CAMPAIGN TABLE ═══ --}}
@if(!empty($campaignData))
<section class="mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow]">Campaign Performance</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ count($campaignData) }} campaigns sorted by spend</p>
            </div>
            <span class="text-xs px-2.5 py-1 bg-[#4297A0]/10 text-[#4297A0] rounded-full font-medium">Campaign Level</span>
        </div>
        <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Campaign</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Spend</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Impr.</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Clicks</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CTR</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CPC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Conv.</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CAC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ROAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($campaignData as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-[#2F5061] text-sm">{{ Str::limit($row['name'], 45) }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-semibold text-gray-800">${{ number_format($row['spend'], 2) }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ number_format($row['impressions']) }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ number_format($row['clicks']) }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-medium {{ $row['ctr'] >= 2 ? 'text-emerald-600' : ($row['ctr'] >= 1 ? 'text-[#4297A0]' : 'text-gray-500') }}">
                                    {{ $row['ctr'] }}%
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-gray-600">${{ $row['cpc'] }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ number_format($row['conversions']) }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">${{ number_format($row['cac'], 2) }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-semibold {{ $row['roas'] >= 2 ? 'text-emerald-600' : ($row['roas'] >= 1 ? 'text-[#4297A0]' : 'text-[#E57F84]') }}">
                                    {{ $row['roas'] }}x
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

{{-- ═══ ADSET TABLE ═══ --}}
@if(!empty($adsetData))
<section class="mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow]">Adset Performance</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ count($adsetData) }} adsets sorted by spend</p>
        </div>
        <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Adset</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Spend</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Clicks</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CTR</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CPC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Conv.</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ROAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($adsetData as $row)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-[#2F5061]">{{ Str::limit($row['name'], 45) }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-gray-800">${{ number_format($row['spend'], 2) }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ number_format($row['clicks']) }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ $row['ctr'] }}%</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">${{ $row['cpc'] }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-600">{{ number_format($row['conversions']) }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold {{ $row['roas'] >= 2 ? 'text-emerald-600' : 'text-gray-600' }}">{{ $row['roas'] }}x</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

{{-- ═══ AI RECOMMENDATIONS ═══ --}}
<section class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-lg font-bold text-[#2F5061] font-[Barlow]">AI Recommendations</h2>
            <p class="text-xs text-gray-400 mt-0.5">Powered by OpenAI analysis of your campaign data</p>
        </div>
        @if($activeReport)
        <form method="POST" action="{{ route('reports.regenerate', $activeReport) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-[#4297A0] border border-[#4297A0]/30 hover:bg-[#4297A0]/10 rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Regenerate
            </button>
        </form>
        @endif
    </div>
    <x-recommendation-panel :recommendations="$recommendations"/>
</section>

@endif {{-- end $reports->isNotEmpty() --}}

@push('scripts')
<script>
(function() {
    const timeline = @json($timeline ?? []);
    if (!timeline || !timeline.labels || !timeline.labels.length) return;

    const defaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#2F5061',
                titleFont: { family: 'Barlow', size: 12, weight: '600' },
                bodyFont: { family: 'IBM Plex Sans', size: 12 },
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { family: 'IBM Plex Sans', size: 11 }, color: '#9ca3af', maxRotation: 45 },
                border: { display: false },
            },
            y: {
                grid: { color: '#f3f4f6' },
                ticks: { font: { family: 'IBM Plex Sans', size: 11 }, color: '#9ca3af' },
                border: { display: false },
            }
        }
    };

    // Spend chart
    new Chart(document.getElementById('spendChart'), {
        type: 'bar',
        data: {
            labels: timeline.labels,
            datasets: [{
                label: 'Daily Spend ($)',
                data: timeline.spend,
                backgroundColor: 'rgba(66,151,160,0.15)',
                borderColor: '#4297A0',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: { ...defaults, plugins: { ...defaults.plugins, legend: { display: false } } }
    });

    // Conversions chart
    new Chart(document.getElementById('convChart'), {
        type: 'line',
        data: {
            labels: timeline.labels,
            datasets: [
                {
                    label: 'Conversions',
                    data: timeline.conversions,
                    borderColor: '#4297A0',
                    backgroundColor: 'rgba(66,151,160,0.08)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4297A0',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                },
                {
                    label: 'Conversations',
                    data: timeline.conversations,
                    borderColor: '#E57F84',
                    backgroundColor: 'rgba(229,127,132,0.06)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#E57F84',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderDash: [5, 3],
                }
            ]
        },
        options: { ...defaults, plugins: { ...defaults.plugins, legend: { display: true, labels: { font: { family: 'IBM Plex Sans', size: 11 }, boxWidth: 10, boxHeight: 10, usePointStyle: true, color: '#6b7280' } } } }
    });

    // CTR chart
    new Chart(document.getElementById('ctrChart'), {
        type: 'line',
        data: {
            labels: timeline.labels,
            datasets: [{
                label: 'CTR (%)',
                data: timeline.ctr,
                borderColor: '#2F5061',
                backgroundColor: 'rgba(47,80,97,0.07)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#2F5061',
                pointHoverRadius: 5,
            }]
        },
        options: { ...defaults }
    });

    // CPC chart
    new Chart(document.getElementById('cpcChart'), {
        type: 'line',
        data: {
            labels: timeline.labels,
            datasets: [{
                label: 'CPC ($)',
                data: timeline.cpc,
                borderColor: '#E57F84',
                backgroundColor: 'rgba(229,127,132,0.08)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#E57F84',
                pointHoverRadius: 5,
            }]
        },
        options: { ...defaults }
    });
})();
</script>
@endpush

</x-layouts.app>
