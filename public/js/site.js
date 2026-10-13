// Site public — interactions légères, sans dépendance.
(function () {
    document.documentElement.classList.remove('no-js');

    // En-tête : fond opaque après défilement.
    var header = document.querySelector('.site-header');
    if (header && !header.classList.contains('is-solid')) {
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 24); };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Menu mobile.
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('site-nav');
    if (toggle && nav) {
        var setOpen = function (open) {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.innerHTML = open ? '<i class="fas fa-xmark" aria-hidden="true"></i>' : '<i class="fas fa-bars" aria-hidden="true"></i>';
        };
        toggle.addEventListener('click', function () { setOpen(!nav.classList.contains('is-open')); });
        nav.addEventListener('click', function (e) { if (e.target.closest('a')) setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    }

    // Apparition des blocs et remplissage des barres de compétences.
    var revealables = document.querySelectorAll('.reveal, .skills-grid');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealables.forEach(function (el) { observer.observe(el); });
    } else {
        revealables.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // Lien actif dans la navigation selon la section visible.
    var links = document.querySelectorAll('.nav a[href^="#"], .nav a[href*="/#"]');
    var sections = Array.prototype.map.call(links, function (link) {
        return document.getElementById(link.getAttribute('href').split('#')[1]);
    });
    if (sections.some(Boolean) && 'IntersectionObserver' in window) {
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                links.forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('href').split('#')[1] === entry.target.id);
                });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        sections.forEach(function (section) { if (section) spy.observe(section); });
    }

    // Formulaire de contact : champ budget visible uniquement pour un devis.
    var form = document.querySelector('[data-contact-form]');
    if (form) {
        var budget = form.querySelector('[data-budget-field]');
        var sync = function () {
            var checked = form.querySelector('input[name="type"]:checked');
            if (budget) budget.hidden = !checked || checked.value !== 'quote';
        };
        form.addEventListener('change', function (e) { if (e.target.name === 'type') sync(); });
        sync();

        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Envoi en cours…';
            }
        });
    }

    var year = document.querySelector('[data-year]');
    if (year) year.textContent = new Date().getFullYear();
})();
