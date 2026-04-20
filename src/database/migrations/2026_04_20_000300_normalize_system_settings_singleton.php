<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $canonical = DB::table('system_settings')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $canonical) {
            DB::table('system_settings')->insert([
                'id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        $payload = (array) $canonical;
        unset($payload['id']);
        $payload['updated_at'] = now();

        if (! array_key_exists('created_at', $payload) || empty($payload['created_at'])) {
            $payload['created_at'] = now();
        }

        if (DB::table('system_settings')->where('id', 1)->exists()) {
            DB::table('system_settings')->where('id', 1)->update($payload);
        } else {
            DB::table('system_settings')->insert(array_merge(['id' => 1], $payload));
        }

        DB::table('system_settings')->where('id', '!=', 1)->delete();
    }

    public function down(): void
    {
        // No-op: cannot safely restore deleted duplicate rows.
    }
};
