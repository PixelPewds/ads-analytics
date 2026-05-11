id();
            $table->foreignId('ad_set_id')->constrained()->cascadeOnDelete();
            $table->string('ad_id', 100)->unique();
            $table->string('name');
            $table->string('status', 50)->default('ACTIVE');
            $table->string('creative_type', 100)->nullable();
            $table->timestamps();

            $table->index('ad_set_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('ads'); }
};