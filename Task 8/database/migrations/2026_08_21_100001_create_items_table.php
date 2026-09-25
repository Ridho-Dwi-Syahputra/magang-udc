<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('id', 16)->primary();

            // restrictOnDelete, bukan cascade. Kategori yang masih dipakai barang
            // nggak boleh dihapus begitu aja, nanti stoknya ikut hilang tanpa sadar.
            $table->string('category_id', 16);
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();

            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
