<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your To Quran password</title>
    <style>
        body {
            margin: 0;
            background: #f5f7fb;
            color: #263143;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .shell {
            max-width: 640px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #dfe6f0;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(27, 54, 93, .10);
        }

        .hero {
            padding: 30px 28px;
            color: #ffffff;
            background: linear-gradient(135deg, #1b365d 0%, #2c5282 100%);
        }

        h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
        }

        .hero p {
            margin: 10px 0 0;
            color: #dbe8f7;
            font-size: 15px;
        }

        .content {
            padding: 28px;
        }

        .notice {
            margin: 22px 0;
            padding: 16px 18px;
            border: 1px solid #d4af37;
            border-radius: 12px;
            background: #fff8e5;
            color: #4f3b0c;
        }

        .actions {
            margin: 26px 0 14px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            margin: 6px;
            padding: 12px 22px;
            border-radius: 8px;
            background: #1b365d;
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
        }

        .footer {
            padding: 20px 28px;
            background: #f8fafc;
            border-top: 1px solid #dfe6f0;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        a {
            color: #1b365d;
        }

        @media (max-width: 600px) {
            .shell {
                padding: 12px;
            }

            .hero,
            .content,
            .footer {
                padding: 22px 18px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    @php
        $supportEmail = config('mail.support_address', config('mail.from.address', 'support@toquran.org'));
        $publicWebsiteUrl = config('app.public_website_url', 'https://toquran.org');
        $publicWebsiteLabel = parse_url($publicWebsiteUrl, PHP_URL_HOST) ?: $publicWebsiteUrl;
    @endphp
    <div class="shell">
        <div class="card">
            <div class="hero">
                <h1>Reset your To Quran password</h1>
                <p>Use this secure link to choose a new password.</p>
            </div>

            <div class="content">
                <p>Hello {{ $user->name ?: 'there' }},</p>
                <p>We received a request to reset the password for your To Quran account.</p>

                <div class="notice">
                    <strong>Important:</strong> Your current password is not shown or changed by this email. The password changes only after you complete the reset form.
                </div>

                <div class="actions">
                    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
                </div>

                <p style="margin-bottom:8px;">If the button does not open, copy this link into your browser:</p>
                <p style="word-break:break-word;margin-top:0;">
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </p>
            </div>

            <div class="footer">
                <strong style="color:#1b365d;">To Quran</strong><br>
                Transforming study resistance into academic excellence<br>
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> | <a href="{{ $publicWebsiteUrl }}">{{ $publicWebsiteLabel }}</a>
            </div>
        </div>
    </div>
</body>
</html>
