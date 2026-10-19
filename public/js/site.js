// Site public — interactions et motion design, sans dépendance.
(function () {
// init() branche toutes les interactions sur la page affichée. Elle est relancée
// après un changement de langue sans rechargement : les écouteurs posés sur
// document et window reçoivent un « signal » qui permet de les retirer.
function init(signal) {
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
    var progress = document.querySelector('.nav-progress');
    var backToTop = document.querySelector('.back-to-top');
    var nav = document.getElementById('site-nav');
    var indicator = nav && nav.querySelector('.nav-indicator');
    var spyLinks = nav ? Array.prototype.slice.call(nav.querySelectorAll('a[data-section]')) : [];
    var spySections = spyLinks
        .map(function (link) { return document.getElementById(link.getAttribute('data-section')); })
        .filter(Boolean);
    // Lien actif fixé par la page (Services, Projets) quand aucune section n'est suivie.
    var pageActive = nav && nav.querySelector('a.is-active[data-section]');

    var moveIndicator = function () {
        if (!indicator || window.innerWidth <= 980) return;
        var active = nav.querySelector('a.is-active[data-section]');
        nav.classList.add('has-indicator');
        if (!active) { indicator.style.opacity = '0'; return; }
        indicator.style.opacity = '1';
        indicator.style.width = active.offsetWidth + 'px';
        indicator.style.transform = 'translate(' + active.offsetLeft + 'px, -50%)';
    };

    var spy = function () {
        if (!spySections.length) return;
        var line = window.innerHeight * 0.35;
        var current = null;
        spySections.forEach(function (section) {
            if (section.getBoundingClientRect().top <= line) current = section.id;
        });
        spyLinks.forEach(function (link) {
            var on = current ? link.getAttribute('data-section') === current : link === pageActive;
            link.classList.toggle('is-active', on);
            if (on) link.setAttribute('aria-current', 'location'); else link.removeAttribute('aria-current');
        });
    };

    var onScroll = function () {
        var y = window.scrollY;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        if (progress) progress.style.setProperty('--progress', max > 0 ? (y / max).toFixed(4) : 0);
        if (header) header.classList.toggle('is-scrolled', y > 24);
        if (backToTop) backToTop.classList.toggle('is-visible', y > 900);
        spy();
        moveIndicator();
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true, signal: signal });
    window.addEventListener('resize', moveIndicator, { signal: signal });
    // La barre change de taille en défilant : on recale l'indicateur à la fin de la transition.
    if (header) header.addEventListener('transitionend', moveIndicator);
    if (backToTop) backToTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });

    /* ---------- Menu mobile ---------- */
    // Panneau qui glisse depuis la droite ; le fond de page ne défile plus pendant l'ouverture.
    var toggle = document.querySelector('.nav-toggle');
    var menu = document.getElementById('mobile-menu');
    if (toggle && menu) {
        var setOpen = function (open) {
            if (open === menu.classList.contains('is-open')) return;
            menu.classList.toggle('is-open', open);
            root.classList.toggle('menu-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                menu.removeAttribute('inert');
                var first = menu.querySelector('.mobile-menu-links a');
                if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
            } else {
                menu.setAttribute('inert', '');
                toggle.focus({ preventScroll: true });
            }
        };
        toggle.addEventListener('click', function () { setOpen(true); });
        menu.addEventListener('click', function (e) {
            if (e.target.closest('[data-menu-close]') || e.target.closest('a')) setOpen(false);
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); }, { signal: signal });
        window.addEventListener('resize', function () { if (window.innerWidth > 980) setOpen(false); }, { signal: signal });

        // Glisser le panneau vers la droite pour le fermer.
        var panel = menu.querySelector('.mobile-menu-panel');
        var startX = null;
        panel.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        panel.addEventListener('touchmove', function (e) {
            if (startX === null) return;
            var dx = Math.max(0, e.touches[0].clientX - startX);
            panel.style.transition = 'none';
            panel.style.transform = 'translateX(' + dx + 'px)';
        }, { passive: true });
        panel.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            startX = null;
            panel.style.transition = '';
            panel.style.transform = '';
            if (dx > 80) setOpen(false);
        });
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
    }, { signal: signal });
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
    }, { passive: true, signal: signal });
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
        var clockTimer = setInterval(render, 15000);
        signal.addEventListener('abort', function () { clearInterval(clockTimer); });
    }

    /* ---------- Lueur qui suit la souris et inclinaison des projets ---------- */
    if (finePointer && !reduceMotion) {
        document.addEventListener('pointermove', function (e) {
            var tile = e.target.closest && e.target.closest('.tile');
            if (!tile) return;
            var rect = tile.getBoundingClientRect();
            tile.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
            tile.style.setProperty('--my', (e.clientY - rect.top) + 'px');
        }, { passive: true, signal: signal });

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

    /* ---------- Copier le lien d'un article ---------- */
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var done = function () {
                button.classList.add('is-copied');
                button.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i>';
                button.setAttribute('aria-label', button.getAttribute('data-copied'));
                setTimeout(function () {
                    button.classList.remove('is-copied');
                    button.innerHTML = '<i class="fas fa-link" aria-hidden="true"></i>';
                }, 1800);
            };
            if (navigator.clipboard) navigator.clipboard.writeText(button.getAttribute('data-copy')).then(done);
        });
    });

    /* ---------- Formulaires : bouton en état « envoi » ---------- */
    document.querySelectorAll('form').forEach(function (f) {
        if (f.hasAttribute('data-contact-form')) return; // déjà géré plus haut
        f.addEventListener('submit', function () {
            var button = f.querySelector('button[type="submit"][data-sending]');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> ' + button.getAttribute('data-sending');
            }
        });
    });

    /* ---------- Mesure d'audience anonyme ---------- */
    // Une page vue à l'arrivée, puis le temps réellement passé sur la page
    // (onglet visible) envoyé au départ. Aucun cookie supplémentaire.
    (function () {
        var endpoint = document.body.getAttribute('data-track');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (!endpoint || !token || !window.fetch) return;

        var viewId = null;
        var activeMs = 0;
        var visibleSince = document.visibilityState === 'visible' ? Date.now() : null;
        var form = function (fields) {
            var data = new FormData();
            data.append('_token', token.getAttribute('content'));
            Object.keys(fields).forEach(function (k) { if (fields[k] != null) data.append(k, fields[k]); });
            return data;
        };
        var timezone = null;
        try { timezone = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch (e) { /* ancien navigateur */ }

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: form({
                path: location.pathname,
                title: document.title,
                referrer: document.referrer || null,
                timezone: timezone,
                locale: document.documentElement.lang
            })
        }).then(function (r) { return r.status === 201 ? r.json() : null; })
          .then(function (json) { if (json) viewId = json.id; })
          .catch(function () { /* la mesure ne doit jamais gêner la navigation */ });

        var flush = function () {
            if (visibleSince) { activeMs += Date.now() - visibleSince; visibleSince = null; }
            if (viewId && navigator.sendBeacon) {
                navigator.sendBeacon(endpoint + '/' + viewId, form({ duration: Math.round(activeMs / 1000) }));
            }
        };
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') flush();
            else visibleSince = Date.now();
        }, { signal: signal });
        window.addEventListener('pagehide', flush, { signal: signal });
        // Changement de langue sans rechargement : la page quittée envoie sa durée.
        signal.addEventListener('abort', flush);
    })();

    /* ---------- Consentement Google Analytics ---------- */
    // Google Analytics n'est chargé qu'après un clic sur « Accepter ». Le choix
    // est mémorisé dans le navigateur et peut être changé depuis le pied de page.
    (function () {
        var banner = document.querySelector('.consent-banner');
        if (!banner) return;
        var id = banner.getAttribute('data-ga');
        var key = 'analytics-consent';
        var read = function () { try { return localStorage.getItem(key); } catch (e) { return null; } };
        var save = function (value) { try { localStorage.setItem(key, value); } catch (e) { /* stockage indisponible */ } };

        var loadAnalytics = function () {
            if (window.__gaLoaded) return;
            window.__gaLoaded = true;
            window.dataLayer = window.dataLayer || [];
            window.gtag = function () { window.dataLayer.push(arguments); };
            window.gtag('js', new Date());
            window.gtag('config', id);
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
            document.head.appendChild(script);
        };

        // Retrait du consentement : on supprime les cookies _ga déjà déposés.
        var clearCookies = function () {
            var domains = ['', location.hostname, '.' + location.hostname.replace(/^www\./, '')];
            document.cookie.split(';').forEach(function (c) {
                var name = c.split('=')[0].trim();
                if (name.indexOf('_ga') !== 0) return;
                domains.forEach(function (d) {
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (d ? '; domain=' + d : '');
                });
            });
        };

        var show = function () {
            banner.hidden = false;
            requestAnimationFrame(function () { banner.classList.add('is-visible'); });
        };
        var hide = function () {
            banner.classList.remove('is-visible');
            setTimeout(function () { banner.hidden = true; }, 300);
        };

        var choice = read();
        if (choice === 'granted') loadAnalytics();
        else if (choice !== 'denied') show();

        banner.querySelector('[data-consent-accept]').addEventListener('click', function () {
            save('granted');
            loadAnalytics();
            hide();
        });
        banner.querySelector('[data-consent-refuse]').addEventListener('click', function () {
            var wasLoaded = window.__gaLoaded;
            save('denied');
            clearCookies();
            hide();
            // Google Analytics déjà chargé : seul un rechargement l'arrête.
            if (wasLoaded) location.reload();
        });
        document.querySelectorAll('[data-consent-open]').forEach(function (button) {
            button.addEventListener('click', show);
        });
    })();

    /* ---------- Protection du contenu ---------- */
    // Bloque clic droit, copie, sélection, glisser d'images et raccourcis
    // d'inspection, avec une notification discrète au lieu d'une alerte.
    // Les champs de formulaire restent utilisables normalement.
    (function () {
        var toast = document.querySelector('.protect-toast');
        if (!toast) return;
        var text = toast.querySelector('[data-protect-text]');
        var timer = null;
        var lastShown = 0;

        var isEditable = function (el) {
            return !!(el && el.closest && el.closest('input, textarea, select, [contenteditable="true"]'));
        };

        var hide = function () {
            toast.classList.remove('is-visible');
            clearTimeout(timer);
            timer = setTimeout(function () { toast.hidden = true; }, 300);
        };

        var notify = function (kind) {
            var now = Date.now();
            if (now - lastShown < 600 && !toast.hidden) return; // évite le clignotement
            lastShown = now;
            text.textContent = toast.getAttribute('data-msg-' + kind) || toast.getAttribute('data-msg-copy');
            clearTimeout(timer);
            toast.hidden = false;
            requestAnimationFrame(function () { toast.classList.add('is-visible'); });
            timer = setTimeout(hide, 4200);
        };

        toast.querySelector('[data-protect-close]').addEventListener('click', hide);

        document.addEventListener('contextmenu', function (e) {
            if (isEditable(e.target)) return;
            e.preventDefault();
            notify(e.target.closest && e.target.closest('img, picture, video, .media') ? 'image' : 'menu');
        }, { signal: signal });

        ['copy', 'cut'].forEach(function (type) {
            document.addEventListener(type, function (e) {
                if (isEditable(e.target)) return;
                e.preventDefault();
                notify('copy');
            }, { signal: signal });
        });

        document.addEventListener('selectstart', function (e) {
            if (!isEditable(e.target)) e.preventDefault();
        }, { signal: signal });

        document.addEventListener('dragstart', function (e) {
            if (e.target.closest && e.target.closest('img, a, video')) {
                e.preventDefault();
                notify('image');
            }
        }, { signal: signal });

        document.addEventListener('keydown', function (e) {
            var key = (e.key || '').toLowerCase();
            var mod = e.ctrlKey || e.metaKey;
            var devtools = key === 'f12' || (mod && e.shiftKey && ['i', 'j', 'c', 'k'].indexOf(key) !== -1) || (e.metaKey && e.altKey && ['i', 'j', 'c', 'u'].indexOf(key) !== -1);
            var blocked = mod && !e.shiftKey && ['u', 's', 'p'].indexOf(key) !== -1;
            var copying = mod && !e.shiftKey && ['c', 'x', 'a'].indexOf(key) !== -1 && !isEditable(e.target);
            if (devtools || blocked || copying) {
                e.preventDefault();
                notify(copying ? 'copy' : 'keys');
            }
        }, { signal: signal });
    })();

    /* ---------- Compétences : flèches en pointillés entre les colonnes ---------- */
    // Frontend → Backend → Outils. Les flèches suivent la disposition réelle :
    // horizontales quand les cartes sont côte à côte, verticales quand elles
    // s'empilent (tablette, mobile). Les positions viennent de offsetLeft/Top,
    // insensibles aux animations d'apparition des cartes.
    (function () {
        var flow = document.querySelector('[data-skills-flow]');
        if (!flow) return;
        var NS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(NS, 'svg');
        svg.setAttribute('class', 'skills-links');
        svg.setAttribute('aria-hidden', 'true');
        svg.innerHTML = '<defs><marker id="skills-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M1 1 L9 5 L1 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></marker></defs>';
        flow.appendChild(svg);
        var labels = [];

        var draw = function () {
            var tiles = Array.prototype.slice.call(flow.querySelectorAll(':scope > .skill-tile'));
            Array.prototype.slice.call(svg.querySelectorAll('g')).forEach(function (g) { g.remove(); });
            labels.forEach(function (label) { label.remove(); });
            labels = [];
            svg.setAttribute('viewBox', '0 0 ' + flow.offsetWidth + ' ' + flow.offsetHeight);

            for (var i = 0; i < tiles.length - 1; i++) {
                var a = tiles[i], b = tiles[i + 1];
                var ar = { l: a.offsetLeft, t: a.offsetTop, r: a.offsetLeft + a.offsetWidth, b: a.offsetTop + a.offsetHeight };
                var br = { l: b.offsetLeft, t: b.offsetTop, r: b.offsetLeft + b.offsetWidth, b: b.offsetTop + b.offsetHeight };
                var x1, y1, x2, y2, vertical;
                if (br.l >= ar.r - 1) {
                    // Côte à côte : de bord droit à bord gauche, à mi-hauteur commune.
                    y1 = y2 = Math.round((Math.max(ar.t, br.t) + Math.min(ar.b, br.b)) / 2);
                    x1 = ar.r + 6; x2 = br.l - 6; vertical = false;
                } else if (br.t >= ar.b - 1) {
                    // Empilées : du bas vers le haut de la carte suivante.
                    x1 = x2 = Math.round((Math.max(ar.l, br.l) + Math.min(ar.r, br.r)) / 2);
                    y1 = ar.b + 6; y2 = br.t - 6; vertical = true;
                } else {
                    continue;
                }
                if (Math.abs(x2 - x1) + Math.abs(y2 - y1) < 16) continue;

                var d = 'M' + x1 + ' ' + y1 + ' L' + x2 + ' ' + y2;
                var g = document.createElementNS(NS, 'g');
                g.innerHTML = '<path class="skills-link-track" d="' + d + '"/>'
                    + '<path class="skills-link" d="' + d + '" marker-end="url(#skills-arrow)"/>'
                    + (reduceMotion ? '' : '<circle class="skills-link-dot" r="3.5"><animateMotion dur="2.2s" repeatCount="indefinite" path="' + d + '"/></circle>');
                svg.appendChild(g);

                var text = a.getAttribute('data-flow-label');
                if (text) {
                    var label = document.createElement('span');
                    label.className = 'skills-link-label' + (vertical ? ' is-vertical' : '');
                    label.textContent = text;
                    label.style.left = ((x1 + x2) / 2) + 'px';
                    label.style.top = ((y1 + y2) / 2) + 'px';
                    flow.appendChild(label);
                    labels.push(label);
                    // Espace trop étroit entre deux cartes côte à côte : libellé à la verticale,
                    // pour ne jamais recouvrir le contenu des cartes.
                    if (!vertical && label.offsetWidth > (br.l - ar.r) + 36) label.classList.add('is-upright');
                }
            }
        };

        draw();
        if ('ResizeObserver' in window) {
            var resize = new ResizeObserver(function () { draw(); });
            resize.observe(flow);
            signal.addEventListener('abort', function () { resize.disconnect(); });
        } else {
            window.addEventListener('resize', draw, { signal: signal });
        }
        // Les flèches apparaissent quand la section entre à l'écran.
        if ('IntersectionObserver' in window) {
            var seen = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) { flow.classList.add('is-linked'); seen.disconnect(); }
            }, { threshold: 0.25 });
            seen.observe(flow);
            signal.addEventListener('abort', function () { seen.disconnect(); });
        } else {
            flow.classList.add('is-linked');
        }
    })();

    var year = document.querySelector('[data-year]');
    if (year) year.textContent = new Date().getFullYear();
}

