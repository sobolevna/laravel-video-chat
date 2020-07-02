<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Sobolevna\LaravelVideoChat\Tests\Helpers\User::class, function (Faker $faker) {
    static $password;

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'name'=>  $faker->unique()->name,
    ];
});
$factory->define(\Sobolevna\LaravelVideoChat\Tests\Helpers\Profile::class, function (Faker $faker) {
    $user = factory(\Sobolevna\LaravelVideoChat\Tests\Helpers\User::class)->create();
    return [
        'user_id' => $user->id,
        'first_name' => $faker->firstname,
        'middle_name' => $faker->firstname,
        'last_name' => $faker->lastname,
        'avatar' => $faker->imageUrl
    ];
});
