<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your To Quran family account is active</title>
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
            background: linear-gradient(135deg, #46412f 0%, #6f6848 100%);
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
            border: 1px solid #c9a24d;
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
            color: #46412f;
            font-size: 13px;
        }

        .details tr:last-child th,
        .details tr:last-child td {
            border-bottom: 0;
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
            background: #46412f;
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-secondary {
            background: #c9a24d;
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
            color: #46412f;
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
                <h1>Welcome to To Quran, {{ $parent->first_name ?: 'there' }}</h1>
                <p>
                    {{ $isResend
                        ? 'This is the latest copy of your family login details.'
                        : 'Your family workspace is ready. Use the details below to sign in and manage your account.' }}
                </p>
            </div>

            <div class="content">
                <p>Hello {{ $parent->first_name ?: 'there' }},</p>
                <p>Your To Quran family account is active. Keep these details private; support can always help with a reset later.</p>

                <div class="notice">
                    <strong>Security note:</strong> The initial password is provided for first access. You can change it from the parent dashboard after signing in.
                </div>

                <table class="details" role="presentation">
                    <tr>
                        <th>Login URL</th>
                        <td><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></td>
                    </tr>
                    <tr>
                        <th>Login email</th>
                        <td>{{ $parent->email }}</td>
                    </tr>
                    <tr>
                        <th>Initial password</th>
                        <td>{{ $password }}</td>
                    </tr>
                    <tr>
                        <th>Task Completion PIN</th>
                        {{-- Intentional default parent Task Completion PIN; parents can change it after first login. --}}
                        <td>1414</td>
                    </tr>
                </table>

                <p>The Task Completion PIN is separate from your account password and can be changed from the parent dashboard.</p>

                <div class="actions">
                    <a href="{{ $loginUrl }}" class="btn">Sign In</a>
                    <a href="{{ $passwordResetUrl }}" class="btn btn-secondary">Reset Password</a>
                </div>
            </div>

            <div class="footer">
                <strong style="color:#46412f;">To Quran</strong><br>
                Quran learning, Arabic, My Deen Journey, and thoughtful family follow-up.<br>
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> | <a href="{{ $publicWebsiteUrl }}">{{ $publicWebsiteLabel }}</a>
            </div>
        </div>
    </div>
</body>
</html>
