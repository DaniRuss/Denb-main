<?php

namespace Database\Seeders;

use App\Models\ActionType;
use App\Models\ConfiscatedAsset;
use App\Models\Employee;
use App\Models\FollowUpAction;
use App\Models\IncidentReport;
use App\Models\PenaltyAssignment;
use App\Models\PenaltyReceipt;
use App\Models\PenaltySchedule;
use App\Models\PenaltyType;
use App\Models\SubCity;
use App\Models\User;
use App\Models\ViolationRecord;
use App\Models\ViolationType;
use App\Models\Violator;
use App\Models\WarningLetter;
use App\Models\Woreda;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PenaltyModuleSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // REFERENCE DATA
        // ============================================================
        $addisKetema = SubCity::find(1);
        $akakiKality = SubCity::find(2);
        $arada       = SubCity::find(3);
        $bole        = SubCity::find(4);

        $akW1 = Woreda::where('sub_city_id', 1)->orderBy('id')->first();
        $akW2 = Woreda::where('sub_city_id', 1)->orderBy('id')->skip(1)->first();
        $aqW1 = Woreda::where('sub_city_id', 2)->orderBy('id')->first();
        $arW1 = Woreda::where('sub_city_id', 3)->orderBy('id')->first();
        $arW2 = Woreda::where('sub_city_id', 3)->orderBy('id')->skip(1)->first();
        $blW1 = Woreda::where('sub_city_id', 4)->orderBy('id')->first();

        // Users
        $admin          = User::find(1);   // Super Admin
        $officerAKW1    = User::find(4);   // Officer Test → AK W01
        $officerAKW2    = User::find(5);   // Officer Boka → AK W02
        $supervisorAK   = User::find(10);  // Abera Supervisor → AK W01
        $officerAradaW1 = User::find(11);  // Arada1 Officer → Arada W01
        $officerAkaki   = User::find(12);  // Akaki Officer → Akaki W01
        $supervisorAkaki = User::find(18); // superisorr20 → Akaki W01
        $supervisorArada = User::find(19); // arada supervisor → Arada (all)
        $officerAradaW2 = User::find(20);  // arada01 Officer → Arada W02
        $officerBole    = User::find(21);  // Olyad Akaki → Bole W01

        // Assign locations
        $officerAKW1->update(['sub_city' => 1, 'woreda' => $akW1->id]);
        $officerAKW2->update(['sub_city' => 1, 'woreda' => $akW2->id]);
        $supervisorAK->update(['sub_city' => 1, 'woreda' => $akW1->id]);
        $officerAradaW1->update(['sub_city' => 3, 'woreda' => $arW1->id]);
        $officerAradaW2->update(['sub_city' => 3, 'woreda' => $arW2->id]);
        $supervisorArada->update(['sub_city' => 3, 'woreda' => null]);
        $officerAkaki->update(['sub_city' => 2, 'woreda' => $aqW1->id]);
        $supervisorAkaki->update(['sub_city' => 2, 'woreda' => $aqW1->id]);
        $officerBole->update(['sub_city' => 4, 'woreda' => $blW1->id]);

        // Lookup tables (keep existing or create)
        $penaltyTypes = [];
        foreach ([
            ['name' => 'Written Warning', 'default_duration_days' => 30, 'description' => 'Formal written warning'],
            ['name' => 'Suspension', 'default_duration_days' => 14, 'description' => 'Temporary suspension'],
            ['name' => 'Demotion', 'default_duration_days' => 180, 'description' => 'Reduction in rank'],
            ['name' => 'Dismissal', 'default_duration_days' => null, 'description' => 'Termination'],
            ['name' => 'Fine Deduction', 'default_duration_days' => 1, 'description' => 'Salary deduction'],
        ] as $pt) {
            $penaltyTypes[] = PenaltyType::firstOrCreate(['name' => $pt['name']], $pt + ['is_active' => true]);
        }

        $actionTypes = [];
        foreach ([
            ['name' => 'Verbal Counseling', 'description' => 'Informal discussion'],
            ['name' => 'Written Notice', 'description' => 'Formal notice'],
            ['name' => 'Asset Seizure', 'description' => 'Confiscation'],
            ['name' => 'Court Referral', 'description' => 'Court system referral'],
            ['name' => 'Task Force Referral', 'description' => 'Task force escalation'],
            ['name' => 'Follow-up Inspection', 'description' => 'Re-inspection'],
        ] as $at) {
            $actionTypes[] = ActionType::firstOrCreate(['name' => $at['name']], $at + ['is_active' => true]);
        }

        // Penalty Schedules & Violation Types
        $vtIds = [];
        foreach ([
            ['name_am' => 'ቀላል ጥፋት', 'name_en' => 'Minor Offenses', 'level' => 1, 'description' => 'Minor violations', 'violations' => [
                ['code' => 'M-001', 'name_am' => 'ያልተፈቀደ ምልክት መለጠፍ', 'name_en' => 'Unauthorized signage', 'regulation_reference' => 'ደንብ 64/2009 አንቀጽ 12', 'fine_amount' => 500, 'min_fine' => 200, 'max_fine' => 1000],
                ['code' => 'M-002', 'name_am' => 'ጥራት ያልጠበቀ ቆሻሻ ማስወገድ', 'name_en' => 'Improper waste disposal', 'regulation_reference' => 'ደንብ 64/2009 አንቀጽ 15', 'fine_amount' => 300, 'min_fine' => 100, 'max_fine' => 500],
                ['code' => 'M-003', 'name_am' => 'የንግድ ቦታ ንፅህና አለመጠበቅ', 'name_en' => 'Cleanliness violation', 'regulation_reference' => 'ደንብ 64/2009 አንቀጽ 18', 'fine_amount' => 400, 'min_fine' => 200, 'max_fine' => 800],
            ]],
            ['name_am' => 'መካከለኛ ጥፋት', 'name_en' => 'Moderate Offenses', 'level' => 2, 'description' => 'Moderate violations', 'violations' => [
                ['code' => 'MD-001', 'name_am' => 'ያልተፈቀደ ግንባታ', 'name_en' => 'Unauthorized construction', 'regulation_reference' => 'ደንብ 150/2015 አንቀጽ 22', 'fine_amount' => 5000, 'min_fine' => 2000, 'max_fine' => 10000],
                ['code' => 'MD-002', 'name_am' => 'የመንገድ ላይ ንግድ ያለ ፈቃድ', 'name_en' => 'Street vending without permit', 'regulation_reference' => 'ደንብ 64/2009 አንቀጽ 25', 'fine_amount' => 2000, 'min_fine' => 1000, 'max_fine' => 5000],
                ['code' => 'MD-003', 'name_am' => 'ህገ-ወጥ ማስታወቂያ', 'name_en' => 'Illegal advertisement', 'regulation_reference' => 'ደንብ 64/2009 አንቀጽ 28', 'fine_amount' => 3000, 'min_fine' => 1500, 'max_fine' => 7000],
            ]],
            ['name_am' => 'ከባድ ጥፋት', 'name_en' => 'Severe Offenses', 'level' => 3, 'description' => 'Severe violations', 'violations' => [
                ['code' => 'S-001', 'name_am' => 'ህገ-ወጥ ግንባታ ማስፋፋት', 'name_en' => 'Illegal construction expansion', 'regulation_reference' => 'ደንብ 150/2015 አንቀጽ 35', 'fine_amount' => 25000, 'min_fine' => 10000, 'max_fine' => 50000],
                ['code' => 'S-002', 'name_am' => 'የአካባቢ ብክለት', 'name_en' => 'Environmental pollution', 'regulation_reference' => 'ደንብ 150/2015 አንቀጽ 40', 'fine_amount' => 15000, 'min_fine' => 5000, 'max_fine' => 30000],
            ]],
        ] as $sData) {
            $violations = $sData['violations'];
            unset($sData['violations']);
            $schedule = PenaltySchedule::firstOrCreate(['level' => $sData['level']], $sData + ['is_active' => true]);
            foreach ($violations as $vd) {
                $vt = ViolationType::firstOrCreate(['code' => $vd['code']], $vd + ['penalty_schedule_id' => $schedule->id, 'is_active' => true]);
                $vtIds[$vd['code']] = $vt->id;
            }
        }

        // ============================================================
        // SCENARIO 2: DIRECT FINE → PAID ON TIME
        // Street vendor caught → receipt issued → paid within 3 days
        // Location: Addis Ketema W01 (Officer Test sees this)
        // ============================================================
        $this->command->info('--- Scenario 2: Direct Fine → Paid On Time ---');

        $v_scenario2 = Violator::create([
            'type' => 'individual', 'full_name_am' => 'ተስፋዬ በቀለ', 'full_name_en' => 'Tesfaye Bekele',
            'phone' => '0911111111', 'id_number' => 'SC2-001',
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ገበያ ዋና መንገድ',
        ]);

        $rec_sc2 = ViolationRecord::create([
            'violator_id' => $v_scenario2->id, 'violation_type_id' => $vtIds['MD-002'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'block' => 'A-3', 'specific_location' => 'ፒያሳ ገበያ ዋና መንገድ',
            'violation_date' => Carbon::now()->subDays(10), 'violation_time' => '08:30:00',
            'regulation_number' => 'ደንብ 64/2009', 'article' => '25',
            'fine_amount' => 2000.00, 'status' => 'paid',
            'action_taken' => 'ቅጣት ደረሰኝ ተሰጥቷል። በ3 ቀን ውስጥ ክፍያ ተፈጽሟል።',
            'reported_by' => $officerAKW1->id, 'verified_by' => $supervisorAK->id,
        ]);

        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc2->id, 'receipt_number' => 'PR-2026-SC2-001',
            'issued_date' => Carbon::now()->subDays(10), 'issued_time' => '09:00:00',
            'fine_amount' => 2000.00, 'payment_deadline' => Carbon::now()->subDays(7),
            'paid_date' => Carbon::now()->subDays(8), 'paid_amount' => 2000.00,
            'payment_status' => 'paid',
            'issued_by' => $officerAKW1->id,
        ]);

        // ============================================================
        // SCENARIO 3: RECEIPT REFUSED → 3 WITNESSES REQUIRED
        // Violator refuses receipt → 3 officers sign → posted at location
        // Location: Arada W01 (Arada1 Officer sees this)
        // ============================================================
        $this->command->info('--- Scenario 3: Receipt Refused + 3 Witnesses ---');

        $v_scenario3 = Violator::create([
            'type' => 'individual', 'full_name_am' => 'ከበደ ታደሰ', 'full_name_en' => 'Kebede Tadesse',
            'phone' => '0922222222', 'id_number' => 'SC3-001',
            'sub_city_id' => $arada->id, 'woreda_id' => $arW1->id,
            'specific_location' => 'አራዳ ጊዮርጊስ አካባቢ',
        ]);

        $rec_sc3 = ViolationRecord::create([
            'violator_id' => $v_scenario3->id, 'violation_type_id' => $vtIds['MD-003'],
            'sub_city_id' => $arada->id, 'woreda_id' => $arW1->id,
            'specific_location' => 'አራዳ ጊዮርጊስ ዋና መንገድ',
            'violation_date' => Carbon::now()->subDays(5), 'violation_time' => '10:15:00',
            'regulation_number' => 'ደንብ 64/2009', 'article' => '28',
            'fine_amount' => 3000.00, 'status' => 'payment_pending',
            'action_taken' => 'ደንብ ተላላፊው ደረሰኙን አልቀበልም ብሏል። 3 ምስክሮች ፈርመዋል።',
            'reported_by' => $officerAradaW1->id, 'verified_by' => $supervisorArada->id,
        ]);

        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc3->id, 'receipt_number' => 'PR-2026-SC3-001',
            'issued_date' => Carbon::now()->subDays(5), 'issued_time' => '10:30:00',
            'fine_amount' => 3000.00, 'payment_deadline' => Carbon::now()->subDays(2),
            'payment_status' => 'pending',
            'receipt_refused' => true,
            'issued_by' => $officerAradaW1->id,
            'witness_officer_1' => $officerAradaW2->id,
            'witness_officer_2' => $supervisorArada->id,
            'witness_officer_3' => $admin->id,
            'notes' => 'ደንብ ተላላፊው ደረሰኙን አልቀበልም ብሏል። ማስታወቂያው በቦታው ተለጥፏል።',
        ]);

        // ============================================================
        // SCENARIO 4: NON-PAYMENT → COURT ESCALATION (FINE DOUBLED)
        // Receipt issued → 3 days pass → overdue → court filed → fine x2
        // Location: Addis Ketema W02 (Officer Boka sees this)
        // ============================================================
        $this->command->info('--- Scenario 4: Non-Payment → Court (Fine Doubled) ---');

        $v_scenario4 = Violator::create([
            'type' => 'individual', 'full_name_am' => 'አለሙ ገብረ', 'full_name_en' => 'Alemu Gebre',
            'phone' => '0933333333', 'id_number' => 'SC4-001',
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW2->id,
            'specific_location' => 'መርካቶ ገበያ',
        ]);

        $rec_sc4 = ViolationRecord::create([
            'violator_id' => $v_scenario4->id, 'violation_type_id' => $vtIds['MD-002'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW2->id,
            'block' => 'B-7', 'specific_location' => 'መርካቶ ገበያ ውስጥ',
            'violation_date' => Carbon::now()->subDays(20), 'violation_time' => '14:00:00',
            'regulation_number' => 'ደንብ 64/2009', 'article' => '25',
            'fine_amount' => 2000.00, 'status' => 'court_filed',
            'action_taken' => 'በ3 ቀን ውስጥ ክፍያ አልፈጸመም። ክስ ቀርቧል። ቅጣት እጥፍ ሆኗል (4,000 ብር)።',
            'reported_by' => $officerAKW2->id, 'verified_by' => $supervisorAK->id,
        ]);

        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc4->id, 'receipt_number' => 'PR-2026-SC4-001',
            'issued_date' => Carbon::now()->subDays(20), 'issued_time' => '14:30:00',
            'fine_amount' => 2000.00, 'payment_deadline' => Carbon::now()->subDays(17),
            'payment_status' => 'court_filed',
            'is_court_case' => true,
            'court_filed_date' => Carbon::now()->subDays(15),
            'court_fine_amount' => 4000.00,  // DOUBLED
            'issued_by' => $officerAKW2->id,
            'notes' => 'በ3 ቀን ክፍያ አልተፈጸመም። ክስ ቀረበ። ቅጣት እጥፍ ሆነ።',
        ]);

        // ============================================================
        // SCENARIO 5: 3-DAY WARNING → COMPLIED
        // Unauthorized construction → 3-day warning → violator demolished
        // Location: Arada W02 (arada01 Officer sees this)
        // ============================================================
        $this->command->info('--- Scenario 5a: 3-Day Warning → Complied ---');

        $v_scenario5a = Violator::create([
            'type' => 'individual', 'full_name_am' => 'ሰለሞን ዘውዴ', 'full_name_en' => 'Solomon Zewde',
            'phone' => '0944444444', 'id_number' => 'SC5A-001',
            'sub_city_id' => $arada->id, 'woreda_id' => $arW2->id,
            'specific_location' => 'ሰዳስት ኪሎ', 'house_number' => 'H-22',
        ]);

        $rec_sc5a = ViolationRecord::create([
            'violator_id' => $v_scenario5a->id, 'violation_type_id' => $vtIds['MD-001'],
            'sub_city_id' => $arada->id, 'woreda_id' => $arW2->id,
            'specific_location' => 'ሰዳስት ኪሎ ቤት ቁ. H-22',
            'violation_date' => Carbon::now()->subDays(12), 'violation_time' => '11:00:00',
            'regulation_number' => 'ደንብ 150/2015', 'article' => '22',
            'fine_amount' => 5000.00, 'status' => 'closed',
            'action_taken' => 'የ3 ቀን ማስጠንቀቂያ ተሰጠ። ደንብ ተላላፊው በጊዜው አፍርሷል። ጉዳይ ተዘግቷል።',
            'reported_by' => $officerAradaW2->id, 'verified_by' => $supervisorArada->id,
        ]);

        WarningLetter::create([
            'violation_record_id' => $rec_sc5a->id, 'reference_number' => 'WL-2026-SC5A-001',
            'warning_type' => 'three_day',
            'issued_date' => Carbon::now()->subDays(12), 'deadline' => Carbon::now()->subDays(9),
            'complied' => true, 'complied_at' => Carbon::now()->subDays(10),
            'regulation_number' => 'ደንብ 150/2015', 'article' => '22',
            'delivery_method' => 'in_person', 'violator_accepted' => true,
            'issued_by' => $officerAradaW2->id, 'issued_by_officer_2' => $supervisorArada->id,
        ]);

        // ============================================================
        // SCENARIO 5b: 3-DAY WARNING → NOT COMPLIED → PENALTY ISSUED
        // Warning given → deadline passed → penalty receipt issued
        // Location: Addis Ketema W01
        // ============================================================
        $this->command->info('--- Scenario 5b: 3-Day Warning → Not Complied → Penalty ---');

        $v_scenario5b = Violator::create([
            'type' => 'organization', 'full_name_am' => 'ፍቅር ንግድ ድርጅት', 'full_name_en' => 'Fikir Trading PLC',
            'phone' => '0115555555', 'id_number' => 'SC5B-BIZ-001',
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ንግድ ማዕከል', 'house_number' => 'Shop-14',
        ]);

        $rec_sc5b = ViolationRecord::create([
            'violator_id' => $v_scenario5b->id, 'violation_type_id' => $vtIds['M-001'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'block' => 'A-1', 'specific_location' => 'ፒያሳ ንግድ ማዕከል Shop-14',
            'violation_date' => Carbon::now()->subDays(8), 'violation_time' => '09:00:00',
            'regulation_number' => 'ደንብ 64/2009', 'article' => '12',
            'fine_amount' => 500.00, 'status' => 'penalty_issued',
            'action_taken' => 'የ3 ቀን ማስጠንቀቂያ ተሰጠ። ባለመፈጸሙ ቅጣት ደረሰኝ ተሰጥቷል።',
            'reported_by' => $officerAKW1->id, 'verified_by' => $supervisorAK->id,
        ]);

        WarningLetter::create([
            'violation_record_id' => $rec_sc5b->id, 'reference_number' => 'WL-2026-SC5B-001',
            'warning_type' => 'three_day',
            'issued_date' => Carbon::now()->subDays(8), 'deadline' => Carbon::now()->subDays(5),
            'complied' => false,
            'regulation_number' => 'ደንብ 64/2009', 'article' => '12',
            'delivery_method' => 'in_person', 'violator_accepted' => true,
            'issued_by' => $officerAKW1->id,
        ]);

        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc5b->id, 'receipt_number' => 'PR-2026-SC5B-001',
            'issued_date' => Carbon::now()->subDays(4), 'issued_time' => '10:00:00',
            'fine_amount' => 500.00, 'payment_deadline' => Carbon::now()->subDays(1),
            'payment_status' => 'pending',
            'issued_by' => $officerAKW1->id,
        ]);

        // ============================================================
        // SCENARIO 6: 24-HOUR WARNING → TASK FORCE DEMOLITION
        // Severe violation → 24hr warning → not complied → task force
        // Location: Bole W01 (Olyad Akaki sees this)
        // ============================================================
        $this->command->info('--- Scenario 6: 24-Hour Warning → Task Force Demolition ---');

        $v_scenario6 = Violator::create([
            'type' => 'individual', 'full_name_am' => 'ሙሉጌታ አሰፋ', 'full_name_en' => 'Mulugeta Assefa',
            'phone' => '0966666666', 'id_number' => 'SC6-001',
            'sub_city_id' => $bole->id, 'woreda_id' => $blW1->id,
            'specific_location' => 'ቦሌ ሜዳ አካባቢ', 'house_number' => 'N/A',
        ]);

        $rec_sc6 = ViolationRecord::create([
            'violator_id' => $v_scenario6->id, 'violation_type_id' => $vtIds['S-001'],
            'sub_city_id' => $bole->id, 'woreda_id' => $blW1->id,
            'block' => 'D-2', 'specific_location' => 'ቦሌ ሜዳ ህገ-ወጥ ግንባታ',
            'violation_date' => Carbon::now()->subDays(30), 'violation_time' => '07:00:00',
            'regulation_number' => 'ደንብ 150/2015', 'article' => '35',
            'fine_amount' => 25000.00, 'status' => 'closed',
            'action_taken' => 'የ24 ሰዓት ማስጠንቀቂያ ተሰጠ። ባለመፈጸሙ በግብረ ኃይል ፈርሷል።',
            'investigation_notes' => 'ህገ-ወጥ ግንባታ በመንግስት መሬት ላይ። ከ24 ሰዓት ማስጠንቀቂያ በኋላ ግብረ ኃይል ተሰማርቷል።',
            'reported_by' => $officerBole->id,
        ]);

        WarningLetter::create([
            'violation_record_id' => $rec_sc6->id, 'reference_number' => 'WL-2026-SC6-001',
            'warning_type' => 'twenty_four_hour',
            'issued_date' => Carbon::now()->subDays(30), 'deadline' => Carbon::now()->subDays(29),
            'complied' => false,
            'escalated_to_task_force' => true, 'escalation_date' => Carbon::now()->subDays(29),
            'regulation_number' => 'ደንብ 150/2015', 'article' => '35',
            'delivery_method' => 'in_person', 'violator_accepted' => true,
            'issued_by' => $officerBole->id,
            'notes' => 'ንብረትዎን በ24 ሰዓት ውስጥ ያውጡ። በግብረ ኃይል ይፈርሳል።',
        ]);

        // ============================================================
        // SCENARIO 7: BEYOND OFFICER CAPACITY → TASK FORCE
        // Large-scale violation → officer reports to shift leader → task force
        // Location: Akaki Kality W01
        // ============================================================
        $this->command->info('--- Scenario 7: Task Force Escalation (Beyond Officer Capacity) ---');

        $v_scenario7 = Violator::create([
            'type' => 'organization', 'full_name_am' => 'ኢትዮ ኮንስትራክሽን', 'full_name_en' => 'Ethio Construction PLC',
            'phone' => '0115777777', 'id_number' => 'SC7-BIZ-001',
            'sub_city_id' => $akakiKality->id, 'woreda_id' => $aqW1->id,
            'specific_location' => 'ካልቲ ኢንዱስትሪ ዞን',
        ]);

        $rec_sc7 = ViolationRecord::create([
            'violator_id' => $v_scenario7->id, 'violation_type_id' => $vtIds['S-001'],
            'sub_city_id' => $akakiKality->id, 'woreda_id' => $aqW1->id,
            'block' => 'IND-5', 'specific_location' => 'ካልቲ ኢንዱስትሪ ዞን ብሎክ 5',
            'violation_date' => Carbon::now()->subDays(3), 'violation_time' => '06:00:00',
            'regulation_number' => 'ደንብ 150/2015', 'article' => '35',
            'fine_amount' => 25000.00, 'status' => 'open',
            'action_taken' => 'ከኦፊሰሩ አቅም በላይ። ለሽፍት መሪ ተሳውቋል። ግብረ ኃይል ያስፈልጋል።',
            'investigation_notes' => 'ትልቅ ድርጅት በሰፊ ቦታ ላይ ህገ-ወጥ ግንባታ እየገነባ ነው። ግብረ ኃይል አፍራሽ ቡድን ያስፈልጋል።',
            'reported_by' => $officerAkaki->id, 'verified_by' => $supervisorAkaki->id,
        ]);

        // ============================================================
        // SCENARIO 8: REPEAT OFFENDER (3rd TIME)
        // Same violator caught 3 times → escalating penalties
        // Location: Addis Ketema W01
        // ============================================================
        $this->command->info('--- Scenario 8: Repeat Offender (3 Violations) ---');

        $v_scenario8 = Violator::create([
            'type' => 'individual', 'full_name_am' => 'ዳዊት መኮንን', 'full_name_en' => 'Dawit Mekonnen',
            'phone' => '0988888888', 'id_number' => 'SC8-001',
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ገበያ',
        ]);

        // 1st offense - 45 days ago, paid
        $rec_sc8_1 = ViolationRecord::create([
            'violator_id' => $v_scenario8->id, 'violation_type_id' => $vtIds['M-002'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ገበያ ደቡብ በር',
            'violation_date' => Carbon::now()->subDays(45), 'regulation_number' => 'ደንብ 64/2009', 'article' => '15',
            'fine_amount' => 300.00, 'repeat_offense_count' => 0, 'status' => 'paid',
            'action_taken' => 'የመጀመሪያ ጥፋት። ቅጣት ደረሰኝ ተሰጥቷል። ክፍያ ተፈጽሟል።',
            'reported_by' => $officerAKW1->id,
        ]);
        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc8_1->id, 'receipt_number' => 'PR-2026-SC8-001',
            'issued_date' => Carbon::now()->subDays(45), 'fine_amount' => 300.00,
            'payment_deadline' => Carbon::now()->subDays(42), 'paid_date' => Carbon::now()->subDays(43),
            'paid_amount' => 300.00, 'payment_status' => 'paid',
            'issued_by' => $officerAKW1->id,
        ]);

        // 2nd offense - 20 days ago, warning + paid late
        $rec_sc8_2 = ViolationRecord::create([
            'violator_id' => $v_scenario8->id, 'violation_type_id' => $vtIds['M-002'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ገበያ ደቡብ በር',
            'violation_date' => Carbon::now()->subDays(20), 'regulation_number' => 'ደንብ 64/2009', 'article' => '15',
            'fine_amount' => 300.00, 'repeat_offense_count' => 1, 'status' => 'paid',
            'action_taken' => '2ኛ ጥፋት። ማስጠንቀቂያ ከቅጣት ጋር ተሰጥቷል።',
            'reported_by' => $officerAKW1->id, 'verified_by' => $supervisorAK->id,
        ]);
        WarningLetter::create([
            'violation_record_id' => $rec_sc8_2->id, 'reference_number' => 'WL-2026-SC8-001',
            'warning_type' => 'three_day',
            'issued_date' => Carbon::now()->subDays(20), 'deadline' => Carbon::now()->subDays(17),
            'complied' => true, 'complied_at' => Carbon::now()->subDays(18),
            'regulation_number' => 'ደንብ 64/2009', 'article' => '15',
            'delivery_method' => 'in_person', 'violator_accepted' => true,
            'issued_by' => $officerAKW1->id,
        ]);
        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc8_2->id, 'receipt_number' => 'PR-2026-SC8-002',
            'issued_date' => Carbon::now()->subDays(20), 'fine_amount' => 300.00,
            'payment_deadline' => Carbon::now()->subDays(17), 'paid_date' => Carbon::now()->subDays(16),
            'paid_amount' => 300.00, 'payment_status' => 'paid',
            'issued_by' => $officerAKW1->id,
        ]);

        // 3rd offense - 2 days ago, court escalation
        $rec_sc8_3 = ViolationRecord::create([
            'violator_id' => $v_scenario8->id, 'violation_type_id' => $vtIds['M-002'],
            'sub_city_id' => $addisKetema->id, 'woreda_id' => $akW1->id,
            'specific_location' => 'ፒያሳ ገበያ ደቡብ በር',
            'violation_date' => Carbon::now()->subDays(2), 'regulation_number' => 'ደንብ 64/2009', 'article' => '15',
            'fine_amount' => 300.00, 'repeat_offense_count' => 2, 'status' => 'penalty_issued',
            'action_taken' => '3ኛ ጥፋት! ተደጋጋሚ ደንብ ተላላፊ። ቅጣት ተሰጥቷል። ለፍርድ ቤት ሊቀርብ ይችላል።',
            'investigation_notes' => 'ተደጋጋሚ ደንብ ተላላፊ - 3ኛ ጊዜ በተመሳሳይ ቦታ ተመሳሳይ ጥፋት።',
            'reported_by' => $officerAKW1->id, 'verified_by' => $supervisorAK->id,
        ]);
        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc8_3->id, 'receipt_number' => 'PR-2026-SC8-003',
            'issued_date' => Carbon::now()->subDays(2), 'fine_amount' => 300.00,
            'payment_deadline' => Carbon::now()->addDays(1),
            'payment_status' => 'pending',
            'issued_by' => $officerAKW1->id,
        ]);

        // ============================================================
        // SCENARIO 9-14: FULL ASSET LIFECYCLE
        // Seizure → Handover → Estimation → Transfer → Sale (60/40)
        // + Perishable fast-track + Unsellable disposal
        // Location: Arada W01
        // ============================================================
        $this->command->info('--- Scenario 9-14: Full Asset Lifecycle ---');

        $v_scenario9 = Violator::create([
            'type' => 'organization', 'full_name_am' => 'ህንፃ ግንባታ ድርጅት', 'full_name_en' => 'Hinsa Construction',
            'phone' => '0115999999', 'id_number' => 'SC9-BIZ-001',
            'sub_city_id' => $arada->id, 'woreda_id' => $arW1->id,
            'specific_location' => 'አራት ኪሎ ግንባታ ቦታ',
        ]);

        $rec_sc9 = ViolationRecord::create([
            'violator_id' => $v_scenario9->id, 'violation_type_id' => $vtIds['S-001'],
            'sub_city_id' => $arada->id, 'woreda_id' => $arW1->id,
            'block' => 'C-8', 'specific_location' => 'አራት ኪሎ ግንባታ ቦታ',
            'violation_date' => Carbon::now()->subDays(25), 'violation_time' => '07:30:00',
            'regulation_number' => 'ደንብ 150/2015', 'article' => '35',
            'fine_amount' => 25000.00, 'status' => 'penalty_issued',
            'action_taken' => 'ንብረት ተወርሷል። ማስጠንቀቂያ ተሰጥቷል። ቅጣት ደረሰኝ ተሰጥቷል።',
            'investigation_notes' => 'ህገ-ወጥ ግንባታ ማስፋፋት። ብረት፣ ሲሚንቶ፣ አለት ተወርሷል። የሚበላሽ ምግብም ተወርሷል።',
            'reported_by' => $officerAradaW1->id, 'verified_by' => $supervisorArada->id,
        ]);

        WarningLetter::create([
            'violation_record_id' => $rec_sc9->id, 'reference_number' => 'WL-2026-SC9-001',
            'warning_type' => 'three_day',
            'issued_date' => Carbon::now()->subDays(25), 'deadline' => Carbon::now()->subDays(22),
            'complied' => false,
            'escalated_to_task_force' => true, 'escalation_date' => Carbon::now()->subDays(22),
            'regulation_number' => 'ደንብ 150/2015', 'article' => '35',
            'delivery_method' => 'in_person', 'violator_accepted' => false,
            'issued_by' => $officerAradaW1->id, 'issued_by_officer_2' => $supervisorArada->id,
            'notes' => 'ደንብ ተላላፊው ማስጠንቀቂያ አልተቀበለም። ንብረት ለመውረስ ተወስኗል።',
        ]);

        PenaltyReceipt::create([
            'violation_record_id' => $rec_sc9->id, 'receipt_number' => 'PR-2026-SC9-001',
            'issued_date' => Carbon::now()->subDays(21), 'issued_time' => '08:00:00',
            'fine_amount' => 25000.00, 'payment_deadline' => Carbon::now()->subDays(18),
            'payment_status' => 'overdue',
            'issued_by' => $officerAradaW1->id,
        ]);

        // Asset 1: Non-perishable → Handed over → Estimated → Transferred → SOLD (60/40)
        ConfiscatedAsset::create([
            'violation_record_id' => $rec_sc9->id,
            'description' => 'የግንባታ ብረት 12mm (Construction iron bars 12mm)',
            'quantity' => 100, 'unit' => 'ቁጥር', 'is_perishable' => false,
            'seized_date' => Carbon::now()->subDays(22), 'seizure_receipt_number' => 'SR-2026-SC9-001',
            'seized_by' => $officerAradaW1->id,
            'handover_date' => Carbon::now()->subDays(22), 'received_by' => $supervisorArada->id,
            'estimated_value' => 35000.00,
            'transferred_date' => Carbon::now()->subDays(19), 'transferred_to_sub_city_id' => $arada->id,
            'sold_amount' => 30000.00, 'authority_share' => 18000.00, 'city_finance_share' => 12000.00,
            'status' => 'sold',
            'notes' => '60% ለባለስልጣን (18,000) + 40% ለከተማ ፋይናንስ (12,000)።',
        ]);

        // Asset 2: Non-perishable → Handed over → Estimated → Transferred (awaiting sale)
        ConfiscatedAsset::create([
            'violation_record_id' => $rec_sc9->id,
            'description' => 'ሲሚንቶ ከረጢት (Cement bags - Derba brand)',
            'quantity' => 50, 'unit' => 'ከረጢት', 'is_perishable' => false,
            'seized_date' => Carbon::now()->subDays(22), 'seizure_receipt_number' => 'SR-2026-SC9-002',
            'seized_by' => $officerAradaW1->id,
            'handover_date' => Carbon::now()->subDays(22), 'received_by' => $supervisorArada->id,
            'estimated_value' => 22500.00,
            'transferred_date' => Carbon::now()->subDays(19), 'transferred_to_sub_city_id' => $arada->id,
            'status' => 'transferred',
            'notes' => 'ወደ ክ/ከተማ ግምጃ ቤት ተላልፏል። ጨረታ ይጠበቃል።',
        ]);

        // Asset 3: Perishable → Fast-track sale (same day)
        ConfiscatedAsset::create([
            'violation_record_id' => $rec_sc9->id,
            'description' => 'የግንባታ ሰራተኞች ምግብ ቁሳቁስ (Perishable food items)',
            'quantity' => 1, 'unit' => 'ሎት', 'is_perishable' => true,
            'seized_date' => Carbon::now()->subDays(22), 'seizure_receipt_number' => 'SR-2026-SC9-003',
            'seized_by' => $officerAradaW1->id,
            'handover_date' => Carbon::now()->subDays(22), 'received_by' => $supervisorArada->id,
            'estimated_value' => 2000.00,
            'sold_amount' => 1500.00, 'authority_share' => 900.00, 'city_finance_share' => 600.00,
            'status' => 'sold',
            'notes' => 'የሚበላሽ ንብረት - በተመሳሳይ ቀን በወረዳ ደረጃ ተሸጧል።',
        ]);

        // Asset 4: Non-perishable → Cannot be sold → DISPOSED
        ConfiscatedAsset::create([
            'violation_record_id' => $rec_sc9->id,
            'description' => 'የተሰባበረ ብሎኬት (Broken concrete blocks)',
            'quantity' => 200, 'unit' => 'ቁጥር', 'is_perishable' => false,
            'seized_date' => Carbon::now()->subDays(22), 'seizure_receipt_number' => 'SR-2026-SC9-004',
            'seized_by' => $officerAradaW1->id,
            'handover_date' => Carbon::now()->subDays(22), 'received_by' => $supervisorArada->id,
            'estimated_value' => 0.00,
            'disposal_reason' => 'ንብረቱ ተሰብሯል። ለጨረታ ማቅረብ አይቻልም። ኮሚቴ ቃለ ጉባኤ ይዟል።',
            'status' => 'disposed',
            'notes' => 'በአስወጋጅ ኮሚቴ ውሳኔ መሰረት ተወግዷል።',
        ]);

        // Asset 5: Just seized (Day 0 - fresh)
        ConfiscatedAsset::create([
            'violation_record_id' => $rec_sc9->id,
            'description' => 'የግንባታ አለት (Construction gravel)',
            'quantity' => 10, 'unit' => 'ኩንታል', 'is_perishable' => false,
            'seized_date' => Carbon::now(), 'seizure_receipt_number' => 'SR-2026-SC9-005',
            'seized_by' => $officerAradaW1->id,
            'status' => 'seized',
            'notes' => 'ዛሬ ተወርሷል። ለወረዳ ንብረት ክፍል ማስረከብ ያስፈልጋል።',
        ]);

        // ============================================================
        // SCENARIO 15-16: INTERNAL HR INCIDENTS
        // ============================================================
        $this->command->info('--- Scenario 15-16: Internal HR Incidents ---');

        $employees = Employee::take(3)->get();
        if ($employees->isNotEmpty()) {
            // Incident 1: Officer absent from post → written warning + fine deduction
            $inc1 = IncidentReport::create([
                'employee_id' => $employees[0]->id,
                'incident_type' => 'misconduct', 'location' => 'Addis Ketema W01 block A-3',
                'incident_date' => Carbon::now()->subDays(15),
                'description' => 'ኦፊሰሩ ያለ ፈቃድ ከስምሪት ቦታው ተገኝቷል። በስምሪት ካርድ ላይ የተመደበው ቦታ ላይ አልነበረም።',
                'status' => 'penalty_assigned',
                'reported_by' => $supervisorAK->id,
            ]);

            PenaltyAssignment::create([
                'incident_report_id' => $inc1->id, 'penalty_type_id' => $penaltyTypes[0]->id,
                'assigned_date' => Carbon::now()->subDays(12), 'due_date' => Carbon::now()->addDays(18),
                'duration_days' => 30, 'status' => 'assigned',
                'notes' => 'የጽሁፍ ማስጠንቀቂያ ተሰጥቷል።',
                'assigned_by' => $supervisorAK->id, 'assigned_to' => $employees[0]->id,
            ]);

            PenaltyAssignment::create([
                'incident_report_id' => $inc1->id, 'penalty_type_id' => $penaltyTypes[4]->id,
                'assigned_date' => Carbon::now()->subDays(12), 'due_date' => Carbon::now()->subDays(1),
                'duration_days' => 1, 'status' => 'completed',
                'notes' => 'የ1 ቀን ደመወዝ ቅነሳ ተፈጽሟል።',
                'assigned_by' => $admin->id, 'assigned_to' => $employees[0]->id,
            ]);

            // Incident 2: Failed shift reports → counseling → follow-up
            $inc2 = IncidentReport::create([
                'employee_id' => $employees->count() > 1 ? $employees[1]->id : $employees[0]->id,
                'incident_type' => 'non_compliance', 'location' => 'Arada W01 patrol area',
                'incident_date' => Carbon::now()->subDays(5),
                'description' => 'ለ3 ተከታታይ ቀናት የውሎ ሪፖርት አላቀረበም።',
                'status' => 'in_follow_up',
                'reported_by' => $officerAradaW1->id,
            ]);

            FollowUpAction::create([
                'incident_report_id' => $inc2->id, 'action_type_id' => $actionTypes[0]->id,
                'due_date' => Carbon::now()->subDays(3), 'status' => 'done',
                'completed_at' => Carbon::now()->subDays(3),
                'notes' => 'የቃል ምክር ተሰጥቷል። ኦፊሰሩ ችግሩን ተቀብሏል።',
                'assigned_by' => $supervisorArada->id, 'assigned_to' => $officerAradaW1->id,
            ]);

            FollowUpAction::create([
                'incident_report_id' => $inc2->id, 'action_type_id' => $actionTypes[5]->id,
                'due_date' => Carbon::now()->addDays(7), 'status' => 'pending',
                'notes' => 'ከ1 ሳምንት በኋላ የውሎ ሪፖርት ማቅረብ ተከታታይ ምርመራ።',
                'assigned_by' => $supervisorArada->id, 'assigned_to' => $officerAradaW1->id,
            ]);

            // Incident 3: Attendance issue → in progress
            $inc3 = IncidentReport::create([
                'employee_id' => $employees->count() > 2 ? $employees[2]->id : $employees[0]->id,
                'incident_type' => 'attendance', 'location' => 'Bole W01 checkpoint',
                'incident_date' => Carbon::now()->subDays(3),
                'description' => 'በዚህ ወር 5 ጊዜ ለጠዋት ሽፍት ዘግይቷል። ከ1:45 ፈንታ ከ2:30 በኋላ ይመጣል።',
                'status' => 'reported',
                'reported_by' => $officerBole->id,
            ]);

            FollowUpAction::create([
                'incident_report_id' => $inc3->id, 'action_type_id' => $actionTypes[1]->id,
                'due_date' => Carbon::now()->addDays(1), 'status' => 'in_progress',
                'notes' => 'የጽሁፍ ማስታወቂያ እየተዘጋጀ ነው።',
                'assigned_by' => $officerBole->id, 'assigned_to' => $officerBole->id,
            ]);
        }

        // ============================================================
        // SUMMARY
        // ============================================================
        $this->command->info('');
        $this->command->info('======================================');
        $this->command->info(' PENALTY MODULE SEEDED SUCCESSFULLY');
        $this->command->info('======================================');
        $this->command->info('');
        $this->command->info('SCENARIO 2:  Direct Fine → Paid (AK W01)');
        $this->command->info('SCENARIO 3:  Receipt Refused + 3 Witnesses (Arada W01)');
        $this->command->info('SCENARIO 4:  Non-Payment → Court, Fine Doubled (AK W02)');
        $this->command->info('SCENARIO 5a: 3-Day Warning → Complied (Arada W02)');
        $this->command->info('SCENARIO 5b: 3-Day Warning → Not Complied → Penalty (AK W01)');
        $this->command->info('SCENARIO 6:  24-Hour Warning → Task Force Demolition (Bole W01)');
        $this->command->info('SCENARIO 7:  Task Force Escalation - Beyond Capacity (Akaki W01)');
        $this->command->info('SCENARIO 8:  Repeat Offender - 3 violations (AK W01)');
        $this->command->info('SCENARIO 9:  Asset Seizure + Handover (Arada W01)');
        $this->command->info('SCENARIO 10: Asset Estimation + Transfer (Arada W01)');
        $this->command->info('SCENARIO 11: Asset Sold - 60/40 split (Arada W01)');
        $this->command->info('SCENARIO 12: Perishable Fast-Track Sale (Arada W01)');
        $this->command->info('SCENARIO 13: Unsellable → Disposed (Arada W01)');
        $this->command->info('SCENARIO 14: Fresh Seizure - Day 0 (Arada W01)');
        $this->command->info('SCENARIO 15: HR Misconduct → Penalty Assignment');
        $this->command->info('SCENARIO 16: HR Non-Compliance → Follow-up Actions');
        $this->command->info('');
        $this->command->info('DATA COUNTS:');
        $this->command->info('  ' . Violator::count() . ' violators | ' . ViolationRecord::count() . ' violation records');
        $this->command->info('  ' . WarningLetter::count() . ' warning letters | ' . PenaltyReceipt::count() . ' penalty receipts');
        $this->command->info('  ' . ConfiscatedAsset::count() . ' confiscated assets | ' . IncidentReport::count() . ' incidents');
        $this->command->info('');
        $this->command->info('SCOPING TEST (who sees what in Violation Records):');
        $this->command->info('  Admin [1]:             ALL records');
        $this->command->info('  Officer Test [4]:      AK W01 → SC2, SC5b, SC8(x3) = 5 records');
        $this->command->info('  Officer Boka [5]:      AK W02 → SC4 = 1 record');
        $this->command->info('  Abera Supervisor [10]: AK W01 → SC2, SC5b, SC8(x3) = 5 records');
        $this->command->info('  Arada1 Officer [11]:   Arada W01 → SC3, SC9 = 2 records');
        $this->command->info('  arada01 Officer [20]:  Arada W02 → SC5a = 1 record');
        $this->command->info('  arada supervisor [19]: Arada ALL → SC3, SC5a, SC9 = 3 records');
        $this->command->info('  Olyad Akaki [21]:      Bole W01 → SC6 = 1 record');
        $this->command->info('  Akaki Officer [12]:    Akaki W01 → SC7 = 1 record');
    }
}
