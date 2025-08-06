<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CambodiaAddressService
{
    private const CACHE_KEY = 'cambodia_postal_codes';
    private const CACHE_DURATION = 60 * 60 * 24; // 24 hours
    private const CDN_URL = 'https://cdn.jsdelivr.net/gh/seanghay/cambodia-postal-codes@main/cambodia-postal-codes.json';

    /**
     * Get all Cambodia postal codes data.
     */
    public function getPostalCodesData(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                $response = Http::timeout(10)->get(self::CDN_URL);
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                // Fallback to local data if CDN fails
                return $this->getLocalPostalCodesData();
            } catch (\Exception $e) {
                // Fallback to local data if request fails
                return $this->getLocalPostalCodesData();
            }
        });
    }

    /**
     * Get provinces/cities list.
     */
    public function getProvinces(): array
    {
        $data = $this->getPostalCodesData();
        
        return collect($data)->map(function ($province) {
            return [
                'id' => $province['id'],
                'name_kh' => $province['name'],
                'name_en' => $this->translateProvinceName($province['name']),
                'districts_count' => count($province['districts']),
            ];
        })->toArray();
    }

    /**
     * Get districts for a specific province.
     */
    public function getDistricts(int $provinceId): array
    {
        $data = $this->getPostalCodesData();
        
        $province = collect($data)->firstWhere('id', $provinceId);
        
        if (!$province) {
            return [];
        }

        return collect($province['districts'])->map(function ($district) {
            return [
                'id' => $district['id'],
                'no' => $district['no'],
                'name_kh' => $district['location_kh'],
                'name_en' => $district['location_en'],
                'communes_count' => count($district['codes']),
            ];
        })->toArray();
    }

    /**
     * Get communes/sangkats for a specific district.
     */
    public function getCommunes(int $districtId): array
    {
        $data = $this->getPostalCodesData();
        
        foreach ($data as $province) {
            $district = collect($province['districts'])->firstWhere('id', $districtId);
            
            if ($district) {
                return collect($district['codes'])->map(function ($commune) {
                    return [
                        'name_kh' => $commune['km'],
                        'name_en' => $commune['en'],
                        'postal_code' => $commune['code'],
                    ];
                })->toArray();
            }
        }

        return [];
    }

    /**
     * Get postal code for specific area selection.
     */
    public function getPostalCode(int $provinceId, int $districtId, string $communeName): ?string
    {
        $communes = $this->getCommunes($districtId);
        
        $commune = collect($communes)->first(function ($commune) use ($communeName) {
            return $commune['name_kh'] === $communeName || $commune['name_en'] === $communeName;
        });

        return $commune['postal_code'] ?? null;
    }

    /**
     * Search areas by name (for autocomplete).
     */
    public function searchAreas(string $query): array
    {
        $data = $this->getPostalCodesData();
        $results = [];

        foreach ($data as $province) {
            foreach ($province['districts'] as $district) {
                foreach ($district['codes'] as $commune) {
                    if (
                        str_contains(strtolower($commune['km']), strtolower($query)) ||
                        str_contains(strtolower($commune['en']), strtolower($query)) ||
                        str_contains(strtolower($district['location_kh']), strtolower($query)) ||
                        str_contains(strtolower($district['location_en']), strtolower($query))
                    ) {
                        $results[] = [
                            'province_id' => $province['id'],
                            'province_name' => $province['name'],
                            'district_id' => $district['id'],
                            'district_name' => $district['location_kh'],
                            'commune_name' => $commune['km'],
                            'commune_name_en' => $commune['en'],
                            'postal_code' => $commune['code'],
                            'full_address' => $commune['km'] . ', ' . $district['location_kh'] . ', ' . $province['name'],
                        ];
                    }
                }
            }
        }

        return array_slice($results, 0, 20); // Limit to 20 results
    }

    /**
     * Get local postal codes data as fallback.
     */
    private function getLocalPostalCodesData(): array
    {
        // Return minimal Phnom Penh data as fallback
        return [
            [
                'id' => 12,
                'name' => 'រាជធានីភ្នំពេញ',
                'districts' => [
                    [
                        'id' => 3,
                        'no' => '12.1',
                        'location_kh' => 'ខណ្ឌចំការមន',
                        'location_en' => 'Khan Chamkar Mon',
                        'codes' => [
                            [
                                'km' => 'សង្កាត់ ទន្លេបាសាក់',
                                'en' => 'Sangkat Tonle Basak',
                                'code' => '120101'
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Translate province names to English.
     */
    private function translateProvinceName(string $khmerName): string
    {
        $translations = [
            'រាជធានីភ្នំពេញ' => 'Phnom Penh',
            'ខេត្តបន្ទាយមានជ័យ' => 'Banteay Meanchey',
            'ខេត្តបាត់ដំបង' => 'Battambang',
            'ខេត្តកំពង់ចាម' => 'Kampong Cham',
            'ខេត្តកំពង់ឆ្នាំង' => 'Kampong Chhnang',
            'ខេត្តកំពង់ស្ពឺ' => 'Kampong Speu',
            'ខេត្តកំពង់ធំ' => 'Kampong Thom',
            'ខេត្តកំពត' => 'Kampot',
            'ខេត្តកណ្តាល' => 'Kandal',
            'ខេត្តកោះកុង' => 'Koh Kong',
            'ខេត្តក្រចេះ' => 'Kratie',
            'ខេត្តមណ្ឌលគីរី' => 'Mondul Kiri',
            'ខេត្តឧត្តរមានជ័យ' => 'Oddar Meanchey',
            'ខេត្តប៉ៃលិន' => 'Pailin',
            'ខេត្តព្រះវិហារ' => 'Preah Vihear',
            'ខេត្តព្រៃវែង' => 'Prey Veng',
            'ខេត្តពោធិ៍សាត់' => 'Pursat',
            'ខេត្តរតនគីរី' => 'Ratanak Kiri',
            'ខេត្តសៀមរាប' => 'Siem Reap',
            'ខេត្តព្រះសីហនុ' => 'Preah Sihanouk',
            'ខេត្តស្ទឹងត្រែង' => 'Stung Treng',
            'ខេត្តស្វាយរៀង' => 'Svay Rieng',
            'ខេត្តតាកែវ' => 'Takeo',
            'ខេត្តកែប' => 'Kep',
            'ខេត្តត្បូងឃ្មុំ' => 'Tboung Khmum',
        ];

        return $translations[$khmerName] ?? $khmerName;
    }

    /**
     * Clear cached postal codes data.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
