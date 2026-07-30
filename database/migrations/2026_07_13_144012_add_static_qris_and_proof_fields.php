<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->text('qris_static_string')->nullable()->after('qris_image');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('xendit_qr_id');
            $table->text('rejection_reason')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('qris_static_string');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'rejection_reason']);
        });
    }
};
