id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('ad_set_id', 100)->unique();
            $table->string('name');
            $table->string('status', 50)->default('ACTIVE');
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->decimal('lifetime_budget', 12, 2)->nullable();
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('ad_sets'); }
};