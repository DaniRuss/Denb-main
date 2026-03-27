# Module One: Information & Awareness Management
## Technical Implementation Step Plan (`plan_step.md`)

**Stack:** Laravel 11 | Filament v3 | MySQL (`callcenter` DB)
**Roles:** Paramilitary (Field) · Woreda Coordinator (Approver) · Officer (Enforcement) · Admin (Analytics)

> **How to use this file:** Work through each phase sequentially. Each phase has a checklist. Check off items as you complete them. Code snippets are reference implementations — adapt to your exact context.

---

## Phase 1 — Database Schema & Migrations

### 1.1 Replace the 3 Stub Migrations

The stubs (`2026_03_25_105632_create_campaigns_table.php`, `_create_awareness_engagements_table.php`, `_create_volunteer_tips_table.php`) are currently empty. Replace each:

---

#### `campaigns` table — Full Migration

```php
// database/migrations/2026_03_25_105632_create_campaigns_table.php
Schema::create('campaigns', function (Blueprint $table) {
    $table->id();
    $table->string('campaign_code')->unique(); // e.g. CAMP-20260325-001
    $table->string('name_am');                 // Amharic title
    $table->string('name_en');                 // English title
    $table->text('description_am')->nullable();
    $table->text('description_en')->nullable();
    $table->enum('category', [
        'city_wide',       // Admin creates — cascades down
        'sub_city',
        'woreda',
    ])->default('woreda');
    // Geography — FK to existing tables
    $table->foreignId('sub_city_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('woreda_id')->nullable()->constrained()->nullOnDelete();
    // Targeting
    $table->date('start_date');
    $table->date('end_date');
    $table->text('target_audience')->nullable();
    $table->text('target_location')->nullable();    // free-text block/area
    // Status lifecycle
    $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
    // Ownership
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

---

#### `awareness_engagements` table — Full Migration

```php
// database/migrations/2026_03_25_105634_create_awareness_engagements_table.php
Schema::create('awareness_engagements', function (Blueprint $table) {
    $table->id();
    $table->string('engagement_code')->unique(); // ENG-20260325-001

    // Link to campaign
    $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();

    // Type determines which sub-fields are required (enforced in Filament form)
    $table->enum('engagement_type', [
        'house_to_house',   // ቤት ለቤት
        'coffee_ceremony',  // ቡና ጠጡ
        'organization',     // በአደረጃጀት
    ]);

    // Geography - reuse existing FK pattern from employees table
    $table->foreignId('sub_city_id')->constrained();
    $table->foreignId('woreda_id')->constrained();
    $table->string('block_number')->nullable();         // ብሎክ ቁጥር

    // Violation focus — The 9 code violation types (taxonomy)
    $table->enum('violation_type', [
        'illegal_land_invasion',       // በህገ-ወጥ መሬት ወረራ
        'illegal_construction',        // በህገ-ወጥ ግንባታ
        'illegal_expansion',           // በህገ-ወጥ ማስፋፋት
        'illegal_waste_disposal',      // በህገ-ወጥ ደረቅ እና ፍሳሽ ማስወገድ
        'road_safety',                 // መንገድ ደህንነት
        'illegal_trade',               // በህገ-ወጥ ንግድ
        'illegal_animal_trade',        // በህገ-ወጥ የእንስሳት ዝውውር/ዕርድ
        'disturbing_acts',             // በአዋኪ ድርጊት
        'illegal_advertisement',       // በህገ-ወጥ ማስታወቂያ
    ]);

    // Recurrence tracking — ለስንተኛ ግዜ
    $table->unsignedTinyInteger('round_number')->default(1);

    // ── House-to-House specific (nullable; populated when type = house_to_house)
    $table->string('citizen_name')->nullable();
    $table->enum('citizen_gender', ['male', 'female'])->nullable();
    $table->unsignedTinyInteger('citizen_age')->nullable();

    // ── Coffee Ceremony specific (nullable; populated when type = coffee_ceremony)
    $table->unsignedSmallInteger('headcount')->nullable();
    $table->string('stakeholder_partner')->nullable(); // ባለድርሻ አካል

    // ── Organization specific (nullable; populated when type = organization)
    $table->enum('organization_type', [
        'womens_association',    // ሴት ማህበር
        'youth_association',     // ወጣት ማህበር
        'edir',                  // እድር
        'religious_institution', // የሀይማኖት ተቋማት
        'block_leaders',         // ብሎክ አመራሮች
        'peace_army',            // የሰላም ሰራዊት
        'equb',                  // እቁብ
    ])->nullable();
    $table->unsignedSmallInteger('org_headcount_male')->nullable();
    $table->unsignedSmallInteger('org_headcount_female')->nullable();

    // Timestamp of the session itself (not created_at — the actual field time)
    $table->dateTime('session_datetime');

    // Personnel — ግንዛቤ ፈጣሪው ባለሞያ
    $table->foreignId('created_by')->constrained('users'); // Field Officer (Paramilitary)

    // ── Approval Workflow ──
    $table->enum('status', [
        'draft',      // saved but not submitted
        'submitted',  // waiting for Woreda Coordinator review
        'approved',   // signed off (የረጋገጠው ሀላፊ ስም)
        'rejected',   // sent back for correction
    ])->default('draft');

    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->text('rejection_note')->nullable(); // Coordinator's reason when rejecting

    $table->timestamps();
    $table->softDeletes();
});
```

---

#### `engagement_attendees` table — New Migration (Supplementary)

For coffee_ceremony and organization types, individual demographic rows are needed:

```php
// Create new file: 2026_03_25_200000_create_engagement_attendees_table.php
Schema::create('engagement_attendees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('engagement_id')->constrained('awareness_engagements')->cascadeOnDelete();
    $table->string('name_am');
    $table->enum('gender', ['male', 'female']);
    $table->unsignedTinyInteger('age')->nullable();
    $table->timestamps();
});
```

---

#### `volunteer_tips` table — Full Migration (Tikoma — ጥቆማ)

```php
// database/migrations/2026_03_25_105636_create_volunteer_tips_table.php
Schema::create('volunteer_tips', function (Blueprint $table) {
    $table->id();
    $table->string('tip_code')->unique(); // TIP-20260325-001

    // Link to the engagement that generated this tip (optional — tip can stand alone)
    $table->foreignId('engagement_id')->nullable()->constrained('awareness_engagements')->nullOnDelete();

    // Suspect Information
    $table->string('suspect_name')->nullable();
    $table->enum('violation_type', [
        'illegal_land_invasion', 'illegal_construction', 'illegal_expansion',
        'illegal_waste_disposal', 'road_safety', 'illegal_trade',
        'illegal_animal_trade', 'disturbing_acts', 'illegal_advertisement',
    ]);
    $table->string('violation_location');     // ቀጣና / Block / precise address
    $table->foreignId('sub_city_id')->constrained();
    $table->foreignId('woreda_id')->constrained();
    $table->string('block_number')->nullable();
    $table->date('violation_date');
    $table->date('reported_date');

    // Volunteer Reporter — ጥቆማ ያቀረበ
    $table->string('volunteer_name')->nullable();      // may be anonymous
    $table->boolean('is_anonymous')->default(false);
    $table->string('volunteer_signature_path')->nullable(); // scanned/photo path

    // Intake
    $table->foreignId('received_by')->constrained('users'); // Paramilitary who logged it

    // Approval chain (mirrors engagement workflow)
    $table->enum('status', [
        'pending',        // logged, awaiting Woreda Coordinator review
        'verified',       // Coordinator approved — visible to Officer
        'investigating',  // Officer has picked up
        'action_taken',   // Officer closed with an action
        'false_report',   // Coordinator or Officer dismissed
    ])->default('pending');

    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();

    // Enforcement (Officer layer)
    $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('action_taken', [
        'formal_warning',
        'financial_penalty',
        'asset_confiscation',
        'legal_referral',
        'no_action',
    ])->nullable();
    $table->text('action_notes')->nullable();           // የተወሰደ እርምጃ - full notes
    $table->date('action_date')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

---

#### `confiscated_assets` table — New Migration (Officer Sub-Module)

```php
// Create: 2026_03_25_200001_create_confiscated_assets_table.php
Schema::create('confiscated_assets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('volunteer_tip_id')->constrained()->cascadeOnDelete();
    $table->string('item_description');
    $table->decimal('estimated_value', 12, 2)->nullable();
    $table->string('seizure_location');
    $table->foreignId('seized_by')->constrained('users');
    $table->date('seizure_date');
    $table->enum('handover_status', ['impounded', 'auctioned', 'destroyed', 'returned'])->default('impounded');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

**Phase 1 Checklist:**
- [ ] Replace stub migration: `campaigns` with full schema above
- [ ] Replace stub migration: `awareness_engagements` with full schema above
- [ ] Replace stub migration: `volunteer_tips` with full schema above
- [ ] Create new migration: `engagement_attendees`
- [ ] Create new migration: `confiscated_assets`
- [ ] Run: `php artisan migrate`
- [ ] Verify all tables created: `php artisan tinker --execute="echo implode(', ', array_keys(DB::connection()->getDoctrineSchemaManager()->listTableNames()));"`

---

## Phase 2 — Eloquent Models & Relationships

### 2.1 `Campaign` Model

```php
// app/Models/Campaign.php
class Campaign extends Model {
    use SoftDeletes;
    protected $fillable = ['campaign_code','name_am','name_en','description_am',
        'description_en','category','sub_city_id','woreda_id','start_date',
        'end_date','target_audience','target_location','status','created_by'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->campaign_code = 'CAMP-'.date('Ymd').'-'.str_pad(random_int(0,9999),4,'0',STR_PAD_LEFT));
    }

    public function engagements()  { return $this->hasMany(AwarenessEngagement::class); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }
    public function subCity()      { return $this->belongsTo(SubCity::class); }
    public function woreda()       { return $this->belongsTo(Woreda::class); }

    // Scope: only active campaigns (used by Field Officer form dropdown)
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
```

### 2.2 `AwarenessEngagement` Model

```php
// app/Models/AwarenessEngagement.php
class AwarenessEngagement extends Model {
    use SoftDeletes;
    protected $fillable = [/* all columns from migration */];
    protected $casts   = ['session_datetime' => 'datetime', 'approved_at' => 'timestamp'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->engagement_code = 'ENG-'.date('Ymd').'-'.str_pad(random_int(0,9999),4,'0',STR_PAD_LEFT));
    }

    public function campaign()    { return $this->belongsTo(Campaign::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function attendees()   { return $this->hasMany(EngagementAttendee::class, 'engagement_id'); }
    public function volunteerTips() { return $this->hasMany(VolunteerTip::class, 'engagement_id'); }
    public function subCity()     { return $this->belongsTo(SubCity::class); }
    public function woreda()      { return $this->belongsTo(Woreda::class); }

    // Block-level scope — Paramilitary sees only their woreda
    public function scopeForUser($q, User $user) {
        if ($user->hasRole('paramilitary')) {
            return $q->where('created_by', $user->id);
        }
        if ($user->hasRole('woreda_coordinator')) {
            // Assumes User has woreda_id — add to users table in Phase 1 if missing
            return $q->where('woreda_id', $user->woreda_id);
        }
        return $q; // Admin / Officer sees all
    }

    public function scopePendingApproval($q) { return $q->where('status', 'submitted'); }

    // Violation type label map (used by Accessors and Filament formatStateUsing)
    public static function violationLabels(): array {
        return [
            'illegal_land_invasion'  => 'በህገ-ወጥ መሬት ወረራ',
            'illegal_construction'   => 'በህገ-ወጥ ግንባታ',
            'illegal_expansion'      => 'በህገ-ወጥ ማስፋፋት',
            'illegal_waste_disposal' => 'በህገ-ወጥ ደረቅ እና ፍሳሽ ማስወገድ',
            'road_safety'            => 'መንገድ ደህንነት',
            'illegal_trade'          => 'በህገ-ወጥ ንግድ',
            'illegal_animal_trade'   => 'በህገ-ወጥ የእንስሳት ዝውውር/ዕርድ',
            'disturbing_acts'        => 'በአዋኪ ድርጊት',
            'illegal_advertisement'  => 'በህገ-ወጥ ማስታወቂያ',
        ];
    }
}
```

### 2.3 `VolunteerTip` Model

```php
// app/Models/VolunteerTip.php
class VolunteerTip extends Model {
    use SoftDeletes;
    protected $fillable = [/* all columns */];
    protected $casts = ['violation_date' => 'date', 'reported_date' => 'date',
                        'verified_at' => 'timestamp', 'action_date' => 'date', 'is_anonymous' => 'boolean'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->tip_code = 'TIP-'.date('Ymd').'-'.str_pad(random_int(0,9999),4,'0',STR_PAD_LEFT));
    }

    public function engagement()    { return $this->belongsTo(AwarenessEngagement::class); }
    public function receivedBy()    { return $this->belongsTo(User::class, 'received_by'); }
    public function verifiedBy()    { return $this->belongsTo(User::class, 'verified_by'); }
    public function investigatedBy(){ return $this->belongsTo(User::class, 'investigated_by'); }
    public function assets()        { return $this->hasMany(ConfiscatedAsset::class, 'volunteer_tip_id'); }

    // Only Officers can see verified tips
    public function scopeVerified($q) { return $q->where('status', 'verified'); }
    public function scopeForOfficer($q) {
        return $q->whereIn('status', ['verified', 'investigating', 'action_taken']);
    }
}
```

### 2.4 Add `woreda_id` to `users` table (if not present)

```php
// Create: 2026_03_26_000001_add_woreda_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('woreda_id')->nullable()->after('id')->constrained()->nullOnDelete();
    $table->foreignId('sub_city_id')->nullable()->after('woreda_id')->constrained()->nullOnDelete();
});
```

**Phase 2 Checklist:**
- [ ] Update `Campaign` model with all relationships and auto-code boot
- [ ] Update `AwarenessEngagement` model with scopes and violation label map
- [ ] Update `VolunteerTip` model with status scopes
- [ ] Create `EngagementAttendee` model
- [ ] Create `ConfiscatedAsset` model
- [ ] Add migration for `woreda_id` / `sub_city_id` on `users` table
- [ ] Run `php artisan migrate` and verify with `php artisan tinker`

---

## Phase 3 — Filament Resource Development

### 3.1 Campaign Resource (Admin Only)

**File:** `app/Filament/Resources/CampaignResource.php`

```php
// Navigation group mirrors the existing pattern in TipResource
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
protected static ?string $navigationLabel = 'Campaigns | ዘመቻዎች';
protected static ?int $navigationSort = 1;

