@extends('layouts.portal')

@php $locale = app()->getLocale(); @endphp

@section('title', __('messages.faq') . ' - ' . __('messages.site_title'))

@section('content')
    <div class="breadcrumb-portal">
        <div class="container">
            <h2><i class="bi bi-question-circle me-2"></i>{{ __('messages.frequently_asked_questions') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('messages.faq') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="portal-section faq-2 section light-background">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
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
                                data-aos-delay="{{ 100 + ($index * 50) }}">
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
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card form-card p-4 mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-headset me-2 text-primary"></i>{{ __('messages.need_more_help') }}</h5>
                        <p class="text-muted">{{ __('messages.cant_find_answer') }}</p>
                        <a href="{{ route('contact') }}" class="btn btn-primary w-100 mb-2">{{ __('messages.contact_us') }}</a>
                        <div class="text-center text-muted small mt-2">
                            <i class="bi bi-telephone me-1"></i>{{ __('messages.emergency_hotline') }}: <strong class="text-danger">991</strong>
                        </div>
                    </div>

                    <div class="card form-card p-4"
                        style="background: linear-gradient(135deg, #1e3a5f, #2d6a9f); color: white;">
                        <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-megaphone me-2"></i>{{ __('messages.ready_to_submit') }}</h5>
                        <p style="font-size:0.9rem; opacity:0.85;">{{ __('messages.submit_securely') }} submissions are encrypted.</p>
                        <a href="{{ route('complaint.create') }}"
                            class="btn btn-warning text-dark fw-bold w-100 mb-2">{{ __('messages.submit_complaint') }}</a>
                        <a href="{{ route('tip.create') }}" class="btn btn-outline-light w-100">{{ __('messages.report_anonymously') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
