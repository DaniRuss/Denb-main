@extends('layouts.portal')

@php
    $locale = app()->getLocale();
    $title = $announcement->{'title_' . $locale} ?: ($announcement->title_en ?: $announcement->title_am);
    $altTitle = $locale === 'am' ? $announcement->title_en : $announcement->title_am;
    $primaryContent = $announcement->{'content_' . $locale} ?: ($announcement->content_en ?: $announcement->content_am);
    $secondaryContent = $locale === 'am' ? $announcement->content_en : $announcement->content_am;
@endphp

@section('title', $title)

@section('content')
<div class="breadcrumb-portal">
    <div class="container">
        <h2 data-aos="fade-down"><i class="bi bi-megaphone me-2"></i>{{ $title }}</h2>
        <nav aria-label="breadcrumb" data-aos="fade-down" data-aos-delay="100">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">{{ __('messages.announcements') }}</a></li>
                <li class="breadcrumb-item active">
                    {{ \Illuminate\Support\Str::limit($title, 30) }}
                </li>
            </ol>
        </nav>
    </div>
</div>

<section id="announcement-detail" class="announcement-detail section">
    <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    @if($announcement->featured_image)
                        <img src="{{ Storage::url($announcement->featured_image) }}" class="card-img-top"
                            alt="{{ $title }}" style="max-height: 400px; object-fit: cover;">
                    @endif

                    <div class="card-body p-5">
                        @if($announcement->is_urgent)
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                <div>
                                    <strong>{{ __('messages.urgent_announcement') }}</strong> {{ __('messages.read_carefully') }}
                                </div>
                            </div>
                        @endif

                        <h1 class="card-title mb-1">{{ $title }}</h1>
                        @if($altTitle)
                            <h4 class="card-subtitle text-muted mb-3">{{ $altTitle }}</h4>
                        @endif

                        <div class="d-flex align-items-center text-muted mb-4">
                            <i class="bi bi-calendar me-1"></i>
                            <span class="me-3">{{ $announcement->publish_date->format('Y-m-d') }}</span>

                            <i class="bi bi-person me-1"></i>
                            <span>{{ $announcement->creator?->name ?? __('messages.system_admin') }}</span>
                        </div>

                        @if($primaryContent)
                            <div class="announcement-content">{!! $primaryContent !!}</div>
                        @endif

                        @if($secondaryContent)
                            <hr class="my-4 border-light">
                            <div class="announcement-content" style="font-family: 'Inter', sans-serif;">
                                {!! $secondaryContent !!}
                            </div>
                        @endif

                        <hr class="my-5">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('announcements.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i>
                                {{ __('messages.back_to_announcements') }}
                            </a>

                            <div class="share-buttons">
                                <span class="me-2">{{ __('messages.share') }}</span>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" onclick="shareOnFacebook()">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-info me-1" onclick="shareOnTwitter()">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-success" onclick="shareOnTelegram()">
                                    <i class="bi bi-telegram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
    <style>
        .announcement-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .announcement-content p {
            margin-bottom: 1.5rem;
        }

        .announcement-content h2,
        .announcement-content h3,
        .announcement-content h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .announcement-content ul,
        .announcement-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .announcement-content table {
            width: 100%;
            margin-bottom: 1.5rem;
            color: var(--bs-body-color);
            border-collapse: collapse;
        }

        .announcement-content table th,
        .announcement-content table td {
            padding: 0.75rem;
            vertical-align: top;
            border: 1px solid #dee2e6;
        }

        .announcement-content table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function shareOnFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href),
                'facebook-share',
                'width=580,height=296');
        }

        function shareOnTwitter() {
            window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(document.title) + '&url=' + encodeURIComponent(window.location.href),
                'twitter-share',
                'width=550,height=235');
        }

        function shareOnTelegram() {
            window.open('https://t.me/share/url?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title),
                'telegram-share',
                'width=550,height=400');
        }
    </script>
@endpush
