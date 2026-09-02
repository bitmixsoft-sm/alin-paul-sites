<?php

use Faker\Generator as Faker;
use App\Pack;
use App\User;

$factory->define(Model::class, function (Faker $faker) {
    $users = Sponsor::all()->pluck('id')->toArray();
    $packeges = Pack::all()->pluck('price', 'id')->toArray();
    $user_id = $this->faker->randomElement($users);
    $pack_id = $$this->faker->randomElement(array_keys($packages));
    return [
        'userid'     => $user_id,
        'pack_id'    => $pack_id,
        'price'      => $packeges[$pack_id],
        'currency'   => 'EUR',
        'ip_address' => intval(rand()%255).'.'.intval(rand()%255).'.'.intval(rand()%255).'.'.intval(rand()%255),
    ];
});
