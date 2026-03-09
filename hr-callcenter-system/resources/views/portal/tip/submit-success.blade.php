{{-- resources/views/portal/tip/submit-success.blade.php --}}
@extends('layouts.portal')

@section('title', 'Tip Submitted Successfully — AALEA Portal')

@section('content')

    <div class="breadcrumb-portal">
        <div class="container text-center">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="fw-bold">Tip Submitted Successfully</h2>
            <p class="lead">Thank you for your contribution to public safety.</p>
        </div>
    </div>

    <section class="portal-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card form-card p-4 p-md-5 text-center" data-aos="fade-up">
                        <h4 class="fw-bold mb-4">Your Anonymous Tip Number:</h4>
                        <div class="display-5 fw-bold text-primary mb-4 p-3 bg-light rounded shadow-sm border">
                            {{ $tip->tip_number }}
                        </div>

                        <div class="alert alert-warning border-warning p-4 mb-4 text-start">
                            <h5 class="fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Critical: Action Required</h5>
                            <p class="mb-0">Since this is an anonymous tip, we cannot send you confirmation via email or
                                phone.
                                <strong>Please save this unique tracking URL</strong> to check the status of your tip later:
                            </p>

                            <div class="input-group mt-3">
                                <input type="text" id="trackingLink" class="form-control" value="{{ $trackingUrl }}"
                                    readonly>
                                <button class="btn btn-primary" onclick="copyTrackingLink()">
                                    <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                            </div>
                        </div>

                        <div class="row gy-3 mt-2">
                            <div class="col-md-6">
                                <a href="{{ $trackingUrl }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="bi bi-search me-2"></i>Track Status Now
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('home') }}" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-house me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>

                        <p class="text-muted mt-4 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Your anonymity is protected. This token is the only way to identify your tip.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            function copyTrackingLink() {
                const copyText = document.getElementById("trackingLink");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);

                alert("Tracking link copied to clipboard!");
            }
        </script>
    @endpush

@endsection