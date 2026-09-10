<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('name', 200);
            $table->string('unit', 30)->default('قطعة');
            $table->decimal('quantity_in_stock', 10, 2)->default(0);
            $table->decimal('min_stock_alert', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->boolean('is_sellable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type', 15); // in | out | adjustment
            $table->decimal('quantity', 10, 2);
            $table->string('reason', 30); // purchase|service_consumption|sale|damage|adjustment|return
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->date('movement_date');
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('purchase_date');
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        Schema::create('service_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_consumed', 10, 2)->default(1);
            $table->timestamps();
            $table->unique(['service_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_product');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('suppliers');
    }
};