// Restrict creation to Admin only
public static function canCreate(): bool {
    return auth()->user()->hasAnyRole(['admin', 'super_admin']);
}

public static function form(Schema $schema): Schema {
    return $schema->schema([
        Forms\Components\TextInput::make('name_am')->label('Campaign Name (Amharic)')->required(),
        Forms\Components\TextInput::make('name_en')->label('Campaign Name (English)')->required(),
        Forms\Components\Select::make('category')
            ->options(['city_wide' => 'City Wide', 'sub_city' => 'Sub-City', 'woreda' => 'Woreda Level'])
            ->required()->live(),
        Forms\Components\Select::make('sub_city_id')->relationship('subCity', 'name')->searchable(),
        Forms\Components\Select::make('woreda_id')->relationship('woreda', 'name')->searchable(),
        Forms\Components\DatePicker::make('start_date')->required(),
        Forms\Components\DatePicker::make('end_date')->required()->after('start_date'),
        Forms\Components\Textarea::make('target_audience')->columnSpanFull(),
        Forms\Components\Select::make('status')
            ->options(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
            ->default('draft'),
    ]);
}
```

---

### 3.2 Awareness Engagement Resource (Core Module Resource)

**File:** `app/Filament/Resources/AwarenessEngagementResource.php`

This is the central, most complex resource. Key: the form must be **dynamically conditional** on `engagement_type`.

```php
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
protected static ?string $navigationLabel = 'Engagement Logs | ምዝገባ';
protected static ?int $navigationSort = 2;

public static function form(Schema $schema): Schema {
    return $schema->schema([

        // ── Section 1: Campaign Link ──
        Forms\Components\Select::make('campaign_id')
            ->relationship('campaign', 'name_am', fn($q) => $q->active())
            ->label('Campaign (ዘመቻ)')->required()->searchable(),

        // ── Section 2: Engagement Type — This drives conditional visibility ──
        Forms\Components\Select::make('engagement_type')
            ->label('Engagement Type (የግንዛቤ ዓይነት)')
            ->options([
                'house_to_house'  => 'ቤት ለቤት (House to House)',
                'coffee_ceremony' => 'ቡና ጠጡ (Coffee Ceremony)',
                'organization'    => 'በአደረጃጀት (Organization)',
            ])
            ->required()->live(), // live() triggers reactive visibility

        // ── Section 3: Geography ──
        Forms\Components\Select::make('sub_city_id')->relationship('subCity', 'name')->required(),
        Forms\Components\Select::make('woreda_id')->relationship('woreda', 'name')->required(),
        Forms\Components\TextInput::make('block_number')->label('Block No. (ብሎክ ቁጥር)'),

        // ── Section 4: Violation Type ——
        Forms\Components\Select::make('violation_type')
            ->label('Violation Type (የደንብ መተላለፍ ዓይነት)')
            ->options(AwarenessEngagement::violationLabels())
            ->required(),

        Forms\Components\TextInput::make('round_number')
            ->label('Round / ዙር (ለስንተኛ ግዜ)')
            ->numeric()->default(1)->required(),

        // ── Section 5A: House-to-House Fields (visible only when type = house_to_house) ──
        Forms\Components\Section::make('ቤት ለቤት — Citizen Details')
            ->schema([
                Forms\Components\TextInput::make('citizen_name')->label('Citizen Name (ስም)'),
                Forms\Components\Select::make('citizen_gender')->label('Gender (ጾታ)')
                    ->options(['male' => 'Male / 남성', 'female' => 'Female / ሴት']),
                Forms\Components\TextInput::make('citizen_age')->label('Age (እድሜ)')->numeric(),
            ])
            ->visible(fn(\Filament\Forms\Get $get) => $get('engagement_type') === 'house_to_house'),

        // ── Section 5B: Coffee Ceremony Fields ──
        Forms\Components\Section::make('ቡና ጠጡ — Group Details')
            ->schema([
                Forms\Components\TextInput::make('headcount')->label('Total Headcount (ብዛት)')->numeric()->required(),
                Forms\Components\TextInput::make('stakeholder_partner')->label('Stakeholder Partner (ባለድርሻ አካል)'),
                Forms\Components\Repeater::make('attendees')
                    ->relationship('attendees')
                    ->schema([
                        Forms\Components\TextInput::make('name_am')->label('Name'),
                        Forms\Components\Select::make('gender')->options(['male' => 'Male', 'female' => 'Female']),
                        Forms\Components\TextInput::make('age')->numeric(),
                    ])
                    ->collapsible()->label('Individual Attendees (optional)'),
            ])
            ->visible(fn(\Filament\Forms\Get $get) => $get('engagement_type') === 'coffee_ceremony'),

        // ── Section 5C: Organization Fields ──
        Forms\Components\Section::make('በአደረጃጀት — Organization Details')
            ->schema([
                Forms\Components\Select::make('organization_type')
                    ->label('Organization (አደረጃጀት ስም)')
                    ->options([
                        'womens_association'    => 'ሴት ማህበር (Women\'s Association)',
                        'youth_association'     => 'ወጣት ማህበር (Youth Association)',
                        'edir'                  => 'እድር (Edir)',
                        'religious_institution' => 'የሀይማኖት ተቋማት (Religious Institution)',
                        'block_leaders'         => 'ብሎክ አመራሮች (Block Leaders)',
                        'peace_army'            => 'የሰላም ሰራዊት (Peace Army)',
                        'equb'                  => 'እቁብ (Equb)',
                    ]),
                Forms\Components\TextInput::make('org_headcount_male')->label('Male Headcount')->numeric(),
                Forms\Components\TextInput::make('org_headcount_female')->label('Female Headcount')->numeric(),
            ])
            ->visible(fn(\Filament\Forms\Get $get) => $get('engagement_type') === 'organization'),

        // ── Section 6: Timestamp ──
        Forms\Components\DateTimePicker::make('session_datetime')
            ->label('Session Date/Time (ሰዓት፣ ቀን)')->required(),
    ]);
}
```

---

### 3.3 Volunteer Tip (Tikoma) Resource

**File:** `app/Filament/Resources/VolunteerTipResource.php`

```php
// Paramilitary creates; Woreda Coordinator verifies; Officer takes action
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';
protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
protected static ?string $navigationLabel = 'Volunteer Tips | ጥቆማ';
protected static ?int $navigationSort = 3;

public static function form(Schema $schema): Schema {
    return $schema->schema([
        Forms\Components\Select::make('engagement_id')
            ->relationship('engagement', 'engagement_code')->label('Linked Engagement (optional)')->searchable(),
        Forms\Components\TextInput::make('suspect_name')->label('Suspect Name (ስም)'),
        Forms\Components\Select::make('violation_type')
            ->options(AwarenessEngagement::violationLabels())->required(),
        Forms\Components\TextInput::make('violation_location')->label('Location (ቦታ / ቀጣና)')->required(),
        Forms\Components\Select::make('sub_city_id')->relationship('subCity', 'name')->required(),
        Forms\Components\Select::make('woreda_id')->relationship('woreda', 'name')->required(),
        Forms\Components\TextInput::make('block_number')->label('Block No.'),
        Forms\Components\DatePicker::make('violation_date')->label('Date of Violation (ቀን)')->required(),
        Forms\Components\DatePicker::make('reported_date')->label('Date Reported')->default(now())->required(),
        Forms\Components\TextInput::make('volunteer_name')->label('Volunteer\'s Name (ጥቆማ ያቀረበው)'),
        Forms\Components\Toggle::make('is_anonymous')->label('Anonymous Tip?'),
    ]);
}

// Table actions mirror TipResource pattern (verify, action_taken)
public static function table(Table $table): Table {
    return $table
        ->modifyQueryUsing(fn($q) => $q->forOfficer()) // Officers see verified+
        ->actions([
            Action::make('verify')
                ->label('Verify / ያረጋግጡ')
                ->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn($r) => $r->status === 'pending' && auth()->user()->hasRole('woreda_coordinator'))
                ->action(fn($r) => $r->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()])),

            Action::make('take_action')
                ->label('Log Action / እርምጃ ይዝገቡ')
                ->icon('heroicon-o-shield-check')->color('danger')
                ->visible(fn($r) => in_array($r->status, ['verified','investigating']) && auth()->user()->hasRole(['officer','admin']))
                ->form([
                    Forms\Components\Select::make('action_taken')
                        ->options([
                            'formal_warning'    => 'Formal Warning',
                            'financial_penalty' => 'Financial Penalty / ቅጣት',
                            'asset_confiscation'=> 'Asset Confiscation / ዕቃ ሙሌቀት',
                            'legal_referral'    => 'Legal Referral',
                            'no_action'         => 'No Action',
                        ])->required(),
                    Forms\Components\Textarea::make('action_notes')->label('Notes (ማስታወሻ)'),
                    Forms\Components\DatePicker::make('action_date')->label('Action Date')->default(now()),
                ])
                ->action(fn($r, $d) => $r->update([
                    'action_taken' => $d['action_taken'],
                    'action_notes' => $d['action_notes'],
                    'action_date'  => $d['action_date'],
                    'status'       => 'action_taken',
                    'investigated_by' => auth()->id(),
                ])),
        ]);
}
```

**Phase 3 Checklist:**
- [ ] Create `CampaignResource.php` + `Pages/` subfolder (ListCampaigns, CreateCampaign, EditCampaign)
- [ ] Create `AwarenessEngagementResource.php` + Pages subfolder
- [ ] Create `VolunteerTipResource.php` + Pages subfolder (List, View)
- [ ] Register all 3 resources in `AdminPanelProvider.php`
- [ ] Test conditional form visibility: select each `engagement_type` and confirm correct section appears/disappears
- [ ] Test role-based `canCreate()` / `modifyQueryUsing` scoping

---

## Phase 4 — Multi-Tier Approval Workflow

### 4.1 Submission by Paramilitary

When a Field Officer finishes logging, they click **"Submit for Review"** — not auto-submit. This maps to changing `status` from `draft` → `submitted`.

Add a table action in `AwarenessEngagementResource`:

```php
Action::make('submit')
    ->label('Submit for Approval / ቀድሞ ያቅርቡ')
    ->icon('heroicon-o-paper-airplane')->color('info')
    ->visible(fn($r) => $r->status === 'draft' && $r->created_by === auth()->id())
    ->requiresConfirmation()
    ->action(fn($r) => $r->update(['status' => 'submitted'])),
