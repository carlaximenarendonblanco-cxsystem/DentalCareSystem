<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained('payment_plans')->cascadeOnDelete();
            $table->integer('number'); // número de cuota
            $table->decimal('amount', 10, 2); // monto de la cuota
            $table->date('due_date'); // fecha de vencimiento
            $table->boolean('paid')->default(false); // si ya fue pagada
            $table->date('paid_at')->nullable(); // fecha de pago
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_plan_installments');
    }
};
