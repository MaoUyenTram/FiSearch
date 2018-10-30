<?php

use Faker\Generator as Faker;

$factory->define(App\Work::class, function (Faker $faker) {
    return [
        'user_name' => $faker->name,
        'year' => 2018,
    ];
});
