<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portfolio de Mahoulolo SEBIO - Développeur Full Stack et Ingénieur d'exploitation informatique">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $data['personal']['name'] }} - Portfolio</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/portfolio.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">{{ $data['personal']['name'] }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#skills">Compétences</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Projets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#social">Réseaux</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">{{ $data['personal']['name'] }}</h1>
                        <h2 class="hero-subtitle">{{ $data['personal']['title'] }}</h2>
                        <p class="hero-description">{{ $data['personal']['description'] }}</p>
                        
                        <div class="hero-buttons">
                            <a href="#contact" class="btn btn-primary-custom me-3">Me contacter</a>
                            <a href="#projects" class="btn btn-outline-custom">Voir mes projets</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div id="three-container" style="height: 500px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">À propos de moi</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content" data-aos="fade-right" data-aos-delay="200">
                        <h3 class="mb-4">Qui suis-je ?</h3>
                        <p class="mb-4">{{ $data['about']['description'] }}</p>
                        <p class="hero-description">{{ $data['personal']['objective'] }}</p>
                        {{-- <p class="mb-4"><strong>Poste actuel :</strong> {{ $data['about']['current_job'] }}</p>
                         --}}
                        <h3 class="mb-4 mt-5">Mon projet phare</h3>
                        <p class="mb-4">{{ $data['about']['project'] }}</p>
                       
                        <a href="www.africode.tech" class="btn btn-primary-custom btn-sm me-2" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>Visiter AfriCode
                        </a>
                     
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="cube-container" data-aos="fade-left" data-aos-delay="400">
                        <div class="cube-controls mb-3">
                            <p class="text-center text-muted">
                                <i class="fas fa-keyboard me-2"></i>
                                Utilisez les flèches du clavier pour faire tourner le cube
                            </p>
                        </div>
                        <div class="cube-wrapper">
                            <div class="cube" id="aboutCube">
                                <div class="cube-face front">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/3.jpg') }}" alt="Photo 1" class="cube-image">
                                        <h4>Développeur</h4>
                                    </div>
                                </div>
                                <div class="cube-face back">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/2.jpg') }}" alt="Photo 2" class="cube-image">
                                        <h4>Ingénieur</h4>
                                    </div>
                                </div>
                                <div class="cube-face right">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/1.png') }}" alt="Photo 3" class="cube-image">
                                        <h4>Innovateur</h4>
                                    </div>
                                </div>
                                <div class="cube-face left">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/lines-code.jpg') }}" alt="Photo 4" class="cube-image">
                                        <h4>Créatif</h4>
                                    </div>
                                </div>
                                <div class="cube-face top">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/5.jpg') }}" alt="Photo 5" class="cube-image">
                                        <h4>Passionné</h4>
                                    </div>
                                </div>
                                <div class="cube-face bottom">
                                    <div class="face-content">
                                        <img src="{{ asset('images/about/6.jpg') }}" alt="Photo 6" class="cube-image">
                                        <h4>Déterminé</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="section" style="background: #f8fafc;">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Compétences techniques</h2>
            <div class="row">
                <div class="col-lg-4">
                    <h3 class="text-center mb-4" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-laptop-code me-2"></i>Frontend
                    </h3>
                    @foreach($data['skills']['frontend'] as $skill)
                    <div class="skill-item" data-aos="fade-right" data-aos-delay="{{ $loop->index * 100 + 200 }}">
                        <div class="skill-header">
                            <i class="fas fa-code skill-icon"></i>
                            <h5>{{ $skill['name'] }}</h5>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="{{ $skill['level'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-lg-4">
                    <h3 class="text-center mb-4" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-server me-2"></i>Backend
                    </h3>
                    @foreach($data['skills']['backend'] as $skill)
                    <div class="skill-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 + 200 }}">
                        <div class="skill-header">
                            <i class="fas fa-database skill-icon"></i>
                            <h5>{{ $skill['name'] }}</h5>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="{{ $skill['level'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-lg-4">
                    <h3 class="text-center mb-4" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-tools me-2"></i>Outils
                    </h3>
                    @foreach($data['skills']['tools'] as $skill)
                    <div class="skill-item" data-aos="fade-left" data-aos-delay="{{ $loop->index * 100 + 200 }}">
                        <div class="skill-header">
                            <i class="fas fa-wrench skill-icon"></i>
                            <h5>{{ $skill['name'] }}</h5>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="{{ $skill['level'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Mes projets</h2>
            <div class="row">
                @foreach($data['projects'] as $index => $project)
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="project-card">
                        <div class="project-image">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="project-content">
                            <h3 class="project-title">{{ $project['name'] }}</h3>
                            <p class="project-description">{{ $project['description'] }}</p>
                            <div class="tech-tags">
                                @foreach($project['technologies'] as $tech)
                                <span class="tech-tag">{{ $tech }}</span>
                                @endforeach
                            </div>
                            <div class="project-buttons mt-3">
                                <a href="{{ $project['demo_url'] ?? '#' }}" class="btn btn-primary-custom btn-sm me-2" target="_blank">
                                    <i class="fas fa-external-link-alt me-1"></i>Visiter
                                </a>
                                
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Social Links Section -->
    <section id="social" class="section" style="background: #f8fafc;">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Mes réseaux sociaux</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="social-links" data-aos="fade-up" data-aos-delay="200">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-4">
                                <a href="{{ $data['social']['github'] ?? '#' }}" class="social-link" target="_blank" data-aos="zoom-in" data-aos-delay="100">
                                    <div class="social-icon github">
                                        <i class="fab fa-github"></i>
                                    </div>
                                    <h5>GitHub</h5>
                                    <p>Mes dépôts</p>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <a href="{{ $data['social']['linkedin'] ?? '#' }}" class="social-link" target="_blank" data-aos="zoom-in" data-aos-delay="200">
                                    <div class="social-icon linkedin">
                                        <i class="fab fa-linkedin"></i>
                                    </div>
                                    <h5>LinkedIn</h5>
                                    <p>Mon profil pro</p>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <a href="{{ $data['social']['facebook'] ?? '#' }}" class="social-link" target="_blank" data-aos="zoom-in" data-aos-delay="300">
                                    <div class="social-icon facebook">
                                        <i class="fab fa-facebook"></i>
                                    </div>
                                    <h5>Facebook</h5>
                                    <p>Suivez-moi</p>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-4">
                                <a href="https://wa.me/{{ $data['social']['whatsapp'] ?? '' }}" class="social-link" target="_blank" data-aos="zoom-in" data-aos-delay="400">
                                    <div class="social-icon whatsapp">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <h5>WhatsApp</h5>
                                    <p>Contactez-moi</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Contactez-moi</h2>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="contact-content" data-aos="fade-up" data-aos-delay="200">
                        <div class="row">
                            <!-- Contact Methods -->
                            <div class="col-lg-6">
                                <h3 class="mb-4 text-center">Me contacter</h3>
                                <div class="contact-methods">
                                    <a href="https://wa.me/+22965603040" class="contact-method whatsapp" target="_blank" data-aos="zoom-in" data-aos-delay="300">
                                        <div class="contact-icon">
                                            <i class="fab fa-whatsapp"></i>
                                        </div>
                                        <div class="contact-info">
                                            <h4>WhatsApp</h4>
                                            <p>{{ $data['personal']['phone'] ?? 'Non disponible' }}</p>
                                            <span class="contact-action">Cliquez pour discuter</span>
                                        </div>
                                    </a>
                                    
                                    <a href="mailto:{{ $data['personal']['email'] ?? '' }}" class="contact-method email" target="_blank" data-aos="zoom-in" data-aos-delay="400">
                                        <div class="contact-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="contact-info">
                                            <h4>Email</h4>
                                            <p>{{ $data['personal']['email'] ?? 'Non disponible' }}</p>
                                            <span class="contact-action">Cliquez pour envoyer un email</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- CV Download -->
                            <div class="col-lg-6">
                                <h3 class="mb-4 text-center">Mon CV</h3>
                                <div class="cv-download" data-aos="zoom-in" data-aos-delay="500">
                                    <div class="cv-preview">
                                        <i class="fas fa-file-pdf"></i>
                                        <h4>CV - {{ $data['personal']['name'] ?? 'Portfolio' }}</h4>
                                        <p>Téléchargez mon CV pour en savoir plus sur mon parcours professionnel</p>
                                    </div>
                                    <a href="{{ asset('cv/CV Resume SEBIO MAHOULOLO 2025.pdf') }}" class="btn btn-primary-custom btn-download" download>
                                        <i class="fas fa-download me-2"></i>
                                        Télécharger le CV
                                    </a>
                                    <div class="cv-info">
                                        <small class="text-muted">
                                            <i class="fas fa-file-pdf me-1"></i>
                                            Format PDF • Dernière mise à jour : {{ date('d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Contact Info -->
                       
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4" style="background: var(--dark-color); color: white;">
        <div class="container text-center">
            <p>&copy; 2024 {{ $data['personal']['name'] }}. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="{{ asset('js/portfolio.js') }}"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>