<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX - Security Alert: Key Missing / Unreturned</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">Security Alert</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Key Missing: Schedule Expired</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX Hardware Monitoring & Security System</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Alert Banner -->
        <tr>
            <td style="padding: 24px 30px 10px 30px;">
                <div style="background-color: rgba(225, 29, 72, 0.15); border: 1px solid rgba(225, 29, 72, 0.45); border-radius: 12px; padding: 16px 18px;">
                    <p style="margin: 0 0 6px 0; font-size: 14px; line-height: 1.5; color: #fecdd3; font-weight: 700;">
                        Notice to System Administrator: Key is physically MISSING.
                    </p>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #fca5a5;">
                        Key <strong>{{ $key->key_name }} (Slot #{{ $key->slot_number }})</strong> was not returned upon schedule expiration. The user detailed below was the <strong>LAST PERSON</strong> who scanned and retrieved this key from the lockbox.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Details Section -->
        <tr>
            <td style="padding: 15px 30px;">
                <h3 style="color: #c4b5fd; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0; border-bottom: 1px solid #292244; padding-bottom: 8px;">Key & Last Borrower Details</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="8" style="font-size: 13px; background-color: #130f24; border-radius: 10px; border: 1px solid #292244;">
                    <tr>
                        <td width="40%" style="color: #94a3b8; font-weight: 600;">Key Name:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $key->key_name }} ({{ $key->room_name }})</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Slot Number:</td>
                        <td style="color: #ffffff;">Slot #{{ $key->slot_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Current Hardware Status:</td>
                        <td style="color: #f43f5e; font-weight: 800; text-transform: uppercase;">MISSING / UNRETURNED</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Last Person to Retrieve:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $borrower->name ?? 'No recent borrower record' }}</td>
                    </tr>
                    @if($borrower)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Employee ID / Dept:</td>
                        <td style="color: #cbd5e1;">{{ $borrower->employee_id ?? 'N/A' }} &bull; {{ $borrower->department ?? 'General' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Borrower Email:</td>
                        <td style="color: #a78bfa; font-weight: 600;">{{ $borrower->email }}</td>
                    </tr>
                    @else
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Borrower Email:</td>
                        <td style="color: #94a3b8; font-style: italic;">No borrower recorded (Locker physical sensor alert)</td>
                    </tr>
                    @endif
                    @if($transaction && $transaction->borrowed_at)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Retrieved On:</td>
                        <td style="color: #cbd5e1;">{{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('F j, Y - h:i A') }} ({{ \Carbon\Carbon::parse($transaction->borrowed_at)->diffForHumans() }})</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Expiration Reason:</td>
                        <td style="color: #fca5a5; font-size: 12px;">{{ $reason }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Alert Timestamp:</td>
                        <td style="color: #ffffff;">{{ $timestamp }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Actions -->
        <tr>
            <td style="padding: 10px 30px 25px 30px;">
                <p style="font-size: 12px; color: #94a3b8; margin: 0 0 16px 0;">
                    An automated return reminder was also sent to the borrower. You may review the full transaction logs or adjust the key status on the admin dashboard:
                </p>
                <div style="text-align: center;">
                    <a href="{{ url('/dashboard') }}" style="display: inline-block; background-color: #7c3aed; color: #ffffff; text-decoration: none; padding: 12px 24px; font-size: 13px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 12px rgba(124,58,237,0.4);">
                        Open Admin Dashboard &rarr;
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 30px; background-color: #130f24; border-top: 1px solid #292244; text-align: center;">
                <p style="margin: 0; font-size: 11px; color: #64748b;">
                    AUTOBOX Automated Security & Accountability System &bull; CCSICT
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
