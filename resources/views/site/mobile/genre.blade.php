@extends('site.mobile.master')
@section('head')
    <title>{{ $genre->name }} - {{ $settings['site_name'] }}</title>
    <meta name="description" content="{{ $genre->description ?? $settings['description'] }}">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="shortcut icon" href="{{ sourceSetting($settings['favicon']) }}" type="image/x-icon">

    {{-- Open Graph --}}
    <meta property="og:locale" content="vi_VN">
    <meta property="og:title" content="{{ $genre->name }} - {{ $settings['site_name'] }}">
    <meta property="og:description" content="{{ $genre->description ?? $settings['description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ sourceSetting($settings['logo']) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $settings['site_name'] }}">

    {{-- Breadcrumb Schema --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $settings['site_name'],
                'item' => url('/')
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $genre->name,
                'item' => url()->current()
            ]
        ]
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection


@section('main')
    <div id="container">
        <h2 id="page-title" class="breadcrumb">{{ $genre->name }}</h2>
        @foreach ($movies as $movie)
            <div id="video-{{ $movie->id }}" class="video-item">
                <a title="{{ $movie->title }}" href="{{ route('movie.watch', $movie->slug) }}">
                    <img class="video-image lazyload" src="{{ asset('storage/images/posters/' . $movie->poster) }}"
                        data-original="{{ asset('storage/images/posters/' . $movie->poster) }}" width="240px"
                        height="180px" alt="{{ $movie->title }}">

                    @if($movie->language == 'Vietsub')
                        <div class="ribbon">{{$movie->language == 'Vietsub' ? 'Vietsub' : ""}}</div>
                        @endif
                </a>
                <div class="video-name">
                    <a title="{{ $movie->title }}" href="{{ route('movie.watch', $movie->slug) }}">
                        {{ $movie->title }}
                    </a>
                </div>
            </div>
        @endforeach

        <div class="clear"></div>

        {{ $movies->appends(request()->input())->links('site.pagination') }}
    </div>
@endsection
