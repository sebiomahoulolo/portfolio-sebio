<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\ProfileSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu initial du portfolio (profil LinkedIn d'octobre 2026), en français
 * et en anglais. Idempotent : peut être relancé sans créer de doublons et
 * ne remplace pas un contenu déjà modifié depuis l'administration.
 */
class PortfolioContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProfile();
        $technologies = $this->seedTechnologies();
        $this->seedProjects($technologies);
        $this->seedCareer();
        $this->seedServices();
        $this->seedTestimonials();
    }

    private function seedProfile(): void
    {
        if (ProfileSetting::query()->exists()) {
            return;
        }

        ProfileSetting::create([
            'name' => 'Mahoulolo SEBIO',
            'headline' => ['fr' => 'Développeur Full Stack Laravel', 'en' => 'Full Stack Laravel Developer'],
            'subtitle' => ['fr' => "Fondateur & CEO d'AfriCode", 'en' => 'Founder & CEO @ AfriCode'],
            'tagline' => [
                'fr' => 'Je bâtis des SaaS sécurisés. Je forme les devs de demain.',
                'en' => "I build secure SaaS. I train tomorrow's developers.",
            ],
            'expertise' => [
                'fr' => ['Laravel', 'SaaS multi-tenant', 'SSO & RBAC', 'Sécurité applicative', 'Formation tech'],
                'en' => ['Laravel', 'Multi-tenant SaaS', 'SSO & RBAC', 'Application security', 'Tech training'],
            ],
            'years_experience' => 2,
            'intro' => [
                'fr' => "Je transforme des systèmes éparpillés en plateformes uniques, sécurisées et faciles à faire évoluer. Développeur Full Stack Laravel depuis plus de 2 ans, je conçois des applications web et des plateformes SaaS, du cahier des charges jusqu'à la mise en production.",
                'en' => 'I turn scattered systems into unified platforms that are secure and easy to evolve. A Full Stack Laravel developer for over 2 years, I design web applications and SaaS platforms, from the specification to production.',
            ],
            'about' => [
                'fr' => "Je travaille sur des projets variés, des plateformes éducatives aux solutions digitales pour les entreprises et les institutions. Formateur, je partage mon savoir-faire avec les apprenants et les professionnels qui veulent renforcer leurs compétences numériques. Avec AfriCode, j'œuvre à rendre l'apprentissage du numérique accessible aux jeunes Africains.",
                'en' => 'I work on a wide range of projects, from educational platforms to digital solutions for companies and institutions. As a trainer, I share my know-how with learners and professionals who want to strengthen their digital skills. Through AfriCode, I help make digital learning accessible to young Africans.',
            ],
            'objective' => [
                'fr' => 'Concevoir des solutions numériques fiables qui répondent aux besoins réels des organisations, tout en ayant un impact positif sur la société.',
                'en' => 'Building reliable digital solutions that meet the real needs of organisations while having a positive impact on society.',
            ],
            'current_position' => [
                'fr' => "Digital Platform Assistant à l'African Institute for Research in Infectious Diseases (AIRID)",
                'en' => 'Digital Platform Assistant at the African Institute for Research in Infectious Diseases (AIRID)',
            ],
            'featured_project' => [
                'fr' => "AfriCode - Une initiative qui offre aux jeunes Africains un accès simple à l'apprentissage du numérique, via une communauté, une plateforme éducative et une application dédiée.",
                'en' => 'AfriCode - An initiative giving young Africans simple access to digital learning through a community, an educational platform and a dedicated app.',
            ],
            'featured_project_url' => 'https://africode.tech/',
            'email' => 'contact@mahoulolo-sebio.com',
            'phone' => '+229 01 65 60 30 40',
            'whatsapp' => '2290165603040',
            'location' => 'Cotonou, Bénin',
            'social_links' => [
                'linkedin' => 'https://www.linkedin.com/in/mahoulolo-sebio-9046a6261',
                'github' => 'https://github.com/sebiomahoulolo',
                'facebook' => 'https://facebook.com/sebio.mahoulolo',
            ],
            'spoken_languages' => [
                ['name' => 'Français', 'level' => 'Natif'],
                ['name' => 'Anglais', 'level' => 'Intermédiaire'],
                ['name' => 'Adja', 'level' => 'Natif'],
                ['name' => 'Baatonou', 'level' => 'Natif'],
            ],
            'interests' => [
                'fr' => ['Innovation numérique', 'Transmission', 'Sport', 'Musique', 'Cinéma'],
                'en' => ['Digital innovation', 'Teaching', 'Sport', 'Music', 'Cinema'],
            ],
            'seo_title' => [
                'fr' => 'Mahoulolo SEBIO — Développeur Full Stack Laravel & SaaS au Bénin',
                'en' => 'Mahoulolo SEBIO — Full Stack Laravel & SaaS Developer in Benin',
            ],
            'seo_description' => [
                'fr' => 'Développeur Full Stack Laravel, fondateur d’AfriCode. Plateformes SaaS sécurisées, applications web sur mesure et formation tech à Cotonou.',
                'en' => 'Full Stack Laravel developer and founder of AfriCode. Secure SaaS platforms, custom web applications and tech training from Cotonou, Benin.',
            ],
        ]);
    }

    /**
     * @return array<string, Technology> indexées par nom
     */
    private function seedTechnologies(): array
    {
        $skills = [
            'frontend' => [['HTML5', 95, 'fab fa-html5'], ['CSS3', 90, 'fab fa-css3-alt'], ['JavaScript', 85, 'fab fa-js'], ['React.js', 80, 'fab fa-react'], ['Angular', 75, 'fab fa-angular'], ['Bootstrap', 90, 'fab fa-bootstrap']],
            'backend' => [['Laravel', 90, 'fab fa-laravel'], ['PHP', 85, 'fab fa-php'], ['MySQL', 80, 'fas fa-database'], ['PostgreSQL', 75, 'fas fa-database'], ['API REST', 85, 'fas fa-plug']],
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
            ['StagesBENIN', 'Plateforme qui connecte candidats et entreprises pour stages et emplois.', 'A platform connecting candidates and companies for internships and jobs.', ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL', 'FedaPay API'], 'https://stagesbenin.com/', 'Plateforme emploi'],
            ['AfriCode', "Plateforme éducative 100 % Laravel dédiée à l'apprentissage du numérique en Afrique.", 'A 100% Laravel educational platform dedicated to digital learning in Africa.', ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://africode.tech/', 'EdTech'],
            ['Africa-Location', 'Site immobilier de location et de vente développé avec Laravel.', 'A real-estate rental and sales website built with Laravel.', ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://africa-location.com/', 'Immobilier'],
            ['CVLink', 'Plateforme de création et de partage de CV professionnels en ligne.', 'A platform to create and share professional resumes online.', ['Laravel', 'Bootstrap', 'JavaScript', 'HTML', 'CSS'], 'https://cvlink.stagesbenin.com/', 'SaaS'],
            ['BBLove', 'Plateforme de rencontres avec système de billetterie et paiement en ligne.', 'A dating platform with ticketing and online payment.', ['Laravel', 'FedaPay API', 'Bootstrap', 'JavaScript', 'MySQL'], 'https://app.bb-love.com/', 'Événementiel'],
            ['École des Arcanes Majeurs', 'Site vitrine interactif pour des services éducatifs.', 'An interactive showcase website for educational services.', ['Bootstrap', 'PHP', 'JavaScript', 'HTML', 'CSS'], 'https://ecoledesarcanesmajeurs.org/', 'Site vitrine'],
        ];

        foreach ($projects as $order => [$title, $fr, $en, $techNames, $demoUrl, $category]) {
            $project = Project::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => ['fr' => $title, 'en' => $title],
                    'excerpt' => ['fr' => $fr, 'en' => $en],
                    'category' => $category,
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
            $experiences = [
                [
                    ['fr' => 'Digital Platform Assistant', 'en' => 'Digital Platform Assistant'],
                    'African Institute for Research in Infectious Diseases (AIRID)', 'Cotonou, Bénin', 'full_time', '2026-01-01', null,
                    ['fr' => 'Création, mise à jour et gestion des plateformes web institutionnelles ; configuration et optimisation des systèmes.', 'en' => 'Building, updating and managing institutional web platforms; configuring and optimising systems.'],
                ],
                [
                    ['fr' => 'Directeur général', 'en' => 'Chief Executive Officer'],
                    'AfriCode : No code, No future', 'Cotonou, Bénin', 'freelance', '2025-11-01', null,
                    ['fr' => "Direction d'AfriCode : stratégie, plateforme éducative et programmes de formation pour rendre le numérique accessible aux jeunes Africains.", 'en' => 'Leading AfriCode: strategy, educational platform and training programmes making digital skills accessible to young Africans.'],
                ],
                [
                    ['fr' => "Ingénieur d'exploitation informatique — Développeur web et chef d'équipe", 'en' => 'IT Operations Engineer — Web Developer & Team Lead'],
                    'FHC GROUPE BENIN', 'Cotonou, Bénin', 'full_time', '2024-11-01', '2026-01-31',
                    ['fr' => "Conception de solutions alliant performance, sécurité et design intuitif ; formation et encadrement de l'équipe informatique.", 'en' => 'Delivered solutions combining performance, security and intuitive design; trained and mentored the IT team.'],
                ],
                [
                    ['fr' => 'Stagiaire en recherche en sciences du numérique', 'en' => 'Digital Sciences Research Intern'],
                    'IMHOTEP Labs', 'Parakou, Bénin', 'internship', '2024-02-01', '2024-04-30',
                    ['fr' => 'Conception et réalisation d’un didacticiel multilingue des sciences et technologies (Epistémoscience) et contribution à la maintenance.', 'en' => 'Designed and built a multilingual science and technology learning tool (Epistémoscience) and contributed to its maintenance.'],
                ],
                [
                    ['fr' => 'Stagiaire en développement web', 'en' => 'Web Development Intern'],
                    'LIGHT Innovation', 'Parakou, Bénin', 'internship', '2023-07-01', '2023-09-30',
                    ['fr' => 'Développement de sites web et de fonctionnalités e-commerce au sein d’une entreprise spécialisée dans le numérique.', 'en' => 'Built websites and e-commerce features within a digital services company.'],
                ],
            ];

            foreach ($experiences as $order => [$title, $company, $location, $type, $start, $end, $description]) {
                Experience::create([
                    'title' => $title,
                    'company' => $company,
                    'location' => $location,
                    'employment_type' => $type,
                    'description' => $description,
                    'started_at' => $start,
                    'ended_at' => $end,
                    'sort_order' => $order,
                ]);
            }
        }

        if (! Education::query()->exists()) {
            Education::create([
                'degree' => ['fr' => 'Licence professionnelle — mention Bien', 'en' => "Professional Bachelor's degree — with honours"],
                'field' => ['fr' => 'Systèmes informatiques et logiciels', 'en' => 'Computer Systems and Software'],
                'institution' => 'Institut Universitaire LES COURS SONOU',
                'location' => 'Parakou, Bénin',
                'start_year' => 2021,
                'end_year' => 2024,
                'sort_order' => 0,
            ]);
            Education::create([
                'degree' => ['fr' => 'Baccalauréat série D', 'en' => 'Baccalaureate (Science)'],
                'field' => null,
                'institution' => 'CEG 1 Partago',
                'location' => 'Djougou, Bénin',
                'start_year' => 2018,
                'end_year' => 2021,
                'sort_order' => 1,
            ]);
        }

        if (! Certification::query()->exists()) {
            $certifications = [
                [['fr' => 'Certificat en Développement Front-end', 'en' => 'Front-end Development Certificate'], 'FORCE-N Sénégal', '2024-10-01'],
                [['fr' => 'Cours sur le chiffrement', 'en' => 'Encryption course'], 'Internet Society', '2024-02-01'],
                [['fr' => 'Certificat en gestion de projet', 'en' => 'Project Management Certificate'], 'DisasterReady', '2023-10-01'],
                [['fr' => 'Formation des formateurs sur la Gouvernance de l’Internet', 'en' => 'Train-the-trainers on Internet Governance'], 'Forum sur la Gouvernance de l’Internet au Bénin (FGI Bénin)', '2023-09-01'],
            ];

            foreach ($certifications as $order => [$name, $issuer, $issuedAt]) {
                Certification::create(['name' => $name, 'issuer' => $issuer, 'issued_at' => $issuedAt, 'sort_order' => $order]);
            }
        }
    }

    private function seedServices(): void
    {
        $services = [
            [
                'developpement-web', 'fas fa-globe',
                ['fr' => 'Développement web', 'en' => 'Web development'],
                ['fr' => 'Sites et plateformes web rapides, sécurisés et faciles à administrer.', 'en' => 'Fast, secure websites and web platforms that are easy to manage.'],
                ['fr' => "Du site vitrine à la plateforme métier, je développe des applications web avec Laravel, pensées pour la performance, le référencement et la sécurité. Vous disposez d'un espace d'administration simple pour faire vivre votre contenu sans dépendre d'un développeur.", 'en' => 'From showcase sites to business platforms, I build Laravel web applications designed for performance, SEO and security. You get a simple admin area to keep your content up to date without depending on a developer.'],
                ['fr' => ['Analyse du besoin et maquettes', 'Développement Laravel sur mesure', 'Espace d’administration', 'Référencement et performance', 'Mise en ligne et formation'], 'en' => ['Needs analysis and mock-ups', 'Custom Laravel development', 'Admin dashboard', 'SEO and performance', 'Go-live and training']],
            ],
            [
                'logiciels-sur-mesure-saas', 'fas fa-layer-group',
                ['fr' => 'Logiciels sur mesure & SaaS', 'en' => 'Custom software & SaaS'],
                ['fr' => 'Plateformes SaaS multi-tenant, SSO et gestion fine des rôles.', 'en' => 'Multi-tenant SaaS platforms with SSO and fine-grained roles.'],
                ['fr' => 'Je conçois des logiciels qui remplacent vos fichiers et outils éparpillés par une plateforme unique : architecture multi-tenant, authentification unique (SSO), rôles et permissions (RBAC), abonnements et paiements en ligne. Chaque projet est livré avec des tests automatisés et une documentation.', 'en' => 'I build software that replaces scattered files and tools with a single platform: multi-tenant architecture, single sign-on (SSO), roles and permissions (RBAC), subscriptions and online payments. Every project ships with automated tests and documentation.'],
                ['fr' => ['Architecture multi-tenant', 'SSO et RBAC', 'Paiement en ligne (FedaPay…)', 'Tests automatisés', 'Documentation et maintenance'], 'en' => ['Multi-tenant architecture', 'SSO and RBAC', 'Online payments (FedaPay…)', 'Automated tests', 'Documentation and maintenance']],
            ],
            [
                'developpement-applications', 'fas fa-mobile-screen-button',
                ['fr' => "Développement d'applications", 'en' => 'Application development'],
                ['fr' => 'Applications web progressives et API pour le terrain et le mobile.', 'en' => 'Progressive web apps and APIs for field and mobile use.'],
                ['fr' => 'Applications web progressives utilisables hors connexion, API REST pour vos applications mobiles, intégrations avec vos outils existants : je construis des applications pensées pour les usages réels, y compris là où la connexion est instable.', 'en' => 'Offline-ready progressive web apps, REST APIs for your mobile apps and integrations with your existing tools: I build applications designed for real-world use, including where connectivity is unreliable.'],
                ['fr' => ['Applications web progressives (PWA)', 'Fonctionnement hors ligne', 'API REST documentées', 'Intégrations tierces'], 'en' => ['Progressive web apps (PWA)', 'Offline support', 'Documented REST APIs', 'Third-party integrations']],
            ],
            [
                'bases-de-donnees', 'fas fa-database',
                ['fr' => 'Bases de données', 'en' => 'Database development'],
                ['fr' => 'Modélisation, optimisation et migration de vos données.', 'en' => 'Modelling, optimisation and migration of your data.'],
                ['fr' => 'Une application ne vaut que par la qualité de ses données. Je modélise vos bases MySQL ou PostgreSQL, optimise les requêtes lentes, sécurise les accès et migre vos données existantes (Excel, anciens logiciels) sans perte.', 'en' => 'An application is only as good as its data. I model your MySQL or PostgreSQL databases, optimise slow queries, secure access and migrate your existing data (spreadsheets, legacy software) without loss.'],
                ['fr' => ['Modélisation MySQL / PostgreSQL', 'Optimisation des performances', 'Migration de données', 'Sauvegardes et sécurité'], 'en' => ['MySQL / PostgreSQL modelling', 'Performance tuning', 'Data migration', 'Backups and security']],
            ],
            [
                'conseil-securite', 'fas fa-shield-halved',
                ['fr' => 'Conseil & sécurité informatique', 'en' => 'IT consulting & security'],
                ['fr' => 'Audit, bonnes pratiques et accompagnement technique.', 'en' => 'Audits, best practices and technical guidance.'],
                ['fr' => "Avant de développer, il faut faire les bons choix. Je vous aide à définir votre architecture, à auditer la sécurité d'une application existante (OWASP, gestion des accès, données sensibles) et à mettre en place des processus de déploiement fiables.", 'en' => 'Before building, you need to make the right choices. I help you define your architecture, audit the security of an existing application (OWASP, access control, sensitive data) and set up reliable deployment processes.'],
                ['fr' => ['Audit de sécurité applicative', 'Choix d’architecture', 'Revue de code', 'Déploiement et hébergement'], 'en' => ['Application security audit', 'Architecture decisions', 'Code review', 'Deployment and hosting']],
            ],
            [
                'formation', 'fas fa-chalkboard-user',
                ['fr' => 'Formation', 'en' => 'Training'],
                ['fr' => 'Formations Laravel et développement web pour équipes et apprenants.', 'en' => 'Laravel and web development training for teams and learners.'],
                ['fr' => "Fondateur d'AfriCode, je forme les développeurs de demain : formations pratiques en développement web, Laravel et sécurité, pour les débutants comme pour les équipes en entreprise. Programmes adaptés à votre niveau, en présentiel ou à distance.", 'en' => "As founder of AfriCode, I train tomorrow's developers: hands-on courses in web development, Laravel and security for beginners and in-house teams alike. Programmes tailored to your level, on-site or remote."],
                ['fr' => ['Initiation au développement web', 'Laravel de A à Z', 'Sécurité des applications', 'Programmes sur mesure pour les équipes'], 'en' => ['Introduction to web development', 'Laravel from A to Z', 'Application security', 'Custom programmes for teams']],
            ],
        ];

        foreach ($services as $order => [$slug, $icon, $title, $summary, $description, $features]) {
            Service::firstOrCreate(['slug' => $slug], [
                'icon' => $icon,
                'title' => $title,
                'summary' => $summary,
                'description' => $description,
                'features' => $features,
                'price_label' => ['fr' => 'Sur devis', 'en' => 'Custom quote'],
                'seo_description' => $summary,
                'sort_order' => $order,
            ]);
        }
    }

    private function seedTestimonials(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }

        Testimonial::create([
            'author_name' => 'Kuessi Bruno DAVI',
            'author_title' => ['fr' => 'Assistant comptable à APAVE BENIN', 'en' => 'Accounting Assistant at APAVE BENIN'],
            'quote' => [
                'fr' => "J'ai le plaisir de recommander ce professionnel pour ses compétences en développement web avec Laravel et en sécurité des applications web. Son expertise dans la conception et le développement d'applications web, associée à sa connaissance des bonnes pratiques de sécurité, constitue un atout pour la réalisation de solutions numériques fiables et performantes.",
                'en' => 'I am pleased to recommend this professional for his skills in Laravel web development and web application security. His expertise in designing and building web applications, combined with his knowledge of security best practices, is a real asset for delivering reliable, high-performing digital solutions.',
            ],
            'source' => 'linkedin',
            'given_at' => '2026-10-12',
            'sort_order' => 0,
        ]);

        Testimonial::create([
            'author_name' => 'Fortuné Toby ABATTAN',
            'author_title' => ['fr' => 'Développeur WordPress · Marketing digital', 'en' => 'WordPress Developer · Digital Marketing'],
            'quote' => ['fr' => 'Très bon professionnellement, je vous le recommande !', 'en' => 'Very good professionally, I recommend him!'],
            'source' => 'linkedin',
            'given_at' => '2026-10-12',
            'sort_order' => 1,
        ]);
    }
}
