id();
            $table->string('filename');
            $table->string('original_filename');
            // pending | processing | completed | failed
            $table->string('status', 50)->default('pending');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void { Schema::dropIfExists('import_logs'); }
};