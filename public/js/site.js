// Site public — interactions et animations légères, sans dépendance.
(function () {
    var root = document.documentElement;
    root.classList.remove('no-js');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    // En-tête : ombre renforcée après défilement.
    var header = document.querySelector('.site-header');
    if (header) {
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 16); };
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

    // Compteurs animés (années d'expérience, projets...).
    var animateCounter = function (el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        if (reduceMotion) { el.textContent = target; return; }
        var start = null, duration = 1400;
        var step = function (ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    // Apparition des blocs, barres de compétences et compteurs.
    var revealables = document.querySelectorAll('.reveal, [data-count]');
    var show = function (el) {
        el.classList.add('is-visible');
        if (el.hasAttribute('data-count')) animateCounter(el);
    };
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { show(entry.target); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });
        revealables.forEach(function (el) { observer.observe(el); });
    } else {
        revealables.forEach(show);
    }

    // Texte qui s'écrit tout seul.
    var typed = document.querySelector('[data-typed]');
    if (typed) {
        var phrases = JSON.parse(typed.getAttribute('data-typed') || '[]').filter(Boolean);
        var output = typed.querySelector('.typed-text');
        if (phrases.length && output) {
            if (reduceMotion) {
                output.textContent = phrases[0];
            } else {
                var p = 0, i = 0, deleting = false;
                var tick = function () {
                    var phrase = phrases[p];
                    i += deleting ? -1 : 1;
                    output.textContent = phrase.slice(0, i);
                    var delay = deleting ? 35 : 65;
                    if (!deleting && i === phrase.length) { deleting = phrases.length > 1; delay = 2200; if (!deleting) return; }
                    else if (deleting && i === 0) { deleting = false; p = (p + 1) % phrases.length; delay = 350; }
                    setTimeout(tick, delay);
                };
                setTimeout(tick, 500);
            }
        }
    }

    // Heure locale en direct.
    var clock = document.querySelector('[data-clock]');
    if (clock) {
        var tz = clock.getAttribute('data-clock');
        var render = function () {
            try {
                var parts = new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: tz }).formatToParts(new Date());
                var h = parts.find(function (x) { return x.type === 'hour'; }).value;
                var m = parts.find(function (x) { return x.type === 'minute'; }).value;
                clock.innerHTML = h + '<span class="sep">:</span>' + m;
            } catch (e) { /* fuseau inconnu : on garde la valeur serveur */ }
        };
        render();
        setInterval(render, 15000);
    }

    // Lueur qui suit la souris sur les blocs.
    if (finePointer && !reduceMotion) {
        document.addEventListener('pointermove', function (e) {
            var tile = e.target.closest && e.target.closest('.tile');
            if (!tile) return;
            var rect = tile.getBoundingClientRect();
            tile.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
            tile.style.setProperty('--my', (e.clientY - rect.top) + 'px');
        }, { passive: true });

        // Inclinaison 3D des cartes de projet.
        document.querySelectorAll('.project-tile').forEach(function (card) {
            card.addEventListener('pointermove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(900px) rotateX(' + (-y * 6).toFixed(2) + 'deg) rotateY(' + (x * 8).toFixed(2) + 'deg) translateY(-4px)';
            });
            card.addEventListener('pointerleave', function () { card.style.transform = ''; });
        });
    }

    // Formulaire de contact : budget visible uniquement pour un devis.
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
