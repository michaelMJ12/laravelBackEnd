<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('from');
            $table->date('to');
            $table->decimal('total_budget', 10, 2);
            $table->decimal('daily_budget', 10, 2);
            $table->json('creatives')->nullable();
            $table->string('role')->default('staff');
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
    
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role'); 
    });
}

}
