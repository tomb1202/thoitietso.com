<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="index, follow">

    @yield('head')

    <link rel="stylesheet" href="{{ url('assets/css/mobile-default.css') }}?v=1.0" type="text/css">
    <script type="text/javascript" src="{{ url('assets/js/jquery.min.js') }}?v=1.0"></script>
    <script type="text/javascript" src="{{ url('assets/js/mobile-default.js') }}?v=1.0"></script>
    <script type="text/javascript" src="{{ url('assets/js/jquery.lazyload.min.js') }}?v=1.0"></script>
    <script>
        $(function() {
            $("img.lazyload").lazyload();
        })
    </script>

    <style>
        body {
            font-family: math;
        }
    </style>

     @if ($isDesktop)
        <link type="text/css" href="{{ url('assets/css/desktop-default.css') }}?v=1.0" rel="stylesheet">
    @else
        <link type="text/css" href="{{ url('assets/css/mobile-default.css') }}?v=1.0" rel="stylesheet">
    @endif

    <script type="text/javascript" src="{{ url('assets/js/jquery.min.js') }}?v=1.0"></script>

    @if ($isDesktop)
        <script type="text/javascript" src="{{ url('assets/js/desktop-default.js') }}?v=1.0"></script>
    @else
        <script type="text/javascript" src="{{ url('assets/js/mobile-default.js') }}?v=1.0"></script>
    @endif

    <script type="text/javascript" src="{{ url('assets/js/jquery.lazyload.min.js') }}?v=1.0"></script>
    <script>
        $(function() {
            $("img.lazyload").lazyload();
        })
    </script>

    <style>
        body {
            font-family: math;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! $settings['google_console'] ?? null !!}
    {!! $settings['google_analytics'] ?? null !!}

    @if ($isDesktop)
        <link href="{{ url('assets/adv/desktop-adx.css') }}?v=1.0" rel="stylesheet" type="text/css">
    @else
        <link href="{{ url('assets/adv/mobile-adx.css') }}?v=1.0" rel="stylesheet" type="text/css">
    @endif

    @if (isset($headerScript))
        @foreach ($headerScript as $header)
            {!! $header->script !!}
        @endforeach
    @endif

    <style>
        .banner-row {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            margin-top: 20px;
        }

        .banner-side {
            width: 160px;
            flex-shrink: 0;
        }

        .banner-side img {
            width: 100%;
            height: auto;
            display: block;
        }

        .adv-center {
            flex: 1;
            max-width: 100%;
        }

        .main-content {
            margin-top: 20px;
        }


        .banner-row {
            width: 70%;
            margin: 0 auto;
        }

        .fixed-banner {
            position: fixed;
            top: 130px;
            z-index: 999;
            width: 120px;
        }

        .fixed-left {
            left: 8%;
        }

        .fixed-right {
            right: 8%;
        }

        .fixed-banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .banner-side img {
            margin-bottom: 5px;
        }


        #vl-header-adx {
            display: flex;
            flex-wrap: wrap;
        }

        #vl-header-adx p {
            width: 50%;
            /* mỗi hàng 2 ảnh */
            box-sizing: border-box;
        }

        @media screen and (max-width:1200px) {
            .banner-row {
                width: 100%;
                margin: 0 auto;
            }

            .banner-row {

                margin-top: 0px;
            }

            .main-content {
                margin-top: 0px;
            }

            div#vl-header-adx {
                padding: 15px;
            }

            div#vl-header-adx p {
                margin-bottom: 0;
            }

        }


        .banner-catfish-bottom img {
            width: 100%;
        }

        .banner-catfish-bottom a {
            /* width: 80%; */
        }

        .banner-catfish-bottom:nth-child(odd) img {
            width: 80%;
            display: block;
            margin-left: auto;
        }

        .banner-catfish-bottom:nth-child(even) img {
            width: 80%;
            display: block;
            margin-right: auto;
        }


        .banner-catfish-bottom {
            box-shadow: none;
        }


        .banner-preload-container>a {
            max-width: 560px;
        }

        @media screen and (max-width:720px) {
            .fixed-banner {
                display: none;
            }

            #vl-header-adx p {
                width: 100%;
                box-sizing: border-box;
            }

            #vl-header-adx {
                display: inline-flex;
                flex-wrap: wrap;
            }

            .catfish-bottom {
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <div id="header">
            <header>
                <h1 class="hidden">{{$settings['site_name']}}</h1>
                <div id="logo">
                    <div itemscope="" itemtype="https://schema.org/Organization" class="logoWrapper"><a itemprop="url"
                            href="/">
                            <img itemprop="logo"
                                src="{{ $settings['logo'] != '' ? sourceSetting($settings['logo']) ?? '/assets/img/logo.png' : '/assets/img/logo.png' }}"
                                width="200" height="30px" title="{{ $settings['site_name'] }}"
                                alt="{{ $settings['title'] }}"></a>
                    </div>
                </div>
            </header>

            <div id="primary-nav">
                @php
                    $icons = ['icon-thumbs-up', 'icon-verify', 'icon-eye-off', 'icon-hashtag', 'icon-views'];
                @endphp

                <ul class="menu">
                    {{-- Trang chủ --}}
                    <li>
                        <a href="{{ route('site.home') }}"
                            style="background: rgba(234, 67, 53, 0.6); color: rgb(218, 218, 218);">
                            <span class="icon icon-home">Trang chủ</span>
                        </a>
                    </li>

                    {{-- Loop genres --}}
                    @foreach ($genres as $genre)
                        @php
                            $icon = $icons[array_rand($icons)];
                        @endphp
                        <li>
                            <a title="{{ $genre->name }}" href="{{ route('site.genre', $genre->slug) }}">
                                <span class="icon {{ $icon }}">{{ $genre->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>


            <div style="background: #2b2b2b;font-size: 14px;line-height: 1.6;margin: 5px 5px 0 5px;padding: 5px;">
                Sử dụng app VPN <a href="https://1.1.1.1" target="_blank" rel="noopener noreferrer">
                    <font color="#db4437">1.1.1.1</font>
                </a> (<a href="https://1.1.1.1" target="_blank" rel="noopener noreferrer">
                    <font color="#f90">tải về</font>
                </a>) để truy cập <font color="#db4437">{{ $settings['site_name'] ?? 'MESEX' }}</font> trong trường hợp
                web bị chặn.
            </div>

            <div id="search-box">
                <form class="search" method="get">
                    <span class="icon-search"></span>
                    <input type="text" placeholder="Thể loại, diễn viên, code,..."
                        onfocus="if (this.value == 'Thể loại, diễn viên, code,...') {this.value = '';}"
                        onblur="if (this.value == '') {this.placeholder = 'Thể loại, diễn viên, code,...';}"
                        class="searchTxt">
                    <input type="submit" value="Tìm kiếm" class="searchBtn">
                </form>
            </div>
            <div id="vl-header-adx">

            </div>
        </div>

        @yield('main')

        <div id="footer">
            <footer>
                <div class="web-link">
                    <h2 id="page-title" class="breadcrumb">Liên kết</h2>

                    @if (isset($textLinks) && count($textLinks) > 0)
                        @foreach ($textLinks as $textLink)
                            <a title="Xvideo" title="{{ $textLink->title }}" href="{{ $textLink->link }}"
                                target="_blank"><span class="icon icon-xvideos">{{ $textLink->title }}</span></a>
                        @endforeach
                    @endif

                </div>

                <div class="search-history">
                    <h2 id="page-title" class="breadcrumb">Top tìm kiếm</h2>
                    @foreach ($topSearches as $search)
                        <a href="{{ route('site.search', ['keyword' => Str::slug($search->keyword)]) }}"
                            title="{{ $search->keyword }}">
                            {{ $search->keyword }}
                        </a>
                    @endforeach
                </div>

                <div class="footer-wrap">
                    <p>{{ $settings['site_name'] }} là web xem <a title="phim sex" href="{{ url('/') }}"><span
                                class="url">phim
                                sex</span></a> dành cho người lớn trên 19 tuổi, giúp bạn giải trí, thỏa mãn sinh lý,
                        dưới 19 tuổi xin vui lòng quay ra.</p>
                    <p>Trang web này không đăng tải clip sex Việt Nam, video sex trẻ em. Nội dung phim được dàn dựng từ
                        trước, hoàn toàn không có thật, người xem tuyệt đối không bắt chước hành động trong phim, tránh
                        vi phạm pháp luật.</p>
                    <p></p>
                    <div style="font-size: 12px;color: #dadada;opacity: .8;">
                        <p>© 2023 Mesex.tv</p>
                    </div>
                    <p></p>
                </div>
            </footer>
        </div>

    </div>

</body>

{{-- adx js --}}
<script type="text/javascript" src="{{ url('assets/adv/vl-header-adx.js?v=' . time()) }}?v=1.0"></script>

@if ($isDesktop)
    <script type="text/javascript" src="{{ url('assets/adv/vl-desktop-adx.js?v=' . time()) }}?v=1.0"></script>
@else
    <script type="text/javascript" src="{{ url('assets/adv/vl-mobile-adx.js?v=' . time()) }}?v=1.0"></script>
@endif

@if (Route::currentRouteName() == 'web.movie.view')
    <script type="text/javascript" src="{{ url('assets/adv/vl-underplayer-adx.js?v=' . time()) }}?v=1.0"></script>
@endif

{{-- push js --}}
@if (isset($pushJs))
    @foreach ($pushJs as $push)
        {!! $push->script !!} <br>
    @endforeach
@endif

{{-- popup js --}}
@if (isset($popupJs))
    @foreach ($popupJs as $popup)
        {!! $popup->script !!} <br>
    @endforeach
@endif

{{-- bottom script --}}
@if (isset($bottomScript))
    @foreach ($bottomScript as $bottom)
        {!! $bottom->script !!} <br>
    @endforeach
@endif

</html>
