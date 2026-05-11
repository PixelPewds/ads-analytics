id();
            $table->foreignId('import_log_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('row_data');
            $table->text('error_message');
            $table->timestamps();

            $table->index('import_log_id');
        });
    }

    public function down(): void { Schema::dropIfExists('failed_import_rows'); }
};