<!DOCTYPE html>
<html lang="am">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ጥገና ላይ ነው | Under Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .maintenance-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 50px;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 25px;
            font-weight: 700;
        }

        p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }
    </style>
</head>

<body>
    <div class="maintenance-box">
        <span class="icon">🔧</span>
        <h1>ጥገና ላይ ነን</h1>
        <p>{{ App\Models\SiteSetting::get('maintenance_message_am', 'ይቅርታ፣ ድረ-ገጹ በእድሳት ላይ ነው። እባክዎ በኋላ ላይ ይሞክሩ።') }}</p>
        <hr style="border-color: rgba(255,255,255,0.3); margin: 30px 0;">
        <p>{{ App\Models\SiteSetting::get('maintenance_message_en', 'Sorry, the site is under maintenance. Please try again later.') }}
        </p>
    </div>
</body>

</html>