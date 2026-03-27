@extends('layouts.portal')

@section('title', 'FAQ — AALEA Portal')

@section('content')

    <div class="breadcrumb-portal">
        <div class="container">
            <h2><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">FAQ</li>
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
                            <div class="faq-item {{ $index === 0 ? 'faq-active' : '' }}" data-aos="fade-up"
                                data-aos-delay="{{ 100 + ($index * 50) }}">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>
                                    {{ $faq['question_en'] }}
                                    <span class="amharic-label d-block">{{ $faq['question_am'] }}</span>
                                </h3>
                                <div class="faq-content">
                                    <p>{{ $faq['answer_en'] }}</p>
                                    <p class="amharic-answer">{{ $faq['answer_am'] }}</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card form-card p-4 mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-headset me-2 text-primary"></i>Need More Help?</h5>
                        <p class="text-muted">Can't find your answer? Contact our support team.</p>
                        <a href="{{ route('contact') }}" class="btn btn-primary w-100 mb-2">Contact Us</a>
                        <div class="text-center text-muted small mt-2">
                            <i class="bi bi-telephone me-1"></i>Emergency: <strong class="text-danger">991</strong>
                        </div>
                    </div>

                    <div class="card form-card p-4"
                        style="background: linear-gradient(135deg, #1e3a5f, #2d6a9f); color: white;">
                        <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-megaphone me-2"></i>Ready to Submit?</h5>
                        <p style="font-size:0.9rem; opacity:0.85;">Submit your complaint or tip securely online. All
                            submissions are encrypted.</p>
                        <a href="{{ route('complaint.create') }}"
                            class="btn btn-warning text-dark fw-bold w-100 mb-2">Submit Complaint</a>
                        <a href="{{ route('tip.create') }}" class="btn btn-outline-light w-100">Report Anonymously</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection