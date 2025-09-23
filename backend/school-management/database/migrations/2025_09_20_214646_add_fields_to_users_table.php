<?php

use App\Models\Career;
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
            $table->foreignIdFor(Career::class)->nullable()->constrained('careers')->onDelete('set null')->after('password');
            $table->date('registration_date');
            $table->boolean('status')->default(1);





        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['career_id']);
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
                'career_id',
                'registration_date',
                'status',
            ]);
        });
    }
};
