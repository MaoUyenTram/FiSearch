<?php

use Faker\Generator as Faker;

$factory->define(App\Work::class, function (Faker $faker) {
    return [
        'finalworkTitle'=> $faker->text,
        'finalworkURL' => $faker->url,
        'finalworkDescription'=> $faker->text,
        'finalworkAuthor'=> $faker->name,
        'departement'=> $faker->name,
        'finalworkField'=> $faker->name,
        'finalworkYear'=> $faker->year,
        'finalworkPromoter'=> $faker->name
    ];
});
