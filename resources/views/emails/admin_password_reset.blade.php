<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUTOBOX — Reset Administrator Password</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f0c1b; color: #e2e8f0; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #19142c; border-radius: 16px; border: 1px solid #382d5e; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <!-- Header -->
        <tr>
            <td style="padding: 28px 30px; background: linear-gradient(135deg, #6d28d9 0%, #2563eb 100%); text-align: left;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <span style="display: inline-block; background-color: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #ffffff;">Admin Security</span>
                            <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 8px 0 0 0; letter-spacing: -0.5px;">Password Reset Request</h1>
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
                    You are receiving this email because we received a request to reset the password for your AUTOBOX administrator account associated with <strong style="color: #a78bfa;">{{ $user->email ?? '' }}</strong>.
                </p>
            </td>
        </tr>

        <!-- Call to Action Button -->
        <tr>
            <td style="padding: 20px 30px; text-align: center;">
                <div style="background-color: #130f24; border-radius: 12px; border: 1px solid #292244; padding: 24px 20px;">
                    <p style="margin: 0 0 16px 0; font-size: 13px; color: #94a3b8;">
                        Click the secure button below to set a new password:
                    </p>
                    <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; font-size: 14px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 14px rgba(124,58,237,0.45); letter-spacing: 0.3px;">
                        Reset Administrator Password &rarr;
                    </a>
                    <p style="margin: 16px 0 0 0; font-size: 12px; color: #64748b;">
                        ⏳ This password reset link will expire in <strong style="color: #cbd5e1;">{{ $expire ?? 60 }} minutes</strong>.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Security Warning Notice -->
        <tr>
            <td style="padding: 0 30px 20px 30px;">
                <div style="background-color: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 10px; padding: 12px 16px;">
                    <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #cbd5e1;">
                        <strong style="color: #c4b5fd;">Security Note:</strong> If you did not request a password reset, no further action is required. Your current password will remain unchanged and secure. If you suspect unauthorized activity, please inspect the system access logs immediately.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Fallback Link -->
        <tr>
            <td style="padding: 0 30px 25px 30px;">
                <p style="font-size: 11px; color: #64748b; margin: 0 0 6px 0;">
                    If you're having trouble clicking the "Reset Administrator Password" button, copy and paste this URL into your web browser:
                </p>
                <div style="background-color: #130f24; border: 1px solid #292244; border-radius: 8px; padding: 10px; word-break: break-all;">
                    <a href="{{ $resetUrl }}" style="font-size: 11px; color: #818cf8; text-decoration: none; font-family: monospace;">
                        {{ $resetUrl }}
                    </a>
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
