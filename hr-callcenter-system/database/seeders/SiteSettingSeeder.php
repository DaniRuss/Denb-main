<?php
// database/seeders/SiteSettingSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // General
            ['key' => 'organization_name_am', 'value' => 'የደንብ ማስከበር ባለስልጣን', 'type' => 'text', 'group' => 'general'],
            ['key' => 'organization_name_en', 'value' => 'Law Enforcement Authority', 'type' => 'text', 'group' => 'general'],
            ['key' => 'tagline_am', 'value' => 'የሰው ሀብት አስተዳደር ዳይሬክቶሬት', 'type' => 'text', 'group' => 'general'],
            ['key' => 'tagline_en', 'value' => 'Human Resource Management Directorate', 'type' => 'text', 'group' => 'general'],
            ['key' => 'footer_text_am', 'value' => 'መብቱ በህግ የተጠበቀ ነው', 'type' => 'text', 'group' => 'general'],
            ['key' => 'footer_text_en', 'value' => 'All rights reserved', 'type' => 'text', 'group' => 'general'],
            ['key' => 'copyright_text', 'value' => '© 2024 Law Enforcement Authority. All rights reserved.', 'type' => 'text', 'group' => 'general'],

            // Contact
            ['key' => 'phone_primary', 'value' => '+251 11 123 4567', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'phone_secondary', 'value' => '+251 11 765 4321', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'email_primary', 'value' => 'info@lawenforcement.gov.et', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'email_secondary', 'value' => 'support@lawenforcement.gov.et', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'address_am', 'value' => 'አዲስ አበባ፣ ኢትዮጵያ', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'address_en', 'value' => 'Addis Ababa, Ethiopia', 'type' => 'text', 'group' => 'contact'],

            // Appearance
            ['key' => 'primary_color', 'value' => '#0d6efd', 'type' => 'color', 'group' => 'appearance'],
            ['key' => 'secondary_color', 'value' => '#6c757d', 'type' => 'color', 'group' => 'appearance'],
            ['key' => 'accent_color', 'value' => '#198754', 'type' => 'color', 'group' => 'appearance'],

            // Hero
            ['key' => 'hero_title_am', 'value' => 'እንኳን ወደ ደንብ ማስከበር ባለስልጣን በደህና መጡ', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_title_en', 'value' => 'Welcome to Law Enforcement Authority', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_subtitle_am', 'value' => 'የሰው ሀብት አስተዳደር ዳይሬክቶሬት', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_subtitle_en', 'value' => 'Human Resource Management Directorate', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_description_en', 'value' => 'A secure, transparent platform to submit complaints, report illegal activities anonymously, and track case statuses. Your voice matters.', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_tagline_am', 'value' => 'ቅሬታዎን ያስገቡ ● ህገ-ወጥ ስራዎችን ሪፖርት ያድርጉ ● ጉዳይዎን ይከታተሉ', 'type' => 'text', 'group' => 'hero'],

            // Stats (JSON encoded)
            [
                'key' => 'stats',
                'value' => json_encode([
                    ['label_am' => 'ጠቅላላ ሰራተኞች', 'label_en' => 'Total Employees', 'value' => '1500'],
                    ['label_am' => 'ፓራ ሚሊተሪ ኦፊሰሮች', 'label_en' => 'Para Military Officers', 'value' => '850'],
                    ['label_am' => 'ሲቪል ሰራተኞች', 'label_en' => 'Civil Employees', 'value' => '450'],
                    ['label_am' => 'ወረዳዎች', 'label_en' => 'Woredas', 'value' => '120'],
                ]),
                'type' => 'json',
                'group' => 'hero'
            ],

            // Working Hours (JSON encoded)
            [
                'key' => 'working_hours',
                'value' => json_encode([
                    ['days_am' => 'ሰኞ - ዓርብ', 'days_en' => 'Monday - Friday', 'hours' => '8:30 - 17:30'],
                    ['days_am' => 'ቅዳሜ', 'days_en' => 'Saturday', 'hours' => '8:30 - 12:30'],
                    ['days_am' => 'እሁድ', 'days_en' => 'Sunday', 'hours' => 'Closed'],
                ]),
                'type' => 'json',
                'group' => 'contact'
            ],

            // SEO
            ['key' => 'site_title', 'value' => 'የደንብ ማስከበር ባለስልጣን | Law Enforcement Authority', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Official portal of the Law Enforcement Authority - Submit complaints, report tips, and access public information.', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'meta_keywords', 'value' => 'law enforcement, complaint, tip, illegal trade, land grabbing, Ethiopia, Addis Ababa', 'type' => 'text', 'group' => 'seo'],

            // Features
            ['key' => 'enable_complaints', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_tips', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_announcements', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_faq', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_contact_form', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'enable_newsletter', 'value' => '1', 'type' => 'boolean', 'group' => 'features'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'features'],

            // FAQs (JSON encoded)
            [
                'key' => 'faqs',
                'value' => json_encode([
                    [
                        'question_am' => 'ቅሬታ እንዴት ማቅረብ እችላለሁ?',
                        'question_en' => 'How do I submit a complaint?',
                        'answer_am' => 'በምናሌው ውስጥ "ቅሬታ አቅርብ" የሚለውን ይጫኑ። የግል ዝርዝሮችዎን ይሙሉ፣ ቅሬታዎን ያብራሩ እና እንደ ምርጫዎ ደጋፊ ፋይሎችን ያያይዙ። ጉዳይዎን ለመከታተል የሚያስችል ልዩ የቲኬት ቁጥር ይደርስዎታል።',
                        'answer_en' => 'Click "Submit Complaint" in the menu. Fill in your personal details, describe your complaint, and optionally attach supporting files. You\'ll receive a unique ticket number to track your case.',
                    ],
                    [
                        'question_am' => 'ጥቆማ ስሰጥ ማንነቴ ይጠበቃል?',
                        'question_en' => 'Is my identity protected when reporting a tip?',
                        'answer_am' => 'አዎ። ስም-አልባ የጥቆማ ማቅረቢያዎች የግል መረጃ አያስፈልጋቸውም። ለመከታተያ አገልግሎት የሚውል የመዳረሻ ኮድ ይፈጠራል። ማንነትዎ ሙሉ በሙሉ ሚስጥራዊ ሆኖ ይቆያል።',
                        'answer_en' => 'Yes. Anonymous tip submissions do not require personal information. An access token is generated for tracking purposes. Your identity remains completely confidential.',
                    ],
                    [
                        'question_am' => 'ቅሬታን ለማስተናገድ ምን ያህል ጊዜ ይወስዳል?',
                        'question_en' => 'How long does it take to process a complaint?',
                        'answer_am' => 'የመጀመሪያ ደረጃ ግምገማ ከ1-3 የስራ ቀናት ይወስዳል። ውስብስብ ጉዳዮች ረዘም ያለ ጊዜ ሊወስዱ ይችላሉ። የቲኬት ቁጥርዎን በመጠቀም በማንኛውም ጊዜ የጉዳይዎን ሁኔታ መከታተል ይችላሉ።',
                        'answer_en' => 'Initial review takes 1–3 business days. Complex cases may take longer. You can track your case status at any time using your ticket number.',
                    ],
                ]),
                'type' => 'json',
                'group' => 'general'
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