```

### 4.2 Coordinator Approval Queue

Woreda Coordinator sees a filtered list of `status = submitted` engagements scoped to their `woreda_id`:

```php
// In AwarenessEngagementResource::table()
->modifyQueryUsing(function ($q) {
    $user = auth()->user();
    if ($user->hasRole('woreda_coordinator')) {
        return $q->pendingApproval()->where('woreda_id', $user->woreda_id);
    }
    if ($user->hasRole('paramilitary')) {
        return $q->where('created_by', $user->id);
    }
    return $q;
})
```

Add approve/reject actions:

```php
Action::make('approve')
    ->label('Approve / ያረጋግጡ (የረጋገጠው ሀላፊ)')
    ->icon('heroicon-o-check-circle')->color('success')
    ->visible(fn($r) => $r->status === 'submitted' && auth()->user()->hasRole('woreda_coordinator'))
    ->action(fn($r) => $r->update([
        'status'      => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
    ])),

Action::make('reject')
    ->label('Reject / ይመልሱ')
    ->icon('heroicon-o-x-circle')->color('danger')
    ->form([Forms\Components\Textarea::make('rejection_note')->required()])
    ->visible(fn($r) => $r->status === 'submitted' && auth()->user()->hasRole('woreda_coordinator'))
    ->action(fn($r, $d) => $r->update([
        'status'         => 'rejected',
        'rejection_note' => $d['rejection_note'],
    ])),
