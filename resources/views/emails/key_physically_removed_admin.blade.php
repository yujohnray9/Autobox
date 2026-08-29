<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX - SECURITY ALERT: Unauthorized Key Removal</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #7f1d1d; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">

        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">⚠️ CRITICAL SECURITY ALERT</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Unauthorized Physical Removal</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX IR Sensor &mdash; Hardware Intrusion Detection</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Critical Alert Banner -->
        <tr>
            <td style="padding: 24px 30px 10px 30px;">
                <div style="background-color: rgba(220, 38, 38, 0.2); border: 2px solid rgba(220, 38, 38, 0.6); border-radius: 12px; padding: 16px 18px;">
                    <p style="margin: 0 0 6px 0; font-size: 15px; line-height: 1.5; color: #fca5a5; font-weight: 800;">
                        🚨 A key was physically removed from the lockbox WITHOUT any QR code scan!
                    </p>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #fecaca;">
                        The AUTOBOX IR sensor confirmed that <strong>{{ $key->key_name }} (Slot #{{ $key->slot_number }})</strong>
                        was present and armed inside the lockbox, but has since been physically removed
                        <strong>without an authorized QR code scan</strong>. This may indicate unauthorized access or theft.
                        Immediate action is required.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Incident Details -->
        <tr>
            <td style="padding: 15px 30px;">
                <h3 style="color: #f87171; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0; border-bottom: 1px solid #3f1515; padding-bottom: 8px;">Incident Details</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="8" style="font-size: 13px; background-color: #130f24; border-radius: 10px; border: 1px solid #3f1515;">
                    <tr>
                        <td width="40%" style="color: #94a3b8; font-weight: 600;">Key Name:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $key->key_name }} ({{ $key->room_name }})</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Slot Number:</td>
                        <td style="color: #ffffff;">Slot #{{ $key->slot_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Detection Method:</td>
                        <td style="color: #fca5a5; font-weight: 700;">IR Sensor — Physical Presence Confirmed then Lost</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Trigger:</td>
                        <td style="color: #fca5a5; font-weight: 800; text-transform: uppercase;">Unauthorized Removal (No QR Scan Detected)</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Current Status:</td>
                        <td style="color: #ef4444; font-weight: 800; text-transform: uppercase;">MISSING</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Alert Timestamp:</td>
                        <td style="color: #ffffff;">{{ $timestamp }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Last Borrower Info -->
        <tr>
            <td style="padding: 0px 30px 15px 30px;">
                <h3 style="color: #c4b5fd; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0; border-bottom: 1px solid #292244; padding-bottom: 8px;">Last Known Borrower (Reference Only)</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="8" style="font-size: 13px; background-color: #130f24; border-radius: 10px; border: 1px solid #292244;">
                    @if($lastBorrower)
                    <tr>
                        <td width="40%" style="color: #94a3b8; font-weight: 600;">Name:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $lastBorrower->name }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Email:</td>
                        <td style="color: #a78bfa;">{{ $lastBorrower->email }}</td>
                    </tr>
                    @if($lastTransaction && $lastTransaction->borrowed_at)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Last Borrowed At:</td>
                        <td style="color: #cbd5e1;">{{ \Carbon\Carbon::parse($lastTransaction->borrowed_at)->format('F j, Y - h:i A') }} ({{ \Carbon\Carbon::parse($lastTransaction->borrowed_at)->diffForHumans() }})</td>
                    </tr>
                    @endif
                    @if($lastTransaction && $lastTransaction->returned_at)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Last Returned At:</td>
                        <td style="color: #86efac;">{{ \Carbon\Carbon::parse($lastTransaction->returned_at)->format('F j, Y - h:i A') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" style="padding-top: 8px;">
                            <p style="margin: 0; font-size: 11px; color: #64748b; font-style: italic;">
                                Note: This is the last recorded borrower. The actual person who removed the key may differ. Do NOT assume guilt without investigation.
                            </p>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="2" style="color: #94a3b8; font-style: italic; font-size: 12px;">
                            No previous borrower record found. The key may have been removed directly from the locked slot.
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>

        <!-- Recommended Actions -->
        <tr>
            <td style="padding: 0px 30px 15px 30px;">
                <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 800; color: #fcd34d;">⚡ Recommended Immediate Actions:</p>
                    <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #fde68a; line-height: 1.8;">
                        <li>Check CCTV footage near the AUTOBOX lockbox terminal at the time of detection.</li>
                        <li>Contact the last recorded borrower listed above if applicable.</li>
                        <li>Physically inspect the lockbox for signs of forced entry or tampering.</li>
                        <li>Update the key status on the admin dashboard once the situation is resolved.</li>
                        <li>Consider rekeying or replacing the lock if tampering is confirmed.</li>
                    </ul>
                </div>
            </td>
        </tr>

        <!-- CTA -->
        <tr>
            <td style="padding: 0px 30px 25px 30px; text-align: center;">
                <a href="{{ url('/keys') }}" style="display: inline-block; background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 12px 28px; font-size: 13px; font-weight: 800; border-radius: 10px; box-shadow: 0 4px 12px rgba(220,38,38,0.4); margin-right: 10px;">
                    View Key Status &rarr;
                </a>
                <a href="{{ url('/dashboard') }}" style="display: inline-block; background-color: #7c3aed; color: #ffffff; text-decoration: none; padding: 12px 24px; font-size: 13px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 12px rgba(124,58,237,0.4);">
                    Open Dashboard
                </a>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 30px; background-color: #130f24; border-top: 1px solid #292244; text-align: center;">
                <p style="margin: 0; font-size: 11px; color: #64748b;">
                    AUTOBOX Automated Security &amp; Accountability System &bull; CCSICT &bull; This is an automated security alert.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