var controller = new AbortController();
init(controller.signal);

/* ---------- Changement de langue sans rechargement ---------- */
// Le clic sur FR / EN récupère la page traduite en arrière-plan, puis remplace
// le contenu avec un fondu, à la même position de lecture. L'adresse change
// (/ ↔ /en) : le bouton Précédent, le partage et le référencement restent justes.
// En cas de problème (réseau, ancien navigateur), on charge simplement la page.
(function () {
    if (!window.fetch || !window.DOMParser || !window.history.pushState) return;

    // Balises du <head> propres à chaque langue (titre, SEO, partage, données structurées).
    var HEAD_TAGS = 'meta[name="description"], meta[name="robots"], meta[name="author"], link[rel="canonical"], link[rel="alternate"][hreflang], meta[property^="og:"], meta[property^="article:"], meta[name^="twitter:"], script[type="application/ld+json"]';
    var pending = null;
    var shownPath = location.pathname;

    history.replaceState({ page: true }, '', location.href);

    // Les blocs déjà apparus et les compteurs déjà animés restent en place :
    // changer de langue ne rejoue pas les animations d'arrivée.
    var keepRevealed = function (doc) {
        [['.reveal', 'reveal'], ['[data-count]', 'count'], ['[data-code]', 'code']].forEach(function (pair) {
            var before = document.querySelectorAll(pair[0]);
            var after = doc.querySelectorAll(pair[0]);
            if (before.length !== after.length) return;
            after.forEach(function (el, i) {
                if (!before[i].classList.contains('is-visible')) return;
                el.classList.add('is-visible');
                if (pair[1] === 'count') el.removeAttribute('data-count');
                if (pair[1] === 'code') {
                    var source = el.querySelector('template');
                    if (source) el.innerHTML = source.innerHTML.trim();
                    el.removeAttribute('data-code');
                }
            });
        });
    };

    var swapHead = function (doc) {
        document.title = doc.title;
        document.documentElement.lang = doc.documentElement.lang;
        document.head.querySelectorAll(HEAD_TAGS).forEach(function (el) { el.remove(); });
        var anchor = document.head.querySelector('link[rel="icon"]');
        doc.head.querySelectorAll(HEAD_TAGS).forEach(function (el) {
            document.head.insertBefore(document.importNode(el, true), anchor);
        });
    };

    var load = function (url, push) {
        if (pending) pending.abort();
        pending = new AbortController();
        var progress = (window.scrollY || 0) / Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        document.documentElement.classList.add('is-switching-lang');

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'text/html' }, signal: pending.signal })
            .then(function (response) {
                var type = response.headers.get('Content-Type') || '';
                if (!response.ok || type.indexOf('text/html') === -1) throw new Error('page indisponible');
                return response.text().then(function (html) { return { html: html, url: response.url || url }; });
            })
            .then(function (result) {
                var doc = new DOMParser().parseFromString(result.html, 'text/html');
                if (!doc.body || !doc.querySelector('.site-header')) throw new Error('page inattendue');
                keepRevealed(doc);

                var apply = function () {
                    document.documentElement.classList.remove('is-switching-lang');
                    controller.abort();
                    swapHead(doc);
                    document.documentElement.classList.remove('menu-open');
                    document.body.replaceWith(document.adoptNode(doc.body));
                    if (push) history.pushState({ page: true }, '', result.url);
                    shownPath = location.pathname;
                    var max = document.documentElement.scrollHeight - window.innerHeight;
                    window.scrollTo(0, Math.round(progress * Math.max(0, max)));
                    controller = new AbortController();
                    init(controller.signal);
                };

                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (document.startViewTransition && !reduce) {
                    document.startViewTransition(apply).finished.finally(function () {
                        document.documentElement.classList.remove('is-switching-lang');
                    });
                } else {
                    apply();
                    document.documentElement.classList.remove('is-switching-lang');
                }
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') return;
                window.location.href = url;
            });
    };

    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('.lang-switch a[hreflang]');
        if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        if (link.classList.contains('is-current')) return;

        // Retour immédiat : la pastille glisse vers la langue choisie pendant le chargement.
        var group = link.closest('.lang-switch');
        var links = group.querySelectorAll('a[hreflang]');
        links.forEach(function (a, i) {
            var on = a === link;
            a.classList.toggle('is-current', on);
            if (on) { a.setAttribute('aria-current', 'true'); group.setAttribute('data-current', i); } else { a.removeAttribute('aria-current'); }
        });

        load(link.href, true);
    });

    // Précédent / Suivant entre les deux langues.
    window.addEventListener('popstate', function (e) {
        // Une ancre (#contact) ne change pas de page : seul un changement d'adresse recharge le contenu.
        if (e.state && e.state.page && location.pathname !== shownPath) load(location.href, false);
    });
})();
})();
