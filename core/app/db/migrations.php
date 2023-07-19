<?php
require_once __DIR__ . "/bootstrap.php";
use Illuminate\Database\Capsule\Manager as Capsule;

if (!Capsule::schema()->hasTable('app_vacancys')) {
    Capsule::schema()->create('app_vacancys', function ($table) {
        $table->increments('id');
        $table->string('name')->nullable();
        $table->string('status')->nullable();
        $table->string('code')->nullable();
        $table->string('specialization')->nullable();
        $table->json('areas')->nullable();
        $table->json('salary')->nullable();
        $table->json('address')->nullable();
        $table->string('experience')->nullable();
        $table->mediumText('description')->nullable();
        $table->mediumText('short_description')->nullable();
        $table->json('key_skills')->nullable();
        $table->json('contacts')->nullable();
        $table->json('employment')->nullable();
        $table->json('working_time_modes')->nullable();
        $table->string('schedule')->nullable();
        $table->json('languages')->nullable();
        $table->json('driver_license_types')->nullable();
        $table->json('misc')->nullable();
        $table->integer('company_id')->unsigned()->nullable();
        $table->integer('user_id')->unsigned()->nullable();
        $table->integer('manager_id')->unsigned()->nullable();
        $table->timestamps();
    });
}

Capsule::schema()->table('app_companys', function ($table) {
    if (!Capsule::schema()->hasColumn('app_companys', 'status')) {
        $table->string('status')->nullable();
    }
});

// Capsule::schema()->table('app_vacancys', function ($table) {
//     $table->string('name')->nullable()->change();
//     $table->string('shedule')->nullable()->change();

//     if (!Capsule::schema()->hasColumn('app_vacancys', 'address')) {
//         $table->json('address')->nullable();
//     }

//     if (!Capsule::schema()->hasColumn('app_vacancys', 'misc')) {
//         $table->json('misc')->nullable();
//     }
// });

//Capsule::schema()->hasTable('users1')
//Capsule::schema()->hasColumn('users', 'id')

// Capsule::schema()->create('todos', function ($table) {
//     $table->increments('id');
//     $table->string('todo');
//     $table->string('description');
//     $table->string('category');
//     $table->integer('user_id')->unsigned();
//     $table->timestamps();
// });

// Capsule::schema()->table('todos22', function ($table) {
//     //$table->string('todo')->nullable()->change();
//     $table->string('todo2')->nullable();
// });