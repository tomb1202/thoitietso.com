@extends('site.master')

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

        <div id="video" data-id="{{ $movie->id }}" >
            <div class="desktop video-player" style="height: 450px">
                @php
                    $embedSource = $movie->sources->where('active', 1)->where('type', 'embed')->first();
                @endphp

                @if ($embedSource)
                    <iframe src="{{ $embedSource->video }}" scrolling="no" frameborder="0" width="100%" height="100%"
                        allowfullscreen webkitallowfullscreen mozallowfullscreen>
                    </iframe>
                @else
                    <p style="text-align:center; color:#fff; padding:20px;">Hiện chưa có nguồn video khả dụng.</p>
                @endif
            </div>


            {{-- Server switch --}}
            <div id="video-actions">
                <div class="video-stats" style="float:right">
                    <span class="views"><span>{{ number_format($movie->view) }}</span></span>
                </div>
            </div>

            <div class="clear"></div>
            {{-- Ads slot (nếu có) --}}
            <div id="vl-underplayer-adx" style="max-width: 728px; margin: 5px auto; text-align: center;">
                {!! $settings['ads_under_player'] ?? '' !!}
            </div>

            <div class="video-content">
                <div class="video-description">{!! $movie->description !!}</div>

                <div class="video-tags">
                    {{-- @if ($movie->actors->count())
                        <div class="actress-tag">
                            @foreach ($movie->actors as $actor)
                                <a href="{{ route('site.actor', $actor->slug) }}"
                                    title="{{ $actor->name }}">{{ $actor->name }}</a>
                            @endforeach
                        </div>
                    @endif --}}

                    @if ($movie->genres->count())
                        <div class="category-tag">
                            @foreach ($movie->genres as $genre)
                                <a href="{{ route('site.genre', $genre->slug) }}"
                                    title="{{ $genre->name }}">{{ $genre->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Related Movies --}}
        <div id="video-list">
            <h2 class="breadcrumb">Phim liên quan</h2>
            @foreach ($relatedMovies as $rel)
                <div id="video-{{ $rel->id }}" class="video-item">
                    <a title="{{ $rel->title }}" href="{{ route('movie.watch', ['slug' => $rel->slug]) }}">
                        <img class="video-image lazyload" src="{{ asset('storage/images/posters/' . $rel->poster) }}"
                            data-original="{{ asset('storage/images/posters/' . $rel->poster) }}" width="240px"
                            height="180px" alt="{{ $rel->title }}">
                        @if ($rel->is_trending)
                            <div class="ribbon">Hot</div>
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
        <div class="clear"></div>
    </div>
@endsection
