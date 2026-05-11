id();
            $table->foreignId('ad_id')        ->constrained()->cascadeOnDelete();
            $table->foreignId('ad_set_id')    ->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')  ->constrained()->cascadeOnDelete();
            $table->date('date');

            // Reach & Engagement
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->decimal('frequency', 8, 4)->default(0);

            // Clicks
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('link_clicks')->default(0);
            $table->decimal('ctr', 8, 4)->default(0);          // percent

            // Cost
            $table->decimal('spend', 12, 4)->default(0);
            $table->decimal('cpc',   10, 4)->default(0);
            $table->decimal('cpm',   10, 4)->default(0);

            // Conversions & Revenue
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('cost_per_conversion', 10, 4)->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('roas',    8,  4)->default(0);

            // Dimensions
            $table->string('device_platform', 100)->nullable();
            $table->string('region',          150)->nullable();
            $table->string('country',         100)->nullable();

            $table->timestamps();

            // Composite unique to prevent duplicates on re-import
            $table->unique(
                ['ad_id', 'date', 'device_platform', 'region'],
                'uq_perf_record'
            );

            // Query-optimised indexes
            $table->index(['campaign_id', 'date']);
            $table->index(['ad_set_id',   'date']);
            $table->index(['ad_id',       'date']);
            $table->index('date');
            $table->index('region');
            $table->index('device_platform');
        });
    }

    public function down(): void { Schema::dropIfExists('performance_records'); }
};