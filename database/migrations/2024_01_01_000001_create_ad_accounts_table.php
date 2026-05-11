id();
            $table->string('name');
            $table->string('account_id', 100)->unique();
            $table->string('platform', 50)->default('facebook');
            $table->string('currency', 10)->default('USD');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('ad_accounts'); }
};