<?php

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Category::class)->constraine()->cascadeOnDelete();
            $table->foreignIdFor(Brand::class)->constraine()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable()->default(0);
            $table->smallInteger('stock')->default(0);
            $table->string('thumbnail')->nullable();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->bigInteger('views')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_variants_enabled')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index([
                'sku',
                'slug'
            ]);

            $table->fullText([
                'name',
                'description',
                'content'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
