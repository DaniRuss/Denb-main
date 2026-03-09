@extends('layouts.portal')

@section('title', 'Report Anonymous Tip — AALEA Portal')

@section('content')

  <div class="breadcrumb-portal">
    <div class="container">
      <h2><i class="bi bi-eye-slash me-2"></i>Anonymous Tip Reporting</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active">Anonymous Tip</li>
        </ol>
      </nav>
      <p class="text-white-50 mt-2">Your identity is fully protected. Report illegal activities safely. ● ማንነትዎ ሙሉ በሙሉ
        ይጠበቃል</p>
    </div>
  </div>

  <section class="portal-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">

          {{-- Privacy Notice --}}
          <div class="alert mb-4"
            style="background: linear-gradient(135deg, #1e3a5f, #2d6a9f); color: white; border: none; border-radius: 12px;"
            data-aos="fade-up">
            <div class="row align-items-center">
              <div class="col-auto"><i class="bi bi-shield-lock fs-2 text-warning"></i></div>
              <div class="col">
                <h6 class="mb-1 text-warning fw-bold">Your Privacy is Protected</h6>
                <p class="mb-0" style="font-size:0.9rem;">
                  This tip is submitted anonymously. You can optionally provide your name for follow-up, but it is not
                  required.
                  A unique access token will be generated so you can track the status of your tip without revealing your
                  identity.
                </p>
              </div>
            </div>
          </div>

          @if($errors->any())
            <div class="alert alert-danger mb-4">
              <h6 class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following:</h6>
              <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('tip.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Reporter Info (Optional) --}}
            <div class="card form-card mb-4" data-aos="fade-up">
              <div class="form-section-header">
                <div class="d-flex align-items-center gap-3">
                  <div class="form-step-badge">1</div>
                  <div>
                    <h5 class="mb-0 fw-bold text-white"><i class="bi bi-person-circle me-2"></i>Reporter Information <span
                        class="badge bg-white text-primary ms-2" style="font-size:0.65rem;">Optional</span></h5>
                    <small class="text-white mt-1 d-block" style="opacity:0.85; font-size:0.85rem;">የጥቆማ ሰጪ መረጃ</small>
                  </div>
                </div>
              </div>
              <div class="card-body p-4">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous', '1') == '1' ? 'checked' : '' }}>
                      <label class="form-check-label fw-semibold" for="is_anonymous">
                        Submit completely anonymously (no personal info)
                      </label>
                    </div>
                  </div>
                  <div id="reporterFields" class="{{ old('is_anonymous', '1') == '1' ? 'd-none' : '' }}">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="reporter_name" class="form-control form-control-lg"
                          value="{{ old('reporter_name') }}" placeholder="Your name (optional)">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="reporter_email" class="form-control form-control-lg"
                          value="{{ old('reporter_email') }}" placeholder="your@email.com">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" name="reporter_phone" class="form-control form-control-lg"
                          value="{{ old('reporter_phone') }}" placeholder="+251 9X XXX XXXX">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Tip Details --}}
            <div class="card form-card mb-4" data-aos="fade-up" data-aos-delay="100">
              <div class="form-section-header">
                <div class="d-flex align-items-center gap-3">
                  <div class="form-step-badge">2</div>
                  <div>
                    <h5 class="mb-0 fw-bold text-white"><i class="bi bi-flag me-2"></i>Tip Details <span
                        class="text-danger">*</span></h5>
                    <small class="text-white mt-1 d-block" style="opacity:0.85; font-size:0.85rem;">የጥቆማ ዝርዝር — የታዩዎትን
                      ህገ-ወጥ ድርጊት ይግለጹ</small>
                  </div>
                </div>
              </div>
              <div class="card-body p-4">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Type of Illegal Activity <span
                        class="text-danger">*</span></label>
                    <select name="tip_type" id="tip_type"
                      class="form-select form-select-lg @error('tip_type') is-invalid @enderror" required>
                      <option value="">-- Select Type --</option>
                      <option value="illegal_trade" {{ old('tip_type') == 'illegal_trade' ? 'selected' : '' }}>Illegal Trade
                        (ህገ-ወጥ ንግድ)</option>
                      <option value="alcohol_sales" {{ old('tip_type') == 'alcohol_sales' ? 'selected' : '' }}>Illegal
                        Alcohol Sales</option>
                      <option value="land_grabbing" {{ old('tip_type') == 'land_grabbing' ? 'selected' : '' }}>Land Grabbing
                        (የመሬት ወረራ)</option>
                      <option value="drug_activity" {{ old('tip_type') == 'drug_activity' ? 'selected' : '' }}>Drug Activity
                        (አደንዛዥ እፅ)</option>
                      <option value="counterfeit_goods" {{ old('tip_type') == 'counterfeit_goods' ? 'selected' : '' }}>
                        Counterfeit Goods</option>
                      <option value="illegal_construction" {{ old('tip_type') == 'illegal_construction' ? 'selected' : '' }}>Illegal Construction</option>
                      <option value="environmental_violation" {{ old('tip_type') == 'environmental_violation' ? 'selected' : '' }}>Environmental Violation</option>
                      <option value="other" {{ old('tip_type') == 'other' ? 'selected' : '' }}>Other (ሌላ)</option>
                    </select>
                    @error('tip_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-6" id="other_type_container"
                    style="display: {{ old('tip_type') == 'other' ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">Please Specify Type <span class="text-danger">*</span></label>
                    <input type="text" name="tip_type_other"
                      class="form-control form-control-lg @error('tip_type_other') is-invalid @enderror"
                      value="{{ old('tip_type_other') }}" placeholder="Specify the tip type">
                    @error('tip_type_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Urgency Level <span class="text-danger">*</span></label>
                    <select name="urgency_level" class="form-select form-select-lg" required>
                      <option value="low" {{ old('urgency_level') == 'low' ? 'selected' : '' }}>Low</option>
                      <option value="medium" selected {{ old('urgency_level') == 'medium' ? 'selected' : '' }}>Medium
                      </option>
                      <option value="high" {{ old('urgency_level') == 'high' ? 'selected' : '' }}>High</option>
                      <option value="immediate" {{ old('urgency_level') == 'immediate' ? 'selected' : '' }}>Immediate —
                        Still Ongoing</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Location / Area <span class="text-danger">*</span></label>
                    <input type="text" name="location"
                      class="form-control form-control-lg @error('location') is-invalid @enderror"
                      value="{{ old('location') }}" placeholder="General area/neighborhood" required>
                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Sub-City</label>
                    <input type="text" name="sub_city" class="form-control form-control-lg" value="{{ old('sub_city') }}"
                      placeholder="e.g. Bole">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Woreda</label>
                    <input type="text" name="woreda" class="form-control form-control-lg" value="{{ old('woreda') }}"
                      placeholder="e.g. Woreda 3">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5"
                      placeholder="Describe what you witnessed in as much detail as possible. Include times, dates, vehicle info, etc."
                      required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Suspect Name/Description (if known)</label>
                    <input type="text" name="suspect_name" class="form-control" value="{{ old('suspect_name') }}"
                      placeholder="Name or physical description">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Vehicle Information</label>
                    <input type="text" name="suspect_vehicle" class="form-control" value="{{ old('suspect_vehicle') }}"
                      placeholder="Plate/color/model">
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Evidence Description (Optional)</label>
                    <textarea name="evidence_description" class="form-control" rows="2"
                      placeholder="Briefly describe what the evidence shows">{{ old('evidence_description') }}</textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Upload Evidence (Photos/Videos/Documents)</label>
                    <input type="file" name="evidence_files[]" class="form-control" multiple
                      accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <div class="form-text">Accepted: JPG, PNG, MP4, PDF, Word, Excel, Text. Max 10MB each.</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card form-card mb-4" data-aos="fade-up" data-aos-delay="200">
              <div class="card-body p-4">
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="tipConsent" name="consent" required>
                  <label class="form-check-label" for="tipConsent">
                    I confirm that the information I am providing is truthful to the best of my knowledge.
                  </label>
                </div>
                <div class="d-flex gap-3 justify-content-between align-items-center">
                  <div class="alert alert-warning mb-0 py-2 px-3 flex-grow-1" style="font-size:0.85rem;">
                    <i class="bi bi-shield-lock me-1"></i>
                    You will receive a <strong>unique access token</strong> after submission to track this tip
                    anonymously.
                  </div>
                  <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">
                    <i class="bi bi-send me-2"></i>Submit Tip
                  </button>
                </div>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </section>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('tip_type');
        const otherContainer = document.getElementById('other_type_container');
        const otherInput = otherContainer.querySelector('input');

        if (typeSelect) {
          typeSelect.addEventListener('change', function () {
            if (this.value === 'other') {
              otherContainer.style.display = 'block';
              otherInput.setAttribute('required', 'required');
            } else {
              otherContainer.style.display = 'none';
              otherInput.removeAttribute('required');
            }
          });
        }
      });
    </script>
  @endpush
@endsection

@push('scripts')
  <script>
    document.getElementById('is_anonymous').addEventListener('change', function () {
      const fields = document.getElementById('reporterFields');
      fields.classList.toggle('d-none', this.checked);
    });
  </script>
@endpush