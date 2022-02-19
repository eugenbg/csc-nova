<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>@yield('title')</title>

        <link href="https://fonts.googleapis.com/css2?family=Jost&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/owl.carousel.min.css">
        <link rel="stylesheet" href="/css/owl.theme.default.min.css">
        <link rel="stylesheet" href="/css/jquery.fancybox.min.css">
        <link rel="stylesheet" href="/fonts/icomoon/style.css">
        <link rel="stylesheet" href="/fonts/flaticon/font/flaticon.css">
        <link rel="stylesheet" href="/css/aos.css">
        <link rel="stylesheet" href="/css/style.css">

    </head>
    <body>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(84037354, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/84037354" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

    <div class="site-mobile-menu site-navbar-target">
        <div class="site-mobile-menu-header">
            <div class="site-mobile-menu-close">
                <span class="icofont-close js-menu-toggle"></span>
            </div>
        </div>
        <div class="site-mobile-menu-body"></div>
    </div>

    <div class="container">
        <nav class="site-nav">
            <div class="row justify-content-between align-items-center">
                <div class="d-none d-lg-block col-lg-3 top-menu">
                    {{--<a href="#" class="d-inline-flex align-items-center"><span class="icon-lock mr-2"></span><span>Sign In</span></a>--}}
                </div>
                <div class="col-3 col-md-6 col-lg-6 text-lg-center logo">
                    <a href="/">VmWare Walk Throughs<span class="text-primary">.</span> </a>
                </div>
                <div class="col-9 col-md-6 col-lg-3 text-right top-menu">
                    <div class="d-inline-flex align-items-center">
                        <div class="search-wrap">
                            <a href="#" class="d-inline-flex align-items-center js-search-toggle"><span class="icon-search2 mr-2"></span><span>Search</span></a>

                            <form action="/search" class="d-flex">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <input name="query" type="search" id="s" class="form-control" placeholder="Enter search term and hit enter...">
                            </form>
                        </div>

                        <span class="mx-2 d-inline-block d-lg-none"></span>
                        <a href="#" class="d-inline-flex align-items-center d-inline-block d-lg-none"><span class="icon-lock mr-2"></span><span>Sign In</span></a>

                        <a href="#" class="burger ml-3 site-menu-toggle js-menu-toggle d-inline-block d-lg-none" data-toggle="collapse" data-target="#main-navbar">
                            <span></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="d-none d-lg-block row align-items-center pt-5 pb-1">


                <div class="col-12 col-sm-12 col-lg-12 site-navigation text-center">
                    <x-menu-component/>
                </div>

            </div>
        </nav> <!-- END nav -->


        @if($message = \Illuminate\Support\Facades\Session::get("success"))
            <h3 class="alert alert-success text-center">{{$message}}</h3>
        @endif

    </div> <!-- END container -->

    {{ $slot }}

    <div class="site-footer">
        <div class="container">
            <div class="row justify-content-center copyright">

                <div class="col-lg-7 text-center">
                    <div class="widget">
                        <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved</p>

                        <div class="d-block">
                            <a href="/disclaimers" class="m-2">Disclaimers</a>/
                            <a href="/privacy-policy" class="m-2">Privacy Policy</a>/
                            <a href="/about-us" class="m-2">About Us</a>
                        </div>
                    </div>

                </div>


            </div>
        </div>

        <div id="overlayer"></div>
        <div class="loader">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <script src="/js/jquery-3.4.1.min.js"></script>
        <script src="/js/popper.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/owl.carousel.min.js"></script>
        <script src="/js/aos.js"></script>
        <script src="/js/jquery.animateNumber.min.js"></script>
        <script src="/js/jquery.waypoints.min.js"></script>
        <script src="/js/jquery.fancybox.min.js"></script>
        <script src="/js/aos.js"></script>
        <script src="/js/custom.js"></script>

    </body>
</html>
