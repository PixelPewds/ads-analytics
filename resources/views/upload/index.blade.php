@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-[#264653] font-barlow">Upload Report</h2>
        <p class="text-sm text-[#6B7C8D] mt-1">
            Upload a Meta Ads CSV or Excel export. AI recommendations are generated automatically.
        </p>
    </div>

    {{-- Upload card --}}
    <div class="chart-card mb-6"
         x-data="{
            dragging: false,
            fileName: '',
            fileSize: '',
            uploading: false,
            handleFile(files) {
                if (!files.length) return;
                this.fileName = files[0].name;
                this.fileSize = (files[0].size / 1024 / 1024).toFixed(2) + ' MB';
            }
         }">

        <form
            method="POST"
            action="{{ route('upload.store') }}"
            enctype="multipart/form-data"
            @submit="uploading = true"
        >
            @csrf

            {{-- Drop zone --}}
            <div
                class="drop-zone flex flex-col items-center justify-center py-14 px-6 text-center cursor-pointer mb-5"
                :class="{ 'drag-over': dragging }"
                @dragover.prevent="dragging = true"
                @dragleave="dragging = false"
                @drop.prevent="
                    dragging = false;
                    handleFile($event.dataTransfer.files);
                    $el.querySelector('input[type=file]').files = $event.dataTransfer.files
                "
                @click="$el.querySelector('input[type=file]').click()"
            >
                <input
                    type="file"
                    name="report"
                    id="report"
                    accept=".csv,.xlsx,.xls"
                    class="sr-only"
                    @change="handleFile($event.target.files)"
                >

                <div class="w-14 h-14 rounded-2xl bg-[#E8EEF4] flex items-center justify-center mb-3 transition-colors"
                     :class="fileName ? 'bg-[#E6F7F5]' : 'bg-[#E8EEF4]'">
                    <svg class="w-7 h-7 transition-colors"
                         :class="fileName ? 'text-[#2A9D8F]' : 'text-[#8AABBF]'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>

                <p class="text-sm font-semibold text-[#264653] mb-1"
                   x-text="fileName || 'Drop your file here or click to browse'"></p>
                <p class="text-xs text-[#6B7C8D]"
                   x-text="fileSize || 'CSV · XLSX · XLS — max 20 MB'"></p>
            </div>

            <button
                type="submit"
                class="btn-coral w-full py-3 text-sm flex items-center justify-center gap-2"
                :disabled="uploading || !fileName"
                :class="(!fileName) ? 'opacity-50 cursor-not-allowed' : ''"
            >
                <template x-if="!uploading">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload & Analyse
                    </span>
                </template>
                <template x-if="uploading">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Processing…
                    </span>
                </template>
            </button>
        </form>
    </div>

    {{-- Expected columns --}}
    <div class="chart-card mb-6">
        <h3 class="text-sm font-bold text-[#264653] mb-3 font-barlow">Expected CSV Columns</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach([
                'Day / Date', 'Campaign Name', 'Ad Set Name', 'Ad Name',
                'Amount Spent', 'Impressions', 'Reach', 'Link Clicks',
                'CTR', 'CPC', 'Results (Conversions)', 'Cost per Result',
                'Revenue', 'ROAS', 'Conversations Started'
            ] as $col)
            <div class="flex items-center gap-1.5 text-xs text-[#6B7C8D]">
                <svg class="w-3.5 h-3.5 text-[#2A9D8F] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
                {{ $col }}
            </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 rounded-xl bg-[#F7FAFB] border border-[#DDE8F0]">
            <p class="text-xs text-[#6B7C8D] leading-relaxed">
                <strong class="text-[#264653]">Tip:</strong>
                Export directly from Meta Ads Manager → Reports. Column names are matched flexibly —
                minor naming differences are handled automatically.
            </p>
        </div>
    </div>

    {{-- Recent uploads --}}
    @if($recentReports->isNotEmpty())
    <div class="chart-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-[#264653] font-barlow">Recent Uploads</h3>
            <a href="{{ route('reports.index') }}" class="text-xs text-[#2A9D8F] hover:underline font-medium">
                View all reports →
            </a>
        </div>
        <div class="space-y-1">
            @foreach($recentReports as $r)
            <div class="flex items-center justify-between py-2.5 border-b border-[#F0F5F9] last:border-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#E8EEF4] flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-[#4A6A82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#264653] truncate">{{ $r->original_filename }}</p>
                            <p class="text-xs text-[#6B7C8D]">
                                @if($r->date_range_label){{ $r->date_range_label }} ·@endif
                                {{ number_format($r->row_count) }} rows ·
                                {{ $r->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        {{ $r->status === 'processed' ? 'badge-teal' : ($r->status === 'failed' ? 'badge-coral' : 'badge-amber') }}">
                        {{ $r->status }}
                    </span>
                    @if($r->status === 'processed')
                    <a href="{{ route('dashboard', ['report_id' => $r->id]) }}"
                       class="text-xs text-[#2A9D8F] hover:underline font-semibold">
                        View →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
