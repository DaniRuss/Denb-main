<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', \App\Models\SiteSetting::get('site_title', 'Addis Ababa Law Enforcement Portal'))</title>
    <meta name="description"
        content="@yield('description', \App\Models\SiteSetting::get('meta_description', 'Official portal for submitting complaints and anonymous tips to Addis Ababa Law Enforcement Authority'))">
    <meta name="keywords"
        content="{{ \App\Models\SiteSetting::get('meta_keywords', 'law enforcement, complaints, tips') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title"
        content="@yield('title', \App\Models\SiteSetting::get('site_title', 'Addis Ababa Law Enforcement Portal'))">
    <meta property="og:description"
        content="@yield('description', \App\Models\SiteSetting::get('meta_description', 'Official portal for submitting complaints and anonymous tips'))">
    @php $ogImage = \App\Models\SiteSetting::get('og_image'); @endphp
    @if($ogImage)
        <meta property="og:image" content="{{ asset('storage/' . $ogImage) }}">
    @endif

    <!-- Favicons -->
    @php $favicon = \App\Models\SiteSetting::get('favicon'); @endphp
    @if($favicon)
        <link href="{{ asset('storage/' . $favicon) }}" rel="icon">
    @else
        <link href="{{ asset('favicon.ico') }}" rel="icon">
    @endif

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="{{ asset('arsha/Arsha/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('arsha/Arsha/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('arsha/Arsha/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('arsha/Arsha/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('arsha/Arsha/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Arsha Main CSS -->
    <link href="{{ asset('arsha/Arsha/assets/css/main.css') }}" rel="stylesheet">

    <!-- Custom Portal CSS -->
    <style>
        :root {
            --portal-primary:
                {{ \App\Models\SiteSetting::get('primary_color', '#1e3a5f') }}
            ;
            --portal-accent:
                {{ \App\Models\SiteSetting::get('accent_color', '#e8b84b') }}
            ;
            --portal-secondary:
                {{ \App\Models\SiteSetting::get('secondary_color', '#6c757d') }}
            ;
            @php
                function hexToRgb($hex)
                {
                    $hex = str_replace('#', '', $hex);
                    if (strlen($hex) == 3) {
                        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
                    } else {
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                    }
                    return "$r, $g, $b";
                }
                $primaryHex = \App\Models\SiteSetting::get('primary_color', '#1e3a5f');
                $accentHex = \App\Models\SiteSetting::get('accent_color', '#e8b84b');
            @endphp
            --portal-primary-rgb:
                {{ hexToRgb($primaryHex) }}
            ;
            --portal-accent-rgb:
                {{ hexToRgb($accentHex) }}
            ;
        }

        .navbar-brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .navbar-brand-sub {
            font-size: 0.72rem;
            font-weight: 400;
            opacity: 0.85;
        }

        .badge-amharic {
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        .header {
            padding: 10px 0;
            transition: all 0.5s;
        }

        .alert-session {
            border-left: 4px solid var(--portal-accent);
        }

        /* Premium Luxury Breadcrumb Banner */
        .breadcrumb-portal {
            background: linear-gradient(135deg, #09152b 0%, #173a65 50%, #0d223d 100%);
            padding: 130px 0 40px;
            /* Reduced padding from 160px top, 60px bottom */
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 -10px 30px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .breadcrumb-portal::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(255, 255, 255, 0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .breadcrumb-portal h2 {
            color: white;
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: 0.5px;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.6), 0 0 25px rgba(255, 255, 255, 0.1);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .breadcrumb-portal h2 i {
            color: #d4af37;
            /* Metallic Gold */
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
            margin-right: 15px !important;
        }

        /* Glassmorphism Breadcrumb Container */
        .breadcrumb-portal .breadcrumb {
            display: inline-flex;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            padding: 10px 25px;
            margin-bottom: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .breadcrumb-portal .breadcrumb-item {
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .breadcrumb-portal .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .breadcrumb-portal .breadcrumb-item a:hover {
            color: white;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.6);
        }

        /* Active Text & Glowing Underline */
        .breadcrumb-portal .breadcrumb-item.active {
            color: white;
            position: relative;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        }

        .breadcrumb-portal .breadcrumb-item.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #4facfe;
            /* Glowing blue line */
            box-shadow: 0 0 8px #4facfe, 0 0 15px #4facfe;
            border-radius: 2px;
        }

        /* Custom Separator */
        .breadcrumb-portal .breadcrumb-item+.breadcrumb-item::before {
            content: '›';
            font-family: inherit;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.3);
            margin: 0 12px;
        }

        .breadcrumb-portal p.text-white-50 {
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            color: rgba(255, 255, 255, 0.6) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            max-width: 800px;
        }

        .form-card {
            border: none;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
        }

        /* Premium Form Section Headers */
        .form-section-header {
            background: linear-gradient(135deg, #0d2340 0%, #1e3a5f 50%, #245080 100%);
            padding: 20px 28px;
            border-radius: 12px 12px 0 0;
            border-left: 5px solid #4facfe;
            box-shadow: inset 0 -2px 12px rgba(0, 0, 0, 0.15), 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section-header h5 {
            font-size: 1.15rem;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
            color: #ffffff !important;
        }

        .form-section-header h5 i {
            color: #4facfe;
            filter: drop-shadow(0 0 4px rgba(79, 172, 254, 0.5));
        }

        .form-section-header small {
            color: rgba(255, 255, 255, 0.75) !important;
            font-style: italic;
        }

        /* Numbered Step Badge */
        .form-step-badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            text-shadow: none;
            box-shadow: 0 0 12px rgba(79, 172, 254, 0.3);
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--portal-primary);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--portal-primary);
        }

        .portal-section {
            padding: 60px 0;
        }

        .portal-footer {
            background: #1e2d3d;
            color: #ccc;
            padding: 40px 0 20px;
        }

        .portal-footer h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .portal-footer a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.2s;
        }

        .portal-footer a:hover {
            color: var(--portal-accent);
        }

        /* Premium FAQ Styles */
        .faq-2 {
            padding: 80px 0;
            background: #f8faff;
        }

        .faq-2 .faq-container {
            margin-top: 20px;
        }

        .faq-2 .faq-item {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 11, 40, 0.04);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
        }

        .faq-2 .faq-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(0, 11, 40, 0.08);
            border-color: rgba(var(--portal-primary-rgb), 0.2);
        }

        .faq-2 .faq-item.faq-active {
            background: #fff;
            border-color: var(--portal-primary);
            box-shadow: 0 15px 45px rgba(var(--portal-primary-rgb), 0.1);
        }

        .faq-2 .faq-item h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--portal-primary);
            padding-left: 50px;
            margin-bottom: 0;
            transition: color 0.3s;
            line-height: 1.4;
        }

        .faq-2 .faq-item.faq-active h3 {
            color: var(--portal-primary);
        }

        .faq-2 .faq-item .faq-icon {
            position: absolute;
            left: 24px;
            top: 24px;
            font-size: 1.5rem;
            color: var(--portal-accent);
            line-height: normal;
        }

        .faq-2 .faq-item .faq-content {
            padding-left: 50px;
            margin-top: 0;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: all 0.5s ease;
        }

        .faq-2 .faq-item.faq-active .faq-content {
            max-height: 500px;
            opacity: 1;
            margin-top: 15px;
        }

        .faq-2 .faq-item .faq-content p {
            margin-bottom: 0;
            color: #555;
            line-height: 1.7;
        }

        .faq-2 .faq-item .faq-toggle {
            position: absolute;
            right: 24px;
            top: 28px;
            font-size: 0.9rem;
            color: #ccc;
            transition: all 0.4s ease;
        }

        .faq-2 .faq-item.faq-active .faq-toggle {
            transform: rotate(90deg);
            color: var(--portal-primary);
        }

        .faq-2 .amharic-label {
            display: inline-block;
            background: rgba(232, 184, 75, 0.1);
            color: var(--portal-accent);
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .faq-2 .amharic-answer {
            color: var(--portal-secondary);
            font-style: italic;
            border-left: 2px solid rgba(var(--portal-accent-rgb), 0.3);
            padding-left: 15px;
            margin-top: 10px !important;
        }
    </style>

    @stack('styles')
</head>

<body class="@yield('body-class', '')">

    <!-- Header -->
    <header id="header" class="header d-flex align-items-center fixed-top" style="background: #1e3a5f;">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="{{ route('home') }}" class="logo d-flex align-items-center me-auto text-decoration-none">
                @php $logo = \App\Models\SiteSetting::get('logo_light'); @endphp
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="img-fluid me-2" style="max-height: 85px;">
                @else
                    <i class="bi bi-shield-check fs-2 text-warning me-2"></i>
                @endif
                <div>
                    <div class="navbar-brand-text text-white">
                        {{ \App\Models\SiteSetting::get('organization_name_en', 'Addis Ababa') }}
                    </div>
                    <div class="navbar-brand-sub text-white">
                        {{ \App\Models\SiteSetting::get('tagline_en', 'Law Enforcement Authority') }}
                    </div>
                    <div class="badge-amharic text-warning">
                        {{ \App\Models\SiteSetting::get('organization_name_am', 'አዲስ አበባ የሕግ ማስከበር ባለሥልጣን') }}
                    </div>
                </div>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    </li>
                    <li class="dropdown">
                        <a href="#"><span>Report</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="{{ route('complaint.create') }}"><i class="bi bi-megaphone me-1"></i> Submit
                                    Complaint</a></li>
                            <li><a href="{{ route('tip.create') }}"><i class="bi bi-eye-slash me-1"></i> Anonymous
                                    Tip</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#"><span>Track Status</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="{{ route('complaint.track') }}"><i class="bi bi-search me-1"></i> Track
                                    Complaint</a></li>
                        </ul>
                    </li>
                    <li><a href="{{ route('announcements.index') }}"
                            class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">Announcements</a></li>
                    <li><a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a>
                    </li>
                    <li><a href="{{ route('contact') }}"
                            class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="btn-getstarted" href="/admin" style="background: #e8b84b; color: #1e3a5f; font-weight: 700;">
                <i class="bi bi-person-lock me-1"></i> Admin
            </a>

        </div>
    </header>

    <main class="main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-session mx-4 mt-2 d-none d-lg-block"
                role="alert" style="position:fixed;top:100px;right:20px;z-index:9999;max-width:400px;">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-4 mt-2" role="alert"
                style="position:fixed;top:100px;right:20px;z-index:9999;max-width:400px;">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="portal-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <h5><i
                            class="bi bi-shield-check me-2 text-warning"></i>{{ \App\Models\SiteSetting::get('organization_name_en', 'AALEA Portal') }}
                    </h5>
                    <p style="font-size:0.9rem;">
                        {{ \App\Models\SiteSetting::get('footer_text_en', 'Official digital platform for citizens to submit complaints and anonymous tips.') }}
                    </p>
                    <p class="text-warning" style="font-size:0.85rem;">
                        {{ \App\Models\SiteSetting::get('footer_text_am', 'አዲስ አበባ ህጋዊ ጉዳዮች ማስከበሪያ ዳይሬክቶሬት') }}
                    </p>
                </div>
                <div class="col-lg-2 col-6">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('complaint.create') }}"><i class="bi bi-chevron-right"></i> Submit
                                Complaint</a></li>
                        <li><a href="{{ route('tip.create') }}"><i class="bi bi-chevron-right"></i> Report a Tip</a>
                        </li>
                        <li><a href="{{ route('complaint.track') }}"><i class="bi bi-chevron-right"></i> Track
                                Status</a></li>
                        <li><a href="{{ route('faq') }}"><i class="bi bi-chevron-right"></i> FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-6">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i
                                class="bi bi-geo-alt me-2 text-warning"></i>{{ \App\Models\SiteSetting::get('address_en', 'Addis Ababa, Ethiopia') }}
                        </li>
                        <li class="mb-2"><i
                                class="bi bi-telephone me-2 text-warning"></i>{{ \App\Models\SiteSetting::get('phone_primary', '+251 11 XXX XXXX') }}
                        </li>
                        <li class="mb-2"><i class="bi bi-envelope me-2 text-warning"></i>{{
                            \App\Models\SiteSetting::get('email_primary', 'info@aalea.gov.et') }}</li>
                        @php $hours = json_decode(\App\Models\SiteSetting::get('working_hours', '[]'), true); @endphp
                        @if(!empty($hours))
                            <li class="mb-2"><i class="bi bi-clock me-2 text-warning"></i>{{ $hours[0]['days_en'] ?? '' }},
                                {{ $hours[0]['hours'] ?? '' }}
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5>Emergency Hotline</h5>
                    <div class="p-3"
                        style="background: rgba(232,184,75,0.15); border-radius: 8px; border: 1px solid rgba(232,184,75,0.4);">
                        <p class="text-warning fw-bold fs-4 mb-1"><i class="bi bi-telephone-fill me-2"></i>991</p>
                        <p style="font-size:0.85rem;" class="mb-0">24/7 Emergency Response</p>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="me-2 text-warning fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-2 text-warning fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="me-2 text-warning fs-5"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-12 text-center" style="font-size:0.85rem;">
                    <p class="mb-0">
                        {{ \App\Models\SiteSetting::get('copyright_text', '© ' . date('Y') . ' Law Enforcement Authority. All Rights Reserved.') }}
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS -->
    <script src="{{ asset('arsha/Arsha/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('arsha/Arsha/assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('arsha/Arsha/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('arsha/Arsha/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('arsha/Arsha/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('arsha/Arsha/assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>

    <!-- Arsha Main JS -->
    <script src="{{ asset('arsha/Arsha/assets/js/main.js') }}"></script>

    @stack('scripts')
</body>

</html>