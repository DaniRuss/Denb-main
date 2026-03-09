@extends('layouts.portal')

@section('title', \App\Models\SiteSetting::get('site_title', 'Home'))
@section('description', 'Submit complaints, report anonymous tips, and track your case status with the Addis Ababa Law Enforcement Authority.')

@section('content')

    {{-- Hero Section --}}
    <section id="hero" class="hero section dark-background"
        style="background: linear-gradient(135deg, #0f2644 0%, #1e3a5f 60%, #2d6a9f 100%); min-height: 100vh; display:flex; align-items:center;">
        <div class="container" style="padding-top: 80px;">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                    <div class="badge bg-warning text-dark mb-3 px-3 py-2 d-inline-block"
                        style="width:fit-content; font-size:0.75rem; letter-spacing:0.05em;">
                        <i class="bi bi-shield-check me-1"></i> OFFICIAL GOVERNMENT PORTAL
                    </div>
                    <h1 class="text-white"
                        style="font-weight:900; font-size:2.0rem; line-height:1.05; letter-spacing:-0.03em;">
                        {{ \App\Models\SiteSetting::get('hero_title_en', 'Addis Ababa') }}<br>
                        <span
                            style="color:#e8b84b; font-size:1.5rem;">{{ \App\Models\SiteSetting::get('hero_subtitle_en', 'Citizen Portal') }}</span>
                    </h1>
                    <p class="text-white-50 mt-3 mb-4" style="font-size:1.05rem;">
                        {{ \App\Models\SiteSetting::get('hero_description_en', 'A secure, transparent platform to submit complaints, report illegal activities anonymously, and track case statuses. Your voice matters.') }}
                    </p>
                    <p class="text-warning mb-4" style="font-size:0.9rem; font-style:italic;">
                        {{ \App\Models\SiteSetting::get('hero_tagline_am', 'ቅሬታዎን ያስገቡ ● ህገ-ወጥ ስራዎችን ሪፖርት ያድርጉ ● ጉዳይዎን ይከታተሉ') }}
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        @if(\App\Models\SiteSetting::get('enable_complaints', '1') == '1')
                            <a href="{{ route('complaint.create') }}" class="btn btn-warning btn-lg px-4 fw-bold text-dark">
                                <i class="bi bi-megaphone-fill me-2"></i>Submit Complaint
                            </a>
                        @endif
                        @if(\App\Models\SiteSetting::get('enable_tips', '1') == '1')
                            <a href="{{ route('tip.create') }}" class="btn btn-outline-light btn-lg px-4">
                                <i class="bi bi-eye-slash me-2"></i>Report Anonymously
                            </a>
                        @endif
                    </div>
                    @php $stats = json_decode(\App\Models\SiteSetting::get('stats', '[]'), true); @endphp
                    @if(!empty($stats))
                        <div class="mt-4 d-flex gap-4">
                            @foreach(array_slice($stats, 0, 3) as $stat)
                                <div class="text-center">
                                    <div class="text-warning fs-4 fw-bold">{{ $stat['value'] }}</div>
                                    <div class="text-white-50 small">{{ $stat['label_en'] }}</div>
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
                                    <div class="text-white small">Track Your Complaint</div>
                                    <a href="{{ route('complaint.track') }}"
                                        class="btn btn-sm btn-warning mt-2 w-100 text-dark fw-bold">Track Now</a>
                                </div>
                            </div>
                            <div class="col-6">
                                <div
                                    style="background: rgba(255,255,255,0.08); border-radius: 12px; padding: 15px; text-align:center;">
                                    <i class="bi bi-question-circle text-warning fs-3 d-block mb-1"></i>
                                    <div class="text-white small">Frequently Asked Questions</div>
                                    <a href="{{ route('faq') }}" class="btn btn-sm btn-outline-warning mt-2 w-100">View
                                        FAQ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services Section --}}
    <section id="services" class="services section light-background">
        <div class="container section-title" data-aos="fade-up">
            <h2>Our Services</h2>
            <p>Transparent, accessible, and secure citizen services for Addis Ababa residents</p>
        </div>
        <div class="container">
            <div class="row gy-4">
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-megaphone icon"></i></div>
                        <h4><a href="{{ route('complaint.create') }}" class="stretched-link">Submit Complaint</a></h4>
                        <p>File a formal complaint about law violations, misconduct, or illegal activities with full case
                            tracking support.</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-eye-slash icon"></i></div>
                        <h4><a href="{{ route('tip.create') }}" class="stretched-link">Anonymous Tip</a></h4>
                        <p>Report illegal trade, contraband, drug activity, or other violations anonymously. Your identity
                            is protected.</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-search icon"></i></div>
                        <h4><a href="{{ route('complaint.track') }}" class="stretched-link">Track Status</a></h4>
                        <p>Use your ticket number to check real-time complaint status, assigned officer, and case updates.
                        </p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-item position-relative w-100">
                        <div class="icon"><i class="bi bi-bell icon"></i></div>
                        <h4><a href="{{ route('announcements.index') }}" class="stretched-link">Announcements</a></h4>
                        <p>Stay informed on law enforcement updates, public safety notices, and community announcements.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works Section --}}
    <section id="how-it-works" class="work-process section">
        <div class="container section-title" data-aos="fade-up">
            <h2>How It Works</h2>
            <p>Simple, fast, and transparent process for all citizen services</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-5">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">01</div>
                            <h3>Submit Your Request</h3>
                            <p>Fill out the online form with details of your complaint or tip. Attach supporting evidence if
                                available.</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Secure encrypted
                                        submission</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Upload photo/video
                                        evidence</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Get a unique ticket
                                        number</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">02</div>
                            <h3>Case Assignment</h3>
                            <p>Your case is reviewed and assigned to the appropriate department. You'll receive updates on
                                your ticket.</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Automatic ticket
                                        generation</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Department routing</span>
                                </div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Email/SMS
                                        notifications</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="steps-item">
                        <div class="steps-content">
                            <div class="steps-number">03</div>
                            <h3>Resolution & Feedback</h3>
                            <p>Track progress online, receive case updates, and get notified when your complaint is
                                resolved.</p>
                            <div class="steps-features">
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Real-time status
                                        tracking</span></div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Transparent updates</span>
                                </div>
                                <div class="feature-item"><i class="bi bi-check-circle"></i><span>Resolution report</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action --}}
    <section class="call-to-action section dark-background"
        style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);">
        <div class="container">
            <div class="row" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-xl-9 text-center text-xl-start">
                    <h3 class="text-white">Need to Report Something Urgently?</h3>
                    <p class="text-white-50">
                        For emergencies, call <strong class="text-warning">991</strong>. For non-emergency reports, use our
                        online portal to submit complaints or anonymous tips.
                    </p>
                </div>
                <div class="col-xl-3 cta-btn-container text-center d-flex align-items-center justify-content-center gap-2">
                    <a class="cta-btn" href="{{ route('complaint.create') }}">Submit Complaint</a>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section id="faq" class="faq-2 section light-background">
        <div class="container section-title" data-aos="fade-up">
            <h2>Common Questions</h2>
            <p>Quick answers to what citizens ask most</p>
        </div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="faq-container">
                        @php $faqs = json_decode(\App\Models\SiteSetting::get('faqs', '[]'), true); @endphp
                        @foreach($faqs as $index => $faq)
                            <div class="faq-item {{ $index === 0 ? 'faq-active' : '' }}" data-aos="fade-up"
                                data-aos-delay="{{ 200 + ($index * 100) }}">
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

                    @if(count($faqs) > 3)
                        <div class="text-center mt-4">
                            <a href="{{ route('faq') }}" class="btn btn-primary px-5"
                                style="background-color: var(--portal-primary); border-color: var(--portal-primary);">View All
                                FAQs</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection