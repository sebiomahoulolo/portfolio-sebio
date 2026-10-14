<p>
    This website collects as little data as possible. It uses <strong>no advertising cookies and no third-party tracking</strong> (no Google Analytics, no Facebook pixel),
    and never sells or shares your data.
</p>

<h2>Data controller</h2>
<p>
    {{ $profile->name }}{{ $profile->location ? ', '.$profile->location : '' }}{{ $profile->email ? ' · '.$profile->email : '' }}.
</p>

<h2>Data collected and how it is used</h2>

<h3>Contact and quote form</h3>
<ul>
    <li><strong>Data:</strong> name, email address and, if you provide them, phone, company and budget, plus the subject and content of your message.</li>
    <li><strong>Use:</strong> replying to your request and, where relevant, preparing a quote. You get a reply by email; no newsletter is sent.</li>
    <li><strong>Legal basis:</strong> your request (pre-contractual steps) and the legitimate interest in replying to you.</li>
    <li><strong>Retention:</strong> {{ intdiv($retention['contact_messages_months'], 12) }} years after receipt, then automatic deletion.</li>
</ul>

<h3>Reviews left on the website</h3>
<ul>
    <li><strong>Data:</strong> name, job title or company, rating, review text and email address.</li>
    <li><strong>Use:</strong> reviews are checked before publication. Your email is <strong>never published</strong>: it is only used to contact you about your review.</li>
    <li><strong>Legal basis:</strong> your consent, which you can withdraw at any time (the review is then removed).</li>
    <li><strong>Retention:</strong> as long as the review is published; an unpublished review is deleted after {{ $retention['unpublished_reviews_months'] }} months.</li>
</ul>

<h3>Audience measurement</h3>
<ul>
    <li><strong>Data:</strong> page viewed, language, referring website (domain name only), country inferred from the time zone, device type, browser, operating system and time spent on the page.</li>
    <li><strong>No cookies, no IP address:</strong> your IP address is never stored. An encrypted fingerprint, renewed every day, counts visitors without tracking them from one day to the next or identifying them.</li>
    <li><strong>Do Not Track:</strong> if your browser sends this signal, nothing is measured.</li>
    <li><strong>Legal basis:</strong> legitimate interest in knowing how the website is used.</li>
    <li><strong>Retention:</strong> {{ $retention['page_views_months'] }} months, then deletion (only the total number of visitors is kept).</li>
</ul>

<h3>Technical security measures</h3>
<p>
    To prevent abuse (spam, intrusion attempts), an encrypted fingerprint of the IP address and the browser type are stored with messages and reviews.
    The original IP address cannot be recovered from them.
</p>

<h2>Cookies and local storage</h2>
<p>Only items strictly necessary for the website to work are used. They do not require consent:</p>
<ul>
    <li><strong>Session cookie</strong>: secures forms and audience measurement during your visit ({{ config('session.lifetime') }} minutes of inactivity).</li>
    <li><strong>XSRF-TOKEN cookie</strong>: protects forms against forged submissions.</li>
    <li><strong>Theme preference</strong> (light or dark): stored in your browser only, never sent to the server.</li>
</ul>

<h2>External services</h2>
<p>To display the website, your browser loads a few resources from other servers, which then receive your IP address:</p>
<ul>
    <li><strong>Google Fonts</strong> (typefaces) and <strong>cdnjs / Cloudflare</strong> (icons);</li>
    <li><strong>YouTube</strong>, in privacy-enhanced mode, only when an article contains a video.</li>
</ul>
<p>Notification emails are sent through the hosting provider’s mail server, Hostinger.</p>

<h2>Who can access your data?</h2>
<p>
    Only {{ $profile->name }}, through an administration area protected by a password and two-factor authentication, and the host Hostinger for storage.
    Data is backed up every night on the server; backups are kept for {{ config('portfolio.backups.keep_days') }} days.
</p>

<h2>Your rights</h2>
<p>
    At any time you can ask to <strong>access</strong>, <strong>correct</strong> or <strong>delete</strong> your data, <strong>restrict</strong> or <strong>object</strong> to its use,
    or receive a copy of it (<strong>portability</strong>).
    @if ($profile->email)
        Write to <strong>{{ $profile->email }}</strong>: you will get an answer within one month at most.
    @endif
</p>
<p>
    If you believe your rights are not respected, please write to me first: every request is reviewed carefully and a solution is sought quickly.
    You also remain free to contact the data protection authority of your country.
</p>

<h2>Changes</h2>
<p>This policy may change along with the website. The date of the last update is shown at the top of this page.</p>
