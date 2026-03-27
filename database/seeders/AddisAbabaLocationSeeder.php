<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubCity;
use App\Models\Woreda;

class AddisAbabaLocationSeeder extends Seeder
{
    private function makeWoredas(int $count): array
    {
        $woredas = [];
        for ($i = 1; $i <= $count; $i++) {
            $woredas[] = [
                'code' => $i,
                'name_en' => sprintf('Woreda %02d', $i),
                'name_am' => sprintf('ወረዳ %02d', $i),
            ];
        }
        return $woredas;
    }

    public function run(): void
    {
        Woreda::query()->delete();
        SubCity::query()->delete();

        $subCities = [
            ['code' => 1, 'name_en' => 'Addis Ketema', 'name_am' => 'አዲስ ከተማ', 'woredas' => 14],
            ['code' => 2, 'name_en' => 'Akaki Kaliti', 'name_am' => 'አቃቂ ቃሊቲ', 'woredas' => 11],
            ['code' => 3, 'name_en' => 'Arada', 'name_am' => 'አራዳ', 'woredas' => 10],
            ['code' => 4, 'name_en' => 'Bole', 'name_am' => 'ቦሌ', 'woredas' => 15],
            ['code' => 5, 'name_en' => 'Gullele', 'name_am' => 'ጉለሌ', 'woredas' => 10],
            ['code' => 6, 'name_en' => 'Kirkos', 'name_am' => 'ቂርቆስ', 'woredas' => 11],
            ['code' => 7, 'name_en' => 'Kolfe Keranio', 'name_am' => 'ኮልፈ ቀራኒዮ', 'woredas' => 15],
            ['code' => 8, 'name_en' => 'Lemi Kura', 'name_am' => 'ለሚ ኩራ', 'woredas' => 14],
            ['code' => 9, 'name_en' => 'Lideta', 'name_am' => 'ልደታ', 'woredas' => 10],
            ['code' => 10, 'name_en' => 'Nifas Silk-Lafto', 'name_am' => 'ንፋስ ስልክ ላፍቶ', 'woredas' => 15],
            ['code' => 11, 'name_en' => 'Yeka', 'name_am' => 'የካ', 'woredas' => 13],
        ];

        foreach ($subCities as $data) {
            $woredaCount = $data['woredas'];
            unset($data['woredas']);

            $subCity = SubCity::create($data);

            foreach ($this->makeWoredas($woredaCount) as $woreda) {
                $subCity->woredas()->create($woreda);
            }
        }

        $this->command->info('✅ Seeded ' . SubCity::count() . ' sub-cities and ' . Woreda::count() . ' woredas.');
    }
}
