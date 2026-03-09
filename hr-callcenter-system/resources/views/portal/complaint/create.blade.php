@extends('layouts.portal')

@section('title', 'Submit a Complaint — AALEA Portal')
@section('description', 'Submit a formal complaint to the Addis Ababa Law Enforcement Authority.')

@section('content')

{{-- Page Header --}}
<div class="breadcrumb-portal">
  <div class="container">
    <h2 data-aos="fade-down"><i class="bi bi-megaphone me-2"></i>Submit a Complaint</h2>
    <nav aria-label="breadcrumb" data-aos="fade-down" data-aos-delay="100">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Submit Complaint</li>
      </ol>
    </nav>
    <p class="text-white-50 mt-2" data-aos="fade-down" data-aos-delay="150">
      ቅሬታዎ ሲቀርቡ ልዩ የትኬት ቁጥር ይሰጥዎታል — Track it anytime using your ticket number.
    </p>
  </div>
</div>

<section class="portal-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        @if($errors->any())
          <div class="alert alert-danger mb-4">
            <h6 class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following:</h6>
            <ul class="mb-0 mt-2">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('complaint.store') }}" method="POST" enctype="multipart/form-data" id="complaintForm">
          @csrf

          {{-- Step 1: Complainant Info --}}
          <div class="card form-card mb-4" data-aos="fade-up">
            <div class="form-section-header">
              <div class="d-flex align-items-center gap-3">
                <div class="form-step-badge">1</div>
                <div>
                  <h5 class="mb-0 fw-bold text-white"><i class="bi bi-person-circle me-2"></i>Complainant Information</h5>
                  <small class="text-white mt-1 d-block" style="opacity:0.85; font-size:0.85rem;">ቅሬታ አቅራቢ መረጃ</small>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="full_name" class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                    value="{{ old('full_name') }}" placeholder="Enter your full name"  required>
                  @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="your@email.com" required>
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                  <input type="tel" name="phone" class="form-control form-control-lg @error('phone') is-invalid @enderror"
                    value="{{ old('phone') }}" placeholder="+251 9X XXX XXXX" required>
                  @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">National ID / Kebele ID</label>
                  <input type="text" name="id_number" class="form-control form-control-lg" value="{{ old('id_number') }}" placeholder="Optional">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Your Address</label>
                  <textarea name="address" class="form-control" rows="2" placeholder="Sub-city, Woreda, House number">{{ old('address') }}</textarea>
                </div>
              </div>
            </div>
          </div>

          {{-- Step 2: Complaint Details --}}
          <div class="card form-card mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="form-section-header">
              <div class="d-flex align-items-center gap-3">
                <div class="form-step-badge">2</div>
                <div>
                  <h5 class="mb-0 fw-bold text-white"><i class="bi bi-file-text me-2"></i>Complaint Details</h5>
                  <small class="text-white mt-1 d-block" style="opacity:0.85; font-size:0.85rem;">የቅሬታ ዝርዝር</small>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Complaint Type <span class="text-danger">*</span></label>
                  <select name="complaint_type" id="complaint_type" class="form-select form-select-lg @error('complaint_type') is-invalid @enderror" required>
                    <option value="">-- Select Type --</option>
                    <option value="illegal_trade" {{ old('complaint_type') == 'illegal_trade' ? 'selected' : '' }}>Illegal Trade (ህገ-ወጥ ንግድ)</option>
                    <option value="corruption" {{ old('complaint_type') == 'corruption' ? 'selected' : '' }}>Corruption (ሙስና)</option>
                    <option value="misconduct" {{ old('complaint_type') == 'misconduct' ? 'selected' : '' }}>Officer Misconduct (የፖሊስ ጥፋት)</option>
                    <option value="property_dispute" {{ old('complaint_type') == 'property_dispute' ? 'selected' : '' }}>Property Dispute (የንብረት ክርክር)</option>
                    <option value="harassment" {{ old('complaint_type') == 'harassment' ? 'selected' : '' }}>Harassment (ማስፈራራት)</option>
                    <option value="fraud" {{ old('complaint_type') == 'fraud' ? 'selected' : '' }}>Fraud (ማጭበርበር)</option>
                    <option value="environmental" {{ old('complaint_type') == 'environmental' ? 'selected' : '' }}>Environmental Violation</option>
                    <option value="other" {{ old('complaint_type') == 'other' ? 'selected' : '' }}>Other (ሌላ)</option>
                  </select>
                  @error('complaint_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6" id="other_type_container" style="display: {{ old('complaint_type') == 'other' ? 'block' : 'none' }};">
                  <label class="form-label fw-semibold">Please Specify Type <span class="text-danger">*</span></label>
                  <input type="text" name="complaint_type_other" class="form-control form-control-lg @error('complaint_type_other') is-invalid @enderror"
                    value="{{ old('complaint_type_other') }}" placeholder="Specify the complaint type">
                  @error('complaint_type_other')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                  <select name="priority" class="form-select form-select-lg @error('priority') is-invalid @enderror" required>
                    <option value="">-- Select Priority --</option>
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low — Not Urgent</option>
                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium — Moderate Urgency</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High — Urgent</option>
                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical — Immediate Action Needed</option>
                  </select>
                  @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Complaint Subject <span class="text-danger">*</span></label>
                  <input type="text" name="subject" class="form-control form-control-lg @error('subject') is-invalid @enderror"
                    value="{{ old('subject') }}" placeholder="Brief subject of your complaint" required>
                  @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Evidence Description <span class="badge bg-secondary ms-2" style="font-size:0.65rem;">Optional</span></label>
                  <textarea name="evidence_description" class="form-control" rows="2" 
                    placeholder="Briefly describe what the attached files show">{{ old('evidence_description') }}</textarea>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Upload Files <span class="badge bg-secondary ms-2" style="font-size:0.65rem;">Optional</span></label>
                  <input type="file" name="attachments[]" class="form-control form-control-lg @error('attachments') is-invalid @enderror"
                    multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                  <div class="form-text">Accepted: JPG, PNG, PDF, Word, Excel, Text. Max 10MB per file.</div>
                  @error('attachments')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                  <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                    rows="5" placeholder="Describe your complaint in detail. Include dates, locations, people involved, and any relevant facts..." required>{{ old('description') }}</textarea>
                  @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Incident Date</label>
                  <input type="date" name="incident_date" class="form-control form-control-lg" value="{{ old('incident_date') }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Location (Sub-City)</label>
                  <input type="text" name="incident_location" class="form-control form-control-lg" value="{{ old('incident_location') }}" placeholder="e.g. Bole Sub-City">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Woreda</label>
                  <input type="text" name="incident_woreda" class="form-control form-control-lg" value="{{ old('incident_woreda') }}" placeholder="e.g. Woreda 3">
                </div>
              </div>
            </div>
          </div>

          {{-- Step 3: Evidence --}}
          <div class="card form-card mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="form-section-header">
              <div class="d-flex align-items-center gap-3">
                <div class="form-step-badge">3</div>
                <div>
                  <h5 class="mb-0 fw-bold text-white"><i class="bi bi-paperclip me-2"></i>Supporting Evidence <span class="badge bg-white text-primary ms-2" style="font-size:0.65rem;">Optional</span></h5>
                  <small class="text-white mt-1 d-block" style="opacity:0.85; font-size:0.85rem;">ማስረጃ ያያይዙ</small>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Accused Person (if known)</label>
                  <input type="text" name="accused_name" class="form-control" value="{{ old('accused_name') }}" placeholder="Name of person/business being complained about">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Accused Organization</label>
                  <input type="text" name="accused_organization" class="form-control" value="{{ old('accused_organization') }}" placeholder="Organization/business name (if any)">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Upload Files</label>
                  <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,video/*,application/pdf,.doc,.docx">
                  <div class="form-text">Photos, videos, or documents (max 10MB each, up to 5 files). Accepted: JPG, PNG, MP4, PDF, DOC</div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Additional Notes</label>
                  <textarea name="additional_notes" class="form-control" rows="3" placeholder="Any other relevant information...">{{ old('additional_notes') }}</textarea>
                </div>
              </div>
            </div>
          </div>

          {{-- Submit --}}
          <div class="card form-card mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card-body p-4">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                <label class="form-check-label" for="consent">
                  I declare that the information provided is accurate to the best of my knowledge, and I consent to this complaint being processed by the AALEA.
                </label>
              </div>
              <div class="d-flex gap-3 justify-content-between align-items-center">
                <div class="alert alert-info mb-0 py-2 px-3 flex-grow-1" style="font-size:0.85rem;">
                  <i class="bi bi-info-circle me-1"></i>
                  After submission, you'll receive a <strong>ticket number</strong> to track your case status.
                </div>
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">
                  <i class="bi bi-send me-2"></i>Submit Complaint
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
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('complaint_type');
        const otherContainer = document.getElementById('other_type_container');
        const otherInput = otherContainer.querySelector('input');

        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
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
