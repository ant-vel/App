<?php

use App\User;
use Faker\Generator as Faker;
use Antvel\Components\AddressBook\Models\Address;

$factory->define(Address::class, function (Faker $faker)  use ($factory) {

	return [
		'default' => 0,
		'city' => str_limit($faker->city, 100),
		'state' => str_limit($faker->state, 100),
		'country' => str_limit($faker->country, 100),
		'zipcode' => str_limit($faker->postcode, 20),
		'line1' => str_limit($faker->streetAddress, 250),
		'line2' => str_limit($faker->streetAddress, 250),
		'phone' => str_limit($faker->e164PhoneNumber, 20),
		'name_contact' => str_limit($faker->streetName, 100),
		'user_id' => function () { return factory(User::class)->create()->id; },
	];
});

$factory->defineAs(Address::class, 'buyer', function (Faker $faker)  use ($factory) {

	return array_merge(
        $factory->raw(Address::class), [
            'user_id' => 4,
        ]
    );
});