<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nazura Group</title>
    <link href="{{ asset('assets/img/logo-nazura-favicon.png') }}" rel="icon">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('templates/dist/css/adminlte.min.css') }}">

    <style>
        .overlay::before {
            content: "";
            display: block;
            position: absolute;
            z-index: -1;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: #09B2E5;
            background: -webkit-linear-gradient(bottom, #0284c7, #09B2E5);
            background: -o-linear-gradient(bottom, #0284c7, #09B2E5);
            background: -moz-linear-gradient(bottom, #0284c7, #09B2E5);
            background: linear-gradient(to top, #0284c7, #09B2E5);

            opacity: 0.9;
        }

        .form-control-icon {
            position: relative;
        }

        .form-control-icon i {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 13px;
            pointer-events: none;
        }

        .form-control-icon input.form-control {
            padding-left: 2.5rem;
        }

        .form-group.is-invalid .invalid-feedback {
            display: block;
        }

        .form-control,
        .btn {
            border-radius: 50rem !important;
        }

        .input-filled {
            font-size: 1rem;
            font-weight: 600;
            color: #242424;
        }

        .login-box {
            width: 450px;
            max-width: 90%;
            font-size: 1rem;

        }

        ::placeholder {
            font-size: 1rem;
            font-weight: 600;
            color: #999999;
            opacity: 1;
        }

        .btn-login {
            background-color: #333333;
            /* Gradasi biru awal */
            color: white;
            font-weight: bold;
            border-radius: 50rem;
            transition: background 0.3s ease;
            border: none;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #0056b3, #00aaff);
            /* Gradasi saat hover */
            color: white;
        }
    </style>

</head>

<body class="hold-transition overlay login-page"
    style="background-image: url('{{ asset('assets/img/img-01.jpg') }}');
">
    <div class="login-box bg-white p-4" style="border-radius: 20px">
        <div class=" text-center d-col items-center justify-content-center">
            <img src="{{ asset('assets/img/logo-nazura.png') }}" alt="Properti Logo" style="max-width: 110px; height: auto" class="logo-image mb-2"
                style="width: 120px; height: 120px;">
            <h2 style="font-size: 25px; color: #333333;"><b>Nazura Group</b></h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.loginPost') }}" id="formLogin">
                @csrf

                {{-- Username --}}
                <div class="form-group mb-2">
                    <div class="form-control-icon">
                        <i class="fas fa-user fa-sm"></i>
                        <input type="text"
                            class="form-control form-control-lg w-100"
                            name="username" id="username" placeholder="Username">
                    </div>
                    <span class="invalid-feedback" role="alert" id="username-error"></span>
                </div>

                {{-- Password --}}
                <div class="form-group mb-2">
                    <div class="form-control-icon">
                        <i class="fas fa-lock fa-sm"></i>
                        <input type="password"
                            class="form-control form-control-lg w-100"
                            name="password" id="password" placeholder="Password">
                    </div>
                    <span class="invalid-feedback" role="alert" id="password-error"></span>
                </div>

                {{-- Button --}}
                <div class="form-group">
                    <button type="submit" class="btn btn-lg w-100 btn-login" id="submitBtn">
                        <span class="spinner-border spinner-border-sm mx-1 d-none" role="status" aria-hidden="true"></span>
                        <span class="button-text font-weight-bold">Login</span>
                    </button>
                </div>
            </form>
        </div>
        <!-- /.card-body -->
        <!-- /.card -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('templates/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('templates/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('templates/dist/js/adminlte.min.js') }}"></script>
    <script>
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        $(document).ready(function() {
            function refreshCsrfToken(callback) {
                $.get('{{ route('refresh.csrf') }}', function(data) {
                    $('meta[name="csrf-token"]').attr('content', data.token);
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': data.token
                        }
                    });
                    if (typeof callback === 'function') callback();
                });
            }

            $('#formLogin').on('submit', function(e) {
                e.preventDefault();

                let form = this;

                refreshCsrfToken(function() {
                    let url = '{{ route('admin.loginPost') }}';
                    let formData = new FormData(form);

                    $('.is-invalid').removeClass('is-invalid');
                    $('.form-group.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').empty();

                    let submitBtn = $('#submitBtn');
                    let spinner = submitBtn.find('.spinner-border');
                    let btnText = submitBtn.find('.button-text');

                    spinner.removeClass('d-none');
                    btnText.text('Masuk...');
                    submitBtn.prop('disabled', true);

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function() {
                            window.location.href = "{{ route('dashboard') }}";
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                audio.play();
                                let errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, val) {
                                    let input = $('#' + key);
                                    input.addClass('is-invalid');
                                    input.closest('.form-group').addClass('is-invalid');
                                    $('#' + key + '-error').html(
                                        '<strong>' + val[0] + '</strong>'
                                    );
                                });
                            } else {
                                alert(
                                'Terjadi kesalahan pada server. Silakan coba lagi.'
                                );
                            }
                        },
                        complete: function() {
                            spinner.addClass('d-none');
                            btnText.text('Login');
                            submitBtn.prop('disabled', false);
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>
