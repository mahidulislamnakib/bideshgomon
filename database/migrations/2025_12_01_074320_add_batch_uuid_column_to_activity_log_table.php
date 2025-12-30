<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchUuidColumnToActivityLogTable extends Migration
{
    public function up()
    {
        $conn = config('activitylog.database_connection') ?: null;
        $tableName = config('activitylog.table_name') ?: 'activity_log';
        if (! Schema::connection($conn)->hasTable($tableName) || Schema::connection($conn)->hasColumn($tableName, 'batch_uuid')) {
            return;
        }
        Schema::connection($conn)->table($tableName, function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('properties');
        });
    }

    public function down()
    {
        $conn = config('activitylog.database_connection') ?: null;
        $tableName = config('activitylog.table_name') ?: 'activity_log';
        if (! Schema::connection($conn)->hasTable($tableName) || ! Schema::connection($conn)->hasColumn($tableName, 'batch_uuid')) {
            return;
        }
        Schema::connection($conn)->table($tableName, function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }
}
