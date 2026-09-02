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

$factory->define(App\User::class, function (Faker $faker) {
	$gender = $faker->randomElement(['male', 'female']);
	$sc = $faker->randomElement(['relationship', 'complicated', 'married', 'single', 'dating']);
	$fs = $faker->unique()->firstName;
	$ls = $faker->unique()->lastName;
    return [
        'firstname' => $fs,
        'lastname' => $ls,
        'username' => $fs.$ls,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
        'gender' => $gender,
        'country' => 'Romania',
        'county' => 'Fake',
        'city' => $faker->city,
        'birthday' => $faker->date($format = 'Y-m-d', $max = '-15 years', $min = '-60 years'),
        'social_status' => $sc,
        'status' => 'online',
        'credits' => $faker->numberBetween($min = 0, $max = 100000),
        'remember_token' => str_random(10),
    ];
});
