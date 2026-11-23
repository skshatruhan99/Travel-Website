<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $catalogPrices = [
            'tours' => [
                'price' => [2000, 1000],
                'child_price' => [1000, 500],
            ],
            'hotels' => [
                'price_per_night' => [1000, 2000],
            ],
            'hotel_rooms' => [
                'price_per_night' => [1000, 2000],
            ],
            'activities' => [
                'price' => [500, 1000, 2000],
            ],
            'flights' => [
                'price' => [2000, 1000],
            ],
        ];

        foreach ($catalogPrices as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = [];
            foreach ($columns as $column => $tiers) {
                if (Schema::hasColumn($table, $column)) {
                    $availableColumns[$column] = $tiers;
                }
            }

            $index = 0;
            DB::table($table)
                ->select('id')
                ->orderBy('id')
                ->chunk(100, function ($records) use ($table, $availableColumns, &$index): void {
                    foreach ($records as $record) {
                        $values = [];
                        foreach ($availableColumns as $column => $tiers) {
                            $values[$column] = $tiers[$index % count($tiers)];
                        }

                        if ($values !== []) {
                            DB::table($table)->where('id', $record->id)->update($values);
                        }

                        $index++;
                    }
                });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('key', 'site_name')
                ->where('value', 'NpAdvanture')
                ->update(['value' => 'NpAdventure']);
        }
    }

    public function down(): void
    {
        throw new LogicException('Catalog prices cannot be restored because their previous values were not retained.');
    }
};
