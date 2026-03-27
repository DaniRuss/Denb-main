{{-- resources/views/portal/complaint/track.blade.php --}}
@extends('layouts.portal')

@section('title', 'ቅሬታ ተከታተል')

@section('content')

<div class="breadcrumb-portal">
    <div class="container">
        <h2><i class="bi bi-search me-2"></i>ቅሬታ ተከታተል (Track Complaint)</h2>
        <nav aria-label="breadcrumb" data-aos="fade-down" data-aos-delay="100">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Track Status</li>
            </ol>
        </nav>
        <p class="text-white-50">የቲኬት ቁጥርዎን በማስገባት የቅሬታዎን ሁኔታ ይከታተሉ / Track your complaint status using your ticket
            number.</p>
    </div>
</div>

<section id="track" class="track section">
    <div class="container" data-aos="fade-up">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <form action="{{ route('complaint.check') }}" method="POST" id="trackForm">
                            @csrf

                            <div class="mb-3">
                                <label for="ticket_number" class="form-label">የቲኬት ቁጥር</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-ticket"></i></span>
                                    <input type="text" name="ticket_number" id="ticket_number"
                                        class="form-control form-control-lg @error('ticket_number') is-invalid @enderror"
                                        placeholder="ለምሳሌ፡ CMP-20240306-123456" value="{{ old('ticket_number') }}"
                                        required>
                                    @error('ticket_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">የቲኬት ቁጥርዎን በትክክል ያስገቡ</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>ፈልግ
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                ቲኬት ቁጥር ከጠፋዎት? እባክዎ በስልክ ቁጥር <strong>+251 11 123 4567</strong> ደውለው ያነጋግሩን።
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Status Check -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ፈጣን ሁኔታ ማወቂያ</h5>
                            <p class="card-text">በቅርብ ጊዜ የጠየቁትን ቲኬት ቁጥር እዚህ ያያሉ።</p>

                            <div id="recentSearches" class="mt-3">
                                <!-- Recent searches will be loaded via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Result Area --}}
            <div class="row justify-content-center mt-4" id="resultArea" style="display:none !important;"></div>
        </div>
</section>

@push('scripts')
    <script>
        const trackForm = document.getElementById('trackForm');
        const resultArea = document.getElementById('resultArea');

        // Auto-search if ticket param in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('ticket')) {
            document.getElementById('ticket_number').value = urlParams.get('ticket');
            submitTicketSearch(urlParams.get('ticket'));
        }

        trackForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const ticket = document.getElementById('ticket_number').value.trim();
            if (!ticket) return;
            saveToRecentSearches(ticket);
            submitTicketSearch(ticket);
        });

        function submitTicketSearch(ticket) {
            const btn = trackForm.querySelector('button[type=submit]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>በመፈለግ ላይ...';
            btn.disabled = true;

            fetch('{{ route("complaint.check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ticket_number: ticket })
            })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = '<i class="bi bi-search me-2"></i>ፈልግ';
                    btn.disabled = false;
                    renderResult(data);
                })
                .catch(() => {
                    btn.innerHTML = '<i class="bi bi-search me-2"></i>ፈልግ';
                    btn.disabled = false;
                    resultArea.style.display = '';
                    resultArea.innerHTML = `<div class="col-lg-10">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ስህተት ተከስቷል፣ እባክዎ ደግመው ይሞክሩ።
                    </div>
                </div>`;
                });
        }

        function renderResult(data) {
            resultArea.style.display = '';

            if (!data.found) {
                resultArea.innerHTML = `<div class="col-lg-10">
                    <div class="alert alert-warning d-flex align-items-center gap-3">
                        <i class="bi bi-search fs-3"></i>
                        <div>
                            <strong>ቲኬቱ አልተገኘም</strong><br>
                            <span class="small">ያስገቡት የቲኬት ቁጥር “${document.getElementById('ticket_number').value}” ልክ አይደለም። እባክዎ ደግመው ይሞክሩ።</span>
                        </div>
                    </div>
                </div>`;
                return;
            }

            const statusColors = { success: 'success', warning: 'warning', info: 'info', primary: 'primary', secondary: 'secondary', danger: 'danger' };
            const color = statusColors[data.status_color] || 'secondary';
            const resolvedHtml = data.resolved_at ? `
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block">የተፈታበት ቀን</small>
                        <strong>${data.resolved_at}</strong>
                    </div>
                </div>` : '';
            const resolutionHtml = data.resolution ? `
                <div class="mt-3 p-3 bg-light rounded border-start border-success border-3">
                    <small class="text-muted d-block fw-bold">የመፍትሄ ሃሳብ</small>
                    <p class="mb-0 mt-1">${data.resolution}</p>
                </div>` : '';

            resultArea.innerHTML = `
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0 overflow-hidden">
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #0d2340, #1e3a5f); border-left: 5px solid #4facfe;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0 text-white fw-bold"><i class="bi bi-ticket-perforated me-2"></i>${data.ticket_number}</h5>
                                    <small class="text-white" style="opacity:0.75">የቅሬታ ሁኔታ — የቀረበበት ቀን: ${data.created_at}</small>
                                </div>
                                <span class="badge bg-${color} fs-6 px-3 py-2">${data.status}</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block">አመልካች</small>
                                        <strong>${data.full_name}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block">የቅድሚያ ደረጃ</small>
                                        <strong>${data.priority}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block">የመጨረሻ ለውጥ</small>
                                        <strong>${data.last_update}</strong>
                                    </div>
                                </div>
                                ${resolvedHtml}
                            </div>
                            ${resolutionHtml}
                        </div>
                    </div>
                </div>`;

            resultArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function saveToRecentSearches(ticket) {
            let searches = JSON.parse(localStorage.getItem('recentTicketSearches') || '[]');
            searches = searches.filter(t => t !== ticket);
            searches.unshift(ticket);
            searches = searches.slice(0, 5);
            localStorage.setItem('recentTicketSearches', JSON.stringify(searches));
            loadRecentSearches();
        }

        function loadRecentSearches() {
            const searches = JSON.parse(localStorage.getItem('recentTicketSearches') || '[]');
            const container = document.getElementById('recentSearches');
            if (!container) return;
            if (searches.length === 0) {
                container.innerHTML = '<p class="text-muted small">ምንም የቅርብ ጊዜ ፍለጋ የለም</p>';
                return;
            }
            let html = '<div class="list-group">';
            searches.forEach(ticket => {
                html += `<a href="javascript:void(0)" onclick="document.getElementById('ticket_number').value='${ticket}';submitTicketSearch('${ticket}')" 
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    ${ticket}
                    <i class="bi bi-arrow-right"></i>
                </a>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', loadRecentSearches);
    </script>
@endpush