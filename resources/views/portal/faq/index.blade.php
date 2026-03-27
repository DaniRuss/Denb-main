{{-- resources/views/portal/faq/index.blade.php --}}
@extends('layouts.portal')

@section('title', 'ተደጋጋሚ ጥያቄዎች')

@section('content')
<section id="faq" class="faq section">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>ተደጋጋሚ ጥያቄዎች</h2>
            <p>በተደጋጋሚ የሚጠየቁ ጥያቄዎች መልስ እዚህ ያገኛሉ</p>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="faq-categories card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">ምድቦች</h5>
                    </div>
                    <div class="list-group list-group-flush" id="categoryList">
                        <a href="#all" class="list-group-item list-group-item-action active" data-category="all">
                            ሁሉም
                        </a>
                        @foreach($faqs->keys() as $category)
                            <a href="#{{ $category }}" class="list-group-item list-group-item-action"
                                data-category="{{ $category }}">
                                ምድብ {{ $category }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="faq-content">
                    @foreach($faqs as $category => $categoryFaqs)
                        <div class="faq-category-section mb-4" data-category="{{ $category }}">
                            <h3 class="mb-3">ምድብ {{ $category }}</h3>

                            <div class="accordion" id="faqAccordion{{ $category }}">
                                @foreach($categoryFaqs as $index => $faq)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                                                {{ $faq->question_am }}
                                            </button>
                                        </h2>
                                        <div id="faq{{ $faq->id }}"
                                            class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                            data-bs-parent="#faqAccordion{{ $category }}">
                                            <div class="accordion-body">
                                                {!! nl2br(e($faq->answer_am)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoryLinks = document.querySelectorAll('#categoryList .list-group-item');
            const faqSections = document.querySelectorAll('.faq-category-section');

            categoryLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Update active state
                    categoryLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');

                    const category = this.dataset.category;

                    // Show/hide sections
                    if (category === 'all') {
                        faqSections.forEach(section => section.style.display = 'block');
                    } else {
                        faqSections.forEach(section => {
                            if (section.dataset.category === category) {
                                section.style.display = 'block';
                            } else {
                                section.style.display = 'none';
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush