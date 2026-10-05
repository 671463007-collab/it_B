<?php

namespace Database\Factories;

use App\Models\ExchangePost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangePost>
 */
class ExchangePostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),

            'category_id' => \App\Models\Category::inRandomOrder()->value('id'),
            'title' => fake()->randomElement([
                'RTX 3060 12GB สภาพสวย',
                'Logitech MX Master 3',
                'คีย์บอร์ด Keychron K2',
                'จอ 24 นิ้ว 144Hz',
                'SSD NVMe 512GB',
                'หูฟัง Sony WH-1000XM4',
            ]),
            'description' => fake()->paragraph(3),
            'condition_percent' => fake()->numberBetween(50, 100),
            'looking_for' => fake()->randomElement([
                'อยากได้การ์ดจอรุ่นใหม่กว่า',
                'แลกกับเมาส์ไร้สาย',
                'สนใจทุกข้อเสนอ',
            ]),
            'status' => 'open',
        ];
    }
}
