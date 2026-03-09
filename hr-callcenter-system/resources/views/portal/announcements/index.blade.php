{{-- resources/views/portal/announcements/index.blade.php --}}
@extends('layouts.portal')

@section('title', 'ማስታወቂያዎች')

@section('content')

<div class="breadcrumb-portal">
    <div class="container">
        <h2 data-aos="fade-down"><i class="bi bi-megaphone me-2"></i>ማስታወቂያዎች (Announcements)</h2>
        <nav aria-label="breadcrumb" data-aos="fade-down" data-aos-delay="100">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Announcements</li>
            </ol>
        </nav>
        <p class="text-white-50 mt-2" data-aos="fade-down" data-aos-delay="150">የቅርብ ጊዜ ማስታወቂያዎችን እና መረጃዎችን ይመልከቱ / View
            the latest news and updates.</p>
    </div>
</div>

<section id="announcements" class="announcements section">
    <div class="container" data-aos="fade-up">

        <div class="row">
            @forelse($announcements as $announcement)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="card h-100 shadow-sm hover-card">
                        @if($announcement->featured_image)
                            <img src="{{ Storage::url($announcement->featured_image) }}" class="card-img-top"
                                alt="{{ $announcement->title_am }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <i class="bi bi-megaphone" style="font-size: 3rem; color: #dee2e6;"></i>
                            </div>
                        @endif

                        <div class="card-body">
                            @if($announcement->is_urgent)
                                <span class="badge bg-danger mb-2">አስቸኳይ</span>
                            @endif

                            <h5 class="card-title mb-1">{{ $announcement->title_am ?: $announcement->title_en }}</h5>
                            @if($announcement->title_en && $announcement->title_am)
                                <h6 class="card-subtitle text-muted mb-2" style="font-size: 0.85rem;">
                                    {{ $announcement->title_en }}</h6>
                            @endif

                            <p class="card-text text-muted small mt-2">
                                <i class="bi bi-calendar me-1"></i>
                                {{ $announcement->publish_date->format('M d, Y') }}
                            </p>

                            <p class="card-text" style="font-size: 0.95rem;">
                                {{ Str::limit(strip_tags($announcement->content_am ?: $announcement->content_en), 120) }}
                            </p>

                            <a href="{{ route('announcements.show', $announcement->id) }}"
                                class="btn btn-outline-primary btn-sm">
                                ተጨማሪ ያንብቡ <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-megaphone" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h3 class="mt-3">ምንም ማስታወቂያ የለም</h3>
                    <p class="text-muted">እባክዎ በኋላ ላይ ይመልከቱ</p>
                </div>
            @endforelse
        </div>

        <div class="row mt-4">
            <div class="col-12">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</section>

@push('styles')
    <style>
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endpush