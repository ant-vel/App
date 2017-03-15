<?php

/**
 * Antvel - Seeder
 * Products Rates Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use App\User;
use App\Order;
use App\Product;
use App\Category;
use App\UserPoints;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Antvel\Components\AddressBook\Models\Address;

class ProductsRatesSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $user = User::select('id')->where('id', 4)->first();

        for ($j = 0; $j < 2; $j++) {
            $userPoints = UserPoints::create([
                'user_id'        => $user->id,
                'action_type_id' => 6,
                'source_id'      => 1,
                'points'         => 100,
            ]);
        }

        $userAddress = Address::create([
            'user_id'      => $user->id,
            'default'      => 1,
            'line1'        => $faker->streetAddress,
            'line2'        => $faker->streetAddress,
            'phone'        => $faker->e164PhoneNumber,
            'name_contact' => $faker->streetName,
            'zipcode'      => $faker->postcode,
            'city'         => $faker->city,
            'country'      => $faker->country,
            'state'        => $faker->state,
        ]);

        $catforseed = Category::where('type', 'store')->first();
        $seededProduct = Product::create([
            'category_id'  => $catforseed->id,
            'user_id'      => '3', //3,
            'name'         => 'My Seeded Product',
            'description'  => $faker->text(90),
            'price'        => $faker->randomNumber(2),
            'stock'        => 100,
            'type'         => 'software',
            'sale_counts'  => $faker->randomNumber(9),
            'view_counts'  => $faker->randomNumber(9),
            'brand'        => $faker->randomElement(['Apple', 'Gigabyte', 'Microsoft', 'Google. Inc', 'Samsung', 'Lg']),
            'features'     => json_encode([
                    'images' => [
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    ],
                    trans('globals.product_features.weight')     => $faker->numberBetween(10, 150).' '.$faker->randomElement(['Mg', 'Gr', 'Kg', 'Oz', 'Lb']),
                    trans('globals.product_features.dimensions') => $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' '.
                                  $faker->randomElement(['inch', 'mm', 'cm']),
                    trans('globals.product_features.color') => $faker->safeColorName,
                    ]),
            'condition' => $faker->randomElement(['new', 'refurbished', 'used']),
            //'currency'=>0,
            'low_stock'  => $faker->randomElement([5, 10, 15]),
            'rate_val'   => '3',
            'rate_count' => '5',
            'tags'       => json_encode($faker->word.','.$faker->word.','.$faker->word),
        ]);

        for ($j = 0; $j < 5; $j++) {
            $order = Order::create([
                'user_id'     => $user->id,
                'seller_id'   => '3',
                'address_id'  => $userAddress->id,
                'status'      => 'closed',
                'type'        => 'order',
                'description' => '',
                'seller_id'   => 3,
                'end_date'    => $faker->dateTime(),
            ]);

            $order->details()->create([
                'product_id'    => $seededProduct->id,
                'price'         => $seededProduct->price,
                'quantity'      => '1',
                'delivery_date' => $faker->dateTime(),
                'rate'          => $faker->numberBetween(1, 5),
                'rate_comment'  => $faker->text(90),
            ]);
        }

        $seededProduct2 = Product::create([
            'category_id'  => $catforseed->id,
            'user_id'      => '3',
            'name'         => 'Another Seeded Product',
            'description'  => $faker->text(90),
            'stock'        => 100,
            'type'         => 'software',
            'sale_counts'  => $faker->randomNumber(9),
            'view_counts'  => $faker->randomNumber(9),
            'price'        => $faker->randomNumber(2),
            'brand'        => $faker->randomElement(['Apple', 'Gigabyte', 'Microsoft', 'Google. Inc', 'Samsung', 'Lg']),
            'stock'        => 100,
            'features'     => json_encode([
                    'images' => [
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    ],
                    trans('globals.product_features.weight')     => $faker->numberBetween(10, 150).' '.$faker->randomElement(['Mg', 'Gr', 'Kg', 'Oz', 'Lb']),
                    trans('globals.product_features.dimensions') => $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' '.
                                  $faker->randomElement(['inch', 'mm', 'cm']),
                    trans('globals.product_features.color') => $faker->safeColorName,
                    ]),
            'condition' => $faker->randomElement(['new', 'refurbished', 'used']),
            //'currency'=>0,
            'low_stock'  => $faker->randomElement([5, 10, 15]),
            'rate_val'   => '4',
            'rate_count' => '5',
            'tags'       => json_encode($faker->word.','.$faker->word.','.$faker->word),
        ]);

        $seededProduct3 = Product::create([
            'category_id'  => $catforseed->id,
            'user_id'      => '3', //3,
            'name'         => 'More on Seeded Product',
            'description'  => $faker->text(90),
            'stock'        => 100,
            'type'         => 'software',
            'sale_counts'  => $faker->randomNumber(9),
            'view_counts'  => $faker->randomNumber(9),
            'price'        => $faker->randomNumber(2),
            'stock'        => 100,
            'brand'        => $faker->randomElement(['Apple', 'Gigabyte', 'Microsoft', 'Google. Inc', 'Samsung', 'Lg']),
            'features'     => json_encode([
                    'images' => [
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    '/img/pt-default/'.$faker->numberBetween(1, 330).'.jpg',
                    ],
                    trans('globals.product_features.weight')     => $faker->numberBetween(10, 150).' '.$faker->randomElement(['Mg', 'Gr', 'Kg', 'Oz', 'Lb']),
                    trans('globals.product_features.dimensions') => $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' X '.
                                  $faker->numberBetween(1, 30).' '.
                                  $faker->randomElement(['inch', 'mm', 'cm']),
                    trans('globals.product_features.color') => $faker->safeColorName,
                    ]),
            'condition' => $faker->randomElement(['new', 'refurbished', 'used']),
            //'currency'=>0,
            'low_stock'  => $faker->randomElement([5, 10, 15]),
            'rate_val'   => '4',
            'rate_count' => '5',
            'tags'       => json_encode($faker->word.','.$faker->word.','.$faker->word),
        ]);

        // Creates closed orders for rates and mails
        for ($j = 0; $j < 5; $j++) {
            $order = Order::create([
                'user_id'     => $user->id,
                'seller_id'   => '3',
                'address_id'  => $userAddress->id,
                'status'      => 'closed',
                'type'        => 'order',
                'description' => '',
                'seller_id'   => 3,
                'end_date'    => $faker->dateTime(),
            ]);

            $order->details()->create([
                'product_id'    => $seededProduct->id,
                'price'         => $seededProduct->price,
                'quantity'      => '1',
                'delivery_date' => $faker->dateTime(),
            ]);
        }

        // Create an open order to test notices
        $order = Order::create([
            'user_id'     => $user->id,
            'seller_id'   => '3',
            'status'      => 'open',
            'type'        => 'order',
            'description' => '',
            'seller_id'   => 3,
            'address_id'  => $userAddress->id,
        ]);

        $order->details()->create([
            'product_id' => $seededProduct->id,
            'price'      => $seededProduct->price,
            'quantity'   => '3',
        ]);
    }
}
