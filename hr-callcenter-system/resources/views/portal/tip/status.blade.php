{{-- resources/views/portal/tip/status.blade.php --}}
@extends('layouts.portal')

@section('title', 'የመረጃ ሁኔታ')

@section('content')
    <section id="tip-status" class="tip-status section">
        <div class="container" data-aos="fade-up">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">የመረጃ ሁኔታ - ቁጥር: {{ $tip->tip_number }}</h4>
                        </div>

                        <div class="card-body p-4">
                            <div class="alert alert-info">
                                <i class="bi bi-shield-lock me-2"></i>
                                <strong>ማሳሰቢያ:</strong> ይህ መረጃ በስም-አልባነት የተላከ ነው። የእርስዎ ማንነት ሚስጥር ሆኖ ይቆያል።
                            </div>

                            <div class="status-timeline mb-4">
                                <h5>የመረጃ ሁኔታ</h5>

                                @php
                                    $statusOrder = [
                                        'pending' => 1,
                                        'under_review' => 2,
                                        'investigating' => 3,
                                        'verified' => 4,
                                        'action_taken' => 5,
                                        'closed' => 6
                                    ];
                                    $currentStatus = $statusOrder[$tip->status] ?? 0;
                                @endphp

                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-{{ 
                                            $tip->status == 'closed' ? 'secondary' :
        ($tip->status == 'action_taken' ? 'success' :
            ($tip->status == 'verified' ? 'info' :
                ($tip->status == 'investigating' ? 'warning' : 'primary'))) 
                                        }}" role="progressbar" style="width: {{ ($currentStatus / 6) * 100 }}%">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span class="badge {{ $currentStatus >= 1 ? 'bg-primary' : 'bg-secondary' }}">ተቀባይነት
                                        አግኝቷል</span>
                                    <span class="badge {{ $currentStatus >= 2 ? 'bg-primary' : 'bg-secondary' }}">በግምገማ
                                        ላይ</span>
                                    <span class="badge {{ $currentStatus >= 3 ? 'bg-primary' : 'bg-secondary' }}">ምርመራ በሂደት
                                        ላይ</span>
                                    <span
                                        class="badge {{ $currentStatus >= 4 ? 'bg-primary' : 'bg-secondary' }}">ተረጋግጧል</span>
                                    <span class="badge {{ $currentStatus >= 5 ? 'bg-primary' : 'bg-secondary' }}">እርምጃ
                                        ተወስዷል</span>
                                    <span
                                        class="badge {{ $currentStatus >= 6 ? 'bg-primary' : 'bg-secondary' }}">ተዘግቷል</span>
                                </div>
                            </div>

                            <div class="current-status mb-4 p-4 bg-light rounded">
                                <h5 class="mb-3">አሁን ያለው ሁኔታ</h5>
                                <p class="mb-0">
                                    <span class="badge bg-{{ 
                                            $tip->status == 'pending' ? 'secondary' :
        ($tip->status == 'under_review' ? 'info' :
            ($tip->status == 'investigating' ? 'warning' :
                ($tip->status == 'verified' ? 'primary' :
                    ($tip->status == 'action_taken' ? 'success' : 'secondary')))) 
                                        }} p-2 fs-6">
                                        {{ $tip->status_name }}
                                    </span>
                                    <span class="ms-3 text-muted">
                                        ተላከ: {{ $tip->created_at->format('Y-m-d H:i') }}
                                    </span>
                                </p>
                            </div>

                            <div class="tip-details">
                                <h5 class="mb-3">የመረጃ ዝርዝር</h5>

                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">አይነት</th>
                                        <td>{{ $tip->tip_type_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>ቦታ</th>
                                        <td>{{ $tip->location }}</td>
                                    </tr>
                                    @if($tip->sub_city)
                                        <tr>
                                            <th>ክፍለ ከተማ</th>
                                            <td>{{ $tip->sub_city }}</td>
                                        </tr>
                                    @endif
                                    @if($tip->woreda)
                                        <tr>
                                            <th>ወረዳ</th>
                                            <td>{{ $tip->woreda }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>ዝርዝር መግለጫ</th>
                                        <td>{{ $tip->description }}</td>
                                    </tr>
                                    <tr>
                                        <th>የአስቸኳይነት ደረጃ</th>
                                        <td>
                                            <span class="badge bg-{{ 
                                                    $tip->urgency_level == 'low' ? 'secondary' :
        ($tip->urgency_level == 'medium' ? 'info' :
            ($tip->urgency_level == 'high' ? 'warning' : 'danger')) 
                                                }}">
                                                {{ $tip->urgency_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>በሂደት ላይ ነው?</th>
                                        <td>
                                            @if($tip->is_ongoing)
                                                <span class="badge bg-danger">አዎ</span>
                                            @else
                                                <span class="badge bg-secondary">አይ</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($tip->has_evidence)
                                        <tr>
                                            <th>ማስረጃ</th>
                                            <td>
                                                <span class="badge bg-success">ተያይዟል</span>
                                                @if($tip->evidence_description)
                                                    <p class="mt-2 mb-0">{{ $tip->evidence_description }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    @if($tip->eligible_for_reward)
                                        <tr>
                                            <th>ሽልማት</th>
                                            <td>
                                                @if($tip->reward_claimed)
                                                    <span class="badge bg-success">ተሰጥቷል</span>
                                                @else
                                                    <span class="badge bg-warning">በመጠባበቅ ላይ</span>
                                                @endif
                                                @if($tip->reward_amount)
                                                    <p class="mt-2 mb-0">መጠን: {{ number_format($tip->reward_amount) }} ብር</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            @if($tip->suspect_name || $tip->suspect_description || $tip->suspect_vehicle)
                                <div class="suspect-details mt-4">
                                    <h5 class="mb-3">የተጠርጣሪ መረጃ</h5>

                                    <table class="table table-bordered">
                                        @if($tip->suspect_name)
                                            <tr>
                                                <th width="200">ስም</th>
                                                <td>{{ $tip->suspect_name }}</td>
                                            </tr>
                                        @endif
                                        @if($tip->suspect_description)
                                            <tr>
                                                <th>መግለጫ</th>
                                                <td>{{ $tip->suspect_description }}</td>
                                            </tr>
                                        @endif
                                        @if($tip->suspect_vehicle)
                                            <tr>
                                                <th>ተሽከርካሪ</th>
                                                <td>{{ $tip->suspect_vehicle }}</td>
                                            </tr>
                                        @endif
                                        @if($tip->suspect_company)
                                            <tr>
                                                <th>ኩባንያ/ንግድ</th>
                                                <td>{{ $tip->suspect_company }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            @endif

                            <div class="alert alert-warning mt-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>ማሳሰቢያ፡-</strong> ይህን ገጽ በማንኛውም ጊዜ በዚሁ አገናኝ በመጠቀም መመልከት ይችላሉ። አገናኙን በአስተማማኝ ቦታ ያስቀምጡ።
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{ route('home') }}" class="btn btn-primary">
                                    <i class="bi bi-house me-2"></i>ወደ መነሻ ተመለስ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection