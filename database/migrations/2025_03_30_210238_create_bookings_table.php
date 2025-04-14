<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id(); // Automatikus azonosító
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // A foglaló felhasználó
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // A kiválasztott fodrász
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade'); // A kiválasztott szolgáltatás
            $table->date('appointment_date'); // Foglalás dátuma
            $table->time('appointment_time'); // Foglalás időpontja
            $table->timestamps(); // Létrehozás és frissítés dátuma
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
