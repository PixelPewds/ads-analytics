id();
            $table->string('session_id', 64)->index();
            $table->foreignId('report_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->longText('content');
            $table->integer('token_count')->default(0);
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};