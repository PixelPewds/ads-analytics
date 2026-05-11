{{ $title ?? 'Ads Analytics' }} — AdsInsight

    {{-- Fonts loaded via app.css @import --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Chart.js via CDN (no build-step dependency) --}}
    

    {{-- Alpine.js --}}
    



    

        {{-- ── Sidebar ────────────────────────────────────────── --}}
        {{-- Mobile overlay --}}
        

        
            {{-- Logo --}}
            
                
                    
                        
                    
                
                
                    AdsInsight
                    Analytics Platform
                
            

            {{-- Navigation --}}
            
                Analytics

                
                    
                        
                    
                    Dashboard
                

                
                    
                        
                    
                    Campaigns
                

                
                    
                        
                    
                    Ad Sets
                

                
                    
                        
                    
                    Ads
                

                
                    Data
                

                
                    
                        
                    
                    Import Data
                

                
                    
                        
                    
                    Reports
                
            

            {{-- Footer --}}
            
                AdsInsight v1.0
            
        

        {{-- ── Main content area ──────────────────────────────── --}}
        

            {{-- Top bar --}}
            
                
                    {{-- Mobile menu toggle --}}
                    
                        
                            
                        
                    

                    
                        {{ $title ?? 'Dashboard' }}
                        @isset($subtitle)
                            {{ $subtitle }}
                        @endisset
                    
                

                
                    {{ $headerActions ?? '' }}

                    
                        
                            
                        
                        Import
                    
                
            

            {{-- Flash messages --}}
            @if(session('success'))
                
                    
                        
                    
                    {{ session('success') }}
                
            @endif

            @if(session('error'))
                
                    
                        
                    
                    {{ session('error') }}
                
            @endif

            {{-- Page content --}}
            
                {{ $slot }}
            
        
    


