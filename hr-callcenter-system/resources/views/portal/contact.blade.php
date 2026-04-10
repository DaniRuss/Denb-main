@extends('layouts.portal')

@php
    $locale = app()->getLocale();
    $address = \App\Models\SiteSetting::get('address_' . $locale, \App\Models\SiteSetting::get('address_en', 'Addis Ababa, Ethiopia'));
    $hours = json_decode(\App\Models\SiteSetting::get('working_hours', '[]'), true);
    $primaryHours = $hours[0] ?? null;
@endphp

@section('title', __('messages.contact_us') . ' - ' . __('messages.site_title'))

@section('content')
    <div class="breadcrumb-portal">
        <div class="container">
            <h2><i class="bi bi-telephone me-2"></i>{{ __('messages.contact_us') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('messages.contact') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="portal-section">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="card form-card p-4 mb-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-building me-2 text-primary"></i>{{ __('messages.headquarters') }}</h5>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-geo-alt-fill"></i></div>
                            <div><strong>{{ __('messages.address') }}</strong><br><span class="text-muted">{{ $address }}</span></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-telephone-fill"></i></div>
                            <div><strong>{{ __('messages.phone') }}</strong><br><span class="text-muted">{{ \App\Models\SiteSetting::get('phone_primary', '+251 11 XXX XXXX') }}</span></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary fs-4"><i class="bi bi-envelope-fill"></i></div>
                            <div><strong>{{ __('messages.email') }}</strong><br><span class="text-muted">{{ \App\Models\SiteSetting::get('email_primary', 'info@aalea.gov.et') }}</span></div>
                        </div>
                        @if($primaryHours)
                            <div class="d-flex gap-3">
                                <div class="text-primary fs-4"><i class="bi bi-clock-fill"></i></div>
                                <div>
                                    <strong>{{ __('messages.office_hours') }}</strong><br>
                                    <span class="text-muted">{{ $primaryHours['days_' . $locale] ?? ($primaryHours['days_en'] ?? '') }}<br>{{ $primaryHours['hours'] ?? '' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card form-card p-4"
                        style="background: linear-gradient(135deg, #c0392b, #e74c3c); color:white; text-align:center;">
                        <i class="bi bi-telephone-fill fs-1 mb-2"></i>
                        <h4 class="fw-bold">991</h4>
                        <p class="mb-0">{{ __('messages.emergency_hotline') }}</p>
                        <small class="opacity-75">{{ __('messages.life_threatening_only') }}</small>
                    </div>
                </div>

                <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="card form-card p-4">
                        <h5 class="fw-bold mb-4"><i class="bi bi-envelope-open me-2 text-primary"></i>{{ __('messages.send_us_a_message') }}</h5>

                        @if(session('success'))
                            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
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
                                    <label class="form-label fw-semibold">{{ __('messages.your_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="{{ __('messages.full_name_placeholder') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.email_address') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="{{ __('messages.email_placeholder') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.phone_number') }}</label>
                                    <input type="tel" name="phone" class="form-control form-control-lg"
                                        value="{{ old('phone') }}" placeholder="{{ __('messages.phone_placeholder') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.subject') }}</label>
                                    <input type="text" name="subject" class="form-control form-control-lg"
                                        value="{{ old('subject') }}" placeholder="{{ __('messages.subject_placeholder') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.message') }} <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                        rows="5" placeholder="{{ __('messages.message_placeholder') }}"
                                        required>{{ old('message') }}</textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-send me-2"></i>{{ __('messages.send_message') }}
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
