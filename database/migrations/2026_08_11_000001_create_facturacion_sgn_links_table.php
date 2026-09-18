<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caja_general_cobros')
            && !Schema::hasColumn('caja_general_cobros', 'grupo_cobro_uuid')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                $table->uuid('grupo_cobro_uuid')->nullable()->after('id')->index();
            });
        }

        Schema::create('facturacion_sgn_links', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30);
            $table->string('source_key', 255);
            $table->unsignedBigInteger('source_id');
            $table->string('external_reference', 100);
            $table->uuid('invoice_id')->nullable()->index();
            $table->string('status', 30)->default('REQUESTING')->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->uuid('request_id');
            $table->uuid('correlation_id');
            $table->unsignedBigInteger('requested_by_id');
            $table->string('requested_by_name', 150);
            $table->longText('request_payload');
            $table->longText('response_payload')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_key'], 'uq_facturacion_sgn_source');
            $table->index(['source_type', 'source_id'], 'ix_facturacion_sgn_source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion_sgn_links');
        if (Schema::hasTable('caja_general_cobros')
            && Schema::hasColumn('caja_general_cobros', 'grupo_cobro_uuid')) {
            Schema::table('caja_general_cobros', function (Blueprint $table) {
                $table->dropColumn('grupo_cobro_uuid');
            });
        }
    }
};
