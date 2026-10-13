<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Aucun compte n'est créé ici : le compte propriétaire se crée avec
     * la commande « php artisan portfolio:create-owner ».
     */
    public function run(): void
    {
        $this->call(PortfolioContentSeeder::class);
    }
}
