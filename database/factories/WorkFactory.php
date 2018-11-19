<?php

use Faker\Generator as Faker;

$factory->define(App\Work::class, function (Faker $faker) {
    return [
        'finalworkTitle'=> $faker->text,
        'finalworkDescription'=> $faker->text,
        'finalworkAuthor'=> $faker->name,
        'finalworkYear'=> $faker->year,
        'promoterID'=> $faker->randomDigitNotNull,
        'workTagID'=> $faker->randomDigitNotNull
    ];
});
