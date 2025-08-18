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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('province_id')->nullable()->comment('ID tỉnh/thành');
            $table->string('name')->comment('Tên quận/huyện');
            $table->string('code')->nullable()->comment('Mã quận/huyện');
             $table->decimal('latitude', 10, 7)->nullable()->comment('Vĩ độ');   // VD: 21.027764
            $table->decimal('longitude', 10, 7)->nullable()->comment('Kinh độ'); // VD: 105.834160
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
