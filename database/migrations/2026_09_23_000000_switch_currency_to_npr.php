<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'payments', 'invoices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'currency')) {
                DB::table($table)->where('currency', 'INR')->update(['currency' => 'NPR']);

                Schema::table($table, function (Blueprint $table) {
                    $table->string('currency')->default('NPR')->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'payments', 'invoices'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'currency')) {
                DB::table($table)->where('currency', 'NPR')->update(['currency' => 'INR']);

                Schema::table($table, function (Blueprint $table) {
                    $table->string('currency')->default('INR')->change();
                });
            }
        }
    }
};
