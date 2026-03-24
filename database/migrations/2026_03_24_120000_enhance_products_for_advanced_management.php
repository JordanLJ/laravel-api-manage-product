<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('marketing_name')->nullable()->after('name');
            $table->string('short_description', 500)->nullable()->after('description');
            $table->longText('long_description')->nullable()->after('short_description');
            $table->string('status')->default('draft')->after('is_active');
            $table->foreignId('primary_category_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            $table->json('secondary_category_ids')->nullable()->after('primary_category_id');
            $table->json('tags')->nullable()->after('secondary_category_ids');
            $table->json('attributes')->nullable()->after('tags');
            $table->string('seo_title')->nullable()->after('attributes');
            $table->string('seo_slug')->nullable()->unique()->after('seo_title');
            $table->string('seo_description', 500)->nullable()->after('seo_slug');
            $table->timestamp('published_at')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_category_id');
            $table->dropUnique(['seo_slug']);
            $table->dropColumn([
                'marketing_name',
                'short_description',
                'long_description',
                'status',
                'secondary_category_ids',
                'tags',
                'attributes',
                'seo_title',
                'seo_slug',
                'seo_description',
                'published_at',
            ]);
        });
    }
};
