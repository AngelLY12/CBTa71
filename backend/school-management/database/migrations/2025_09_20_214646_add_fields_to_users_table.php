<?php

use App\Models\Career;
use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name',25)->after('name');
            $table->string('phone_number',15)->after('last_name');
            $table->date('birthdate')->after('phone_number');
            $table->enum('gender',array('Hombre','Mujer'))->nullable()->after('birthdate');
            $table->char('curp',18)->after('gender')->unique();
            $table->json('address')->nullable()->after('curp');
            $table->string('stripe_customer_id',50)->nullable()->unique();
            $table->enum('blood_type',['O+','O-','A+','A-','B+','B-','AB+','AB-'])->nullable();
            $table->date('registration_date');
            $table->enum('status',['activo','baja','eliminado'])->default('activo');
            $table->index(['last_name', 'created_at','status']);

        });
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained('users')->onDelete('cascade');
            $table->foreignIdFor(Career::class)->nullable()->constrained('careers')->onDelete('set null');
            $table->integer('n_control')->nullable()->unique();
            $table->tinyInteger('semestre')->nullable()->index();
            $table->string('group', 10)->nullable();
            $table->string('workshop')->nullable();
            $table->timestamps();
        });

    }





    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_details');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name',
                'phone_number',
                'birthdate',
                'gender',
                'curp',
                'address',
                'registration_date',
                'status',
            ]);
        });
    }
};
