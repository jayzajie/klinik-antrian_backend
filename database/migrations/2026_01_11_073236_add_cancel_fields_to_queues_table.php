<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('done_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_reason');
            $table->integer('estimated_wait_minutes')->nullable()->after('queue_number');
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_at', 'estimated_wait_minutes']);
        });
    }
};
