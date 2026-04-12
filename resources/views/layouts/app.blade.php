<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HRIS System - @yield('title')</title>
    
    <link rel="stylesheet" href="{{ asset('assets/vendor/nucleo/css/nucleo.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}" type="text/css">
    
    <link rel="stylesheet" href="{{ asset('assets/css/argon.css?v=1.2.0') }}" type="text/css">
    <style>
        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            /* PPC Theme Gradient (GLOBAL) */
            background: linear-gradient(135deg, #0b3c5d, #328cc1, #d9f1ff);
            position: relative;
            overflow-x: hidden;
        }

        /* GLOBAL WATERMARK */
        body::before {
            content: "";
            position: fixed;
            width: 600px;
            height: 600px;
            background: url('{{ asset('assets/img/brand/ppc.png') }}') no-repeat center;
            background-size: contain;
            opacity: 0.05;
            top: 70%;
            left: 80%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        /* CONTENT ABOVE BACKGROUND */
        .main-content {
            position: relative;
            z-index: 1;
        }

        /* STANDARD CARD STYLE (reuse everywhere) */
        .ppc-card {
            border-radius: 14px;
            /* Enhanced multi-layer shadow for depth */
            box-shadow:
                0 2px 8px rgba(44, 62, 80, 0.10),
                0 8px 30px rgba(0,0,0,0.18),
                0 1.5px 6px rgba(50,140,193,0.10);
            border: none;
            background: #ffffff;
            transition: box-shadow 0.2s;
        }

        /* Optional: Add a hover effect for even more depth */
        .ppc-card:hover {
            box-shadow:
                0 6px 24px rgba(44, 62, 80, 0.13),
                0 16px 48px rgba(0,0,0,0.22),
                0 3px 12px rgba(50,140,193,0.13);
        }

        @media (min-width: 1200px) {
            .main-content {
                margin-left: 250px !important;
            }
        }

        [data-ajax-content].is-loading {
            opacity: 0.65;
            pointer-events: none;
            transition: opacity 0.15s ease-in-out;
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    
    <div class="main-content" id="panel">
        
        <nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom">
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <h6 class="h2 text-white d-inline-block mb-0">@yield('title')</h6>
                </div>
            </div>
        </nav>

        @yield('content')
        
    </div>

    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/argon.js?v=1.2.0') }}"></script>
    <script>
        (function () {
            var debounceMap = new WeakMap();
            var activeRequest = null;

            function buildUrlFromForm(form) {
                var action = form.getAttribute('action') || window.location.pathname;
                var url = new URL(action, window.location.origin);
                var params = new URLSearchParams(new FormData(form));
                params.set('page', '1');
                url.search = params.toString();
                return url.toString();
            }

            function swapAjaxContent(targetUrl, sourceElement) {
                var current = sourceElement.closest('[data-ajax-content]') || document.querySelector('[data-ajax-content]');
                if (!current) {
                    window.location.href = targetUrl;
                    return;
                }

                if (activeRequest) {
                    activeRequest.abort();
                }

                var controller = new AbortController();
                activeRequest = controller;
                current.classList.add('is-loading');

                fetch(targetUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var incoming = doc.querySelector('[data-ajax-content]');

                        if (!incoming) {
                            window.location.href = targetUrl;
                            return;
                        }

                        current.replaceWith(incoming);

                        if (window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', targetUrl);
                        }
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            console.error('AJAX search failed:', error);
                            window.location.href = targetUrl;
                        }
                    })
                    .finally(function () {
                        var updated = document.querySelector('[data-ajax-content]');
                        if (updated) {
                            updated.classList.remove('is-loading');
                        }
                    });
            }

            document.addEventListener('submit', function (event) {
                var form = event.target.closest('form[data-ajax-search-form]');
                if (!form) {
                    return;
                }

                event.preventDefault();
                swapAjaxContent(buildUrlFromForm(form), form);
            });

            document.addEventListener('input', function (event) {
                var input = event.target.closest('form[data-ajax-search-form] input[name="search"]');
                if (!input || !input.form) {
                    return;
                }

                var existingTimer = debounceMap.get(input.form);
                if (existingTimer) {
                    clearTimeout(existingTimer);
                }

                var timer = setTimeout(function () {
                    swapAjaxContent(buildUrlFromForm(input.form), input.form);
                }, 750); // Delay of 0.75 seconds after user stops typing

                debounceMap.set(input.form, timer);
            });

            document.addEventListener('click', function (event) {
                var link = event.target.closest('[data-ajax-content] .pagination a, a[data-ajax-clear-search]');
                if (!link) {
                    return;
                }

                event.preventDefault();
                swapAjaxContent(link.href, link);
            });
        })();
    </script>
</body>
</html>