<?php

namespace Database\Factories;

use App\Models\MemoryCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MemoryCardFactory extends Factory
{
    protected $model = MemoryCard::class;

    // Verfügbare Kartenanzahlen für das Spiel
    public const AVAILABLE_CARD_COUNTS = [12, 16, 20];

    private const FIXED_THEMES = [

        'emojis' => [
            'content' => [
                '🐶', '🐱', '🐭', '🐹', '🐰', 
                '🦊', '🐻', '🐼', '🐨', '🐯', 
                '🦁', '🐮', '🐷', '🐸', '🐵',
                '🦄', '🐲', '🐙', '🦉', '🐧'
            ]
        ],
        'sports' => [
            'content' => [
                '⚽', '🏀', '🎾', '🏐', '⚾', 
                '🏊', '🏃', '🚴', '🏌️', '🥊', 
                '🤼', '🥋', '🏇', '🎯', '🏓', 
                '🏒', '🎿', '🏂', '⛵', '🤿'
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
    ];

    public function definition()
    {
        return [
            'game_id' => null,
            'matched_by' => null,
            'group_id' => null,
        ];
    }

    /**
     * Generiert Karten für ein bestimmtes Thema mit variabler Anzahl
     *
     * @param string $theme
     * @param int $pairsCount
     * @return array
     * @throws \InvalidArgumentException
     */
    
    public function generateCardsForTheme(string $theme, int $pairsCount)
    {
        
        // Für Emojis
        if ($theme === 'emojis' || $theme === 'sports') {
            $selectedItems = $this->faker->randomElements(
                self::FIXED_THEMES[$theme]['content'], 
                min($pairsCount, count(self::FIXED_THEMES[$theme]['content']))
            );

            $cards = [];
            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                
                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId
                ];
            }
            return $cards;
        }

        // Für Flags und Planets
        if ($theme === 'flags' || $theme === 'planets') {
            $items = self::FIXED_THEMES[$theme]['items'];
            $selectedKeys = $this->faker->randomElements(
                array_keys($items),
                min($pairsCount, count($items))
            );

            $cards = [];
            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                
                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId
                ];
            }
            return $cards;
        }

        // Für Sportarten

        if ($theme === 'sports') {
            $cards = [];
            $selectedSports = $this->faker->randomElements(
                self::FIXED_THEMES['sports']['content'], 
                min($pairsCount, count(self::FIXED_THEMES['sports']['content']))
            );
        
            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                $sport = $selectedSports[floor($i / 2)];
        
                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId,
                ];
            }
        
            return $cards;
        }

        // Für benutzerdefinierte Themen aus dem public/img/memory/ Ordner
        return $this->generateCustomThemeCards($theme, $pairsCount);
    
        if (!File::exists($customThemePath)) {
            throw new \InvalidArgumentException("Thema nicht gefunden: {$theme}");
        }
    
        $images = collect(File::files($customThemePath))
        ->filter(function($file) {
            // Nur Bild-Dateitypen zulassen
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            return in_array(strtolower($file->getExtension()), $allowedExtensions);
        })
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

        // Validiere die Kartenanzahl
        if (!in_array($pairsCount * 2, self::AVAILABLE_CARD_COUNTS)) {
            throw new \InvalidArgumentException(
                "Ungültige Kartenanzahl. Erlaubt sind: " . implode(', ', self::AVAILABLE_CARD_COUNTS)
            );
        }

        if (isset(self::FIXED_THEMES[$theme])) {
            return $this->generateFixedThemeCards($theme, $pairsCount);
        }

        return $this->generateCustomThemeCards($theme, $pairsCount);
    }

    private function generateFixedThemeCards(string $theme, int $pairsCount)
    {
        $cards = [];
        
        if ($theme === 'emojis' || $theme === 'sports') {
            $content = self::FIXED_THEMES[$theme]['content'];
            $selectedItems = $this->faker->randomElements(
                $content,
                min($pairsCount, count($content))
            );

            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                $content = $selectedItems[floor($i / 2)];

                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId,
                    'card_content' => $content,
                    'card_name' => null
                ];
            }
        } elseif ($theme === 'flags' || $theme === 'planets') {
            $items = self::FIXED_THEMES[$theme]['items'];
            $selectedKeys = $this->faker->randomElements(
                array_keys($items),
                min($pairsCount, count($items))
            );

            for ($i = 0; $i < $pairsCount * 2; $i++) {
                $groupId = floor($i / 2) + 1;
                $key = $selectedKeys[floor($i / 2)];

                $cards[] = [
                    'game_id' => null,
                    'matched_by' => null,
                    'group_id' => $groupId,
                    'card_image' => $items[$key],
                    'card_name' => $key
                ];
            }
        }

        return $cards;
    }
    private function generateCustomThemeCards(string $theme, int $pairsCount)
    {
        $customThemePath = public_path("img/memory/{$theme}");
    
        if (!File::exists($customThemePath)) {
            throw new \InvalidArgumentException("Thema nicht gefunden: {$theme}");
        }
    
        $images = collect(File::files($customThemePath))
            ->filter(function($file) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                return in_array(strtolower($file->getExtension()), $allowedExtensions);
            })
            ->map(function($file) use ($theme) {
                return "/img/memory/{$theme}/" . $file->getFilename();
            })
            ->shuffle()
            ->take($pairsCount)
            ->toArray();

        if (count($images) < $pairsCount) {
            throw new \InvalidArgumentException(
                "Nicht genügend Bilder für das Thema {$theme} und {$pairsCount} Paare"
            );
        }

        $cards = [];
        for ($i = 0; $i < $pairsCount * 2; $i++) {
            $groupId = floor($i / 2) + 1;
            $image = $images[floor($i / 2)];

            $cards[] = [
                'game_id' => null,
                'matched_by' => null,
                'group_id' => $groupId,
            ];
        }

        return $cards;
    }

    /**
     * Gibt den Titel eines Themas zurück
     *
     * @param string $theme
     * @return string
     */
    public function getThemeTitle(string $theme): string
    {
        if (isset(self::FIXED_THEMES[$theme])) {
            return self::FIXED_THEMES[$theme]['title'];
        }
        
        // Für benutzerdefinierte Themen den Ordnernamen formatieren
        return ucfirst(str_replace('-', ' ', $theme));
    }

    /**
     * Listet alle verfügbaren Themen mit Titeln
     *
     * @return array
     */
    public function getAllThemesWithTitles(): array
    {
        $fixedThemes = array_map(function($theme, $key) {
            return [
                'id' => $key,
                'title' => $theme['title']
            ];
        }, self::FIXED_THEMES, array_keys(self::FIXED_THEMES));

        $customThemes = array_map(function($theme) {
            return [
                'id' => $theme,
                'title' => $this->getThemeTitle($theme)
            ];
        }, $this->getCustomThemes());

        return array_merge($fixedThemes, $customThemes);
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
    
        // Debug-Ausgabe
        $themes = collect(File::directories($customThemePath))
            ->map(function($dir) {
                return basename($dir);
            })
            ->toArray();

        Log::info('Custom Themes found:', $themes);

        return $themes;
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

        /**
     * Gibt den Content für eine bestimmte Karte zurück
     */
    public function getCardContent(string $theme, int $groupId): array
    {
        if ($theme === 'emojis' || $theme === 'sports') {
            return [
                'content' => self::FIXED_THEMES[$theme]['content'][$groupId - 1] ?? '❓',
                'name' => null
            ];
        }

        if ($theme === 'flags' || $theme === 'planets') {
            $items = self::FIXED_THEMES[$theme]['items'];
            $keys = array_keys($items);
            $key = $keys[$groupId - 1] ?? null;
            
            return [
                'content' => $items[$key] ?? null,
                'name' => $key ?? null
            ];
        }

        return [
            'content' => null,
            'name' => null
        ];
    }
}