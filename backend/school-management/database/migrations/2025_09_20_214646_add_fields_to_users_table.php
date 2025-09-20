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
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name',25)->after('name');
            $table->integer('n_control')->nullable()->after('email');
            $table->tinyInteger('semestre')->nullable()->after('n_control');
            $table->string('phone_number',15)->after('semestre');
            $table->date('birthdate')->after('phone_number');
            $table->enum('gender',array('Hombre','Mujer'))->nullable()->after('birthdate');
            $table->char('curp',18)->after('gender');
            $table->string('address',100)->nullable()->after('curp');
            $table->string('state',30)->after('address');
            $table->string('municipality',30)->after('state');
            $table->foreignId('id_career')->nullable()->after('password')->constrained('careers');
            $table->date('registration_date')->nullable()->after('id_career');
            $table->boolean('status')->default(1)->after('registration_date');





        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_career']);
            $table->dropColumn([
                'last_name',
                'n_control',
                'semestre',
                'phone_number',
                'birthdate',
                'gender',
                'curp',
                'address',
                'state',
                'municipality',
                'id_career',
                'registration_date',
                'status',
            ]);
        });
    }
};
