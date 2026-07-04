<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreignId('table_id')->nullable()->change();
            $table->foreign('table_id')->references('id')->on('tables')->nullOnDelete();

            $table->string('order_type')->default('dine_in')->after('total_amount'); // dine_in, take_away
            $table->string('pickup_time')->nullable()->after('order_type'); // e.g. "Secepatnya", "12:30 WIB"
            $table->string('take_away_notes')->nullable()->after('pickup_time');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_take_away')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreignId('table_id')->nullable(false)->change();
            $table->foreign('table_id')->references('id')->on('tables')->restrictOnDelete();

            $table->dropColumn(['order_type', 'pickup_time', 'take_away_notes']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('is_take_away');
        });
    }
};
