@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#264653] font-barlow">Reports</h2>
        <p class="text-sm text-[#6B7C8D] mt-0.5">All uploaded Meta Ads reports</p>
    </div>
    <a href="{{ route('upload.index') }}" class="btn-coral">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Upload
    </a>
</div>

@if($reports->isEmpty())
<div class="chart-card text-center py-16">
    <div class="w-14 h-14 rounded-2xl bg-[#E8EEF4] flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-[#8AABBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <h3 class="font-barlow text-xl font-bold text-[#264653] mb-2">No Reports Yet</h3>
    <p class="text-sm text-[#6B7C8D] mb-5">Upload your first Meta Ads report to get started.</p>
    <a href="{{ route('upload.index') }}" class="btn-coral">Upload Report</a>
</div>
@else
<div class="chart-card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Date Range</th>
                    <th class="text-right">Rows</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr x-data="{ confirmDelete: false }">
                    <td>
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#E8EEF4] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#4A6A82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[#264653] text-sm">{{ $report->original_filename }}</p>
                                @if($report->notes)
                                <p class="text-xs text-[#8AABBF] truncate max-w-xs">{{ $report->notes }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-[#6B7C8D] text-sm">
                        {{ $report->date_range_label ?? '—' }}
                    </td>
                    <td class="text-right font-medium text-sm">{{ number_format($report->row_count) }}</td>
                    <td>
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                            {{ $report->status === 'processed' ? 'badge-teal' : ($report->status === 'failed' ? 'badge-coral' : 'badge-amber') }}">
                            @if($report->status === 'processed')
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#1A7A70] inline-block"></span>
                                    Processed
                                </span>
                            @elseif($report->status === 'failed')
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#C0422A] inline-block"></span>
                                    Failed
                                </span>
                            @else
                                <span class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#92400E] inline-block animate-pulse"></span>
                                    {{ ucfirst($report->status) }}
                                </span>
                            @endif
                        </span>
                    </td>
                    <td class="text-[#6B7C8D] text-sm" title="{{ $report->created_at->format('Y-m-d H:i') }}">
                        {{ $report->created_at->diffForHumans() }}
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($report->status === 'processed')
                            <a href="{{ route('dashboard', ['report_id' => $report->id]) }}"
                               class="btn-ghost !py-1.5 !px-3 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>
                            @endif

                            {{-- Regenerate --}}
                            <form method="POST" action="{{ route('reports.regenerate', $report) }}">
                                @csrf
                                <button type="submit" class="btn-ghost !py-1.5 !px-3 text-xs"
                                        title="Regenerate AI recommendations">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Delete --}}
                            <button type="button"
                                    @click="confirmDelete = true"
                                    class="btn-ghost !py-1.5 !px-3 text-xs !text-[#C0422A] !border-[#F5C3B5] hover:!bg-[#FEF1EE]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>

                            {{-- Delete confirm modal --}}
                            <div x-show="confirmDelete"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                                 x-transition>
                                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm"
                                     @click.stop>
                                    <h4 class="font-barlow font-bold text-[#264653] text-base mb-2">Delete Report?</h4>
                                    <p class="text-sm text-[#6B7C8D] mb-5">
                                        "<strong class="text-[#264653]">{{ $report->original_filename }}</strong>"
                                        and all its data will be permanently deleted.
                                    </p>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('reports.destroy', $report) }}" class="flex-1">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-full py-2 rounded-lg bg-[#E76F51] text-white text-sm font-semibold hover:bg-[#d05e40] transition-colors">
                                                Yes, Delete
                                            </button>
                                        </form>
                                        <button @click="confirmDelete = false"
                                                class="flex-1 py-2 rounded-lg border border-[#D0DCE8] text-sm font-medium text-[#4A6A82] hover:bg-[#F0F5F9] transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
