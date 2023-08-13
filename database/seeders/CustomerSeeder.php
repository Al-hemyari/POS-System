<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class CustomerSeeder extends Seeder
{

    const USER_ID = "82da6c32-366b-4095-a5e5-0933b7833a0f";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        foreach (range(1, 20) as $index) {
            $customer = new Customer();
            $customer->name = $faker->name();
            $customer->save();
            $range = rand(5, 10);
            foreach (range(1, $range) as $index) {
                $this->createOrder($faker, $customer);
            }
        }

        foreach (range(1, 10) as $index) {
            $this->createOrder($faker);
        }
    }

    public function createOrder(Generator $faker, Customer $customer = null)
    {
        $subtotal = 0;
        $order = new Order();
        if ($customer) {
            $order->customer_id = $customer->id;
        }
        $order->user_id = self::USER_ID;
        $order->number = $faker->numberBetween();
        $order->delivery_charge = $faker->numberBetween(0, 5);
        $order->tax_rate = 0;
        $order->discount = $faker->numberBetween(0, 2);
        $order->subtotal = $subtotal;
        $order->total = $subtotal;
        $order->tender_amount = $subtotal;
        $order->change = 0;
        $created_at = $faker->dateTimeBetween('-2 years');
        $order->created_at = $created_at;
        $order->save();

        foreach (range(1, 10) as $index) {
            $product = Product::inRandomOrder()->first();
            $orderDetails = new OrderDetail();
            $orderDetails->order_id = $order->id;
            $orderDetails->product_id = $product->id;
            $qty = rand(1, 3);
            $orderDetails->quantity = $qty;
            $orderDetails->price = $product->price;
            $orderDetails->cost = $product->cost;
            $orderDetails->total = $product->price * $qty;
            $orderDetails->total_cost = $product->cost * $qty;
            $orderDetails->created_at = $created_at;
            $orderDetails->save();

            $subtotal += $product->price * $qty;
        }
        $order->subtotal = $subtotal;
        $order->total = $subtotal + $order->delivery_charge - $order->discount;
        $order->tender_amount =  $order->total;
        $order->save();
    }
}
