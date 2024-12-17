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
        ],
        'flags' => [
            'items' => [
                'Deutschland' => 'https://flagcdn.com/w320/de.png',
                'Frankreich' => 'https://flagcdn.com/w320/fr.png', 
                'Italien' => 'https://flagcdn.com/w320/it.png',
                'Spanien' => 'https://flagcdn.com/w320/es.png',
                'USA' => 'https://flagcdn.com/w320/us.png',
                'Kanada' => 'https://flagcdn.com/w320/ca.png',
                'Brasilien' => 'https://flagcdn.com/w320/br.png',
                'Japan' => 'https://flagcdn.com/w320/jp.png',
                'Australien' => 'https://flagcdn.com/w320/au.png',
                'Großbritannien' => 'https://flagcdn.com/w320/gb.png',
                'China' => 'https://flagcdn.com/w320/cn.png',
                'Russland' => 'https://flagcdn.com/w320/ru.png',
                'Indien' => 'https://flagcdn.com/w320/in.png',
                'Mexiko' => 'https://flagcdn.com/w320/mx.png',
                'Südafrika' => 'https://flagcdn.com/w320/za.png',
                'Südkorea' => 'https://flagcdn.com/w320/kr.png',
                'Argentinien' => 'https://flagcdn.com/w320/ar.png',
                'Niederlande' => 'https://flagcdn.com/w320/nl.png',
                'Schweden' => 'https://flagcdn.com/w320/se.png',
                'Schweiz' => 'https://flagcdn.com/w320/ch.png'
            ]
        ],
        'planets' => [
            'items' => [
                'Merkur' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Mercury_in_color_-_Prockter07-edit1.jpg/640px-Mercury_in_color_-_Prockter07-edit1.jpg',
                'Venus' => 'https://upload.wikimedia.org/wikipedia/commons/e/e5/Venus-real_color.jpg',
                'Erde' => 'https://upload.wikimedia.org/wikipedia/commons/9/97/The_Earth_seen_from_Apollo_17.jpg',
                'Mars' => 'https://upload.wikimedia.org/wikipedia/commons/0/02/OSIRIS_Mars_true_color.jpg',
                'Jupiter' => 'https://upload.wikimedia.org/wikipedia/commons/2/2b/Jupiter_and_its_shrunken_Great_Red_Spot.jpg',
                'Saturn' => 'https://upload.wikimedia.org/wikipedia/commons/c/c7/Saturn_during_Equinox.jpg',
                'Uranus' => 'https://upload.wikimedia.org/wikipedia/commons/3/3d/Uranus2.jpg',
                'Neptun' => 'https://upload.wikimedia.org/wikipedia/commons/6/63/Neptune_-_Voyager_2_%2829347980845%29_flatten_crop.jpg',
                'Pluto' => 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Pluto-01_Stern_background.jpg',
                'Sonne' => 'https://upload.wikimedia.org/wikipedia/commons/b/b4/The_Sun_by_the_Atmospheric_Imaging_Assembly_of_NASA%27s_Solar_Dynamics_Observatory_-_20100819.jpg',
                'Mond' => 'https://upload.wikimedia.org/wikipedia/commons/e/e5/Aldrin_Looks_Back_at_Tranquility_Base_-_GPN-2000-001102.jpg',
            ]
        ],
        'numbers' => [
            'method' => function($faker) { 
                return $faker->numberBetween(1, 10);
            }
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

        // Für Zahlen

        if ($theme === 'numbers') {
            $cards = [];
            $selectedNumbers = $this->faker->randomElements(
                range(1, 10), 
                min($pairsCount, 10)
            );
        
            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                $number = $selectedNumbers[floor($i / 2)];
        
                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId,
                    'card_content' => $number
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