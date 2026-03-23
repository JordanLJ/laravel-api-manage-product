<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            "Matériels de bureaux",
            "Accessoires de chambre",
            "Électronique",
            "Informatique",
            "Papeterie",
            "Mobilier",
            "Cuisine",
            "Salle de bain",
            "Vêtements",
            "Chaussures",
            "Jouets",
            "Sport",
            "Bricolage",
            "Jardinage",
            "Livres",
            "Musique",
            "Films",
            "Jeux vidéo",
            "Bijoux",
            "Montres",
            "Sacs",
            "Cosmétiques",
            "Parfums",
            "Téléphonie",
            "Photo & Vidéo",
            "Maison & Décoration",
            "Santé",
            "Automobile",
            "Loisirs créatifs",
            "Alimentation"
        ];

        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }
    }
}
