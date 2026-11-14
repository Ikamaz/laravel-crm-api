<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('reminder_dispatched_at')->nullable();
            $table->index(
                ['completed_at', 'reminder_dispatched_at', 'due_at'],
                'tasks_reminder_lookup_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_reminder_lookup_index');
            $table->dropColumn('reminder_dispatched_at');
        });
    }
};
