@extends('site.master')

@section('main')
<main>
            <section class="weather-general">
                <div class="container">
                    <div class="weather-general-inner">
                        <div class="weather-main" style="width: 100%">
                            <div class="feature-location mb-3">
                                <a class="d-flex" href="https://thoitietso.com/ha-noi">
                                    <h1 class="weather-main-title">Dự báo thời tiết</h1>
                                    <h2 class="weather-main-title">&nbsp;Hà Nội</h2>
                                </a>
                            </div>
                            <div class="weather-main-hero">
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mưa nhẹ">
                                <p class="temp">
                                    32°
                                </p>
                                <div class="desc">
                                    <p>mưa nhẹ</p>
                                    <span>Cảm giác như <span>38°</span></span>
                                </div>
                            </div>
                            <div class="weather-main-desc">
                                <div class="item">
                                    <img src="temperature.svg" alt="Nhiệt độ tại Hà Nội">
                                    <div class="item-title">Thấp/Cao</div>
                                    <div class="temp">
                                        <p>32°/</p>
                                        <p> 32°</p>
                                    </div>
                                </div>
                                <div class="item">
                                    <img src="humidity-xl.svg" alt="Độ ẩm tại Hà Nội">
                                    <div class="item-title">Độ ẩm</div>
                                    <div class="temp">
                                        <p> 68 %</p>
                                    </div>
                                </div>
                                <div class="item">
                                    <img src="clarity-eye-line.svg" alt="Tầm nhìn tại Hà Nội">
                                    <div class="item-title">Tầm nhìn</div>
                                    <div class="temp">
                                        <p>10 km</p>
                                    </div>
                                </div>
                                <div class="item">
                                    <img src="ph-wind.svg" alt="Gió tại Hà Nội">
                                    <div class="item-title">Gió</div>
                                    <div class="temp">
                                        <p>4.06/h</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="weather-highlight">
                <div class="container">
                    <div class="weather-highlight-inner">
                        <div class="weather-highlight-list">
                            <div class="title-main w-100">
                                <h2>Thời tiết nổi bật</h2>
                            </div>
                            <a href="/ha-noi" class="weather-sub">
                                <h3 class="title">
                                    Hà Nội </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mưa nhẹ">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>68%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>32°/</p>
                                        <p>38°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/da-nang" class="weather-sub">
                                <h3 class="title">
                                    Đà Nẵng </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mưa nhẹ">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>89%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>27°/</p>
                                        <p>27°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/quang-nam" class="weather-sub">
                                <h3 class="title">
                                    Quảng Nam </h3>
                                <img src="https://openweathermap.org/img/wn/04d@2x.png" alt="mây đen u ám">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>84%</span>
                                    </div>
                                    <p>mây đen u ám</p>
                                    <div class="temp">
                                        <p>27°/</p>
                                        <p>27°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/ba-ria-vung-tau" class="weather-sub">
                                <h3 class="title">
                                    Bà Rịa - Vũng Tàu </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mây cụm">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>76%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>27°/</p>
                                        <p>30°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/ho-chi-minh" class="weather-sub">
                                <h3 class="title">
                                    Hồ Chí Minh </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mây rải rác">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>71%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>28°/</p>
                                        <p>31°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/ben-tre" class="weather-sub">
                                <h3 class="title">
                                    Bến Tre </h3>
                                <img src="https://openweathermap.org/img/wn/04d@2x.png" alt="mây cụm">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>70%</span>
                                    </div>
                                    <p>mây đen u ám</p>
                                    <div class="temp">
                                        <p>29°/</p>
                                        <p>32°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/thua-thien-hue" class="weather-sub">
                                <h3 class="title">
                                    Huế </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mây cụm">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>89%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>26°/</p>
                                        <p>27°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/lao-cai" class="weather-sub">
                                <h3 class="title">
                                    Lào Cai </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="bầu trời quang đãng">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>68%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>31°/</p>
                                        <p>36°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/hai-phong" class="weather-sub">
                                <h3 class="title">
                                    Hải Phòng </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mây thưa">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>76%</span>
                                    </div>
                                    <p>mưa vừa</p>
                                    <div class="temp">
                                        <p>31°/</p>
                                        <p>37°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/ninh-binh" class="weather-sub">
                                <h3 class="title">
                                    Ninh Bình </h3>
                                <img src="https://openweathermap.org/img/wn/10d@2x.png" alt="mây đen u ám">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>86%</span>
                                    </div>
                                    <p>mưa nhẹ</p>
                                    <div class="temp">
                                        <p>27°/</p>
                                        <p>30°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/binh-dinh" class="weather-sub">
                                <h3 class="title">
                                    Bình Định </h3>
                                <img src="https://openweathermap.org/img/wn/04d@2x.png" alt="mây đen u ám">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>71%</span>
                                    </div>
                                    <p>mây đen u ám</p>
                                    <div class="temp">
                                        <p>27°/</p>
                                        <p>29°</p>
                                    </div>
                                </div>
                            </a>
                            <a href="/khanh-hoa" class="weather-sub">
                                <h3 class="title">
                                    Khánh Hòa </h3>
                                <img src="https://openweathermap.org/img/wn/04d@2x.png" alt="mây đen u ám">
                                <div class="desc">
                                    <div class="humidity">
                                        <img src="dewpoint.svg" alt="humidity">
                                        <span>71%</span>
                                    </div>
                                    <p>mây đen u ám</p>
                                    <div class="temp">
                                        <p>29°/</p>
                                        <p>33°</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="weather-highlight-live">
                            <div class="title-main">
                                <h2>Bản đồ thời tiết Windy</h2>
                            </div>
                            <div class="fluctuating">
                                <iframe width="100%" height="100%"
                                    src="https://embed.windy.com/embed2.html?lat=21.116671&amp;lon=105.883331&amp;detailLat=21.116671&amp;detailLon=105.883331&amp;width=100%25&amp;height=550&amp;zoom=5&amp;level=surface&amp;overlay=wind&amp;product=ecmwf&amp;menu=&amp;message=true&amp;marker=true&amp;calendar=now&amp;pressure=true&amp;type=map&amp;location=coordinates&amp;detail=&amp;metricWind=default&amp;metricTemp=%C2%B0C&amp;radarRange=-1"
                                    frameborder="0"></iframe>
                            </div>

                        </div>
                    </div>
                </div>
                <img class="weather-highlight-bg" src="banner-bot.png" alt="Thời tiết">
            </section>
            <section class="weather-city">
                <div class="container">
                    <div class="title-main">
                        <h2>Thời tiết 63 tỉnh thành</h2>
                    </div>
                    <ul class="weather-city-inner">
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ha-noi">
                                Hà Nội </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ha-giang">
                                Hà Giang </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/cao-bang">
                                Cao Bằng </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/bac-kan">
                                Bắc Kạn </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/tuyen-quang">
                                Tuyên Quang </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/lao-cai">
                                Lào Cai </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/dien-bien">
                                Điện Biên </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/lai-chau">
                                Lai Châu </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/son-la">
                                Sơn La </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/yen-bai">
                                Yên Bái </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/hoa-binh">
                                Hoà Bình </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/thai-nguyen">
                                Thái Nguyên </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/lang-son">
                                Lạng Sơn </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/quang-ninh">
                                Quảng Ninh </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/bac-giang">
                                Bắc Giang </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/phu-tho">
                                Phú Thọ </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/vinh-phuc">
                                Vĩnh Phúc </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/bac-ninh">
                                Bắc Ninh </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/hai-duong">
                                Hải Dương </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/hai-phong">
                                Hải Phòng </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/hung-yen">
                                Hưng Yên </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/thai-binh">
                                Thái Bình </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ha-nam">
                                Hà Nam </a>
                        </li>
                        <li class="shown">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/nam-dinh">
                                Nam Định </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ninh-binh">
                                Ninh Bình </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/thanh-hoa">
                                Thanh Hóa </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/nghe-an">
                                Nghệ An </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ha-tinh">
                                Hà Tĩnh </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/quang-binh">
                                Quảng Bình </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/quang-tri">
                                Quảng Trị </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/thua-thien-hue">
                                Huế </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/da-nang">
                                Đà Nẵng </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/quang-nam">
                                Quảng Nam </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/quang-ngai">
                                Quảng Ngãi </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/binh-dinh">
                                Bình Định </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/phu-yen">
                                Phú Yên </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/khanh-hoa">
                                Khánh Hòa </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ninh-thuan">
                                Ninh Thuận </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/binh-thuan">
                                Bình Thuận </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/kon-tum">
                                Kon Tum </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/gia-lai">
                                Gia Lai </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/dak-lak">
                                Đắk Lắk </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/dak-nong">
                                Đắk Nông </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/lam-dong">
                                Lâm Đồng </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/binh-phuoc">
                                Bình Phước </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/tay-ninh">
                                Tây Ninh </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/binh-duong">
                                Bình Dương </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/dong-nai">
                                Đồng Nai </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ba-ria-vung-tau">
                                Bà Rịa - Vũng Tàu </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ho-chi-minh">
                                Hồ Chí Minh </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/long-an">
                                Long An </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/tien-giang">
                                Tiền Giang </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ben-tre">
                                Bến Tre </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/tra-vinh">
                                Trà Vinh </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/vinh-long">
                                Vĩnh Long </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/dong-thap">
                                Đồng Tháp </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/an-giang">
                                An Giang </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/kien-giang">
                                Kiên Giang </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/can-tho">
                                Cần Thơ </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/hau-giang">
                                Hậu Giang </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/soc-trang">
                                Sóc Trăng </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/bac-lieu">
                                Bạc Liêu </a>
                        </li>
                        <li style="display: none;">
                            <i class="fal fa-arrow-circle-right"></i>
                            <a href="https://thoitietso.com/ca-mau">
                                Cà Mau </a>
                        </li>
                        <div>
                            <button class="button-primary showMore" type="button">
                                <a href="javascript:void(0);"></a>
                            </button>
                        </div>
                    </ul>
                </div>
            </section>
            <section class="new-highlight">
                <div class="container">
                    <div class="title-main">
                        <h3>Tin tức nổi bật</h3>
                    </div>
                    <div class="new-highlight-inner">
                        <div class="card-post">
                            <a href="https://thoitietso.com/le-hoi-lam-chay-tinh-hoa-van-hoa-lich-su-va-long-nhan-ai-cua-nguoi-dan-chau-thanh-4270.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1739497205_DSC04170-JPG-1708889193.jpg"
                                    alt="Lễ hội Làm Chay: Tinh hoa văn hoá, lịch sử và lòng nhân ái của người dân Châu Thành">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/le-hoi-lam-chay-tinh-hoa-van-hoa-lich-su-va-long-nhan-ai-cua-nguoi-dan-chau-thanh-4270.html">Lễ
                                    hội Làm Chay: Tinh hoa văn hoá, lịch sử và lòng nhân ái của người dân Châu Thành</a>
                            </h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/kien-giang-lot-top-10-diem-den-than-thien-nhat-the-gioi-nam-2025-4258.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1738893045_cam-nang-du-lich-kien-giang-ivivu2.jpg"
                                    alt="Kiên Giang lọt top 10 điểm đến thân thiện nhất thế giới năm 2025">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/kien-giang-lot-top-10-diem-den-than-thien-nhat-the-gioi-nam-2025-4258.html">Kiên
                                    Giang lọt top 10 điểm đến thân thiện nhất thế giới năm 2025</a></h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/thoi-tiet-tay-bac-3112-ly-tuong-cho-hoat-cam-trai-tai-thung-trau-hoa-binh-4231.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1735607343_thung_trau_hoa_binh_7.jpg"
                                    alt="Thời tiết Tây Bắc 31/12: Lý tưởng cho hoạt cắm trại tại Thung Trâu, Hoà Bình">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/thoi-tiet-tay-bac-3112-ly-tuong-cho-hoat-cam-trai-tai-thung-trau-hoa-binh-4231.html">Thời
                                    tiết Tây Bắc 31/12: Lý tưởng cho hoạt cắm trại tại Thung Trâu, Hoà Bình</a></h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/bao-so-10-giat-cap-10-tien-nhanh-ve-vung-khanh-hoa-binh-thuan-4213.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1735007850_bão.webp"
                                    alt="Bão số 10 giật cấp 10 tiến nhanh về vùng Khánh Hoà - Bình Thuận">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/bao-so-10-giat-cap-10-tien-nhanh-ve-vung-khanh-hoa-binh-thuan-4213.html">Bão
                                    số 10 giật cấp 10 tiến nhanh về vùng Khánh Hoà - Bình Thuận</a></h4>
                        </div>

                    </div>
                    <button class="button-primary"><a href="/tin-tuc">Xem thêm</a></button>
                </div>
            </section>
            <section class="new-highlight">
                <div class="container">
                    <div class="title-main">
                        <h3>Tin tức mới</h3>
                    </div>
                    <div class="new-highlight-inner">
                        <div class="card-post">
                            <a href="https://thoitietso.com/du-bao-thoi-tiet-nghe-an-trong-10-ngay-toi-4291.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1740100663_Screenshot 2025-02-21 080749.jpg"
                                    alt="Dự báo thời tiết Nghệ An trong 10 ngày tới">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/du-bao-thoi-tiet-nghe-an-trong-10-ngay-toi-4291.html">Dự
                                    báo thời tiết Nghệ An trong 10 ngày tới</a></h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/thoi-tiet-ngay-2102-mien-bac-troi-ret-kem-mua-phun-va-suong-mu-4290.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1740099650_81-1738212523-suong-mu-5-09543177.jpg.crdownload"
                                    alt="Thời tiết ngày 21/02: Miền Bắc trời rét kèm mưa phùn và sương mù">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/thoi-tiet-ngay-2102-mien-bac-troi-ret-kem-mua-phun-va-suong-mu-4290.html">Thời
                                    tiết ngày 21/02: Miền Bắc trời rét kèm mưa phùn và sương mù</a></h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/mien-bac-sap-don-dot-ret-dam-cham-dut-thoi-ky-nom-am-keo-dai-4289.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1740044875_96-1739992898-1733792259-mien-bac-don-2-dot-kkl-1620-width640height397.jpg"
                                    alt="Miền Bắc sắp đón đợt rét đậm, chấm dứt thời kỳ nồm ẩm kéo dài">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/mien-bac-sap-don-dot-ret-dam-cham-dut-thoi-ky-nom-am-keo-dai-4289.html">Miền
                                    Bắc sắp đón đợt rét đậm, chấm dứt thời kỳ nồm ẩm kéo dài</a></h4>
                        </div>

                        <div class="card-post">
                            <a href="https://thoitietso.com/le-hoi-den-ha-den-thuong-den-y-la-2025-ton-vinh-tin-nguong-tho-mau-va-ban-sac-tuyen-quang-4288.html"
                                class="thumb">
                                <img src="https://thoitietso.com/uploads/news/1740023566_346266_20-2-tonvinh.jpg"
                                    alt="Lễ hội Đền Hạ, Đền Thượng, Đền Ỷ La 2025: Tôn vinh tín ngưỡng thờ Mẫu và bản sắc Tuyên Quang">
                            </a>
                            <h4 class="title"><a
                                    href="https://thoitietso.com/le-hoi-den-ha-den-thuong-den-y-la-2025-ton-vinh-tin-nguong-tho-mau-va-ban-sac-tuyen-quang-4288.html">Lễ
                                    hội Đền Hạ, Đền Thượng, Đền Ỷ La 2025: Tôn vinh tín ngưỡng thờ Mẫu và bản sắc Tuyên
                                    Quang</a></h4>
                        </div>

                    </div>
                </div>
            </section>
        </main>
@endsection