id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['working', 'not_working', 'at_risk', 'needs_scaling', 'recommendations']);
            $table->string('title');
            $table->text('content');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};