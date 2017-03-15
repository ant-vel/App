<?php

/**
 * Antvel - Seeder
 * Orders Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use App\Order;
use App\Product;
use App\OrderDetail;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Antvel\Components\AddressBook\Models\Address;

class OrdersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $status_list = array_keys(trans('globals.order_status'));

        for ($i = 0; $i < 20; $i++) {
            $type = $faker->randomElement(['cart', 'wishlist', 'order']);
            $status = 'open';

            switch ($type) {
                case 'order':
                    $status = $faker->randomElement($status_list);
                break;
            }

            $stock = $faker->numberBetween(1, 20);

            $address = Address::inRandomOrder()->first();

            $order = Order::create([
                'user_id'     => $address->user_id,
                'seller_id'   => '3',
                'address_id'  => $address->id,
                'status'      => $status,
                'type'        => $type,
                'description' => $type == 'wishlist' ? $faker->companySuffix : '',
                'end_date'    => $status == 'closed' || $status == 'cancelled' ? $faker->dateTime() : null,
            ]);

            $product = Product::inRandomOrder()->first();
            $product_id = $product->id;

            for ($j = 0; $j < 5; $j++) {

                if ($status == 'closed') {
                    $delivery = $faker->dateTime();
                } else {
                    $delivery = $faker->numberBetween(0, 1) ? $faker->dateTime() : null;
                }

                OrderDetail::create([
                    'order_id'      => $order->first()->id,
                    'product_id'    => $product->id,
                    'price'         => $product->price,
                    'quantity'      => 1,
                    'delivery_date' => $faker->dateTime(),
                ]);

                $product = Product::where('id', '!=', $product_id)->inRandomOrder()->first();
            }
        }
    }
}
