```php id="jlwm163"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('adset_id')->nullable();
            $table->string('adset_name')->nullable();
            $table->string('ad_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->decimal('spend', 12, 4)->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 10, 6)->default(0);
            $table->decimal('cpc', 10, 4)->default(0);
            $table->decimal('conversions', 12, 4)->default(0);
            $table->decimal('cost_per_conversion', 12, 4)->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('roas', 10, 4)->default(0);
            $table->decimal('conversations', 12, 4)->default(0);
            $table->decimal('cost_per_conversation', 12, 4)->default(0);
            $table->timestamps();

            $table->index(['report_id', 'date']);
            $table->index(['report_id', 'campaign_name']);
            $table->index(['report_id', 'adset_name']);
            $table->index(['report_id', 'ad_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};