<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'name' => fake()->unique()->randomElement([
                'การ์ดจอ',
                'เมาส์',
                'คีย์บอร์ด',
                'จอมอนิเตอร์',
                'หูฟัง',
                'อุปกรณ์จัดเก็บข้อมูล',
            ]),
        ];
    }
}
