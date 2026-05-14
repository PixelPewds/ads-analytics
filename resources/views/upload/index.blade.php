<x-layouts.app :title="$title ?? 'Upload Report'">

<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#2F5061] font-[Barlow] mb-1">Upload Meta Ads Report</h2>
        <p class="text-gray-500 text-sm">Upload a CSV or XLSX file exported from Meta Ads Manager to analyze campaign performance.</p>
    </div>

    {{-- Upload card --}}
    <div
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 mb-6"
        x-data="{
            isDragging: false,
            fileName: null,
            fileSize: null,
            hasFile: false,
            uploading: false,
            handleFile(files) {
                const file = files[0];
                if (!file) return;
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['csv','xlsx','xls'].includes(ext)) {
                    alert('Please upload a CSV or XLSX file.');
                    return;
                }
                this.fileName = file.name;
                this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                this.hasFile = true;
            }
        }"
    >
        <form
            method="POST"
            action="{{ route('upload.store') }}"
            enctype="multipart/form-data"
            @submit="uploading = true"
            id="uploadForm"
        >
            @csrf

            {{-- Drop zone --}}
            <div
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="isDragging = false; handleFile($event.dataTransfer.files)"
                @click="$refs.fileInput.click()"
                class="relative border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200 p-10 text-center"
                :class="isDragging ? 'border-[#4297A0] bg-[#4297A0]/5' : (hasFile ? 'border-[#4297A0]/50 bg-[#4297A0]/3' : 'border-gray-200 hover:border-[#4297A0]/40 hover:bg-gray-50/50')"
            >
                <input
                    type="file"
                    name="report"
                    accept=".csv,.xlsx,.xls"
                    x-ref="fileInput"
                    class="sr-only"
                    @change="handleFile($event.target.files)"
                >

                {{-- Icon --}}
                <div class="mb-4 flex justify-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center transition-colors"
                        :class="hasFile ? 'bg-[#4297A0]/15' : 'bg-gray-100'">
                        <svg class="w-8 h-8 transition-colors" :class="hasFile ? 'text-[#4297A0]' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>

                {{-- Text --}}
                <div x-show="!hasFile">
                    <p class="text-[#2F5061] font-semibold text-base font-[Barlow] mb-1">Drop your file here or click to browse</p>
                    <p class="text-gray-400 text-sm">CSV and XLSX files supported &bull; Max 20MB</p>
                </div>

                <div x-show="hasFile" x-cloak>
                    <p class="text-[#4297A0] font-bold text-base font-[Barlow] mb-1" x-text="fileName"></p>
                    <p class="text-gray-400 text-sm" x-text="fileSize"></p>
                    <p class="text-xs text-gray-400 mt-2">Click to change file</p>
                </div>
            </div>

            {{-- Validation errors --}}
            @error('report')
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                </div>
            @enderror

            {{-- Submit --}}
            <div class="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    :disabled="!hasFile || uploading"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-[#E57F84] hover:bg-[#d96e73] disabled:bg-gray-200 disabled:cursor-not-allowed text-white disabled:text-gray-400 font-bold text-sm rounded-xl transition-all shadow-sm hover:shadow-md active:scale-[0.98] font-[Barlow]"
                >
                    <svg x-show="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <svg x-show="uploading" x-cloak class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="uploading ? 'Uploading & Processing…' : 'Upload & Analyze Report'"></span>
                </button>
                <span x-show="uploading" x-cloak class="text-sm text-gray-500">This may take a moment…</span>
            </div>
        </form>
    </div>

    {{-- Info cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-[#4297A0]/10 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-[#4297A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow] mb-1">CSV & XLSX</h3>
            <p class="text-xs text-gray-500 leading-relaxed">Export directly from Meta Ads Manager. All columns are auto-detected.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-[#E57F84]/10 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-[#E57F84]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow] mb-1">AI Analysis</h3>
            <p class="text-xs text-gray-500 leading-relaxed">OpenAI analyzes your data and generates structured recommendations instantly.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-[#2F5061]/10 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-[#2F5061]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow] mb-1">Dashboard View</h3>
            <p class="text-xs text-gray-500 leading-relaxed">Charts, KPI cards, campaign tables and AI insights — all in one view.</p>
        </div>
    </div>

    {{-- Expected columns --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow] mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#4297A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Supported Column Names
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
            @foreach([
                'Day / Date',
                'Campaign Name / ID',
                'Ad Set Name / ID',
                'Ad Name / ID',
                'Amount Spent (USD/EUR)',
                'Impressions',
                'Reach',
                'Link Clicks',
                'CTR (All)',
                'CPC (All)',
                'Results / Purchases / Leads',
                'Cost per Result',
                'Purchase Conversion Value',
                'ROAS',
                'Messaging Conversations Started',
                'Cost per Conversation',
            ] as $col)
                <span class="text-xs bg-gray-50 border border-gray-100 text-gray-600 px-2.5 py-1.5 rounded-lg font-medium">{{ $col }}</span>
            @endforeach
        </div>
    </div>

    {{-- Recent uploads --}}
    @if($recentReports->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-[#2F5061] font-[Barlow]">Recent Uploads</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($recentReports as $report)
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/50 transition-colors">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                        {{ $report->status === 'processed' ? 'bg-emerald-100' : ($report->status === 'failed' ? 'bg-red-100' : 'bg-amber-100') }}">
                        <svg class="w-4 h-4 {{ $report->status === 'processed' ? 'text-emerald-500' : ($report->status === 'failed' ? 'text-red-400' : 'text-amber-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($report->status === 'processed')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @elseif($report->status === 'failed')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-[#2F5061] truncate">{{ $report->original_filename }}</p>
                        <p class="text-xs text-gray-400">{{ $report->date_range_label }} &bull; {{ $report->row_count }} rows</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $report->status === 'processed' ? 'bg-emerald-100 text-emerald-700' : ($report->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($report->status) }}
                        </span>
                        @if($report->isProcessed())
                            <a href="{{ route('dashboard', ['report_id' => $report->id]) }}" class="text-xs px-2.5 py-1 bg-[#4297A0]/10 text-[#4297A0] hover:bg-[#4297A0]/20 rounded-lg font-medium transition-colors">
                                View
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

</x-layouts.app>
