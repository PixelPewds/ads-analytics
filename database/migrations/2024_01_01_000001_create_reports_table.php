id();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_hash', 32)->unique();
            $table->date('date_range_start')->nullable();
            $table->date('date_range_end')->nullable();
            $table->enum('status', ['processing', 'processed', 'failed'])->default('processing');
            $table->integer('row_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};