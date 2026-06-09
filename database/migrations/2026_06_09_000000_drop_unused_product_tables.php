<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the unused "products" feature tables. The system was refactored to
     * track materials directly; these tables have no models, no usage, and no
     * data. Order matters: drop the pivot before its referenced parent.
     */
    public function up(): void
    {
        Schema::dropIfExists('product_material');
        Schema::dropIfExists('products');
        Schema::dropIfExists('finished_goods');
    }

    /**
     * Recreate the tables as they were, for reversibility.
     */
    public function down(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();

            $table->unique(['product_id', 'material_id']);
        });

        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
