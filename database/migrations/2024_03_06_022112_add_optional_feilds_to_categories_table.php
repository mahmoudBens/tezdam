<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            try {
                Schema::table('categories', function (Blueprint $table) {
                    $table->string('nature', 50)->nullable();
                    $table->string('color', 50)->nullable();
                    $table->string('icon', 50)->nullable();
                    $table->unsignedBigInteger('category_id')->nullable();
                });
            } catch (QueryException $e) {
                app('log')->error(sprintf('Could not add optional feilds to table "transaction_currency_user": %s', $e->getMessage()));
                app('log')->error('If this table exists already (see the error message), this is not a problem. Other errors? Please open a discussion on GitHub.');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('categories',function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'nature')) {
                    $table->dropColumn('nature');
                }
                if (Schema::hasColumn('categories', 'color')) {
                    $table->dropColumn('color');
                }
                if (Schema::hasColumn('categories', 'icon')) {
                    $table->dropColumn('icon');
                }
                if (Schema::hasColumn('categories', 'category_id')) {
                    $table->dropColumn('category_id');
                }
            });
        } catch (QueryException | ColumnDoesNotExist $e) {
            app('log')->error(sprintf('Could not execute query: %s', $e->getMessage()));
            app('log')->error('If the column or index already exists (see error), this is not an problem. Otherwise, please open a GitHub discussion.');
        }
    }
};
