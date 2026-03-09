{{-- resources/views/emails/tip-confirmation.blade.php --}}
<!DOCTYPE html>
<html lang="am">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የመረጃ ማረጋገጫ</title>
    <style>
        body {
            font-family: 'Noto Sans Ethiopic', 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #198754;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .tip-box {
            background: #d1e7dd;
            border-left: 4px solid #198754;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .tip-box h3 {
            margin: 0 0 10px;
            color: #198754;
        }

        .tip-number {
            font-size: 28px;
            font-weight: bold;
            color: #198754;
            text-align: center;
            margin: 10px 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .details-table th,
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .details-table th {
            background: #f8f9fa;
            width: 35%;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #198754;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .btn:hover {
            background: #157347;
        }

        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>የደንብ ማስከበር ባለስልጣን</h1>
            <p>የሰው ሀብት አስተዳደር ዳይሬክቶሬት</p>
        </div>

        <div class="content">
            <h2>እንዴት አላችሁ {{ $tip->reporter_name ?? 'ክቡር ዜጋ' }}?</h2>

            <p>የላኩት ሚስጥራዊ መረጃ በተሳካ ሁኔታ በስርዓታችን ተመዝግቧል። ለትብብርዎ እናመሰግናለን።</p>

            <div class="tip-box">
                <h3>የመረጃ ቁጥር</h3>
                <div class="tip-number">{{ $tip->tip_number }}</div>
            </div>

            <h3>የመረጃ ማጠቃለያ</h3>
            <table class="details-table">
                <tr>
                    <th>የተላከበት ቀን</th>
                    <td>{{ $tip->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <th>አይነት</th>
                    <td>{{ $tip->tip_type_name }}</td>
                </tr>
                <tr>
                    <th>ቦታ</th>
                    <td>{{ $tip->location }}</td>
                </tr>
                <tr>
                    <th>የአስቸኳይነት ደረጃ</th>
                    <td>
                        @if($tip->urgency_level == 'low') ዝቅተኛ
                        @elseif($tip->urgency_level == 'medium') መካከለኛ
                        @elseif($tip->urgency_level == 'high') ከፍተኛ
                        @elseif($tip->urgency_level == 'immediate') አፋጣኝ
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>ሁኔታ</th>
                    <td>{{ $tip->status_name }}</td>
                </tr>
            </table>

            @if($tip->is_anonymous && $trackingUrl)
                <div class="warning">
                    <h4 style="margin-top:0; color:#856404;">የመከታተያ አገናኝ</h4>
                    <p>ማንነትዎን ሳይገልጹ የመረጃዎን ሁኔታ ለመከታተል ይህን አገናኝ ይጠቀሙ፦</p>
                    <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all;">
                        <a href="{{ $trackingUrl }}">{{ $trackingUrl }}</a>
                    </p>
                    <p class="small">ይህን አገናኝ በአስተማማኝ ቦታ ያስቀምጡ። ማንም ሰው ይህን አገናኝ ካገኘ የመረጃዎን ሁኔታ ማየት ይችላል።</p>
                </div>
            @endif

            <h3>ቀጣይ እርምጃዎች</h3>
            <ol>
                <li>መረጃዎ በቡድናችን ይገመገማል (በ24 ሰአታት ውስጥ)</li>
                <li>አስፈላጊ ከሆነ ምርመራ ይጀመራል</li>
                <li>ውጤት ሲመጣ በዚህ ኢሜይል ይነገርዎታል</li>
            </ol>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $trackingUrl ?? route('home') }}" class="btn">
                    {{ $tip->is_anonymous ? 'መረጃዎን ይከታተሉ' : 'ወደ ድረ-ገጽ ይሂዱ' }}
                </a>
            </div>

            <p><strong>ማሳሰቢያ፡-</strong> ይህ መረጃ በሙሉ ሚስጥራዊነት ይያዛል። ማንነትዎ ከፍርድ ቤት ትእዛዝ ውጪ ለማንም አይገለጽም።</p>

            <p>ለተጨማሪ መረጃ ወይም ጥያቄ ካለዎት እባክዎ ያግኙን፦</p>
            <ul>
                <li>ስልክ: +251 11 123 4567</li>
                <li>ኢሜይል: tips@lawenforcement.gov.et</li>
            </ul>
        </div>

        <div class="footer">
            <p>ይህ ኢሜይል በራስ-ሰር የተላከ ነው። እባክዎ ለዚህ ኢሜይል መልስ አይስጡ።</p>
            <p>© {{ date('Y') }} የደንብ ማስከበር ባለስልጣን። መብቱ በህግ የተጠበቀ ነው።</p>
        </div>
    </div>
</body>

</html>