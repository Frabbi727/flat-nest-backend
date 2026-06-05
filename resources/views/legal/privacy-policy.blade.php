<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - FlatNest</title>
    <meta name="description" content="FlatNest Privacy Policy — Learn how we collect, use, and protect your personal information.">
    <link rel="icon" type="image/png" href="/flatnest_icon_v4.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            min-height: 100vh;
            line-height: 1.7;
        }

        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
        body::before {
            width: 600px; height: 600px;
            background: #6366f1;
            top: -200px; left: -100px;
            animation: float 12s ease-in-out infinite;
        }
        body::after {
            width: 500px; height: 500px;
            background: #06b6d4;
            bottom: -150px; right: -100px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(40px, 30px); }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        .header {
            text-align: center;
            margin-bottom: 48px;
        }
        .header .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #f8fafc;
            text-decoration: none;
            margin-bottom: 24px;
        }
        .header .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #1A6B72, #46A7AE);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .header .logo-icon svg { width: 22px; height: 22px; color: #fff; }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #a7dde1, #e0f7f8, #46A7AE);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        .header .subtitle { color: #94a3b8; font-size: 1rem; }

        .card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(70, 167, 174, 0.15);
            border-radius: 20px;
            padding: 40px 48px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        .card:hover { border-color: rgba(70, 167, 174, 0.3); }

        .card h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #a7dde1;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(70, 167, 174, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h2 .num {
            width: 28px; height: 28px;
            background: rgba(70, 167, 174, 0.15);
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            color: #46A7AE;
            flex-shrink: 0;
        }

        .card p, .card li {
            color: #cbd5e1;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
        .card p:last-child { margin-bottom: 0; }

        .card ul { padding-left: 20px; margin-bottom: 12px; }
        .card li { margin-bottom: 6px; }
        .card li::marker { color: #46A7AE; }
        .card strong { color: #e2e8f0; font-weight: 600; }

        .card a {
            color: #46A7AE;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .card a:hover { color: #a7dde1; text-decoration: underline; }

        .highlight-box {
            background: rgba(70, 167, 174, 0.08);
            border: 1px solid rgba(70, 167, 174, 0.2);
            border-radius: 12px;
            padding: 16px 20px;
            margin: 12px 0;
        }
        .highlight-box p { margin-bottom: 0; }

        .email-highlight {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(70, 167, 174, 0.12);
            border: 1px solid rgba(70, 167, 174, 0.25);
            border-radius: 10px;
            padding: 10px 18px;
            margin: 8px 0;
            font-weight: 500;
            color: #a7dde1;
            font-size: 1rem;
        }
        .email-highlight svg { width: 18px; height: 18px; flex-shrink: 0; }

        .third-party-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(70, 167, 174, 0.08);
        }
        .third-party-item:last-child { border-bottom: none; padding-bottom: 0; }
        .third-party-item .icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: rgba(70, 167, 174, 0.1);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .third-party-item .info strong {
            display: block;
            color: #e2e8f0;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        .third-party-item .info span {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .footer {
            text-align: center;
            padding: 32px 0 0;
            color: #64748b;
            font-size: 0.85rem;
        }
        .footer a { color: #46A7AE; text-decoration: none; font-weight: 500; }
        .footer a:hover { text-decoration: underline; }
        .footer .links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 16px;
        }

        @media (max-width: 640px) {
            .container { padding: 24px 16px 60px; }
            .card { padding: 24px 20px; }
            .header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <a href="/" class="logo">
                <img src="/flatnest_icon_v4.png" alt="FlatNest" class="logo-icon" style="border-radius:12px;object-fit:contain;background:transparent;"/>
                FlatNest
            </a>
            <h1>Privacy Policy</h1>
            <p class="subtitle">Effective date: {{ now()->format('F d, Y') }}</p>
        </div>

        <div class="card">
            <h2><span class="num">1</span> About FlatNest</h2>
            <p>FlatNest ("we", "our", or "us") is a property rental marketplace operating in Bangladesh that connects flat owners with prospective renters. This Privacy Policy explains what personal data we collect when you use our mobile app, why we collect it, how we use it, and your rights regarding that data.</p>
            <p>By creating an account or using FlatNest, you acknowledge that you have read and understood this policy.</p>
        </div>

        <div class="card">
            <h2><span class="num">2</span> Information We Collect</h2>
            <p><strong>Account information</strong></p>
            <ul>
                <li>Full name, email address, phone number, and date of birth provided during registration.</li>
                <li>Profile photo (avatar) uploaded by you.</li>
                <li>Google account information (name, email, profile picture) if you sign in with Google.</li>
                <li>Account role: whether you are a <em>Renter</em> or a <em>Flat Owner</em>.</li>
            </ul>

            <p><strong>Listing information (Owners only)</strong></p>
            <ul>
                <li>Property details: title, area, address, floor, facing direction, price, deposit, number of beds/baths, size, and description.</li>
                <li>Property photos you upload.</li>
                <li>Geographic coordinates (latitude and longitude) of the listed property.</li>
                <li>Owner contact information attached to the listing (name, phone, email).</li>
                <li>Administrative location data: division, district, upazila, and union.</li>
            </ul>

            <p><strong>Location data (Renters)</strong></p>
            <ul>
                <li>Your last known location (latitude and longitude) — collected only when you grant location permission — to show you nearby listings.</li>
            </ul>

            <p><strong>Usage and device data</strong></p>
            <ul>
                <li>Firebase Cloud Messaging (FCM) device token for delivering push notifications.</li>
                <li>App activity: listings viewed, wishlisted items, chats initiated.</li>
                <li>Device type, operating system version, and app version.</li>
            </ul>

            <p><strong>Chat messages</strong></p>
            <ul>
                <li>Messages exchanged between renters and owners within the in-app chat are stored on our servers to deliver the conversation history.</li>
            </ul>
        </div>

        <div class="card">
            <h2><span class="num">3</span> How We Use Your Information</h2>
            <ul>
                <li>Create and manage your account and authenticate your identity.</li>
                <li>Display your listings to renters and help renters find suitable flats.</li>
                <li>Show you listings near your current location (only when permission is granted).</li>
                <li>Deliver push notifications about listing status changes, new nearby listings, and messages.</li>
                <li>Facilitate in-app chat between owners and renters.</li>
                <li>Review and moderate listings before they are published (admin approval process).</li>
                <li>Send transactional emails or in-app notifications related to your account activity.</li>
                <li>Detect and prevent fraud, abuse, or violations of our Terms and Conditions.</li>
                <li>Improve the app through usage analytics (aggregated and anonymised where possible).</li>
            </ul>
        </div>

        <div class="card">
            <h2><span class="num">4</span> Third-Party Services</h2>
            <p>FlatNest relies on the following third-party services. Each service has its own privacy policy:</p>

            <div class="third-party-item">
                <div class="icon">🔐</div>
                <div class="info">
                    <strong>Google Sign-In (Google LLC)</strong>
                    <span>Used for optional one-tap authentication. We receive your Google account name, email, and profile photo. We do not receive or store your Google password. <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy →</a></span>
                </div>
            </div>

            <div class="third-party-item">
                <div class="icon">🔔</div>
                <div class="info">
                    <strong>Firebase Cloud Messaging — FCM (Google LLC)</strong>
                    <span>Used to deliver push notifications to your device. Your FCM device token is transmitted to Google servers. <a href="https://firebase.google.com/support/privacy" target="_blank" rel="noopener">Firebase Privacy Policy →</a></span>
                </div>
            </div>

            <div class="third-party-item">
                <div class="icon">📦</div>
                <div class="info">
                    <strong>Cloud Storage (our hosting provider)</strong>
                    <span>Listing photos and profile avatars are stored on our server's file storage. Files are accessible via direct URL and are not shared with marketing third parties.</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2><span class="num">5</span> Location Data</h2>
            <div class="highlight-box">
                <p>📍 FlatNest requests access to your device location <strong>only</strong> to display rental listings that are near you. Location is never shared with other users. We store only your last known coordinates and update them when you open the app with location permission active. You can withdraw location permission at any time in your device settings.</p>
            </div>
        </div>

        <div class="card">
            <h2><span class="num">6</span> User-Generated Content</h2>
            <p>Listings, photos, and chat messages you submit are stored on our servers. By uploading content, you grant FlatNest a licence to store, display, and moderate that content solely for the purpose of operating the platform. We do not sell user content to third parties.</p>
            <p>Listing photos you upload are publicly visible to any visitor of FlatNest. Do not include personally sensitive images or documents as listing photos.</p>
        </div>

        <div class="card">
            <h2><span class="num">7</span> Data Sharing</h2>
            <p>We do not sell your personal data. We may share information only in these circumstances:</p>
            <ul>
                <li><strong>Between users:</strong> An owner's contact details attached to a listing (name, phone) are visible to renters who view that listing. Your profile name and avatar are visible in chat conversations.</li>
                <li><strong>Service providers:</strong> Trusted infrastructure providers (hosting, email delivery) who process data strictly on our behalf under confidentiality agreements.</li>
                <li><strong>Legal obligations:</strong> When required by Bangladeshi law, court order, or regulatory authority.</li>
                <li><strong>Business transfer:</strong> If FlatNest is acquired or merged, user data may be transferred as part of that transaction with equivalent privacy protections.</li>
            </ul>
        </div>

        <div class="card">
            <h2><span class="num">8</span> Data Security</h2>
            <p>We apply industry-standard security measures including:</p>
            <ul>
                <li>HTTPS/TLS encryption for all data in transit.</li>
                <li>Passwords stored as bcrypt hashes — we never store plain-text passwords.</li>
                <li>API authentication via short-lived bearer tokens (Laravel Sanctum).</li>
                <li>Server-level access controls and regular security reviews.</li>
            </ul>
            <p>No system is 100% secure. If you suspect unauthorised access to your account, contact us immediately at <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a>.</p>
        </div>

        <div class="card">
            <h2><span class="num">9</span> Data Retention</h2>
            <ul>
                <li>Your account data is retained for as long as your account is active.</li>
                <li>When you delete your account, all your personal data, listings, photos, and chat history are permanently deleted from our servers within 30 days.</li>
                <li>We may retain anonymised, aggregated statistics that cannot identify you.</li>
                <li>Data required for legal compliance may be retained for longer as required by Bangladeshi law.</li>
            </ul>
        </div>

        <div class="card">
            <h2><span class="num">10</span> Your Rights & Account Deletion</h2>
            <p>You have the right to:</p>
            <ul>
                <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                <li><strong>Correction:</strong> Update inaccurate information in your profile settings.</li>
                <li><strong>Deletion:</strong> Permanently delete your account and all associated data at any time via <em>Settings → Delete Account</em> in the app, or by emailing us.</li>
                <li><strong>Withdraw consent:</strong> Revoke location permission or notification permission at any time in your device settings.</li>
                <li><strong>Data portability:</strong> Request an export of your data by contacting us.</li>
            </ul>
            <div class="highlight-box">
                <p>🗑️ Deleting your account will permanently remove your profile, all your listings, uploaded photos, wishlist, and chat history. This action cannot be undone.</p>
            </div>
        </div>

        <div class="card">
            <h2><span class="num">11</span> Children's Privacy</h2>
            <p>FlatNest is not intended for users under the age of 18. We do not knowingly collect personal data from minors. If you believe a minor has created an account, please contact us and we will delete the account promptly.</p>
        </div>

        <div class="card">
            <h2><span class="num">12</span> Changes to This Policy</h2>
            <p>We may update this Privacy Policy as our services evolve or legal requirements change. We will notify you of material changes via in-app notification or email. The "Effective date" at the top of this page reflects when the policy was last updated. Continued use of FlatNest after changes are posted constitutes acceptance.</p>
        </div>

        <div class="card">
            <h2><span class="num">13</span> Contact Us</h2>
            <p>For any privacy-related questions, data requests, or concerns, please contact us:</p>
            <div class="email-highlight">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a>
            </div>
            <p style="margin-top:12px;">We will respond to all data-related requests within 7 business days.</p>
        </div>

        <div class="footer">
            <div class="links">
                <a href="/privacy-policy">Privacy Policy</a>
                <a href="/terms-and-conditions">Terms &amp; Conditions</a>
            </div>
            <p>&copy; {{ date('Y') }} FlatNest. All rights reserved. | Bangladesh</p>
        </div>

    </div>
</body>
</html>
