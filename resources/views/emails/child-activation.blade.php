<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $student->first_name }}'s To Quran account is active</title>
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

        .details {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            border: 1px solid #dfe6f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .details th,
        .details td {
            padding: 13px 14px;
            border-bottom: 1px solid #edf1f6;
            text-align: left;
            vertical-align: top;
        }

        .details th {
            width: 38%;
            background: #f8fafc;
            color: #1b365d;
            font-size: 13px;
        }

        .details tr:last-child th,
        .details tr:last-child td {
            border-bottom: 0;
        }

        .service-list {
            margin: 12px 0 22px;
            padding: 0;
            list-style: none;
        }

        .service-list li {
            margin: 8px 0;
            padding: 10px 12px;
            border-radius: 10px;
            background: #eef9fb;
            color: #0f6172;
            font-weight: 600;
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

        .btn-secondary {
            background: #d4af37;
            color: #1f2937 !important;
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

            .details th,
            .details td {
                display: block;
                width: auto;
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
                <h1>{{ $student->first_name }} can now sign in</h1>
                <p>
                    {{ $isResend
                        ? "This is the latest copy of {$student->first_name}'s login details."
                        : "{$student->first_name}'s To Quran account is ready. Use the details below to help them start." }}
                </p>
            </div>

            <div class="content">
                <p>Hello {{ $parent->first_name ?: 'there' }},</p>
                <p>{{ $student->first_name }}'s To Quran account is active. The child can sign in using the login email or the short username below, and the password can be changed later from the parent dashboard.</p>

                <div class="notice">
                    <strong>Parent reminder:</strong> The Reward System PIN is separate from this account password.
                </div>

                <table class="details" role="presentation">
                    <tr>
                        <th>Login URL</th>
                        <td><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></td>
                    </tr>
                    <tr>
                        <th>Login email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Short username</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Initial password</th>
                        <td>{{ $password }}</td>
                    </tr>
                </table>

                <h2 style="margin:24px 0 8px;color:#1b365d;font-size:18px;">Active services</h2>
                @if (! empty($activeServices))
                    <ul class="service-list">
                        @foreach ($activeServices as $service)
                            <li>{{ $service }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>Your active services will be confirmed by your teacher shortly.</p>
                @endif

                <div class="actions">
                    <a href="{{ $loginUrl }}" class="btn">Sign In</a>
                    <a href="{{ $passwordResetUrl }}" class="btn btn-secondary">Request Reset Link</a>
                </div>
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
