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
        if (Schema::hasTable('transactions')) {
            try {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('payment_type', 50)->nullable();
                    $table->string('paid', 50)->nullable();
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
            Schema::table('transactions',function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'payment_type')) {
                    $table->dropColumn('payment_type');
                }
                if (Schema::hasColumn('transactions', 'paid')) {
                    $table->dropColumn('paid');
                }
            });
        } catch (QueryException | ColumnDoesNotExist $e) {
            app('log')->error(sprintf('Could not execute query: %s', $e->getMessage()));
            app('log')->error('If the column or index already exists (see error), this is not an problem. Otherwise, please open a GitHub discussion.');
        }
    }
};
