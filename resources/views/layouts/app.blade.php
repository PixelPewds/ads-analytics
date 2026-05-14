{{ $title ?? 'Dashboard' }} — AdsAnalytics

    {{-- Fonts --}}
    
    
    

    {{-- Tailwind CSS CDN --}}
    
    
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ivory':      '#F5F5F7',
                        'misty':      '#2F5061',
                        'teal':       '#4297A0',
                        'coral':      '#E57F84',
                        'misty-light': '#3a6278',
                        'misty-dark':  '#243d4d',
                    },
                    fontFamily: {
                        'barlow': ['Barlow', 'sans-serif'],
                        'plex':   ['"IBM Plex Sans"', 'sans-serif'],
                    },
                }
            }
        }
    

    {{-- Alpine.js --}}
    

    {{-- Chart.js --}}
    

    
        [x-cloak] { display: none !important; }
        body { font-family: 'IBM Plex Sans', sans-serif; }
        h1, h2, h3, h4, h5 { font-family: 'Barlow', sans-serif; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #4297A0; border-radius: 2px; }
    

    @stack('head')





    {{-- Mobile overlay --}}
    
    

    {{-- Sidebar --}}
    

        {{-- Logo --}}
        
            
                
                    
                        
                    
                
                AdsAnalytics
            
            
                
                    
                
            
        

        {{-- Nav --}}
        
            @php
                $nav = [
                    ['route' => 'dashboard',    'label' => 'Dashboard',      'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'upload.index', 'label' => 'Upload Report',   'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                    ['route' => 'reports.index','label' => 'Reports History', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'chat.index',   'label' => 'AI Assistant',    'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ];
            @endphp

            @foreach($nav as $item)
                
                    
                        
                    
                    {{ $item['label'] }}
                
            @endforeach
        

        {{-- User Footer --}}
        
            
                
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                
                
                    {{ auth()->user()->name ?? 'User' }}
                    {{ auth()->user()->email ?? '' }}
                
            
            
                @csrf
                
                    
                        
                    
                    Sign out
                
            
        
    

    {{-- Main content --}}
    

        {{-- Top bar --}}
        
            
                {{-- Menu toggle --}}
                
                    
                        
                    
                

                {{-- Page title --}}
                
                    {{ $title ?? 'Dashboard' }}
                

                
                    @isset($headerActions)
                        {{ $headerActions }}
                    @endisset
                    
                        
                            
                        
                        Upload
                    
                
            
        

        {{-- Flash messages --}}
        
            @if(session('success'))
                
                    
                        
                    
                    {{ session('success') }}
                    
                        
                            
                        
                    
                
            @endif
            @if(session('error'))
                
                    
                        
                    
                    {{ session('error') }}
                    
                        
                            
                        
                    
                
            @endif
        

        {{-- Page content --}}
        
            {{ $slot }}
        

    


@stack('scripts')
