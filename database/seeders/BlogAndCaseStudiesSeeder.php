<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Premiers articles du blog et études de cas des projets phares.
 * Idempotent : ne remplace jamais un contenu déjà présent.
 * Les chiffres clés des études de cas sont volontairement laissés vides :
 * ils doivent être renseignés avec des données réelles dans l'administration.
 */
class BlogAndCaseStudiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPosts();
        $this->seedCaseStudies();
    }

    private function seedPosts(): void
    {
        $posts = [
            [
                'slug' => 'securiser-application-laravel-10-reflexes',
                'tags' => ['Laravel', 'Sécurité'],
                'published_at' => '2026-10-06 09:00:00',
                'title' => [
                    'fr' => 'Sécuriser une application Laravel : 10 réflexes essentiels',
                    'en' => 'Securing a Laravel application: 10 essential habits',
                ],
                'excerpt' => [
                    'fr' => 'Laravel protège déjà beaucoup de choses par défaut. Les failles viennent presque toujours de ce que l’on ajoute soi-même. Voici les réflexes que j’applique sur chaque projet.',
                    'en' => 'Laravel already protects a lot by default. Vulnerabilities almost always come from what we add ourselves. Here are the habits I apply on every project.',
                ],
                'body' => [
                    'fr' => <<<'MD'
Laravel est l'un des frameworks PHP les plus sûrs : protection CSRF, requêtes préparées, échappement automatique dans Blade, chiffrement des cookies. Pourtant, la plupart des failles que je rencontre en audit viennent du code applicatif. Voici dix réflexes simples qui font la différence.

## 1. Valider toutes les entrées côté serveur

Une validation en JavaScript améliore l'expérience, mais elle ne protège rien. Chaque donnée qui arrive au serveur doit passer par une règle de validation, idéalement dans une **Form Request**.

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email:rfc', 'max:190'],
        'website' => ['nullable', 'url:https'],
    ];
}
```

## 2. Ne jamais faire confiance à l'assignation de masse

Déclarez explicitement `$fillable` et gardez hors de portée les champs sensibles comme `role` ou `is_admin`. Un champ ajouté au formulaire par un attaquant ne doit rien pouvoir modifier.

## 3. Autoriser chaque action, pas seulement chaque page

Masquer un bouton dans la vue ne sécurise rien. Utilisez les **Policies** et appelez `authorize()` dans chaque contrôleur ou composant Livewire, y compris pour les actions groupées.

## 4. Limiter les tentatives

Connexion, mot de passe oublié, formulaire de contact : tout ce qui peut être automatisé doit être limité avec `RateLimiter`.

## 5. Échapper par défaut

Dans Blade, utilisez `{{ }}`. Réservez `{!! !!}` aux contenus que vous avez vous-même nettoyés.

## 6. Vérifier le vrai type des fichiers téléversés

Les règles `image` et `mimes` analysent le contenu réel du fichier, pas seulement son extension. Stockez les fichiers hors du dossier public lorsqu'ils sont privés.

## 7. Garder les secrets hors du code

Les clés d'API vont dans `.env`, jamais dans Git. Et en production : `APP_DEBUG=false`, sans exception.

## 8. Forcer HTTPS et des cookies sécurisés

Activez `SESSION_SECURE_COOKIE` et configurez correctement les proxies de confiance derrière un CDN.

## 9. Journaliser les actions sensibles

Connexions, suppressions, changements de rôle : un journal d'activité permet de comprendre ce qui s'est passé en cas d'incident.

## 10. Tester les autorisations

Un test qui vérifie qu'un utilisateur sans droit reçoit bien une erreur 403 vaut mieux qu'une longue relecture.

> La sécurité n'est pas une fonctionnalité que l'on ajoute à la fin : c'est une habitude que l'on prend à chaque ligne.

Vous voulez un regard extérieur sur votre application ? Je réalise des audits de sécurité Laravel avec un rapport priorisé et des correctifs concrets.
MD,
                    'en' => <<<'MD'
Laravel is one of the most secure PHP frameworks: CSRF protection, prepared statements, automatic escaping in Blade, encrypted cookies. Yet most of the vulnerabilities I find during audits come from application code. Here are ten simple habits that make the difference.

## 1. Validate every input on the server

JavaScript validation improves the experience, but it protects nothing. Every piece of data reaching the server must go through a validation rule, ideally in a **Form Request**.

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email:rfc', 'max:190'],
        'website' => ['nullable', 'url:https'],
    ];
}
```

## 2. Never trust mass assignment

Declare `$fillable` explicitly and keep sensitive fields such as `role` or `is_admin` out of reach. A field added to the form by an attacker must not be able to change anything.

## 3. Authorise every action, not just every page

Hiding a button in the view secures nothing. Use **Policies** and call `authorize()` in every controller or Livewire component, including bulk actions.

## 4. Rate-limit attempts

Login, password reset, contact form: anything that can be automated must be limited with `RateLimiter`.

## 5. Escape by default

In Blade, use `{{ }}`. Keep `{!! !!}` for content you have sanitised yourself.

## 6. Check the real type of uploaded files

The `image` and `mimes` rules inspect the actual file content, not just its extension. Store files outside the public folder when they are private.

## 7. Keep secrets out of the code

API keys belong in `.env`, never in Git. And in production: `APP_DEBUG=false`, no exceptions.

## 8. Enforce HTTPS and secure cookies

Enable `SESSION_SECURE_COOKIE` and configure trusted proxies correctly behind a CDN.

## 9. Log sensitive actions

Logins, deletions, role changes: an activity log helps you understand what happened during an incident.

## 10. Test your authorisation rules

A test checking that an unauthorised user really gets a 403 error is worth more than a long code review.

> Security is not a feature you add at the end: it is a habit you build into every line.

Want an outside look at your application? I run Laravel security audits with a prioritised report and concrete fixes.
MD,
                ],
            ],
            [
                'slug' => 'saas-multi-tenant-laravel-base-partagee-ou-dediee',
                'tags' => ['SaaS', 'Laravel', 'Architecture'],
                'published_at' => '2026-10-10 09:00:00',
                'title' => [
                    'fr' => 'SaaS multi-tenant avec Laravel : base partagée ou base par client ?',
                    'en' => 'Multi-tenant SaaS with Laravel: shared database or one database per client?',
                ],
                'excerpt' => [
                    'fr' => 'C’est la première décision d’architecture d’un SaaS, et la plus difficile à changer ensuite. Comparaison des deux approches et critères pour choisir.',
                    'en' => 'It is the first architectural decision of any SaaS, and the hardest to change later. A comparison of both approaches and how to choose.',
                ],
                'body' => [
                    'fr' => <<<'MD'
Un SaaS multi-tenant sert plusieurs clients (les « tenants ») avec une seule application. La question qui se pose dès le départ : où ranger les données de chacun ?

## Option 1 : une base partagée

Toutes les tables contiennent une colonne `tenant_id`, et chaque requête est filtrée sur le client courant. Avec Laravel, un **global scope** appliqué automatiquement évite les oublis.

```php
protected static function booted(): void
{
    static::addGlobalScope('tenant', function (Builder $query) {
        $query->where('tenant_id', tenant()->id);
    });
}
```

### Avantages
- Une seule base à sauvegarder, migrer et surveiller.
- Coût d'hébergement faible, idéal pour démarrer.
- Statistiques globales faciles à produire.

### Points de vigilance
- Un oubli de filtre peut exposer les données d'un autre client : les tests sont indispensables.
- Un client très volumineux peut ralentir les autres.

## Option 2 : une base par client

Chaque client possède sa propre base. L'application choisit la connexion au début de chaque requête, souvent à partir du sous-domaine.

### Avantages
- Isolation forte des données, appréciée des clients exigeants (santé, finance, institutions).
- Sauvegarde et restauration client par client.
- Possibilité d'héberger un client dans une région précise.

### Points de vigilance
- Les migrations doivent être exécutées sur toutes les bases.
- Plus de bases à surveiller, donc une exploitation plus lourde.

## Comment choisir ?

- **Vous démarrez, vos clients sont des PME** : base partagée, avec des tests d'isolation solides.
- **Vos clients exigent une séparation contractuelle des données** : base par client.
- **Entre les deux** : commencez partagé, et prévoyez dès le départ une couche d'accès aux données qui permettra de migrer les gros clients vers une base dédiée.

## Le point commun : l'authentification

Quel que soit le choix, un SaaS sérieux a besoin d'une authentification unique (SSO) et d'une gestion fine des rôles (RBAC), propres à chaque client. C'est souvent là que se joue la sécurité réelle de la plateforme.

> Le bon choix n'est pas le plus élégant techniquement : c'est celui qui correspond à vos clients et à votre capacité d'exploitation.

Vous concevez un SaaS ? Je peux vous aider à poser une architecture saine dès le premier jour.
MD,
                    'en' => <<<'MD'
A multi-tenant SaaS serves several customers (the "tenants") with a single application. The question comes up on day one: where should each customer's data live?

## Option 1: a shared database

Every table has a `tenant_id` column and every query is filtered on the current customer. With Laravel, a **global scope** applied automatically prevents oversights.

```php
protected static function booted(): void
{
    static::addGlobalScope('tenant', function (Builder $query) {
        $query->where('tenant_id', tenant()->id);
    });
}
```

### Pros
- A single database to back up, migrate and monitor.
- Low hosting cost, ideal to get started.
- Global statistics are easy to produce.

### Watch out for
- A missing filter can expose another customer's data: tests are essential.
- A very large customer can slow down the others.

## Option 2: one database per customer

Each customer gets its own database. The application picks the connection at the start of each request, often from the subdomain.

### Pros
- Strong data isolation, valued by demanding customers (health, finance, institutions).
- Backup and restore customer by customer.
- Ability to host a customer in a specific region.

### Watch out for
- Migrations must run on every database.
- More databases to monitor, so heavier operations.

## How to choose?

- **You are starting out and your customers are SMEs**: shared database, with solid isolation tests.
- **Your customers require contractual data separation**: one database per customer.
- **In between**: start shared, and design a data-access layer from day one that lets you move large customers to a dedicated database.

## The common ground: authentication

Whatever you choose, a serious SaaS needs single sign-on (SSO) and fine-grained role management (RBAC) per customer. That is often where the real security of the platform is decided.

> The right choice is not the most technically elegant one: it is the one that fits your customers and your ability to operate it.

Designing a SaaS? I can help you lay down a sound architecture from day one.
MD,
                ],
            ],
        ];

        foreach ($posts as $data) {
            if (Post::withTrashed()->where('slug', $data['slug'])->exists()) {
                continue;
            }

            $post = new Post([
                'slug' => $data['slug'],
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'tags' => $data['tags'],
                'seo_description' => $data['excerpt'],
                'published_at' => $data['published_at'],
            ]);
            $post->status = PublicationStatus::Published;
            $post->save();
        }
    }

    private function seedCaseStudies(): void
    {
        $studies = [
            'stagesbenin' => [
                'context' => [
                    'fr' => "Au Bénin, les offres de stage et d'emploi circulent surtout par le bouche-à-oreille et les réseaux sociaux. Les jeunes diplômés peinent à les trouver, et les entreprises à toucher les bons profils.",
                    'en' => 'In Benin, internship and job offers mostly circulate through word of mouth and social media. Young graduates struggle to find them, and companies struggle to reach the right profiles.',
                ],
                'challenge' => [
                    'fr' => 'Créer une plateforme unique où les candidats présentent leur profil et postulent en quelques clics, et où les entreprises publient et gèrent leurs offres, avec des services payants accessibles par paiement mobile.',
                    'en' => 'Build a single platform where candidates present their profile and apply in a few clicks, and where companies publish and manage their offers, with paid services available through mobile payment.',
                ],
                'solution' => [
                    'fr' => "- Espaces séparés pour les candidats et les entreprises\n- Publication, recherche et filtrage des offres\n- Suivi des candidatures\n- Paiement en ligne intégré avec l'API FedaPay (mobile money et carte)\n- Interface responsive pensée pour une consultation sur mobile",
                    'en' => "- Separate areas for candidates and companies\n- Publishing, searching and filtering offers\n- Application tracking\n- Online payment integrated with the FedaPay API (mobile money and card)\n- Responsive interface designed for mobile browsing",
                ],
            ],
            'africode' => [
                'context' => [
                    'fr' => "AfriCode est né d'un constat : beaucoup de jeunes Africains veulent apprendre le numérique, mais les ressources existantes sont peu adaptées à leur réalité (langue, coût, connexion).",
                    'en' => 'AfriCode started from a simple observation: many young Africans want to learn digital skills, but existing resources are poorly suited to their reality (language, cost, connectivity).',
                ],
                'challenge' => [
                    'fr' => "Proposer un parcours d'apprentissage accessible, en français, qui accompagne l'apprenant du premier pas jusqu'à ses premiers projets, et qui fédère une communauté.",
                    'en' => 'Offer an accessible learning path in French that guides learners from their first steps to their first projects, and brings a community together.',
                ],
                'solution' => [
                    'fr' => "- Plateforme éducative développée entièrement avec Laravel\n- Contenus organisés en parcours progressifs\n- Espace communautaire et accompagnement\n- Conception légère, pensée pour les connexions limitées",
                    'en' => "- Educational platform built entirely with Laravel\n- Content organised into progressive learning paths\n- Community space and mentoring\n- Lightweight design for limited connectivity",
                ],
            ],
            'cvlink' => [
                'context' => [
                    'fr' => 'Un bon CV ouvre des portes, mais beaucoup de candidats ne disposent ni des outils ni des modèles pour en produire un de qualité professionnelle.',
                    'en' => 'A good resume opens doors, but many candidates have neither the tools nor the templates to produce a professional-quality one.',
                ],
                'challenge' => [
                    'fr' => 'Permettre à chacun de créer un CV soigné en ligne, de le mettre à jour facilement et de le partager par un simple lien.',
                    'en' => 'Let anyone create a polished resume online, update it easily and share it with a simple link.',
                ],
                'solution' => [
                    'fr' => "- Éditeur de CV en ligne guidé, section par section\n- Modèles de mise en page professionnels\n- Lien de partage public pour chaque CV\n- Intégration avec l'écosystème StagesBENIN",
                    'en' => "- Guided online resume editor, section by section\n- Professional layout templates\n- Public share link for every resume\n- Integration with the StagesBENIN ecosystem",
                ],
            ],
        ];

        foreach ($studies as $slug => $sections) {
            $project = Project::where('slug', $slug)->first();

            if (! $project || $project->hasCaseStudy()) {
                continue;
            }

            $project->update($sections);
        }
    }
}
