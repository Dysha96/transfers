<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlannedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planned_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_user_id')->unsigned();
            $table->integer('recipient_user_id')->unsigned();
            $table->decimal('amount');
            $table->dateTimeTz('planned_date');
            $table->string('status');
            $table->timestamps();
            $table->foreign('sender_user_id')->references('id')->on('users');
            $table->foreign('recipient_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plannedTransactions');
    }
}
