<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('id')->constrained('classes')->nullOnDelete();
        });

        // Carry over the first linked class for each subject from the old class_subject pivot, if it still exists.
        if (Schema::hasTable('class_subject')) {
            DB::table('class_subject')
                ->orderBy('id')
                ->get()
                ->unique('subject_id')
                ->each(function ($pivot) {
                    DB::table('subjects')
                        ->where('id', $pivot->subject_id)
                        ->update(['class_id' => $pivot->class_id]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_id');
        });
    }
};
