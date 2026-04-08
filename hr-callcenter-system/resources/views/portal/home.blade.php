@extends('layouts.portal')

@php
    $locale = app()->getLocale();
    $heroTitle = \App\Models\SiteSetting::get(
        'hero_title_' . $locale,
        $locale === 'am' ? 'የአዲስ አበባ ከተማ አስተዳደር ኮድ ማስከበር' : 'Addis Ababa City Administration Code Enforcement'
    );
    $heroSubtitle = \App\Models\SiteSetting::get(
        'hero_subtitle_' . $locale,
        $locale === 'am' ? 'ባለስልጣን' : 'Authority Portal'
    );
    $heroDescription = \App\Models\SiteSetting::get('hero_description_' . $locale, __('messages.hero_description'));
    $heroTagline = \App\Models\SiteSetting::get(
        'hero_tagline_' . $locale,
        $locale === 'am'
            ? 'ቅሬታዎን ያስገቡ • ሕገ-ወጥ ስራዎችን ሪፖርት ያድርጉ • ጉዳይዎን ይከታተሉ'
            : 'Submit complaints • Report illegal activity • Track your case'
    );
@endphp

@section('title', \App\Models\SiteSetting::get('site_title', __('messages.site_title')))
@section('description', __('messages.hero_description'))

@section('content')

    <section id="hero" class="hero section dark-background"
        style="background: linear-gradient(135deg, #0f2644 0%, #1e3a5f 60%, #2d6a9f 100%); min-height: 100vh; display:flex; align-items:center;">
        <div class="container" style="padding-top: 80px;">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                    <div class="badge bg-warning text-dark mb-3 px-3 py-2 d-inline-block"
                        style="width:fit-content; font-size:0.75rem; letter-spacing:0.05em;">
                        <i class="bi bi-shield-check me-1"></i> {{ __('messages.official_government_portal') }}
                    </div>
                    <h1 class="text-white"
                        style="font-weight:900; font-size:2.0rem; line-height:1.05; letter-spacing:-0.03em;">
                        {{ $heroTitle }}<br>
                        <span style="color:#e8b84b; font-size:1.5rem;">{{ $heroSubtitle }}</span>
                    </h1>
                    <p class="text-white-50 mt-3 mb-4" style="font-size:1.05rem;">
                        {{ $heroDescription }}
                    </p>
                    <p class="text-warning mb-4" style="font-size:0.9rem; font-style:italic;">
                        {{ $heroTagline }}
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        @if(\App\Models\SiteSetting::get('enable_complaints', '1') == '1')
                            <a href="{{ route('complaint.create') }}" class="btn btn-warning btn-lg px-4 fw-bold text-dark">
                                <i class="bi bi-megaphone-fill me-2"></i>{{ __('messages.submit_complaint') }}
                            </a>
                        @endif
                        @if(\App\Models\SiteSetting::get('enable_tips', '1') == '1')
                            <a href="{{ route('tip.create') }}" class="btn btn-outline-light btn-lg px-4">
                                <i class="bi bi-eye-slash me-2"></i>{{ __('messages.report_anonymously') }}
                            </a>
                        @endif
                    </div>
                    @php $stats = json_decode(\App\Models\SiteSetting::get('stats', '[]'), true); @endphp
                    @if(!empty($stats))
                        <div class="mt-4 d-flex gap-4">
                            @foreach(array_slice($stats, 0, 3) as $stat)
                                <div class="text-center">
                                    <div class="text-warning fs-4 fw-bold">{{ $stat['value'] }}</div>
                                    <div class="text-white-50 small">{{ $stat['label_' . $locale] ?? ($stat['label_en'] ?? '') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-lg-6 order-1 order-lg-2 text-center" data-aos="zoom-out" data-aos-delay="200">
                    <div
                        style="background: rgba(255,255,255,0.05); border-radius: 20px; padding: 40px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
                        <i class="bi bi-shield-lock" style="font-size: 8rem; color: #e8b84b; opacity: 0.9;"></i>
                        <div class="row g-3 mt-4">
                            <div class="col-6">
                                <div
                                    style="background: rgba(255,255,255,0.08); border-radius: 12px; padding: 15px; text-align:center;">
                                    <i class="bi bi-ticket-perforated text-warning fs-3 d-block mb-1"></i>
                                    <div class="text-white small">{{ __('messages.track_your_complaint') }}</div>
                                    <a href="{{ route('complaint.track') }}"
                                        class="btn btn-sm btn-warning mt-2 w-100 text-dark fw-bold">{{ __('messages.track_now') }}</a>
                                </div>
                            </div>
                            <div class="col-6">
                                <div
                                    style="background: rgba(255,255,255,0.08); border-radius: 12px; padding: 15px; text-align:center;">
                                    <i class="bi bi-question-circle text-warning fs-3 d-block mb-1"></i>
                                    <div class="text-white small">{{ __('messages.frequently_asked_questions') }}</div>
                                    <a href="{{ route('faq') }}" class="btn btn-sm btn-outline-warning mt-2 w-100">{{ __('messages.view_faq') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="services section light-background">
        <div class="container section-title" data-aos="fade-up">
            <h2>{{ __('messages.our_services') }}</h2>
            <p>{{ __('messages.our_services_description') }}</p>
        </div>
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-megaphone icon"></i></div>
                        <h4><a href="{{ route('complaint.create') }}" class="stretched-link">{{ __('messages.submit_complaint') }}</a></h4>
                        <p>{{ __('messages.service_complaint_description') }}</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-eye-slash icon"></i></div>
                        <h4><a href="{{ route('tip.create') }}" class="stretched-link">{{ __('messages.anonymous_tip') }}</a></h4>
                        <p>{{ __('messages.service_tip_description') }}</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-search icon"></i></div>
                        <h4><a href="{{ route('complaint.track') }}" class="stretched-link">{{ __('messages.track_status') }}</a></h4>
                        <p>{{ __('messages.service_track_description') }}</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-bell icon"></i></div>
                        <h4><a href="{{ route('announcements.index') }}" class="stretched-link">{{ __('messages.announcements') }}</a></h4>
                        <p>{{ __('messages.service_announcements_description') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="work-process section">
        <div class="container section-title" data-aos="fade-up">
            <h2>{{ __('messages.how_it_works') }}</h2>
            <p>{{ __('messages.how_it_works_description') }}</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-5">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">01</div>
                            <h3>{{ __('messages.step_submit_title') }}</h3>
                            <p>{{ __('messages.step_submit_description') }}</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_submit_feature_1') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_submit_feature_2') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_submit_feature_3') }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">02</div>
                            <h3>{{ __('messages.step_assignment_title') }}</h3>
                            <p>{{ __('messages.step_assignment_description') }}</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_assignment_feature_1') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_assignment_feature_2') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_assignment_feature_3') }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">03</div>
                            <h3>{{ __('messages.step_resolution_title') }}</h3>
                            <p>{{ __('messages.step_resolution_description') }}</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_resolution_feature_1') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_resolution_feature_2') }}</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>{{ __('messages.step_resolution_feature_3') }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="call-to-action section dark-background"
        style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);">
        <div class="container">
            <div class="row" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-xl-9 text-center text-xl-start">
                    <h3 class="text-white">{{ __('messages.urgent_report_title') }}</h3>
                    <p class="text-white-50">{{ __('messages.urgent_report_description') }}</p>
                </div>
                <div class="col-xl-3 cta-btn-container text-center d-flex align-items-center justify-content-center gap-2">
                    <a class="cta-btn" href="{{ route('complaint.create') }}">{{ __('messages.submit_complaint') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="faq-2 section light-background">
        <div class="container section-title" data-aos="fade-up">
            <h2>{{ __('messages.common_questions') }}</h2>
            <p>{{ __('messages.common_questions_description') }}</p>
        </div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="faq-container">
                        @php $faqs = json_decode(\App\Models\SiteSetting::get('faqs', '[]'), true); @endphp
                        @foreach($faqs as $index => $faq)
                            @php
                                $question = $faq['question_' . $locale] ?? ($faq['question_en'] ?? '');
                                $answer = $faq['answer_' . $locale] ?? ($faq['answer_en'] ?? '');
                                $altQuestion = $locale === 'am' ? ($faq['question_en'] ?? '') : ($faq['question_am'] ?? '');
                                $altAnswer = $locale === 'am' ? ($faq['answer_en'] ?? '') : ($faq['answer_am'] ?? '');
                            @endphp
                            <div class="faq-item {{ $index === 0 ? 'faq-active' : '' }}" data-aos="fade-up"
                                data-aos-delay="{{ 200 + ($index * 100) }}">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>
                                    {{ $question }}
                                    @if($altQuestion)
                                        <span class="amharic-label d-block">{{ $altQuestion }}</span>
                                    @endif
                                </h3>
                                <div class="faq-content">
                                    <p>{{ $answer }}</p>
                                    @if($altAnswer)
                                        <p class="amharic-answer">{{ $altAnswer }}</p>
                                    @endif
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>
                        @endforeach
                    </div>

                    @if(count($faqs) > 3)
                        <div class="text-center mt-4">
                            <a href="{{ route('faq') }}" class="btn btn-primary px-5"
                                style="background-color: var(--portal-primary); border-color: var(--portal-primary);">{{ __('messages.view_all_faqs') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection
