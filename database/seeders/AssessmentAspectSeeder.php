<?php

namespace Database\Seeders;

use App\Models\AssessmentAspect;
use Illuminate\Database\Seeder;

class AssessmentAspectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aspects = [
            [
                'category' => 'NAM',
                'name' => 'Nilai Agama dan Moral',
                'description' => 'Perkembangan nilai agama dan moral anak',
                'order' => 1,
            ],
            [
                'category' => 'Fisik Motorik',
                'name' => 'Fisik Motorik',
                'description' => 'Perkembangan fisik dan motorik kasar serta halus',
                'order' => 2,
            ],
            [
                'category' => 'Kognitif',
                'name' => 'Kognitif',
                'description' => 'Perkembangan kognitif, berpikir logis, dan pemecahan masalah',
                'order' => 3,
            ],
            [
                'category' => 'Bahasa',
                'name' => 'Bahasa',
                'description' => 'Perkembangan bahasa reseptif dan ekspresif',
                'order' => 4,
            ],
            [
                'category' => 'Sosial Emosional',
                'name' => 'Sosial Emosional',
                'description' => 'Perkembangan sosial dan emosional anak',
                'order' => 5,
            ],
            [
                'category' => 'Seni',
                'name' => 'Seni',
                'description' => 'Perkembangan seni dan kreativitas',
                'order' => 6,
            ],
        ];

        foreach ($aspects as $aspect) {
            AssessmentAspect::updateOrCreate(
                ['category' => $aspect['category']],
                $aspect
            );
        }

        $this->command->info('✓ Assessment aspects seeded successfully');
    }
}
