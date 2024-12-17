<?php

namespace Database\Factories;

use App\Models\MemoryCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

class MemoryCardFactory extends Factory
{
    protected $model = MemoryCard::class;

    private const FIXED_THEMES = [
        'emojis' => [
            'content' => [
                '🐶', '🐱', '🐭', '🐹', '🐰', 
                '🦊', '🐻', '🐼', '🐨', '🐯', 
                '🦁', '🐮', '🐷', '🐸', '🐵'
            ]
        ]
    ];

    public function definition()
    {
        return [
            'game_id' => null,
            'matched_by' => null,
            'group_id' => null,
            'card_image' => null
        ];
    }

    /**
     * Generiert Karten für ein bestimmtes Thema
     *
     * @param string $theme
     * @param int $pairsCount
     * @return array
     */
    
    public function generateCardsForTheme(string $theme, int $pairsCount)
    {
        // Für Emojis
        if ($theme === 'emojis') {
            $selectedEmojis = $this->faker->randomElements(
                self::FIXED_THEMES['emojis']['content'], 
                min($pairsCount, count(self::FIXED_THEMES['emojis']['content']))
            );

            $cards = [];
            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                $content = $selectedEmojis[floor($i / 2)];

                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId,
                    'card_content' => $content
                ];
            }

            return $cards;
        }

        // Für benutzerdefinierte Themen
        $customThemePath = public_path("img/memory/{$theme}");
    
        if (!File::exists($customThemePath)) {
            throw new \InvalidArgumentException("Thema nicht gefunden: {$theme}");
        }
    
        $images = collect(File::files($customThemePath))
            ->map(function($file) use ($theme) {
                return "/img/memory/{$theme}/" . $file->getFilename();
            })
            ->shuffle()
            ->take($pairsCount)
            ->toArray();

        if (count($images) < $pairsCount) {
            throw new \InvalidArgumentException("Nicht genügend Bilder für das Thema {$theme}");
        }

        $cards = [];
        for ($i = 0; $i < $pairsCount * 2; $i++) {
            $groupId = floor($i / 2) + 1;
            $image = $images[floor($i / 2)];

            $cards[] = [
                'game_id' => null,
                'matched_by' => null,
                'group_id' => $groupId,
                'card_image' => $image
            ];
        }

        return $cards;
    }

    /**
     * Listet alle verfügbaren benutzerdefinierten Themen auf
     *
     * @return array
     */
    public function getCustomThemes()
    {
        $customThemePath = public_path('img/memory');
        
        if (!File::exists($customThemePath)) {
            return [];
        }
    
        return collect(File::directories($customThemePath))
            ->map(function($dir) {
                return basename($dir);
            })
            ->toArray();
    }
   
    /**
     * Liste aller verfügbaren Themen
     *
     * @return array
     */
    public function getAllThemes(): array
    {
        return array_merge(
            array_keys(self::FIXED_THEMES),
            $this->getCustomThemes()
        );
    }
}