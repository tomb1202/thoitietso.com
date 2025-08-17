
        var bannerAdv = function() {
            (function() {
                var x = document.getElementById('vl-header-adx');
                x.style.margin = '0 5px';
                x.style.textAlign = 'center';

                var htmlSmallScreen = `
                    
                `;

                var htmlLargeScreen = `
                    
                `;

                x.innerHTML = window.innerWidth < 300 ? '' : window.innerWidth < 768 ? htmlSmallScreen : htmlLargeScreen;
            })();
        }

        window.onload = function() {
            bannerAdv();
        };
        