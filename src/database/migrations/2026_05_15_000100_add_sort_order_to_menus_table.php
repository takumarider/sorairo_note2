<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('is_event');
            $table->index(['is_event', 'sort_order']);
        });

        $this->backfillSortOrder();
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['is_event', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }

    private function backfillSortOrder(): void
    {
        $menusByType = DB::table('menus')
            ->select(['id', 'is_event', 'sort_order'])
            ->orderBy('is_event')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (object $menu): int => (int) $menu->is_event);

        foreach ([0, 1] as $isEvent) {
            $nextSortOrder = 1;

            foreach ($menusByType->get($isEvent, collect()) as $menu) {
                if ($menu->sort_order !== null) {
                    $nextSortOrder = max($nextSortOrder, ((int) $menu->sort_order) + 1);

                    continue;
                }

                DB::table('menus')
                    ->where('id', $menu->id)
                    ->whereNull('sort_order')
                    ->update(['sort_order' => $nextSortOrder]);

                $nextSortOrder++;
            }
        }
    }
};
