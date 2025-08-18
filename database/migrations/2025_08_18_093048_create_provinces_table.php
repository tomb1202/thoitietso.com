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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable()->comment('ID vùng');
            $table->string('name')->nullable()->comment('Tên tỉnh/thành');
            $table->string('code')->nullable()->comment('Mã tỉnh/thành');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Vĩ độ');   // VD: 21.027764
            $table->decimal('longitude', 10, 7)->nullable()->comment('Kinh độ'); // VD: 105.834160
            $table->string('url')->nullable()->comment('URL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
