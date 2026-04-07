<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HRIS System - @yield('title')</title>
    
    <link rel="stylesheet" href="{{ asset('assets/vendor/nucleo/css/nucleo.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}" type="text/css">
    
    <link rel="stylesheet" href="{{ asset('assets/css/argon.css?v=1.2.0') }}" type="text/css">
</head>
<style>
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