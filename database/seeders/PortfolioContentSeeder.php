<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Reprend le contenu qui était codé en dur dans l'ancien PortfolioController.
 * Idempotent : peut être relancé sans créer de doublons, et ne remplace
 * pas un contenu déjà modifié depuis l'administration.
 */
class PortfolioContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProfile();
        $technologies = $this->seedTechnologies();
        $this->seedProjects($technologies);
        $this->seedCareer();
    }

    private function seedProfile(): void
    {
        if (ProfileSetting::query()->exists()) {
            return;
        }

        ProfileSetting::create([
            'name' => 'Mahoulolo SEBIO',
            'headline' => ['fr' => 'Développeur Full Stack'],
            'subtitle' => ['fr' => "Ingénieur d'exploitation informatique"],
            'intro' => ['fr' => 'Développeur passionné avec plus de 2 ans d’expérience dans la conception et la réalisation de plateformes web modernes et performantes. Formateur indépendant, je partage mon savoir-faire avec les apprenants et les professionnels souhaitant renforcer leurs compétences numériques. En tant que promoteur d’AfriCode, j’œuvre à rendre l’apprentissage du numérique accessible aux jeunes Africains et à stimuler l’innovation technologique sur le continent.'],
            'about' => ['fr' => 'Je travaille sur divers projets de développement web, allant de plateformes éducatives à des solutions digitales destinées aux entreprises. Passionné par l’apprentissage continu, j’aime partager mes connaissances et contribuer activement à l’écosystème numérique africain.'],
            'objective' => ['fr' => 'Ma mission est de concevoir des solutions numériques innovantes qui répondent aux besoins des entreprises tout en ayant un impact positif sur la société.'],
            'current_position' => ['fr' => "Ingénieur d'exploitation informatique chez FINANCIAL HOSANNA CONSULTING GROUPE SARL"],
            'featured_project' => ['fr' => "AfriCode - une initiative qui vise à offrir aux jeunes Africains un accès simple à l'apprentissage du numérique, via une communauté, un site web éducatif et une application dédiée."],
            'featured_project_url' => 'https://africode.tech/',
            'email' => 'contact@mahoulolo-sebio.com',
            'phone' => '+229 01 65 60 30 40',
            'whatsapp' => '2290165603040',
            'location' => 'Godomey-Cotonou, Bénin',
            'cv_path' => null, // l'ancien CV reste servi depuis public/cv tant qu'aucun n'est téléversé
            'social_links' => [
                'github' => 'https://github.com/sebiomahoulolo',
                'linkedin' => 'https://www.linkedin.com/in/mahoulolo-sebio-9046a6261',
                'facebook' => 'https://facebook.com/sebio.mahoulolo',
            ],
            'spoken_languages' => [
                ['name' => 'Français', 'level' => 'Natif'],
                ['name' => 'Anglais', 'level' => 'Intermédiaire'],
                ['name' => 'Adja', 'level' => 'Natif'],
                ['name' => 'Baatonou', 'level' => 'Natif'],
            ],
            'interests' => ['fr' => ['Sport', 'Musique', 'Cinéma', 'Développement web', 'Innovation numérique']],
            'seo_title' => ['fr' => 'Mahoulolo SEBIO - Développeur Full Stack Laravel au Bénin'],
            'seo_description' => ['fr' => "Portfolio de Mahoulolo SEBIO - Développeur Full Stack et Ingénieur d'exploitation informatique"],
        ]);
    }

    /**
     * @return array<string, Technology> indexées par nom
     */
    private function seedTechnologies(): array
    {
        $skills = [
            'frontend' => [['HTML5', 95, 'fab fa-html5'], ['CSS3', 90, 'fab fa-css3-alt'], ['JavaScript', 85, 'fab fa-js'], ['React.js', 80, 'fab fa-react'], ['Angular', 75, 'fab fa-angular'], ['Bootstrap', 90, 'fab fa-bootstrap']],
            'backend' => [['Laravel', 90, 'fab fa-laravel'], ['PHP', 85, 'fab fa-php'], ['MySQL', 80, 'fas fa-database'], ['API REST', 85, 'fas fa-plug']],
            'tools' => [['Git/GitHub', 85, 'fab fa-github'], ['NPM', 80, 'fab fa-npm'], ['Composer', 85, 'fas fa-box'], ['FedaPay API', 75, 'fas fa-credit-card']],
        ];

        $technologies = [];

        foreach ($skills as $category => $items) {
            foreach ($items as $order => [$name, $level, $icon]) {
                $technologies[$name] = Technology::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    compact('name', 'category', 'level', 'icon') + ['sort_order' => $order, 'show_in_skills' => true],
                );
            }
        }

        // Technologies citées dans les projets mais absentes de la section « Compétences ».
        foreach (['HTML', 'CSS'] as $name) {
            $technologies[$name] = Technology::firstOrCreate(
                ['slug' => Str::slug($name).'-projet'],
                ['name' => $name, 'category' => 'frontend', 'show_in_skills' => false, 'sort_order' => 99],
            );
        }

        return $technologies;
    }

    /**
     * @param  array<string, Technology>  $technologies
     */
    private function seedProjects(array $technologies): void
    {
        $projects = [
            ['StagesBENIN', 'Plateforme qui connecte candidats et entreprises pour stages et emplois.', ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL', 'FedaPay API'], 'https://stagesbenin.com/'],
            ['Africa-Location', 'Site immobilier développé avec Laravel et Bootstrap.', ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://africa-location.com/'],
            ['CVLink', 'Plateforme de création et de partage de CV professionnels en ligne.', ['Laravel', 'Bootstrap', 'JavaScript', 'HTML', 'CSS'], 'https://cvlink.stagesbenin.com/'],
            ['BBLove', 'Plateforme en ligne pour les rencontres avec système de billetterie.', ['Laravel', 'FedaPay API', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://app.bb-love.com/'],
            ['AfriCode', "Plateforme éducative 100% Laravel dédiée à l'apprentissage du numérique en Afrique.", ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://africode.tech/'],
            ['École des Arcanes Majeurs', 'Site vitrine interactif pour services spirituels et éducatifs.', ['Bootstrap', 'PHP', 'JavaScript', 'HTML', 'CSS'], 'https://ecoledesarcanesmajeurs.org/'],
        ];

        foreach ($projects as $order => [$title, $excerpt, $techNames, $demoUrl]) {
            $project = Project::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => ['fr' => $title],
                    'excerpt' => ['fr' => $excerpt],
                    'status' => PublicationStatus::Published,
                    'published_at' => now(),
                    'sort_order' => $order,
                    'is_featured' => true,
                    'demo_url' => $demoUrl,
                ],
            );

            if ($project->wasRecentlyCreated) {
                $project->technologies()->sync(
                    collect($techNames)->map(fn ($name) => $technologies[$name]->id)->all()
                );
            }
        }
    }

    private function seedCareer(): void
    {
        if (! Experience::query()->exists()) {
            Experience::create([
                'title' => ['fr' => "Ingénieur d'exploitation informatique"],
                'company' => 'FINANCIAL HOSANNA CONSULTING GROUPE SARL',
                'location' => 'Abomey-Calavi, Bénin',
                'employment_type' => 'full_time',
                'description' => ['fr' => 'Gestion des infrastructures IT et développement de solutions numériques internes et externes.'],
                'started_at' => '2024-09-01',
                'sort_order' => 0,
            ]);
            Experience::create([
                'title' => ['fr' => 'Développeur web freelance'],
                'company' => 'Freelance',
                'location' => 'Bénin',
                'employment_type' => 'freelance',
                'description' => ['fr' => 'Conception et réalisation de sites et plateformes web pour écoles, entreprises et startups.'],
                'started_at' => '2021-01-01',
                'sort_order' => 1,
            ]);
        }

        if (! Education::query()->exists()) {
            Education::create([
                'degree' => ['fr' => 'Licence professionnelle'],
                'field' => ['fr' => 'Système informatique et logiciels'],
                'institution' => 'INSTITUT UNIVERSITAIRE LES COURS SONOU',
                'location' => 'Parakou, Bénin',
                'start_year' => 2021,
                'end_year' => 2024,
                'sort_order' => 0,
            ]);
            Education::create([
                'degree' => ['fr' => 'Baccalauréat'],
                'field' => ['fr' => 'Série D'],
                'institution' => 'CEG 1 PARTAGO',
                'location' => 'Djougou, Bénin',
                'start_year' => 2018,
                'end_year' => 2021,
                'sort_order' => 1,
            ]);
        }

        if (! Certification::query()->exists()) {
            $certifications = [
                ['Certificat en Développement Front-End', 'FORCE-N SÉNÉGAL', '2024-07-01'],
                ["Certificat d'achèvement du cours sur le cryptage", 'Internet Society', '2024-02-01'],
                ['Certificat en gestion de projet', 'Disasterreadyorg', '2023-10-01'],
                ['Certificat de fin de cours en HTML ET CSS', 'Cursa', '2023-09-01'],
            ];

            foreach ($certifications as $order => [$name, $issuer, $issuedAt]) {
                Certification::create([
                    'name' => ['fr' => $name],
                    'issuer' => $issuer,
                    'issued_at' => $issuedAt,
                    'sort_order' => $order,
                ]);
            }
        }
    }
}
