<?php

namespace Database\Factories;

use App\Models\MemoryCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemoryCardFactory extends Factory
{
    protected $model = MemoryCard::class;

    public function definition()
    {
        return [
            'game_id' => null, // Das Spiel wird dynamisch zugewiesen
            'is_matched' => false,
            'is_flipped' => false,
            'card_image' => $this->faker->imageUrl(100, 100, 'abstract'), // Zufälliges Bild
            'group_id' => $this->faker->numberBetween(1, 8), // null, // Wird später dynamisch gesetzt
            
        ];
    }
    // Reset der GroupID (nützlich für Tests)
    public function resetGroupId()
    {
        static $defaultGroupId = 1;
        return $this;
    }
}

