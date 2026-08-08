<?php

namespace Database\Seeders;

use App\Models\Heroslide;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HeroSlideSeeder extends Seeder
{
public function run()
    {
        Heroslide::create([
            'image'       => 'hero-carousel-1.jpg',
            'title'       => 'We are professional',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
            'button_text' => 'Get Started',
            'button_link' => '#featured-services',
        ]);

        Heroslide::create([
            'image'       => 'hero-carousel-2.jpg',
            'title'       => 'At vero eos et accusamus',
            'description' => 'Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil...',
            'button_text' => 'Get Started',
            'button_link' => '#featured-services',
        ]);

        Heroslide::create([
            'image'       => 'hero-carousel-3.jpg',
            'title'       => 'Temporibus autem quibusdam',
            'description' => 'Beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia...',
            'button_text' => 'Get Started',
            'button_link' => '#featured-services',
        ]);
    }
}
