@extends('layouts.portal')

@section('title', 'Contact Us — AALEA Portal')

@section('content')

    <div class="breadcrumb-portal">
        <div class="container">
            <h2><i class="bi bi-telephone me-2"></i>Contact Us</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Contact</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="portal-section">
        <div class="container">
            <div class="row gy-4">

                {{-- Contact Info --}}
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="card form-card p-4 mb-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-building me-2 text-primary"></i>Headquarters</h5>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-geo-alt-fill"></i></div>
                            <div><strong>Address</strong><br><span class="text-muted">Addis Ababa, Ethiopia<br>Near Federal
                                    Police HQ</span></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-telephone-fill"></i></div>
                            <div><strong>Phone</strong><br><span class="text-muted">+251 11 XXX XXXX</span></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-envelope-fill"></i></div>
                            <div><strong>Email</strong><br><span class="text-muted">info@aalea.gov.et</span></div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="text-primary fs-4"><i class="bi bi-clock-fill"></i></div>
                            <div><strong>Office Hours</strong><br><span class="text-muted">Mon–Fri: 8:00 AM – 5:00
                                    PM<br>Saturday: 9:00 AM – 1:00 PM</span></div>
                        </div>
                    </div>

                    <div class="card form-card p-4"
                        style="background: linear-gradient(135deg, #c0392b, #e74c3c); color:white; text-align:center;">
                        <i class="bi bi-telephone-fill fs-1 mb-2"></i>
                        <h4 class="fw-bold">991</h4>
                        <p class="mb-0">24/7 Emergency Hotline</p>
                        <small class="opacity-75">For life-threatening emergencies only</small>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="card form-card p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-envelope-open me-2 text-primary"></i>Send Us a Message</h5>

                        @if(session('success'))
                            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <form action="{{ route('contact.send') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Your Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Full name" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="your@email.com" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control form-control-lg"
                                        value="{{ old('phone') }}" placeholder="+251 9X XXX XXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Subject</label>
                                    <input type="text" name="subject" class="form-control form-control-lg"
                                        value="{{ old('subject') }}" placeholder="Inquiry subject">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                        rows="5" placeholder="Write your message here..."
                                        required>{{ old('message') }}</textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection