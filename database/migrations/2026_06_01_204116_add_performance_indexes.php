<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->index('user_id');
            $table->index(['commentable_type', 'commentable_id']);
            $table->index('parent_id');
        });

        Schema::table('exercise_members', function (Blueprint $table): void {
            $table->index('user_id');
            $table->index('exercise_id');
        });

        Schema::table('chapter_members', function (Blueprint $table): void {
            $table->index('user_id');
            $table->index('chapter_id');
        });

        Schema::table('solutions', function (Blueprint $table): void {
            $table->index('user_id');
            $table->index('exercise_id');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['commentable_type', 'commentable_id']);
            $table->dropIndex(['parent_id']);
        });

        Schema::table('exercise_members', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['exercise_id']);
        });

        Schema::table('chapter_members', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['chapter_id']);
        });

        Schema::table('solutions', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['exercise_id']);
        });
    }
};
