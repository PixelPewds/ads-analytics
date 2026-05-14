@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-[#264653]" style="font-family:'Barlow',sans-serif">Upload Report</h2>
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
            x-on:submit="$el.querySelector('button[type=submit]').disabled = true"
        >
            @csrf

            <div
                class="drop-zone flex flex-col items-center justify-center py-14 px-6 text-center cursor-pointer mb-5"
                :class="{ 'drag-over': dragging }"
                @dragover.prevent="dragging = true"
                @dragleave="dragging = false"
                @drop.prevent="dragging = false; handleFile($event.dataTransfer.files); $el.querySelector('input[type=file]').files = $event.dataTransfer.files"
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

                <div class="w-14 h-14 rounded-2xl bg-[#E8EEF4] flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-[#2A9D8F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>

                <p class="text-sm font-semibold text-[#264653] mb-1" x-text="fileName || 'Drop your file here or click to browse'"></p>
                <p class="text-xs text-[#6B7C8D]" x-text="fileSize || 'CSV · XLSX · XLS — max 20 MB'"></p>
            </div>

            <button
                type="submit"
                class="btn-coral w-full py-3 text-sm flex items-center justify-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload &amp; Analyse
            </button>
        </form>
    </div>

    {{-- Expected columns --}}
    <div class="chart-card mb-6">
        <h3 class="text-sm font-bold text-[#264653] mb-3" style="font-family:'Barlow',sans-serif">Expected CSV Columns</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach(['Day / Date', 'Campaign Name', 'Ad Set Name', 'Ad Name', 'Amount Spent', 'Impressions', 'Reach', 'Link Clicks', 'CTR', 'CPC', 'Results (Conversions)', 'Cost per Result', 'Revenue', 'ROAS', 'Conversations Started'] as $col)
            <div class="flex items-center gap-1.5 text-xs text-[#6B7C8D]">
                <svg class="w-3.5 h-3.5 text-[#2A9D8F] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
                {{ $col }}
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent uploads --}}
    @if($recentReports->isNotEmpty())
    <div class="chart-card">
        <h3 class="text-sm font-bold text-[#264653] mb-3" style="font-family:'Barlow',sans-serif">Recent Uploads</h3>
        <div class="space-y-2">
            @foreach($recentReports as $r)
            <div class="flex items-center justify-between py-2 border-b border-[#D6E1EA] last:border-0">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[#264653] truncate">{{ $r->original_filename }}</p>
                    <p class="text-xs text-[#6B7C8D]">
                        {{ $r->date_range_label }}
                        &nbsp;·&nbsp;
                        {{ number_format($r->row_count) }} rows
                        &nbsp;·&nbsp;
                        {{ $r->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $r->status === 'processed' ? 'bg-green-100 text-green-700' : ($r->status === 'failed' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ $r->status }}
                    </span>
                    @if($r->status === 'processed')
                    <a href="{{ route('dashboard', ['report_id' => $r->id]) }}" class="text-xs text-[#2A9D8F] hover:underline font-medium">View</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection