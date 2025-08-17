
        var otherpop = "";
        var otherpopmax = 1;

        var banner_preload  = [];
        var catfish_bottom  = [];
        var catfish_top  = [];

        function setVCookie(key, value, date) {
            if (!date) {
                date = 31536000000;
            }
            var expires = new Date();
            expires.setTime(expires.getTime() + date);
            document.cookie = key + '=' + value + '; path=/; expires=' + expires.toUTCString();
        }

       function getVCookie(key) {
            var keyValue = document.cookie.match('(^|;)(?: )?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : null;
        }

        var _c0 = getVCookie('adx');
        var _c1 = getVCookie('adx22');

        var hasPop = !(_c1 == undefined || _c1 == null || _c1 == 0);

        // HTML generation functions
        var html = function(a) {
            return '<div class="banner-preload hidden">' +
                '<div class="banner-preload-container">' +
                '<a href="' + a[1] + '" target="_blank" rel="nofollow" data-wpel-link="external">' +
                '<img id="cc" src="' + a[0] + '">' +
                '</a>' +
                '<div class="banner-preload-close">' + ((otherpopmax > 0 && (_c1 == undefined || _c1 == null || (_c1 && _c1 < otherpopmax))) ? '<a id="bb" href="' + otherpop + '" target="_blank" rel="nofollow" data-wpel-link="external">X</a>' : 'X') + '</div>' +
                '</div>' +
                '</div>'
        };


        var codeMobileAdx = function() {
        (function() {
            var x = document.createElement('link');
            x.setAttribute('rel', 'stylesheet');
            x.setAttribute('href', 'http://localhost/assets/adv/mobile-adx.css');
            document.head.append(x);
        })();

        if (banner_preload.length && (_c0 < 3)) {
            $('body').append(html(banner_preload[(_c0 - 0) % banner_preload.length]));
            $('.banner-preload').removeClass('hidden');
            $('.banner-preload-close').click(function(e) {
                if (!$(e.target).is('#cc') && !(e.clientX == 0 && e.clientY == 0))
                    $('.banner-preload').addClass('hidden');
                setVCookie('adx', _c0 - (-1), 86400000);
                if (otherpopmax > 0 && (_c1 == undefined || _c1 == null || (_c1 && _c1 < otherpopmax))) setVCookie('adx22', (_c1 ? _c1 : 0) - 0 + 1, 86400000);
            });
            $('.banner-preload-container').click(function(e) {
                if ($(e.target).is('.banner-preload-container')) {
                    if (!hasPop) {
                        var clickEvent = new MouseEvent('click', {
                            bubbles: true,
                            cancellable: true
                        });
                        document.getElementById('bb') && document.getElementById('bb').dispatchEvent(clickEvent);
                        hasPop = true;
                        $('.banner-preload-close').html('X');
                    } else {
                        $('.banner-preload').addClass('hidden');
                        setVCookie('adx', _c0 - (-1), 86400000);
                    }
                }
            });
        }

        var _c02 = getVCookie('adx2');
        var html2 = function(a) {
            var n = '<div class="catfish-bottom hidden">';
            for (var i in a) {
                n += '<a href="' + a[i][1] + '" target="_blank" rel="nofollow" data-wpel-link="external">' +
                    '<img id="cc" src="' + a[i][0] + '">' +
                    '</a>'
            }
            n += '<div class="catfish-bottom-close">X</div>' +
                '</div>';
            return n;
        };

        if (catfish_bottom.length && (_c02 < 2)) {
            $('body').append(html2(catfish_bottom[(_c02 - 0) % catfish_bottom.length]));
            $('.catfish-bottom').removeClass('hidden');
            $('.catfish-bottom-close').click(function(e) {
                $('.catfish-bottom').addClass('hidden');
                setVCookie('adx2', _c02 - (-1), 86400000);
            });
        }

        var _c03 = getVCookie('adx3');
        var html3 = function(a) {
            var n = '<div class="catfish-top hidden">';
            for (var i in a) {
                n += '<a href="' + a[i][1] + '" target="_blank" rel="nofollow" data-wpel-link="external">' +
                    '<img id="cc" src="' + a[i][0] + '">' +
                    '</a>'
            }
            n += '<div class="catfish-top-close">X</div>' +
                '</div>';
            return n;
        };

        if (catfish_top.length && (_c03 < 2)) {
            $('body').append(html3(catfish_top[(_c03 - 0) % catfish_top.length]));
            $('.catfish-top').removeClass('hidden');
            $('.catfish-top-close').click(function(e) {
                $('.catfish-top').addClass('hidden');
                setVCookie('adx3', _c03 - (-1), 86400000);
            });
        }
    };

    $(document).ready(function() {
        codeMobileAdx();
    });

    