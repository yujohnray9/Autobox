<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX Security Alert</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">Security Alert</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Multiple QR Code Scans Detected</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX Hardware Monitoring System</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Alert Banner -->
        <tr>
            <td style="padding: 24px 30px 10px 30px;">
                <div style="background-color: rgba(225, 29, 72, 0.15); border: 1px solid rgba(225, 29, 72, 0.4); border-radius: 12px; padding: 14px 18px;">
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #fecdd3; font-weight: 600;">
                        <strong>Notice to System Administrator:</strong> A QR code has been scanned <strong>{{ $scanCount }} times</strong> in rapid succession at the physical lockbox scanner. This may indicate repeated failed attempts, unauthorized access, or hardware tampering.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Details Section -->
        <tr>
            <td style="padding: 15px 30px;">
                <h3 style="color: #c4b5fd; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 12px 0; border-bottom: 1px solid #292244; padding-bottom: 8px;">Incident Details</h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="8" style="font-size: 13px; background-color: #130f24; border-radius: 10px; border: 1px solid #292244;">
                    <tr>
                        <td width="38%" style="color: #94a3b8; font-weight: 600;">Scanned User:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $scannedUser->name ?? 'Unregistered / Unknown' }}</td>
                    </tr>
                    @if($scannedUser)
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Employee / ID:</td>
                        <td style="color: #ffffff; font-weight: 700;">{{ $scannedUser->employee_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Role / Department:</td>
                        <td style="color: #ffffff; text-transform: capitalize;">{{ $scannedUser->role }} &bull; {{ $scannedUser->department ?? 'General' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">User Email:</td>
                        <td style="color: #a78bfa;">{{ $scannedUser->email }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">QR Token:</td>
                        <td style="color: #cbd5e1; font-family: monospace; font-size: 11px;">{{ $qrToken }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Scan Count:</td>
                        <td style="color: #f43f5e; font-weight: 800;">{{ $scanCount }} Scans</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Timestamp:</td>
                        <td style="color: #ffffff;">{{ $timestamp }}</td>
                    </tr>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;">Kiosk IP Address:</td>
                        <td style="color: #ffffff; font-family: monospace;">{{ $ipAddress }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Actions -->
        <tr>
            <td style="padding: 10px 30px 25px 30px;">
                <p style="font-size: 12px; color: #94a3b8; margin: 0 0 16px 0;">
                    Please review the audit log on the AUTOBOX dashboard to verify if the transaction was legitimate or if the user's QR token needs to be regenerated.
                </p>
                <div style="text-align: center;">
                    <a href="{{ url('/access-logs') }}" style="display: inline-block; background-color: #7c3aed; color: #ffffff; text-decoration: none; padding: 12px 24px; font-size: 13px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 12px rgba(124,58,237,0.4);">
                        View Access Logs on Dashboard &rarr;
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 30px; background-color: #130f24; border-top: 1px solid #292244; text-align: center;">
                <p style="margin: 0; font-size: 11px; color: #64748b;">
                    This is an automated security broadcast from the AUTOBOX Key Access System.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
