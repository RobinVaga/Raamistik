<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'name' => $this->faker->name(),
        'description' => $this->faker->sentence(),
        'price' => $this->faker->randomFloat(2, 0, 1000),
        'sku' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
        'stock_quantity' => $this->faker->numberBetween(0, 100),
        'image' => 'https://picsum.photos/seed/' . $this->faker->unique()->numberBetween(1, 10000) . '/372/372',        'created_at' => $this->faker->dateTime(),
        'updated_at' => $this->faker->dateTime(),
    ];
    }
}
