<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('actas', function (Blueprint $table) {
            $table->string('firmado_pdf')->nullable()->after('implementador');
            $table->boolean('firmado')->default(false)->after('firmado_pdf');
        });
    }

    public function down()
    {
        Schema::table('actas', function (Blueprint $table) {
            $table->dropColumn(['firmado_pdf', 'firmado']);
        });
    }
};
