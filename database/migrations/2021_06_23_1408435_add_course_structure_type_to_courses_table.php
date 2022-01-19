<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseStructureTypeToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('course_structure_type_id')->unsigned()->nullable()->after('course_type_id');

            $table->foreign('course_structure_type_id')->references('id')->on('course_structure_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign('courses_course_structure_type_id_foreign');
            $table->dropColumn('course_structure_type_id');
        });
    }
}
