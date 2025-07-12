<?php

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
            $table->string('name');
            $table->string('slug');
            $table->integer('quantity');
            $table->double('regular_price', 8, 2);
            $table->double('sale_price', 8, 2);
            $table->unsignedBigInteger('category');
            $table->string('thumbnail');
            $table->integer('viewer')->default(0);
            $table->unsignedBigInteger('author');
            $table->longText('description');
            $table->timestamps();

            $table->foreign('category')->references('id')->on('category')->onDelete('cascade');
            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');
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
