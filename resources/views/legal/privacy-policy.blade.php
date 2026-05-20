<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - FlatNest</title>
    <meta name="description" content="FlatNest Privacy Policy — Learn how we collect, use, and protect your personal information.">
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

        /* Subtle animated gradient orbs */
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

        /* Header */
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
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #c7d2fe, #e0e7ff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        .header .subtitle {
            color: #94a3b8;
            font-size: 1rem;
        }

        /* Card */
        .card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 20px;
            padding: 48px;
            margin-bottom: 24px;
            transition: border-color 0.3s;
        }
        .card:hover {
            border-color: rgba(99, 102, 241, 0.3);
        }

        .card h2 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #c7d2fe;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(99, 102, 241, 0.15);
        }

        .card p, .card li {
            color: #cbd5e1;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }

        .card ul {
            padding-left: 20px;
            margin-bottom: 16px;
        }
        .card li { margin-bottom: 8px; }
        .card li::marker { color: #818cf8; }

        .card a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .card a:hover { color: #a5b4fc; text-decoration: underline; }

        /* Email highlight */
        .email-highlight {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 10px;
            padding: 10px 18px;
            margin: 8px 0;
            font-weight: 500;
            color: #a5b4fc;
            font-size: 1rem;
        }
        .email-highlight svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 32px 0 0;
            color: #64748b;
            font-size: 0.85rem;
        }
        .footer a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 500;
        }
        .footer a:hover { text-decoration: underline; }
        .footer .links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 16px;
        }

        @media (max-width: 640px) {
            .container { padding: 24px 16px 60px; }
            .card { padding: 28px 20px; }
            .header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="/" class="logo">
                <span class="logo-icon">🏠</span>
                FlatNest
            </a>
            <h1>Privacy Policy</h1>
            <p class="subtitle">Last updated: {{ now()->format('F d, Y') }}</p>
        </div>

        <div class="card">
            <h2>1. Introduction</h2>
            <p>Welcome to FlatNest. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application and services.</p>
        </div>

        <div class="card">
            <h2>2. Information We Collect</h2>
            <p>We may collect the following types of information:</p>
            <ul>
                <li><strong>Personal Information:</strong> Name, email address, phone number, and other contact details you provide during registration.</li>
                <li><strong>Account Data:</strong> Username, password (encrypted), and profile information.</li>
                <li><strong>Usage Data:</strong> Information about how you use our app, including access times, pages viewed, and features used.</li>
                <li><strong>Device Information:</strong> Device type, operating system, unique device identifiers, and mobile network information.</li>
                <li><strong>Location Data:</strong> With your consent, we may collect your approximate or precise location to provide location-based services.</li>
            </ul>
        </div>

        <div class="card">
            <h2>3. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Provide, maintain, and improve our services.</li>
                <li>Process transactions and manage your account.</li>
                <li>Send you updates, notifications, and promotional materials (with your consent).</li>
                <li>Respond to your inquiries and provide customer support.</li>
                <li>Monitor and analyze usage patterns to enhance user experience.</li>
                <li>Ensure the security and integrity of our platform.</li>
            </ul>
        </div>

        <div class="card">
            <h2>4. Sharing Your Information</h2>
            <p>We do not sell your personal information. We may share your information only in the following circumstances:</p>
            <ul>
                <li><strong>Service Providers:</strong> With trusted third-party vendors who assist in operating our services.</li>
                <li><strong>Legal Requirements:</strong> When required by law, regulation, or legal process.</li>
                <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets.</li>
                <li><strong>With Your Consent:</strong> When you have given us explicit permission to share.</li>
            </ul>
        </div>

        <div class="card">
            <h2>5. Data Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>
        </div>

        <div class="card">
            <h2>6. Data Retention</h2>
            <p>We retain your personal information for as long as your account is active or as needed to provide you services. We may also retain and use your information to comply with legal obligations, resolve disputes, and enforce our agreements.</p>
        </div>

        <div class="card">
            <h2>7. Your Rights</h2>
            <p>Depending on your jurisdiction, you may have the right to:</p>
            <ul>
                <li>Access the personal data we hold about you.</li>
                <li>Request correction of inaccurate data.</li>
                <li>Request deletion of your personal data.</li>
                <li>Object to or restrict the processing of your data.</li>
                <li>Withdraw consent at any time where processing is based on consent.</li>
            </ul>
        </div>

        <div class="card">
            <h2>8. Third-Party Services</h2>
            <p>Our app may contain links to or integrate with third-party services. We are not responsible for the privacy practices of these third parties. We encourage you to review the privacy policies of any third-party services you access through our app.</p>
        </div>

        <div class="card">
            <h2>9. Children's Privacy</h2>
            <p>Our services are not intended for children under the age of 13. We do not knowingly collect personal information from children. If we become aware that we have collected data from a child under 13, we will take steps to delete that information promptly.</p>
        </div>

        <div class="card">
            <h2>10. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date. Your continued use of the app after changes are posted constitutes your acceptance of the updated policy.</p>
        </div>

        <div class="card">
            <h2>11. Contact Us</h2>
            <p>If you have any questions or concerns about this Privacy Policy, please contact us at:</p>
            <div class="email-highlight">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a>
            </div>
        </div>

        <div class="footer">
            <div class="links">
                <a href="/privacy-policy">Privacy Policy</a>
                <a href="/terms-and-conditions">Terms & Conditions</a>
            </div>
            <p>&copy; {{ date('Y') }} FlatNest. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
