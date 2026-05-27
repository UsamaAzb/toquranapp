<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultation Scheduled - {{ $booking->booking_reference }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1B365D 0%, #2C5282 100%); color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px 20px; border: 1px solid #e2e8f0; }
        .urgent { background: #fef5e7; border: 2px solid #d4af37; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .detail-section { background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #1B365D; }
        .detail-section h4 { margin-top: 0; color: #1B365D; }
        .action-buttons { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; background: #1B365D; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 5px; }
        .btn-urgent { background: #d4af37; color: #ffffff !important; }
        @media (max-width: 600px) { .details-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; font-size:24px;">CONSULTATION CONFIRMED</h1>
        <p style="margin:10px 0 0 0; font-size:16px; opacity:.9;">{{ $booking->booking_reference }}</p>
    </div>

    <div class="content">
        <div class="urgent">
            <h3 style="margin:0; color:#1B365D;">Final Checks</h3>
            <p style="margin:10px 0 0 0;">
                {{ $booking->formatted_consultation_date ?? '-' }}
                • {{ $booking->formatted_consultation_time ?? '-' }}
                • {{ $booking->formatted_consultation_type ?? '-' }}
            </p>
        </div>

        <div class="details-grid">
            <div class="detail-section">
                <h4>Family</h4>
                <p><strong>Parent:</strong> {{ $booking->parent_name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $booking->parent_email }}">{{ $booking->parent_email }}</a></p>
                <p><strong>Phone:</strong> <a href="tel:{{ $booking->parent_phone }}">{{ $booking->parent_phone }}</a></p>
                <p><strong>Child:</strong> {{ $booking->child_name }} ({{ $booking->child_age }}y, G{{ $booking->child_grade }})</p>
                <p><strong>School Name:</strong> {{ $booking->current_school ?: '-' }}</p>
            </div>
            <div class="detail-section">
                <h4>Consultation</h4>
                <p><strong>Service:</strong> {{ filled($booking->service_interest) ? ucwords(str_replace('-', ' ', $booking->service_interest)) : '-' }}</p>
                @if($booking->consultation_type === 'online')
                    <p><strong>Zoom:</strong> <a href="{{ $booking->meeting_link }}">Click here to join the meeting</a></p>
                @else
                    <p><strong>Address:</strong> {{ $booking->meeting_address }}</p>
                @endif
            </div>
        </div>

        <div class="detail-section">
            <h4>Checklist</h4>
            <ul style="margin:0;">
                <li>Attach preparation guide</li>
                <li>Confirm timezone and reminders</li>
                <li>Pre-read main concerns (below)</li>
            </ul>
        </div>

        @if($booking->main_concerns)
            <div class="detail-section">
                <h4>Main Concerns</h4>
                <p style="margin:0;">{{ $booking->main_concerns }}</p>
            </div>
        @endif

        <div class="action-buttons">
            <a href="mailto:{{ $booking->parent_email }}" class="btn-urgent btn" style="color:#ffffff !important;">Email Parent</a>
            <a href="tel:{{ $booking->parent_phone }}" class="btn" style="color:#ffffff !important;">Call Parent</a>
        </div>
    </div>
</body>
</html>
