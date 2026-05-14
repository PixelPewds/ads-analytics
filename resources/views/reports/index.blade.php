@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#264653]" style="font-family:'Barlow',sans-serif">Reports</h2>
        <p class="text-sm text-[#6B7C8D] mt-0.5">All uploaded Meta Ads reports. Click a report to view its dashboard.</p>
    </div>
    <a href="{{ route('upload.index') }}" class="btn-coral text-xs flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Upload
    </a>
</div>

@if($reports->isEmpty())
    <div class="chart-card flex flex-col items-center justify-center py-16 text-center">
        <svg class="w-12 h-12 text-[#D6E1EA] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-[#264653] font-semibold mb-1">No reports yet</p>
        <p class="text-[#6B7C8D] text-sm">Upload your first Meta Ads export to get started.</p>
    </div>
@else
    <div class="chart-card overflow-x-auto">
        <table class="analytics-table w-full text-left">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Date Range</th>
                    <th class="text-right">Rows</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="max-w-xs">
                        <p class="font-medium text-[#264653] truncate" title="{{ $report->original_filename }}">
                            {{ $report->original_filename }}
                        </p>
                    </td>
                    <td class="text-[#6B7C8D] whitespace-nowrap">{{ $report->date_range_label }}</td>
                    <td class="text-right text-[#6B7C8D]">{{ number_format($report->row_count) }}</td>
                    <td>
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $report->status === 'processed' ? 'bg-green-100 text-green-700' : ($report->status === 'failed' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ $report->status }}
                        </span>
                    </td>
                    <td class="text-[#6B7C8D] whitespace-nowrap text-sm">{{ $report->created_at->format('M d, Y H:i') }}</td>
                    <td class="text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            @if($report->status === 'processed')
                                <a href="{{ route('dashboard', ['report_id' => $report->id]) }}"
                                   class="text-xs text-[#2A9D8F] hover:underline font-medium">View</a>

                                <form method="POST" action="{{ route('reports.regenerate', $report) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-violet-600 hover:underline font-medium">
                                        Regen AI
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('reports.destroy', $report) }}" class="inline"
                                  onsubmit="return confirm('Delete this report and all its data?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($reports->hasPages())
        <div class="px-4 py-4 border-t border-[#D6E1EA]">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
@endif

@endsection