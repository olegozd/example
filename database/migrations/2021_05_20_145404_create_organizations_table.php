<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('organization_name');
            $table->integer('organization_type_id')->unsigned();
            $table->integer('parent_organization_id')->unsigned()->nullable();
            $table->integer('foundation_year');
            $table->longText('organization_head');
            $table->integer('country_id')->unsigned();
            $table->longText('website');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            $table->softDeletes();
            $table->foreign('organization_type_id')->references('id')->on('organization_types')->onDelete('restrict');
            $table->foreign('parent_organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('organizations');
    }
}
