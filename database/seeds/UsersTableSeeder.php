<?php

/**
 * Antvel - Seeder
 * Users Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Antvel\Components\Customer\Models\{ Person, Business};

class UsersTableSeeder extends Seeder
{
    /**
     * Handles the seeder command.
     *
     * @return void
     */
    public function run()
    {
        factory(Person::class, 1)->create([
            'user_id' => factory(User::class, 'root', 1)->create()->first()->id
        ]);

        factory(Person::class, 1)->create([
            'user_id' => factory(User::class, 'admin', 1)->create()->first()->id
        ]);

        factory(Business::class, 1)->create([
            'user_id' => factory(User::class, 'seller', 1)->create()->first()->id
        ]);

        factory(Person::class, 1)->create([
            'user_id' => factory(User::class, 'buyer', 1)->create()->first()->id
        ]);

        factory(Person::class, 3)->create();
        factory(Business::class, 3)->create();
    }
}
