<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX - Urgent: Unreturned Key Alert</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">Urgent Notice</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Unreturned Key: Immediate Action Required</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX Key Access & Security Management</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Alert Banner -->
        <tr>
            <td style="padding: 24px 30px 10px 30px;">
                <div style="background-color: rgba(225, 29, 72, 0.15); border: 1px solid rgba(225, 29, 72, 0.45); border-radius: 12px; padding: 16px 18px;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #fecdd3; font-weight: 700;">
                        Notice: You were the last person recorded to access and retrieve this key.
                    </p>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #fca5a5;">
                        {{ $expiredReason }} Our physical sensor records confirm that the key has <strong>NOT been returned</strong> to its designated slot.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Details Section -->
        <tr>
            <td style="padding: 15px 30px;">
                <h3 style="color: #c4b5fd; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0; border-bottom: 1px solid #292244; padding-bottom: 8px;">Key Borrow Record</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="8" style="font-size: 13px; background-color: #130f24; border-radius: 10px; border: 1px solid #292244;">
                    <tr>
                        <td width="40%" style="color: #94a3b8; font-weight: 600;">Last Registered Borrower:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Employee / ID:</td>
                        <td style="color: #cbd5e1;">{{ $user->employee_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Key Name:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $key->key_name }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Assigned Room:</td>
                        <td style="color: #a78bfa; font-weight: 700;">{{ $key->room_name }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Hardware Slot:</td>
                        <td style="color: #ffffff;">Slot #{{ $key->slot_number }}</td>
                    </tr>
                    @if($transaction && $transaction->borrowed_at)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Retrieved On:</td>
                        <td style="color: #cbd5e1;">{{ \Carbon\Carbon::parse($transaction->borrowed_at)->format('F j, Y - h:i A') }} ({{ \Carbon\Carbon::parse($transaction->borrowed_at)->diffForHumans() }})</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Current Slot Status:</td>
                        <td style="color: #f43f5e; font-weight: 800; text-transform: uppercase;">Unreturned / Missing</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Instructions & Admin Report Warning -->
        <tr>
            <td style="padding: 10px 30px 25px 30px;">
                <div style="background-color: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 12px; padding: 18px; margin-bottom: 16px;">
                    <h4 style="margin: 0 0 10px 0; color: #fde68a; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        Mandatory Next Steps:
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 12px; line-height: 1.7; color: #fef3c7;">
                        <li style="margin-bottom: 6px;">
                            <strong>If you still have the key:</strong> Return immediately to the AUTOBOX lockbox terminal, scan your QR badge, and place the key back in Slot #{{ $key->slot_number }}.
                        </li>
                        <li style="margin-bottom: 6px;">
                            <strong>If the key is lost, misplaced, or passed to someone else:</strong> You must <strong>REPORT TO THE SYSTEM ADMINISTRATOR</strong> or Department Head immediately.
                        </li>
                        <li>
                            Please note that this incident has been automatically dispatched to the System Administrators for security review.
                        </li>
                    </ul>
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