```

### 4.3 Officer Enforcement Closure

Per `VolunteerTipResource`, the `take_action` action (Phase 3) automatically closes the intelligence loop. When `action_taken` is `asset_confiscation`, trigger asset registration by redirecting to `ConfiscatedAssetResource::create` with the tip_id pre-filled.

**Phase 4 Checklist:**
- [ ] Add `submit` action to engagement table with role check
- [ ] Implement `modifyQueryUsing` scoping per role
- [ ] Add `approve` / `reject` actions with coordinator guard
- [ ] Implement Officer `take_action` on verified tips
- [ ] Test full workflow: Paramilitary → Submit → Coordinator Approve → Officer Action

---

## Phase 5 — Scoped Dashboards & Analytics Widgets

### 5.1 Widget Structure

Create widgets in `app/Filament/Widgets/`:

| Widget Class | Role Scope | Description |
|---|---|---|
| `CampaignStatsWidget` | Admin/All | Total campaigns, active vs. completed |
| `EngagementByTypeChart` | Admin/Coordinator | Pie: H2H vs Coffee vs Org |
| `ViolationHeatmapWidget` | Admin | Bar chart: violations by type |
| `WeredaEngagementWidget` | Woreda Coord | Stats filtered to own woreda |
| `PendingApprovalsWidget` | Woreda Coord | Count of pending engagements |
| `OfficerActionSummaryWidget` | Officer/Admin | Tips resolved vs. open |

### 5.2 Example: Pending Approvals Widget

```php
// app/Filament/Widgets/PendingApprovalsWidget.php
class PendingApprovalsWidget extends StatsOverviewWidget {
    public function getStats(): array {
        $user = auth()->user();
        $base = AwarenessEngagement::pendingApproval();

        if ($user->hasRole('woreda_coordinator')) {
            $base = $base->where('woreda_id', $user->woreda_id);
        }

        return [
            Stat::make('Pending Engagement Reviews', $base->count())
                ->description('Awaiting your sign-off (የረጋገጠው ሀላፊ)')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Pending Tips (Tikoma)', VolunteerTip::where('status','pending')
                    ->when($user->hasRole('woreda_coordinator'), fn($q) => $q->where('woreda_id', $user->woreda_id))
                    ->count())
                ->icon('heroicon-o-light-bulb')->color('danger'),
        ];
    }
}
```

### 5.3 Example: Violation Heatmap Widget

```php
// app/Filament/Widgets/ViolationHeatmapWidget.php
class ViolationHeatmapWidget extends ChartWidget {
    protected static ?string $heading = 'Violations by Type | ጥሰት ዓይነቶች';

