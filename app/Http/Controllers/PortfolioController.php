<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PortfolioController extends Controller
{
    public function index()
    {
        $data = [
            'personal' => [
                'name' => 'Mahoulolo SEBIO',
                'title' => 'Développeur Full Stack',
                'subtitle' => 'Ingénieur d\'exploitation informatique',
                'email' => 'sebiomahoulolo4@gmail.com',
                'phone' => '+229 01 65 60 30 40',
                'location' => 'Godomey-Cotonou, Bénin',
                'description' => 'Développeur passionné avec plus de 2 ans d’expérience dans la conception et la réalisation de plateformes web modernes et performantes. Formateur indépendant, je partage mon savoir-faire avec les apprenants et les professionnels souhaitant renforcer leurs compétences numériques. En tant que promoteur d’AfriCode, j’œuvre à rendre l’apprentissage du numérique accessible aux jeunes Africains et à stimuler l’innovation technologique sur le continent.',
                'objective' => 'Ma mission est de concevoir des solutions numériques innovantes qui répondent aux besoins des entreprises tout en ayant un impact positif sur la société.'
            ],
            'about' => [
                'current_job' => 'Ingénieur d\'exploitation informatique chez FINANCIAL HOSANNA CONSULTING GROUPE SARL',
                'location' => 'Abomey-Calavi, Bénin',
                'description' => 'Je travaille sur divers projets de développement web, allant de plateformes éducatives à des solutions digitales destinées aux entreprises. Passionné par l’apprentissage continu, j’aime partager mes connaissances et contribuer activement à l’écosystème numérique africain.

',
                'project' => 'AfriCode - une initiative qui vise à offrir aux jeunes Africains un accès simple à l\'apprentissage du numérique, via une communauté, un site web éducatif et une application dédiée.'
            ],
            'experience' => [
                [
                    'title' => 'Ingénieur d\'exploitation informatique',
                    'company' => 'FINANCIAL HOSANNA CONSULTING GROUPE SARL',
                    'location' => 'Abomey-Calavi, Bénin',
                    'period' => 'Septembre 2024 - en cours',
                    'description' => 'Gestion des infrastructures IT et développement de solutions numériques internes et externes.'
                ],
                [
                    'title' => 'Développeur web freelance',
                    'company' => 'Freelance',
                    'location' => 'Bénin',
                    'period' => '2021 - en cours',
                    'description' => 'Conception et réalisation de sites et plateformes web pour écoles, entreprises et startups.'
                ]
            ],
            'projects' => [
                [
                    'name' => 'StagesBENIN',
                    'description' => 'Plateforme qui connecte candidats et entreprises pour stages et emplois.',
                    'technologies' => ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL', 'FedaPay API'],
                    'image' => 'stagesbenin.jpg',
                    'demo_url' => 'https://stagesbenin.com/',
                    'github_url' => 'https://github.com/mahoulolo/stagesbenin'
                ],
                [
                    'name' => 'Africa-Location',
                    'description' => 'Site immobilier développé avec Laravel et Bootstrap.',
                    'technologies' => ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'],
                    'image' => 'africa-location.jpg',
                    'demo_url' => 'https://africa-location.com/',
                    'github_url' => 'https://github.com/mahoulolo/africa-location'
                ],
                [
                    'name' => 'CVLink',
                    'description' => 'Plateforme de création et de partage de CV professionnels en ligne.',
                    'technologies' => ['Laravel', 'Bootstrap', 'JavaScript', 'HTML', 'CSS'],
                    'image' => 'cvlink.jpg',
                    'demo_url' => 'https://cvlink.stagesbenin.com/',
                    'github_url' => 'https://github.com/mahoulolo/cvlink'
                ],
                [
                    'name' => 'BBLove',
                    'description' => 'Plateforme en ligne pour les rencontres avec système de billetterie.',
                    'technologies' => ['Laravel', 'FedaPay API', 'Bootstrap', 'JavaScript', 'MySQL'],
                    'image' => 'bblove.jpg',
                    'demo_url' => 'https://app.bb-love.com/',
                    'github_url' => 'https://github.com/mahoulolo/bblove'
                ],
                [
                    'name' => 'AfriCode',
                    'description' => 'Plateforme éducative 100% Laravel dédiée à l\'apprentissage du numérique en Afrique.',
                    'technologies' => ['Laravel', 'Bootstrap', 'JavaScript', 'MySQL'],
                    'image' => 'africode.jpg',
                    'demo_url' => 'https://africode.tech/',
                    'github_url' => 'https://github.com/mahoulolo/africode'
                ],
                [
                    'name' => 'École des Arcanes Majeurs',
                    'description' => 'Site vitrine interactif pour services spirituels et éducatifs.',
                    'technologies' => ['Bootstrap', 'PHP', 'JavaScript', 'HTML', 'CSS'],
                    'image' => 'ecole-arcanes.jpg',
                    'demo_url' => 'https://ecoledesarcanesmajeurs.org/',
                    'github_url' => 'https://github.com/mahoulolo/ecole-arcanes'
                ]
            ],
            'skills' => [
                'frontend' => [
                    ['name' => 'HTML5', 'level' => 95],
                    ['name' => 'CSS3', 'level' => 90],
                    ['name' => 'JavaScript', 'level' => 85],
                    ['name' => 'React.js', 'level' => 80],
                    ['name' => 'Angular', 'level' => 75],
                    ['name' => 'Bootstrap', 'level' => 90]
                ],
                'backend' => [
                    ['name' => 'Laravel', 'level' => 90],
                    ['name' => 'PHP', 'level' => 85],
                    ['name' => 'MySQL', 'level' => 80],
                    ['name' => 'API REST', 'level' => 85]
                ],
                'tools' => [
                    ['name' => 'Git/GitHub', 'level' => 85],
                    ['name' => 'NPM', 'level' => 80],
                    ['name' => 'Composer', 'level' => 85],
                    ['name' => 'FedaPay API', 'level' => 75]
                ]
            ],
            'education' => [
                [
                    'degree' => 'Licence professionnelle',
                    'field' => 'Système informatique et logiciels',
                    'institution' => 'INSTITUT UNIVERSITAIRE LES COURS SONOU',
                    'location' => 'Parakou, Bénin',
                    'period' => '2021 - 2024'
                ],
                [
                    'degree' => 'Baccalauréat',
                    'field' => 'Série D',
                    'institution' => 'CEG 1 PARTAGO',
                    'location' => 'Djougou, Bénin',
                    'period' => '2018 - 2021'
                ]
            ],
            'certifications' => [
                [
                    'name' => 'Certificat en Développement Front-End',
                    'issuer' => 'FORCE-N SÉNÉGAL',
                    'period' => 'Mai - Juillet 2024'
                ],
                [
                    'name' => 'Certificat d\'achèvement du cours sur le cryptage',
                    'issuer' => 'Internet Society',
                    'period' => 'Février 2024'
                ],
                [
                    'name' => 'Certificat en gestion de projet',
                    'issuer' => 'Disasterreadyorg',
                    'period' => 'Octobre 2023'
                ],
                [
                    'name' => 'Certificat de fin de cours en HTML ET CSS',
                    'issuer' => 'Cursa',
                    'period' => 'Septembre 2023'
                ]
            ],
            'languages' => [
                ['name' => 'Français', 'level' => 'Natif'],
                ['name' => 'Anglais', 'level' => 'Intermédiaire'],
                ['name' => 'Adja', 'level' => 'Natif'],
                ['name' => 'Baatonou', 'level' => 'Natif']
            ],
            'interests' => ['Sport', 'Musique', 'Cinéma', 'Développement web', 'Innovation numérique'],
            'social' => [
                'github' => 'https://github.com/sebiomahoulolo',
                'linkedin' => 'https://www.linkedin.com/in/mahoulolo-sebio-9046a6261',
                'facebook' => 'https://facebook.com/sebio.mahoulolo',
                'whatsapp' => '2290165603040'
            ]
        ];

        return view('portfolio.index', compact('data'));
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000'
        ]);

        // Ici vous pouvez ajouter l'envoi d'email
        // Mail::to('sebiomahoulolo4@gmail.com')->send(new ContactMail($request->all()));

        return response()->json([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès !'
        ]);
    }
}
