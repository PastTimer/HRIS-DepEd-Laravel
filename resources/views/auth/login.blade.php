
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HRIS System - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 

    <!-- Argon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/nucleo/css/nucleo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/argon.css?v=1.2.0') }}">

    <style>
        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;

            /* PPC Theme Gradient */
            background: linear-gradient(135deg, #0b3c5d, #328cc1, #d9f1ff);
            position: relative;
            overflow: hidden;
        }

        /* Background Logo Watermark */
        body::before {
            content: "";
            position: absolute;
            width: 1000px;
            height: 1000px;
            background: url('{{ asset('assets/img/brand/ppc.png') }}') no-repeat center;
            background-size: contain;
            opacity: 0.06;
            top: 60%;
            left: 85%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .login-container {
            margin-top: 8vh;
            position: relative;
            z-index: 1;
        }

        .login-card {
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border: none;
            background: #ffffff;
        }

        .header-title {
            font-weight: 600;
            color: #0b3c5d;
        }

        .sub-text {
            color: #6c757d;
        }

        .form-control {
            border-radius: 8px;
            height: 45px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #328cc1;
            box-shadow: 0 0 0 2px rgba(50,140,193,0.2);
        }

        .btn-primary {
            border-radius: 8px;
            background-color: #0b3c5d;
            border: none;
            height: 45px;
            font-weight: 500;
            transition: 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #06283d;
        }

        .logo {
            max-width: 85px;
            margin-bottom: 10px;
        }

        .footer-text {
            font-size: 13px;
            color: #e9ecef;
        }
    </style>
</head>

<body>

    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">

                <div class="card ppc-card">
                    <div class="card-body px-lg-5 py-lg-5">

                        <!-- Header -->
                        <div class="text-center mb-4">
                            <img src="{{ asset('assets/img/brand/deped2.png') }}" class="logo">
                            <h3 class="header-title mb-1">Human Resource Information System</h3>
                            <small class="sub-text">DepEd Puerto Princesa</small>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="/login">
                            @csrf 

                            @if($errors->any())
                                <div class="alert alert-danger text-center">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="form-group mb-3">
                                <input class="form-control" name="username"
                                       placeholder="Username or Employee ID"
                                       type="text"
                                       value="{{ old('username') }}"
                                       required autofocus>
                            </div>

                            <div class="form-group mb-3">
                                <input class="form-control" name="password"
                                       placeholder="Password"
                                       type="password"
                                       required>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary w-100">
                                    Sign in
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-3">
                    <small class="footer-text">
                        © {{ date('Y') }} Department of Education, Puerto Princesa
                    </small>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
```
