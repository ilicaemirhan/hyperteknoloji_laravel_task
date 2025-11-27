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
    Schema::create('cart_items', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('cart_id');
        $table->string('product_id'); // external API productID
        $table->string('name');
        $table->string('image_url')->nullable();

        $table->decimal('price', 10, 2);
        $table->integer('qty')->default(1);

        $table->timestamps();

        $table->foreign('cart_id')
            ->references('id')
            ->on('carts')
            ->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
