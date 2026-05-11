id();
            $table->foreignId('ad_account_id')->constrained()->cascadeOnDelete();
            $table->string('campaign_id', 100)->unique();
            $table->string('name');
            $table->string('status', 50)->default('ACTIVE');
            $table->string('objective', 100)->nullable();
            $table->timestamps();

            $table->index('ad_account_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('campaigns'); }
};