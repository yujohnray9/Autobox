<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — Login Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">2-Step Verification</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Login Verification Code</h1>
                            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 4px 0 0 0;">AUTOBOX Key Access & Monitoring System · CCSICT</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Greeting & Notification Intro -->
        <tr>
            <td style="padding: 26px 30px 12px 30px;">
                <h2 style="color: #ffffff; font-size: 16px; font-weight: 700; margin: 0 0 10px 0;">
                    Hello, {{ $user->name ?? 'Administrator' }}
                </h2>
                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                    A sign-in attempt was initiated for your AUTOBOX account (<strong style="color: #a78bfa;">{{ $user->email ?? '' }}</strong>). Please enter the 6-digit verification code below to complete your login:
                </p>
            </td>
        </tr>

        <!-- OTP Code Box -->
        <tr>
            <td style="padding: 16px 30px 20px 30px; text-align: center;">
                <div style="background-color: #130f24; border-radius: 12px; border: 1px solid #292244; padding: 24px 20px;">
                    <p style="margin: 0 0 12px 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8;">
                        Your One-Time Password (OTP)
                    </p>
                    <div style="display: inline-block; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); padding: 2px; border-radius: 12px; box-shadow: 0 6px 20px rgba(124,58,237,0.35);">
                        <div style="background-color: #19142c; padding: 14px 28px; border-radius: 10px;">
                            <span style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #ffffff; display: block; margin-left: 10px;">
                                {{ $otp }}
                            </span>
                        </div>
                    </div>
                    <p style="margin: 16px 0 0 0; font-size: 12px; color: #94a3b8;">
                        ⏳ This code is valid for <strong style="color: #cbd5e1;">{{ $expiresInMinutes }} minutes</strong> and can only be used once.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Sign-in Details Metadata -->
        <tr>
            <td style="padding: 0 30px 20px 30px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #130f24; border: 1px solid #292244; border-radius: 10px; padding: 14px 18px;">
                    <tr>
                        <td style="font-size: 11px; color: #64748b; padding: 3px 0;">Request Time:</td>
                        <td style="font-size: 11px; color: #cbd5e1; font-weight: 600; text-align: right; padding: 3px 0;">{{ $timestamp }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 11px; color: #64748b; padding: 3px 0;">IP Address:</td>
                        <td style="font-size: 11px; color: #cbd5e1; font-family: monospace; font-weight: 600; text-align: right; padding: 3px 0;">{{ $ipAddress }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Security Warning Notice -->
        <tr>
            <td style="padding: 0 30px 25px 30px;">
                <div style="background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 10px; padding: 12px 16px;">
                    <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #fca5a5;">
                        <strong style="color: #f87171;">Didn't request this code?</strong> Never share this code with anyone. If you did not initiate this login, an unauthorized user may know your password. Please reset your administrator password immediately.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 30px; background-color: #130f24; border-top: 1px solid #292244; text-align: center;">
                <p style="margin: 0 0 4px 0; font-size: 11px; color: #64748b;">
                    AUTOBOX © {{ date('Y') }} · College of Computing Studies, Information and Communication Technology
                </p>
                <p style="margin: 0; font-size: 10px; color: #475569;">
                    Automated Key Access & Monitoring Security System
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
