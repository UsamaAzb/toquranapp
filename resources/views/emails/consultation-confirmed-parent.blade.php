<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your To Quran Consultation is Confirmed!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #1B365D 0%, #2C5282 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px 20px;
            border: 1px solid #e2e8f0;
        }
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .highlight {
            background: #e6ffed;
            border: 1px solid #38a169;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #1B365D;
        }
        .btn {
            display: inline-block;
            background: #1B365D;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .btn-secondary {
            background: #d4af37;
            color: #ffffff !important;
        }
        .icon {
            color: #38a169;
            margin-right: 8px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header, .content, .footer {
                padding: 20px 15px;
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
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">To Quran</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px; opacity: 0.9;">Consultation Confirmed</p>
    </div>

    <div class="content">
        <h2 style="color:#1B365D; margin-top:0;">Dear {{ $booking->parent_name }},</h2>

        <p>Your consultation with Dr. Osama has been <strong>confirmed</strong>. We're excited to meet you and {{ $booking->child_name }}.</p>

        <div class="highlight">
            <h3 style="margin-top:0; color:#1B365D;">
                <span class="icon">Appointment</span> Details
            </h3>
            <p style="margin:0;"><strong>Booking Reference:</strong> {{ $booking->booking_reference }}</p>
        </div>

        <h3 style="color:#1B365D;">Consultation</h3>
        <table class="details-table">
            <tr><th>Date</th><td>{{ $booking->formatted_consultation_date ?? '-' }}</td></tr>
            <tr><th>Time</th><td>{{ $booking->formatted_consultation_time ?? '-' }}</td></tr>
            <tr><th>Meeting Type</th><td>{{ $booking->formatted_consultation_type ?? '-' }}</td></tr>
            @if($booking->consultation_type === 'online' && $booking->meeting_link)
                <tr><th>Meeting Link</th><td><a href="{{ $booking->meeting_link }}">Click here to join the meeting</a></td></tr>
            @endif
            @if($booking->consultation_type === 'in-person' && $booking->meeting_address)
                <tr><th>Meeting Address</th><td>{{ $booking->meeting_address }}</td></tr>
            @endif
            <tr>
                <th>Child</th>
                <td>{{ $booking->child_name }} ({{ $booking->child_age }} years, Grade {{ $booking->child_grade }})</td>
            </tr>
            <tr><th>Service Interest</th><td>{{ filled($booking->service_interest) ? $booking->service_interest : '-' }}</td></tr>
        </table>

        <h3 style="color:#1B365D;">How to Prepare</h3>
        <ul>
            <li>Gather recent report cards/teacher feedback.</li>
            <li>Note your top concerns and goals.</li>
            <li>Ensure a quiet 60 minutes (camera & mic ready if Online).</li>
        </ul>

        <div style="text-align:center; margin:30px 0;">
            <a href="mailto:{{ $supportEmail }}" class="btn" style="color:#ffffff !important;">Reschedule</a>
            <a href="{{ $publicWebsiteUrl }}" class="btn btn-secondary" style="color:#ffffff !important;">Visit Website</a>
        </div>
    </div>

    <div class="footer">
        <h4 style="color: #1B365D; margin-top: 0;">To Quran</h4>
        <p style="margin: 5px 0;">Transforming study resistance into academic excellence</p>
        <p style="margin: 5px 0;">
            <strong>Email:</strong> {{ $supportEmail }}<br>
            <strong>Website:</strong> {{ $publicWebsiteLabel }}
        </p>
        <p style="margin: 15px 0 0 0; font-size: 12px; color: #666;">
            This is an automated email. Please do not reply directly to this message.
        </p>
    </div>
</body>
</html>
