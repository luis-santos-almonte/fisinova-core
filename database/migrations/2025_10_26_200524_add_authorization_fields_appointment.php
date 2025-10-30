<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('payment_type', ['insurance', 'private'])
                ->default('insurance')
                ->after('status');

            $table->string('authorization_number')->nullable()->after('payment_type');

            $table->timestamp('confirmed_at')->nullable()->after('authorization_number');

            $table->foreignId('confirmed_by')->nullable()->constrained('users')->after('confirmed_at');
            
            $table->index(['payment_type', 'status']);
        });

        DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['status', 'appointment_date']);
            $table->dropIndex(['payment_type', 'status']);

            $table->dropForeign(['confirmed_by']);
            $table->dropColumn([
                'payment_type',
                'authorization_number',
                'confirmed_at',
                'confirmed_by'
            ]);
        });
    }
};
