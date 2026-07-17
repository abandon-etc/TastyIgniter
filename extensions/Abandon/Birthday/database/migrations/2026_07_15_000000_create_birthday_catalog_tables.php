<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_packages', function (Blueprint $table): void {
            $table->bigIncrements('birthday_package_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('included_items')->nullable();
            $table->unsignedInteger('price_minor')->default(0);
            $table->char('currency', 3)->default('CAD');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedTinyInteger('default_guard')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'archived_at', 'sort_order'], 'birthday_packages_available_index');
            $table->unique('default_guard', 'birthday_packages_default_unique');
        });

        Schema::create('birthday_addons', function (Blueprint $table): void {
            $table->bigIncrements('birthday_addon_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_minor')->default(0);
            $table->char('currency', 3)->default('CAD');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'archived_at', 'sort_order'], 'birthday_addons_available_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_addons');
        Schema::dropIfExists('birthday_packages');
    }
};
