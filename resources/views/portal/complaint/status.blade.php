@extends('layouts.portal')

@section('title', 'Complaint Status — AALEA Portal')

@section('content')

    <div class="breadcrumb-portal">
        <div class="container">
            <h2><i class="bi bi-search me-2"></i>Track Complaint Status</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Track Status</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="portal-section">
        <div class="container">

            {{-- Search Form --}}
            @if(!isset($complaint))
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-6">
                        <div class="card form-card p-4" data-aos="fade-up">
                            <h5 class="fw-bold mb-4"><i class="bi bi-ticket me-2 text-primary"></i>Enter Your Ticket Number</h5>
                            <form action="{{ route('complaint.check') }}" method="POST">
                                @csrf
                                <div class="input-group input-group-lg">
                                    <input type="text" name="ticket_number" class="form-control"
                                        placeholder="e.g. CMP-2024-00001" required>
                                    <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Track</button>
                                </div>
                                <div class="form-text mt-2">Your ticket number was provided after you submitted your complaint.
                                </div>
                            </form>

                            @if(session('error'))
                                <div class="alert alert-danger mt-3"><i
                                        class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Complaint Details --}}
            @if(isset($complaint))
                <div class="row justify-content-center">
                    <div class="col-lg-9">

                        {{-- Status Banner --}}
                        @php
                            $statusColors = [
                                'pending' => ['bg' => '#ffc107', 'text' => '#000', 'icon' => 'bi-hourglass-split'],
                                'under_review' => ['bg' => '#17a2b8', 'text' => '#fff', 'icon' => 'bi-search'],
                                'investigating' => ['bg' => '#0d6efd', 'text' => '#fff', 'icon' => 'bi-shield-shaded'],
                                'resolved' => ['bg' => '#28a745', 'text' => '#fff', 'icon' => 'bi-check-circle'],
                                'rejected' => ['bg' => '#dc3545', 'text' => '#fff', 'icon' => 'bi-x-circle'],
                                'closed' => ['bg' => '#6c757d', 'text' => '#fff', 'icon' => 'bi-archive'],
                            ];
                            $sc = $statusColors[$complaint->status] ?? ['bg' => '#6c757d', 'text' => '#fff', 'icon' => 'bi-circle'];
                        @endphp

                        <div class="card form-card mb-4" data-aos="fade-up">
                            <div class="card-body p-4"
                                style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}; border-radius: 12px;">
                                <div class="row align-items-center">
                                    <div class="col-auto"><i class="bi {{ $sc['icon'] }} fs-1"></i></div>
                                    <div class="col">
                                        <h4 class="mb-0 fw-bold">{{ strtoupper(str_replace('_', ' ', $complaint->status)) }}
                                        </h4>
                                        <div>Ticket: <strong>{{ $complaint->ticket_number }}</strong></div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <div style="font-size:0.85rem; opacity:0.85;">Submitted</div>
                                        <div class="fw-bold">{{ $complaint->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row gy-4">
                            {{-- Complaint Details --}}
                            <div class="col-lg-7">
                                <div class="card form-card p-4" data-aos="fade-up">
                                    <h5 class="fw-bold mb-4"><i class="bi bi-file-text me-2 text-primary"></i>Complaint Details
                                    </h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-semibold text-muted" style="width:40%">Type:</td>
                                            <td>{{ ucwords(str_replace('_', ' ', $complaint->complaint_type)) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Subject:</td>
                                            <td>{{ $complaint->subject ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Priority:</td>
                                            <td><span
                                                    class="badge bg-{{ $complaint->priority == 'high' || $complaint->priority == 'critical' ? 'danger' : ($complaint->priority == 'medium' ? 'warning text-dark' : 'secondary') }}">
                                                    {{ ucfirst($complaint->priority) }}
                                                </span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Submitted:</td>
                                            <td>{{ $complaint->created_at->format('d M Y, g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Last Updated:</td>
                                            <td>{{ $complaint->updated_at->format('d M Y, g:i A') }}</td>
                                        </tr>
                                    </table>
                                    <hr>
                                    <h6 class="fw-bold">Description</h6>
                                    <p class="text-muted">{{ $complaint->description }}</p>
                                </div>
                            </div>

                            {{-- Timeline --}}
                            <div class="col-lg-5">
                                <div class="card form-card p-4" data-aos="fade-up" data-aos-delay="100">
                                    <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Case Timeline
                                    </h5>
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="fw-semibold text-dark">Complaint Submitted</div>
                                            <div class="text-muted small">{{ $complaint->created_at->format('d M Y, g:i A') }}
                                            </div>
                                        </div>
                                        @if($complaint->updates && $complaint->updates->count() > 0)
                                            @foreach($complaint->updates as $update)
                                                <div class="timeline-item">
                                                    <div class="fw-semibold text-dark">{{ $update->title ?? 'Status Updated' }}</div>
                                                    <div class="text-muted small">{{ $update->created_at->format('d M Y, g:i A') }}
                                                    </div>
                                                    @if($update->notes)
                                                    <div class="text-muted">{{ $update->notes }}</div>@endif
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="timeline-item">
                                                <div class="text-muted small">Your case is being reviewed. Updates will appear here.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('complaint.track') }}" class="btn btn-outline-primary me-2">
                                <i class="bi bi-arrow-left me-1"></i>Track Another Ticket
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="bi bi-house me-1"></i>Back to Home
                            </a>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </section>

@endsection