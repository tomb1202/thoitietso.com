@extends('site.master')

@section('head')
    <title>Kết quả tìm kiếm cho "{{ $keyword }}" - {{ $settings['site_name'] }}</title>
    <meta name="description"
        content="Tìm kiếm phim 18+ liên quan đến '{{ $keyword }}' trên {{ $settings['site_name'] }}. Xem phim sex HD, miễn phí, cập nhật mới nhất.">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="shortcut icon" href="{{ sourceSetting($settings['favicon']) }}" type="image/x-icon">

    {{-- Open Graph --}}
    <meta property="og:locale" content="vi_VN">
    <meta property="og:title" content="Kết quả tìm kiếm cho '{{ $keyword }}' - {{ $settings['site_name'] }}">
    <meta property="og:description"
        content="Phim sex HD, miễn phí, cập nhật mới nhất theo từ khóa '{{ $keyword }}' trên {{ $settings['site_name'] }}.">
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
                'name' => 'Kết quả tìm kiếm: ' . $keyword,
                'item' => url()->current()
            ]
        ]
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('main')
    <div id="container">
        <h2 id="page-title" class="breadcrumb">Tìm kiếm: {{$keyword}}</h2>
        <div id="video-list">
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
        </div>

        <div class="clear"></div>

        {{ $movies->appends(request()->input())->links('site.pagination') }}
    </div>
@endsection
