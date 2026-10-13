// Site public — interactions et motion design, sans dépendance.
(function () {
    var root = document.documentElement;
    root.classList.remove('no-js');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* ---------- Thème clair / sombre ---------- */
    var applyTheme = function (theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem('theme', theme); } catch (e) { /* stockage indisponible */ }
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', theme === 'dark' ? '#0B0B0C' : '#F5F3EF');
    };
    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            button.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');

            // Révélation circulaire depuis le bouton (View Transitions API).
            if (!document.startViewTransition || reduceMotion) { applyTheme(next); return; }
            var x = event.clientX || window.innerWidth - 40;
            var y = event.clientY || 40;
            var radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));
            root.classList.add('theme-transition');
            var transition = document.startViewTransition(function () { applyTheme(next); });
            transition.ready.then(function () {
                root.animate(
                    { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + radius + 'px at ' + x + 'px ' + y + 'px)'] },
                    { duration: 650, easing: 'cubic-bezier(.22,.61,.36,1)', pseudoElement: '::view-transition-new(root)' }
                );
            });
            transition.finished.finally(function () { root.classList.remove('theme-transition'); });
        });
    });

    /* ---------- En-tête, progression, retour en haut ---------- */
    var header = document.querySelector('.site-header');
    var progress = document.querySelector('.scroll-progress');
    var backToTop = document.querySelector('.back-to-top');
    var nav = document.getElementById('site-nav');
    var lastY = window.scrollY;
    var onScroll = function () {
        var y = window.scrollY;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        if (progress) progress.style.setProperty('--progress', max > 0 ? (y / max).toFixed(4) : 0);
        if (header) {
            header.classList.toggle('is-scrolled', y > 16);
            var menuOpen = nav && nav.classList.contains('is-open');
            header.classList.toggle('is-hidden', !menuOpen && y > 400 && y > lastY + 4);
            if (y < lastY - 4) header.classList.remove('is-hidden');
        }
        if (backToTop) backToTop.classList.toggle('is-visible', y > 900);
        lastY = y;
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    if (backToTop) backToTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });

    /* ---------- Menu mobile ---------- */
    var toggle = document.querySelector('.nav-toggle');
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

    /* ---------- Boutons : onde au clic et effet magnétique ---------- */
    document.addEventListener('pointerdown', function (e) {
        var button = e.target.closest && e.target.closest('.btn');
        if (!button || reduceMotion) return;
        var rect = button.getBoundingClientRect();
        var size = Math.max(rect.width, rect.height);
        var ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        button.appendChild(ripple);
        setTimeout(function () { ripple.remove(); }, 700);
    });
    if (finePointer && !reduceMotion) {
        document.querySelectorAll('[data-magnetic]').forEach(function (el) {
            el.addEventListener('pointermove', function (e) {
                var r = el.getBoundingClientRect();
                el.style.transform = 'translate(' + ((e.clientX - r.left - r.width / 2) * 0.18).toFixed(1) + 'px,' + ((e.clientY - r.top - r.height / 2) * 0.25).toFixed(1) + 'px)';
            });
            el.addEventListener('pointerleave', function () { el.style.transform = ''; });
        });
    }

    /* ---------- Compteurs et apparitions au défilement ---------- */
    var animateCounter = function (el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        if (reduceMotion) { el.textContent = target; return; }
        var start = null;
        var step = function (ts) {
            if (!start) start = ts;
            var t = Math.min((ts - start) / 1400, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    var revealables = document.querySelectorAll('.reveal, [data-count], [data-code]');
    var show = function (el) {
        el.classList.add('is-visible');
        if (el.hasAttribute('data-count')) animateCounter(el);
        if (el.hasAttribute('data-code')) typeCode(el);
    };
    var pending = Array.prototype.slice.call(revealables);
    var reveal = function (el) {
        if (pending.indexOf(el) === -1) return;
        pending.splice(pending.indexOf(el), 1);
        show(el);
    };
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { reveal(entry.target); observer.unobserve(entry.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });
        revealables.forEach(function (el) { observer.observe(el); });
    }
    // Filet de sécurité : lors d'un défilement rapide, l'observateur peut
    // manquer un bloc. Tout bloc situé au-dessus du bas de l'écran s'affiche.
    var sweepQueued = false;
    var sweep = function () {
        sweepQueued = false;
        pending.slice().forEach(function (el) {
            if (el.getBoundingClientRect().top < window.innerHeight * 0.95) reveal(el);
        });
    };
    window.addEventListener('scroll', function () {
        if (!sweepQueued && pending.length) { sweepQueued = true; requestAnimationFrame(sweep); }
    }, { passive: true });
    if (!('IntersectionObserver' in window)) pending.slice().forEach(reveal);

    /* ---------- Fenêtre de code : saisie animée ---------- */
    function typeCode(pre) {
        var source = pre.querySelector('template');
        if (!source) return;
        var html = source.innerHTML.trim();
        if (reduceMotion) { pre.innerHTML = html; return; }
        // On tape le texte caractère par caractère en conservant la coloration.
        var tokens = html.split(/(<[^>]+>)/);
        var out = '', ti = 0, ci = 0;
        var cursor = '<span class="cursor"></span>';
        var tick = function () {
            if (ti >= tokens.length) { pre.innerHTML = out + cursor; return; }
            var token = tokens[ti];
            if (token.charAt(0) === '<') { out += token; ti++; ci = 0; return tick(); }
            if (ci < token.length) {
                var ch = token.charAt(ci);
                if (ch === '&') { var end = token.indexOf(';', ci); ch = token.slice(ci, end + 1); ci = end + 1; } else { ci++; }
                out += ch;
                pre.innerHTML = out + cursor;
                setTimeout(tick, ch === '\n' ? 120 : 22);
            } else { ti++; ci = 0; tick(); }
        };
        tick();
    }

    /* ---------- Heure locale en direct ---------- */
    var clock = document.querySelector('[data-clock]');
    if (clock) {
        var tz = clock.getAttribute('data-clock');
        var render = function () {
            try {
                var parts = new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: tz }).formatToParts(new Date());
                var get = function (type) { return parts.find(function (p) { return p.type === type; }).value; };
                clock.innerHTML = get('hour') + '<span class="sep">:</span>' + get('minute');
            } catch (e) { /* fuseau inconnu : valeur serveur conservée */ }
        };
        render();
        setInterval(render, 15000);
    }

    /* ---------- Lueur qui suit la souris et inclinaison des projets ---------- */
    if (finePointer && !reduceMotion) {
        document.addEventListener('pointermove', function (e) {
            var tile = e.target.closest && e.target.closest('.tile');
            if (!tile) return;
            var rect = tile.getBoundingClientRect();
            tile.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
            tile.style.setProperty('--my', (e.clientY - rect.top) + 'px');
        }, { passive: true });

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

    /* ---------- Formulaire de contact ---------- */
    var form = document.querySelector('[data-contact-form]');
    if (form) {
        var budget = form.querySelector('[data-budget-field]');
        var sync = function (animate) {
            var checked = form.querySelector('input[name="type"]:checked');
            var hidden = !checked || checked.value !== 'quote';
            if (!budget) return;
            if (!hidden && budget.hidden && animate) {
                budget.classList.remove('is-entering');
                void budget.offsetWidth;
                budget.classList.add('is-entering');
            }
            budget.hidden = hidden;
        };
        form.addEventListener('change', function (e) { if (e.target.name === 'type') sync(true); });
        sync(false);
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> ' + (button.getAttribute('data-sending') || '…');
            }
        });
    }

    var year = document.querySelector('[data-year]');
    if (year) year.textContent = new Date().getFullYear();
})();
