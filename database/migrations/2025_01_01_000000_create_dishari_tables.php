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
        // 1. Menu Groups Table
        Schema::create('dishari_menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->nullable()->unique();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Menu Items Table
        Schema::create('dishari_menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_group_id')
                ->nullable()
                ->constrained('dishari_menu_groups')
                ->onDelete('set null');

            $table->string('title');
            $table->string('url')->nullable();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('dishari_menu_items')
                ->onDelete('cascade');

            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('permission_name')->nullable();
            $table->timestamps();

            $table->index(['menu_group_id', 'order']);
            $table->index(['parent_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishari_menu_items');
        Schema::dropIfExists('dishari_menu_groups');
    }
};
