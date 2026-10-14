<h2>Publisher</h2>
<p>
    The website <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</strong> is published by <strong>{{ $profile->name }}</strong>,
    independent web developer{{ $profile->location ? ', based in '.$profile->location : '' }}.
</p>
<ul>
    @if ($profile->email)<li>Email: {{ $profile->email }}</li>@endif
    @if ($profile->phone)<li>Phone: {{ $profile->phone }}</li>@endif
    <li>Publication director: {{ $profile->name }}</li>
</ul>

<h2>Hosting</h2>
<p>
    The website is hosted by <strong>Hostinger International Ltd</strong>, 61 Lordou Vironos Street, 6023 Larnaca, Cyprus
    (hostinger.com).
</p>

<h2>Intellectual property</h2>
<p>
    All content on this website (texts, articles, case studies, photographs, logos, graphics and code) belongs to {{ $profile->name }},
    unless stated otherwise. Brands and logos of the projects shown belong to their respective owners.
</p>
<p>
    Any reproduction, display, modification or distribution, in whole or in part, without prior written permission is prohibited.
    To reuse content, write to the address above: a short quote with a link to the original page is usually accepted.
</p>

<h2>Links to other websites</h2>
<p>
    This website links to external sites (delivered projects, social networks). They are not controlled by the publisher,
    who cannot be held responsible for their content or their privacy practices.
</p>

<h2>Liability</h2>
<p>
    The information published is provided for guidance and updated regularly. Despite the care taken, it may contain errors
    or be out of date: please report them to the address above. Prices shown are not a firm offer; only a signed quote is binding.
</p>

<h2>Personal data</h2>
<p>
    How your data is collected and used is described in the
    <a href="{{ lroute('legal.privacy') }}">privacy policy</a>.
</p>

<h2>Governing law</h2>
<p>
    This website and this notice are governed by the law of Benin, in particular Law No. 2017-20 of 20 April 2018 on the Digital Code of the Republic of Benin.
    In the event of a dispute, an amicable solution will be sought first.
</p>
