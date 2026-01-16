<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->default(1);
            $table->timestamps();

            // Add unique constraint to prevent duplicate project-department assignments
            $table->unique(['project_id', 'department_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_department');
    }
};
