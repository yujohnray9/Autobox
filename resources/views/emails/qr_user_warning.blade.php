<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX Security Notification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #d97706 0%, #b45309 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">Security Advisory</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Multiple Scans on Your QR Badge</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX Key Management System</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Greeting & Content -->
        <tr>
            <td style="padding: 24px 30px 10px 30px;">
                <p style="margin: 0 0 14px 0; font-size: 15px; color: #ffffff; font-weight: 700;">
                    Hello {{ $user->name }},
                </p>
                <p style="margin: 0 0 14px 0; font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                    Our security system detected that your personal AUTOBOX QR access badge was scanned <strong>{{ $scanCount }} times</strong> in a short period at the key lockbox scanner on:
                </p>
                <div style="background-color: #130f24; border: 1px solid #292244; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px;">
                    <p style="margin: 0; color: #a78bfa; font-weight: 600;">
                        <strong>Time of Occurrence:</strong> <span style="color: #ffffff;">{{ $timestamp }}</span>
                    </p>
                </div>
            </td>
        </tr>

        <!-- Warning instructions box -->
        <tr>
            <td style="padding: 0 30px 20px 30px;">
                <div style="background-color: rgba(217, 119, 6, 0.12); border: 1px solid rgba(217, 119, 6, 0.35); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 8px 0; color: #fde68a; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        Important Security Steps
                    </h4>
                    <ul style="margin: 0; padding-left: 18px; font-size: 12px; line-height: 1.6; color: #fef3c7;">
                        <li style="margin-bottom: 6px;"><strong>If you scanned this yourself:</strong> No further action is required. If your scan failed, please check your key schedule hours.</li>
                        <li><strong>If you DID NOT scan your badge:</strong> Your QR code image or printout may have been copied or used by someone else. Please contact your administrator immediately to revoke and regenerate your QR code.</li>
                    </ul>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 30px; background-color: #130f24; border-top: 1px solid #292244; text-align: center;">
                <p style="margin: 0; font-size: 11px; color: #64748b;">
                    AUTOBOX &bull; Automated Key Access & Security Management &bull; CCSICT
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
