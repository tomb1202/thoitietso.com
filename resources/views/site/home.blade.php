@extends('site.master')

@section('head')
    <title>{{ $settings['title'] }}</title>
    <meta name="description" content="{!! $settings['description'] !!}">

    <link rel="canonical" href="h{{ url('/') }}">
    <link rel="next" href="{{ url('/') }}">
    <link rel="shortcut icon" href="{{ sourceSetting($settings['favicon']) }}" type="image/x-icon">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:title" content="{{ $settings['title'] }}">
    <meta property="og:description" content="{!! $settings['description'] !!}">
    <meta property="og:type" content="video">
    <meta property="og:image" content="{{ sourceSetting($settings['logo']) }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{ $settings['site_name'] }}">
    <script type="application/ld+json">
    {!! json_encode([
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => config('app.name', 'Mesex.tv'),
                'item' => url('/') // Trang chủ
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Xem phim sex chọn lọc mới nhất',
                'item' => url()->current() // URL hiện tại
            ]
        ]
    ]
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('main')
    <div id="container">
        <h2 id="page-title" class="breadcrumb">Phim sex mới</h2>
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