    protected function getData(): array {
        $labels = AwarenessEngagement::violationLabels();
        $data   = AwarenessEngagement::where('status', 'approved')
            ->selectRaw('violation_type, count(*) as total')
            ->groupBy('violation_type')
            ->pluck('total', 'violation_type')->toArray();

        return [
            'datasets' => [['label' => 'Engagements', 'data' => array_values($data), 'backgroundColor' => '#f59e0b']],
            'labels'   => array_values($labels),
        ];
    }
    protected function getType(): string { return 'bar'; }
}
```

**Phase 5 Checklist:**
- [ ] Create all 6 widget classes
- [ ] Register widgets in `AdminPanelProvider.php`
- [ ] Apply `canView()` guards per role on each widget
- [ ] Test Admin sees city-wide chart; Coordinator sees only their Woreda stats

---

## Phase 6 — Localization (English / Amharic Toggle)

### 6.1 Create Translation Files

```
resources/lang/
├── en/
│   └── awareness.php
└── am/
    └── awareness.php
```

**`resources/lang/am/awareness.php`:**
```php
return [
    'engagement_type' => 'የግንዛቤ ዓይነት',
    'violation_type'  => 'የደንብ መተላለፍ ዓይነት',
    'round_number'    => 'ዙር (ለስንተኛ ግዜ)',
    'citizen_name'    => 'ስም',
    'citizen_gender'  => 'ጾታ',
    'citizen_age'     => 'እድሜ',
    'session_datetime'=> 'ሰዓት፣ ቀን',
    'approved_by'     => 'የረጋገጠው ሀላፊ ስም',
    'volunteer_name'  => 'ጥቆማ ያቀረበው',
    'action_taken'    => 'የተወሰደ እርምጃ አይነት',
    // ... etc
];
```

### 6.2 Use in Filament Labels

```php
Forms\Components\TextInput::make('citizen_name')
    ->label(__('awareness.citizen_name')),
```

**Phase 6 Checklist:**
- [ ] Create `resources/lang/am/awareness.php` with all module field keys
- [ ] Create `resources/lang/en/awareness.php` (mirrors with English values)
- [ ] Update `.env` with `APP_LOCALE=am` or build a per-user locale toggle
- [ ] Replace all hardcoded `->label()` strings in the 3 new Resources with `__('awareness.*')` keys

---

## Final Integration Checklist

- [ ] **Phase 1** — All migrations run cleanly with `php artisan migrate`
- [ ] **Phase 2** — All Models created; run `php artisan tinker` and verify relationships load
- [ ] **Phase 3** — 3 new Filament Resources registered and listed in Navigation
- [ ] **Phase 4** — Submit → Approve → Action workflow tested end-to-end with 3 test users
- [ ] **Phase 5** — Widgets visible on dashboard (role-appropriate)
- [ ] **Phase 6** — Labels render in Amharic when locale is `am`
- [ ] **Smoke Test:** Create 1 Campaign (Admin) → Create 1 H2H Engagement linked to it (Paramilitary) → Submit → Approve (Coordinator) → Create 1 Volunteer Tip → Verify (Coordinator) → Take Action (Officer) → Confirm status = `action_taken`
