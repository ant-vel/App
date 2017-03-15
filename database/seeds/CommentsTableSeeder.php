<?php

/**
 * Antvel - Seeder
 * Comments Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use App\ActionType;
use App\Comment;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $actions = ActionType::get();
        $users = User::select('id')->get();
        //Category
        foreach (range(1, 20) as $void) {
            Comment::create([
                'user_id'        => $users->random(1)->first()->id,
                'action_type_id' => $actions->random(1)->first()->id,
                'source_id'      => $faker->numberBetween(50, 1000000),
                'comment'        => $faker->text(50),
            ]);
        }
    }
}
