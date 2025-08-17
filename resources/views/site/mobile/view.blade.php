@extends('site.mobile.master')

@section('head')
    <title>{{ $movie->title }} - MESEX.TV</title>
    <meta name="description" content="{{ $movie->description }}">
    <link rel="canonical" href="{{ route('movie.watch', ['slug' => $movie->slug]) }}">
    <link rel="shortcut icon" href="{{ asset($settings['favicon'] ?: 'assets/img/favicon.ico') }}" type="image/x-icon">

    <meta property="og:locale" content="vi_VN">
    <meta property="og:title" content="{{ $movie->title }} - MESEX.TV">
    <meta property="og:description" content="{{ $movie->description }}">
    <meta property="og:type" content="video">
    <meta property="og:image" content="{{ $movie->poster }}">
    <meta property="og:url" content="{{ route('movie.watch', ['slug' => $movie->slug]) }}">
    <meta property="og:site_name" content="MESEX.TV">

    <script type="application/ld+json">
    [{
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            { "@type": "ListItem", "position": 1, "name": "MESEX", "item": "{{ url('/') }}" },
            { "@type": "ListItem", "position": 2, "name": "{{ $movie->title }}", "item": "{{ route('movie.watch', ['slug' => $movie->slug]) }}" }
        ]
    }]
    </script>
@endsection

@section('main')
    <div id="container">
        <h2 id="page-title" class="breadcrumb" style="text-transform: none;">{{ $movie->title }}</h2>

        <div id="video" data-id="{{ $movie->id }}" data-sv="1">
            <div class="mobile video-player" style="position: relative;">
                @php
                    $embedSource = $movie->sources->where('active', 1)->where('type', 'embed')->first();
                @endphp
                {{-- Non-iOS: Dùng iframe --}}
                <iframe src="{{ $embedSource->video }}" frameborder="0" width="100%" height="100%"
                    allowfullscreen webkitallowfullscreen mozallowfullscreen
                    style="position:absolute;top:0;left:0;"></iframe>
            </div>

            <div class="clear"></div>

            <div id="vl-underplayer-adx" style="max-width: 728px; margin: 5px auto; padding: 0 5px; text-align: center;">
            </div>
            <script src="{{ url('/assets/adv/vl-underplayer-adx.js') }}"></script>

            <div class="clear"></div>
            <div class="video-content" style="margin-top: 15px;">
                <div class="video-description">{!! $movie->description !!}</div>

                <div class="video-tags">
                    @if ($movie->genres->count())
                        <div class="category-tag">
                            @foreach ($movie->genres as $genre)
                                <a href="{{ route('site.genre', $genre->slug) }}" title="{{ $genre->name }}">{{ $genre->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div id="video-list">
            <h2 class="breadcrumb">Phim liên quan</h2>
            @foreach ($relatedMovies as $rel)
                <div id="video-{{ $rel->id }}" class="video-item">
                    <a title="{{ $rel->title }}" href="{{ route('movie.watch', ['slug' => $rel->slug]) }}">
                        <img class="video-image lazyload" src="{{ asset('storage/images/posters/' . $rel->poster) }}"
                            data-original="{{ asset('storage/images/posters/' . $rel->poster) }}" width="240px"
                            height="180px" alt="{{ $rel->title }}">
                        @if ($movie->language == 'Vietsub')
                            <div class="ribbon">Vietsub</div>
                        @endif
                    </a>
                    <div class="video-name">
                        <a title="{{ $rel->title }}" href="{{ route('movie.watch', ['slug' => $rel->slug]) }}">
                            {{ $rel->title }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
