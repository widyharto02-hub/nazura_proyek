<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nazura Group</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/img/logo-nazura-favicon.png') }}" rel="icon">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('templates/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/jqvmap/jqvmap.min.') }}css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('templates/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/summernote/summernote-bs4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('templates/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/toastr/toastr.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    @stack('css')

    <style>
        .modal-body {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">


    <div class="wrapper">

        <!-- Preloader -->
        {{-- <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ asset('templates/img/LOGO_ICON_BEASISWA.png') }}" alt="AdminLTELogo"
                height="60" width="60">
        </div> --}}


        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light bg-warning">

            <!-- Left navbar links: Tanda Panah Kembali -->
            <ul class="navbar-nav d-flex align-items-center" style="margin-left: 5px;">
                <li class="nav-item">
                    <a class="nav-link text-white" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto mr-3 d-flex align-items-center">
                <!-- User Profile Dropdown -->
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center text-white">
                        <i class="fas fa-user-circle fa-2x mr-2 text-white"></i>
                        <span class="text-white">{{ Auth::user()->username }}</span>
                    </a>
                </li>
            </ul>
        </nav>




        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <div class="brand-link" style="cursor: default;">
                <img src="{{ asset('assets/img/logo-nazura.png') }}" alt="AdminLTE Logo"
                    class="brand-image" style="height: 35px;">
                <span class="brand-text font-weight-light"><strong>Nazura Group</strong></span>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        @php
                            use Illuminate\Support\Str;
                            $userId = \Illuminate\Support\Facades\Auth::id();
                            $allowedMenuIds = \App\Models\HakAkses::where('id_user', $userId)
                                ->where('lihat', 1)
                                ->pluck('id_menu')
                                ->toArray();
                            $canRenderMenuRoute = function ($menuItem) {
                                if (($menuItem->jenis_menu ?? 'Internal') !== 'Internal') {
                                    return true;
                                }

                                if (empty($menuItem->url) || ($menuItem->url ?? '#') === '#') {
                                    return true;
                                }

                                if (!\Illuminate\Support\Facades\Route::has($menuItem->url)) {
                                    return false;
                                }

                                $route = app('router')->getRoutes()->getByName($menuItem->url);

                                return $route ? count($route->parameterNames()) === 0 : false;
                            };

                            $hiddenTitles = ['op bangunan', 'op jalan', 'op saluran', 'st bangunan'];
                            $hiddenPrefixes = [
                                'proyek-bangunan.',
                                'jenis-pekerjaan-bangunan.',
                                'proyek-jalan.',
                                'jalan.',
                                'jenis-pekerjaan-jalan.',
                                'proyek-saluran.',
                                'saluran.',
                                'jenis-pekerjaan-saluran.',
                                'st-bangunan.',
                            ];

                            $menus = \App\Models\Menu::where('id_parent', 0)
                                ->whereIn('id', $allowedMenuIds)
                                ->orderBy('urutan')
                                ->with([
                                    'children' => function ($query) use ($allowedMenuIds) {
                                        $query->whereIn('id', $allowedMenuIds)->orderBy('urutan');
                                    },
                                ])
                                ->get()
                                ->reject(function ($menu) use ($hiddenTitles, $hiddenPrefixes) {
                                    $title = Str::lower(trim((string) $menu->title));
                                    $url = Str::lower(trim((string) $menu->url));

                                    return in_array($title, $hiddenTitles, true) || Str::startsWith($url, $hiddenPrefixes);
                                })
                                ->map(function ($menu) use ($hiddenTitles, $hiddenPrefixes) {
                                    $children = collect($menu->children ?? [])
                                        ->reject(function ($child) use ($hiddenTitles, $hiddenPrefixes) {
                                            $title = Str::lower(trim((string) $child->title));
                                            $url = Str::lower(trim((string) $child->url));

                                            return in_array($title, $hiddenTitles, true) || Str::startsWith($url, $hiddenPrefixes);
                                        })
                                        ->values();

                                    $menu->setRelation('children', $children);

                                    return $menu;
                                })
                                ->reject(function ($menu) use ($canRenderMenuRoute) {
                                    return $menu->children->isEmpty() &&
                                        ((($menu->url ?? '#') === '#' || empty($menu->url)) || !$canRenderMenuRoute($menu));
                                })
                                ->values();
                            @endphp

                        @foreach ($menus as $menu)
                            @php
                                $menuHref =
                                    $menu->jenis_menu == 'Internal'
                                        ? ((empty($menu->url) || ($menu->url ?? '#') === '#')
                                            ? '#'
                                            : ($canRenderMenuRoute($menu)
                                            ? route($menu->url)
                                            : '#'))
                                        : url($menu->url);
                            @endphp
                            @php
                                $isActiveParent = false;
                                foreach ($menu->children as $child) {
                                    if (
                                        request()->routeIs($child->url . '*') ||
                                        Str::before(request()->route()->getName(), '.') ===
                                            Str::before($child->url, '.')
                                    ) {
                                        $isActiveParent = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if ($menu->children->isEmpty())
                                @php
                                    $isActiveSingle =
                                        request()->routeIs($menu->url . '*') ||
                                        Str::before(request()->route()->getName(), '.') ===
                                            Str::before($menu->url, '.');
                                @endphp
                                <li class="nav-item">
                                    <a href="{{ $menuHref }}"
                                        class="nav-link {{ $isActiveSingle ? '' : '' }}"
                                        target="{{ $menu->jenis_menu == 'Eksternal' ? '_blank' : '_self' }}"
                                        style="{{ $isActiveSingle ? 'background-color: #ffc107 !important; color: #ffffff !important;' : '' }}">
                                        <i class="nav-icon fas {{ $menu->icon }}"
                                            style="{{ $isActiveSingle ? 'color: #ffffff !important;' : '' }}"></i>
                                        <p style="{{ $isActiveSingle ? 'color: #ffffff !important;' : '' }}">
                                            {{ $menu->title }}</p>
                                    </a>
                                </li>
                            @else
                                <li class="nav-item {{ $isActiveParent ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $isActiveParent ? 'active' : '' }}"
                                        style="{{ $isActiveParent ? 'background-color: #ffc107 !important; color: #ffffff !important;' : '' }}">
                                        <i class="nav-icon fas {{ $menu->icon }}"
                                            style="{{ $isActiveParent ? 'color: #ffffff !important;' : '' }}"></i>
                                        <p style="{{ $isActiveParent ? 'color: #ffffff !important;' : '' }}">
                                            {{ $menu->title }}
                                            <i class="right fas fa-angle-left"
                                                style="{{ $isActiveParent ? 'color: #ffffff !important;' : '' }}"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @foreach ($menu->children as $child)
                                            @php
                                                $childHref =
                                                    $child->jenis_menu == 'Internal'
                                                        ? ((empty($child->url) || ($child->url ?? '#') === '#')
                                                            ? '#'
                                                            : ($canRenderMenuRoute($child)
                                                            ? route($child->url)
                                                            : '#'))
                                                        : url($child->url);
                                                $isActiveChild =
                                                    request()->routeIs($child->url . '*') ||
                                                    Str::before(request()->route()->getName(), '.') ===
                                                        Str::before($child->url, '.');
                                            @endphp
                                            <li class="nav-item">
                                                <a href="{{ $childHref }}"
                                                    class="nav-link {{ $isActiveChild ? 'active' : '' }}"
                                                    target="{{ $child->jenis_menu == 'Eksternal' ? '_blank' : '_self' }}">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p>{{ $child->title }}</p>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach

                        <li class="nav-item">
                            <a href="#" class="nav-link" onclick="logoutConfirm(event)">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>

                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        @yield('content')

        <!-- /.content-wrapper -->
        <footer class="main-footer text-center">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#" class="text-gray">BMS</a>.</strong>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('templates/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('templates/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('templates/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('templates/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('templates/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- Sparkline -->
    <script src="{{ asset('templates/plugins/sparklines/sparkline.js') }}"></script>
    <!-- JQVMap -->
    <script src="{{ asset('templates/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('templates/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('templates/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('templates/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('templates/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('templates/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('templates/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('templates/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <!-- Page specific script -->
    <!-- AdminLTE App -->
    <script src="{{ asset('templates/dist/js/adminlte.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    {{-- <script src="{{ asset('templates/dist/js/demo.js') }}"></script> --}}
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{ asset('templates/dist/js/pages/dashboard.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('templates/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Toastr -->
    <script src="{{ asset('templates/plugins/toastr/toastr.min.js') }}"></script>
    <!-- bs-custom-file-input -->
    <script src="{{ asset('templates/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <!-- Summernote -->
    <script src="{{ asset('templates/plugins/summernote/summernote-bs4.min.js') }}"></script>


    <script>
        var audio = new Audio('{{ asset('audio/notification.ogg') }}');

        function formatNumber(value) {
            return value ? value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
        }

        function unformatNumber(value) {
            return value ? parseInt(value.replace(/\./g, '')) : 0;
        }

        $(document).on('input', '.format-number', function() {
            let input = $(this).val().replace(/[^\d]/g, '');
            let formatted = input.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            $(this).val(formatted);
        });

        function previewFile(inputId, previewId) {
            $('#' + inputId).on('change', function() {
                const file = this.files[0];
                const previewDiv = $('#' + previewId);

                if (!file) {
                    previewDiv.html('<span style="color:#6c757d;">Tidak ada berkas</span>');
                    return;
                }

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewDiv.html(`
                    <img src="${e.target.result}" style="max-width:100%; max-height:100%;">
                `);
                    };
                    reader.readAsDataURL(file);
                    return;
                }

                if (file.type === 'application/pdf') {
                    const pdfUrl = URL.createObjectURL(file);

                    previewDiv.html(`
                <div class="d-flex justify-content-center align-items-center h-100">
                    <a href="${pdfUrl}" target="_blank">
                        Lihat Berkas
                    </a>
                </div>
            `);
                    return;
                }

                previewDiv.html('<span style="color:#6c757d;">Tidak ada berkas</span>');
            });
        }

        function setPreview(fileName, folder, previewId) {
            const preview = $('#' + previewId);

            if (!fileName) {
                preview.html('<span style="color:#6c757d;">Tidak ada berkas</span>');
                return;
            }

            const fileUrl = '/' + folder + '/' + fileName;
            const ext = fileName.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                preview.html(`
            <img src="${fileUrl}" style="max-width:100%; max-height:100%;">
        `);
                return;
            }

            if (ext === 'pdf') {
                preview.html(`
            <div class="d-flex justify-content-center align-items-center h-100">
                <a href="${fileUrl}" target="_blank">Lihat Berkas</a>
            </div>
        `);
                return;
            }

            preview.html('<span style="color:#6c757d;">Tidak ada berkas</span>');
        }


        $(document).on('click', '.btn-lampiran', function() {
            let file = $(this).data('file');
            let ext = file.split('.').pop().toLowerCase();

            if (ext === 'pdf') {
                $('#modalLampiran .modal-dialog')
                    .removeClass('modal-sm')
                    .addClass('modal-lg');

                $('#modalLampiran .modal-body').html(`
                    <div class="text-center">
                        <iframe src="${file}" class="w-100" style="height:80vh;"></iframe>
                    </div>
                `);
            } else {
                $('#modalLampiran .modal-dialog')
                    .removeClass('modal-lg')
                    .addClass('modal-dialog-centered modal-dialog-scrollable');

                $('#modalLampiran .modal-body').html(`
                    <div class="d-flex justify-content-center">
                        <img src="${file}" class="img-fluid" style="max-height:90vh; object-fit:contain;">
                    </div>
                `);
            }

            $('#modalLampiran').modal('show');
        });

        function logoutConfirm(event) {
            event.preventDefault();

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Logout!</span>',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {

                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML =
                            `<span class="spinner-border spinner-border-sm mx-2" role="status"></span> Logging out...`;
                        confirmBtn.disabled = true;

                        fetch('{{ route('refresh.csrf') }}')
                            .then(response => response.json())
                            .then(data => {
                                let form = document.getElementById('logout-form');
                                let tokenField = form.querySelector('input[name="_token"]');

                                if (!tokenField) {
                                    tokenField = document.createElement('input');
                                    tokenField.type = 'hidden';
                                    tokenField.name = '_token';
                                    form.appendChild(tokenField);
                                }

                                tokenField.value = data.token;

                                resolve();

                                form.submit();
                            })
                            .catch(() => {
                                confirmBtn.disabled = false;
                                btnText.innerHTML = `Ya, Logout!`;

                                Swal.showValidationMessage(
                                    `Gagal menghubungi server. Coba lagi.`
                                );
                            });
                    });
                }
            });
        }

        $(document).ready(function() {
            const successMsg = sessionStorage.getItem('success');
            if (successMsg) {
                audioSuccess.play();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMsg,
                    showConfirmButton: false,
                    timer: 2000
                })
                sessionStorage.removeItem('success');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
