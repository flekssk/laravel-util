<?php

declare(strict_types=1);

use FKS\ActivityLog\ActivityLogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->dropTable('activity_logs');
        $this->dropTable('activity_types');
        $this->dropTable('activity_log_entity_types');

        Schema::create('activity_types', static function (Blueprint $table) {
            $table->integer('activity_type_id');
            $table->string('activity_type_name');

            $table->primary(['activity_type_id']);
        });

        foreach (ActivityLogService::activityTypes() as $name => $id) {
            DB::table('activity_types')
                ->insert([
                    'activity_type_id' => $id,
                    'activity_type_name' => str_replace('_', ' ', $name)
                ]);
        }

        Schema::create('activity_log_entity_types', static function (Blueprint $table) {
            $table->integer('activity_log_entity_type_id');
            $table->string('activity_log_entity_name');

            $table->primary(['activity_log_entity_type_id']);
        });

        foreach (ActivityLogService::entityTypes() as $name => $typeId) {
            DB::table('activity_log_entity_types')
                ->insert([
                    'activity_log_entity_type_id' => $typeId,
                    'activity_log_entity_name' => str_replace('_', ' ', $name)
                ]);
        }
        Schema::connection('base-spanner')
            ->create(
                'activity_logs',
                static function (Blueprint $table) {
                    $table->bigInteger('entity_id');
                    $table->bigInteger('activity_log_id');

                    $table->integer('entity_type_id');
                    $table->integer('activity_type_id');
                    $table->json('payload');

                    $table->timestamp('created_at');

                    $table->primary(['entity_id', 'activity_log_id']);

                    $table->foreign('activity_type_id')
                        ->references('activity_type_id')
                        ->on('activity_types');

                    $table->foreign('entity_type_id')
                        ->references('activity_log_entity_type_id')
                        ->on('activity_log_entity_types');
                });
    }

    public function down(): void
    {
        $this->dropTable('activity_logs');
        $this->dropTable('activity_types');
        $this->dropTable('activity_log_entity_types');
    }

    public function dropTable(string $tableName): void
    {
        if (Schema::hasTable($tableName)) {
            $indexes = DB::table('INFORMATION_SCHEMA.INDEX_COLUMNS')
                ->select(['INDEX_NAME'])
                ->where('TABLE_NAME', $tableName)
                ->where('INDEX_NAME', '!=', 'PRIMARY_KEY')
                ->pluck('INDEX_NAME');

            foreach ($indexes as $index) {
                try {
                    Schema::table($tableName, static function (Blueprint $table) use ($index) {
                        $table->dropIndex($index);
                    });
                } catch (Exception $exception) {
                    echo $exception->getMessage() . PHP_EOL;
                }
            }

            Schema::dropIfExists($tableName);
        }
    }
};
