<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>To Quran Consultation Confirmed - {{ $booking->booking_reference }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #263143; max-width: 700px; margin: 0 auto; padding: 20px; background: #f5f7fb; }
        .header { background: linear-gradient(135deg, #46412f 0%, #6f6848 100%); color: #fff; padding: 22px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 28px 20px; border: 1px solid #dfe6f0; }
        .urgent { background: #fff8e5; border: 1px solid #c9a24d; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin: 20px 0; }
        .detail-section { background: #f8fafc; padding: 16px; border-radius: 8px; border-left: 4px solid #46412f; }
        .detail-section h4 { margin-top: 0; color: #46412f; }
        .action-buttons { text-align: center; margin: 28px 0 8px; }
        .btn { display: inline-block; background: #46412f; color: #ffffff !important; padding: 12px 22px; text-decoration: none; border-radius: 6px; font-weight: 700; margin: 5px; }
        .btn-urgent { background: #c9a24d; color: #1f2937 !important; }
        a { color: #46412f; }
        @media (max-width: 600px) { .details-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; font-size:24px;">TO QURAN CONSULTATION CONFIRMED</h1>
        <p style="margin:10px 0 0 0; font-size:16px; opacity:.9;">{{ $booking->booking_reference }}</p>
    </div>

    <div class="content">
        <div class="urgent">
            <h3 style="margin:0; color:#46412f;">Meeting Snapshot</h3>
            <p style="margin:10px 0 0 0;">
                {{ $booking->formatted_consultation_date ?? '-' }}
                &bull; {{ $booking->formatted_consultation_time ?? '-' }}
                &bull; {{ $booking->formatted_consultation_type ?? '-' }}
            </p>
        </div>

        <div class="details-grid">
            <div class="detail-section">
                <h4>Family</h4>
                <p><strong>Parent:</strong> {{ $booking->parent_name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $booking->parent_email }}">{{ $booking->parent_email }}</a></p>
                <p><strong>Phone:</strong> <a href="tel:{{ $booking->parent_phone }}">{{ $booking->parent_phone }}</a></p>
                <p><strong>Learner:</strong> {{ $booking->child_name }}@if(filled($booking->child_age)) ({{ $booking->child_age }}y)@endif</p>
            </div>
            <div class="detail-section">
                <h4>Consultation</h4>
                <p><strong>Services:</strong> {{ filled($booking->service_interest) ? $booking->service_interest : '-' }}</p>
                <p><strong>Type:</strong> {{ $booking->formatted_consultation_type ?? '-' }}</p>
                @if($booking->consultation_type === 'online')
                    <p><strong>Meeting link:</strong> <a href="{{ $booking->meeting_link }}">Open meeting link</a></p>
                @else
                    <p><strong>Address:</strong> {{ $booking->meeting_address ?: '-' }}</p>
                @endif
            </div>
        </div>

        <div class="detail-section">
            <h4>Team Checklist</h4>
            <ul style="margin:0;">
                <li>Confirm the parent timezone and WhatsApp availability.</li>
                <li>Review the selected Quran, Arabic, Sanad, or My Deen Journey services.</li>
                <li>Prepare any parent guidance or follow-up points needed for the meeting.</li>
            </ul>
        </div>

        @if($booking->main_concerns)
            <div class="detail-section" style="margin-top:18px;">
                <h4>Parent Notes</h4>
                <p style="margin:0;">{{ $booking->main_concerns }}</p>
            </div>
        @endif

        <div class="action-buttons">
            <a href="mailto:{{ $booking->parent_email }}" class="btn-urgent btn" style="color:#1f2937 !important;">Email Parent</a>
            <a href="tel:{{ $booking->parent_phone }}" class="btn" style="color:#ffffff !important;">Call Parent</a>
        </div>
    </div>
</body>
</html>
