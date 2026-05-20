<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - FlatNest</title>
    <meta name="description" content="FlatNest Terms and Conditions — Read the terms governing the use of our services.">
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
            background: #8b5cf6;
            top: -200px; right: -100px;
            animation: float 12s ease-in-out infinite;
        }
        body::after {
            width: 500px; height: 500px;
            background: #06b6d4;
            bottom: -150px; left: -100px;
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
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ddd6fe, #e9d5ff, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        .header .subtitle {
            color: #94a3b8;
            font-size: 1rem;
        }

        .card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.15);
            border-radius: 20px;
            padding: 48px;
            margin-bottom: 24px;
            transition: border-color 0.3s;
        }
        .card:hover {
            border-color: rgba(139, 92, 246, 0.3);
        }

        .card h2 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #ddd6fe;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(139, 92, 246, 0.15);
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
        .card li::marker { color: #a78bfa; }

        .card a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .card a:hover { color: #c4b5fd; text-decoration: underline; }

        .email-highlight {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(139, 92, 246, 0.12);
            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 10px;
            padding: 10px 18px;
            margin: 8px 0;
            font-weight: 500;
            color: #c4b5fd;
            font-size: 1rem;
        }
        .email-highlight svg {
            width: 18px; height: 18px;
            flex-shrink: 0;
        }

        .footer {
            text-align: center;
            padding: 32px 0 0;
            color: #64748b;
            font-size: 0.85rem;
        }
        .footer a {
            color: #a78bfa;
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
            <h1>Terms & Conditions</h1>
            <p class="subtitle">Last updated: {{ now()->format('F d, Y') }}</p>
        </div>

        <div class="card">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using the FlatNest mobile application and related services ("Services"), you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our Services.</p>
        </div>

        <div class="card">
            <h2>2. Description of Services</h2>
            <p>FlatNest provides a platform for inventory management, sales tracking, and business operations. We reserve the right to modify, suspend, or discontinue any aspect of our Services at any time without prior notice.</p>
        </div>

        <div class="card">
            <h2>3. User Accounts</h2>
            <ul>
                <li>You must provide accurate and complete information when creating an account.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                <li>You are responsible for all activities that occur under your account.</li>
                <li>You must notify us immediately of any unauthorized use of your account.</li>
                <li>We reserve the right to suspend or terminate accounts that violate these terms.</li>
            </ul>
        </div>

        <div class="card">
            <h2>4. User Responsibilities</h2>
            <p>When using our Services, you agree to:</p>
            <ul>
                <li>Use the Services only for lawful purposes and in compliance with all applicable laws.</li>
                <li>Not engage in any activity that interferes with or disrupts the Services.</li>
                <li>Not attempt to gain unauthorized access to any part of the Services or related systems.</li>
                <li>Not use the Services to transmit harmful, offensive, or illegal content.</li>
                <li>Not reverse-engineer, decompile, or disassemble any part of the Services.</li>
            </ul>
        </div>

        <div class="card">
            <h2>5. Intellectual Property</h2>
            <p>All content, features, and functionality of the Services — including but not limited to text, graphics, logos, icons, software, and design — are the exclusive property of FlatNest and are protected by copyright, trademark, and other intellectual property laws. You may not reproduce, distribute, or create derivative works without our express written consent.</p>
        </div>

        <div class="card">
            <h2>6. Payment & Subscription</h2>
            <p>If applicable, certain features may require a paid subscription. By subscribing, you agree to:</p>
            <ul>
                <li>Pay all fees associated with your chosen plan.</li>
                <li>Provide accurate billing information.</li>
                <li>Accept that fees are non-refundable unless otherwise stated.</li>
                <li>Acknowledge that we may change pricing with reasonable notice.</li>
            </ul>
        </div>

        <div class="card">
            <h2>7. Data & Privacy</h2>
            <p>Your use of our Services is also governed by our <a href="/privacy-policy">Privacy Policy</a>, which describes how we collect, use, and protect your personal information. By using our Services, you consent to the data practices described in our Privacy Policy.</p>
        </div>

        <div class="card">
            <h2>8. Disclaimers</h2>
            <p>Our Services are provided on an "AS IS" and "AS AVAILABLE" basis without warranties of any kind, either express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, and non-infringement. We do not warrant that the Services will be uninterrupted, error-free, or secure.</p>
        </div>

        <div class="card">
            <h2>9. Limitation of Liability</h2>
            <p>To the fullest extent permitted by law, FlatNest and its affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, or business opportunities, arising from your use of or inability to use our Services.</p>
        </div>

        <div class="card">
            <h2>10. Indemnification</h2>
            <p>You agree to indemnify and hold harmless FlatNest, its officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses arising from your use of the Services or violation of these Terms.</p>
        </div>

        <div class="card">
            <h2>11. Termination</h2>
            <p>We may terminate or suspend your access to the Services immediately, without prior notice, for any reason, including breach of these Terms. Upon termination, your right to use the Services will cease immediately. Provisions that by their nature should survive termination shall remain in effect.</p>
        </div>

        <div class="card">
            <h2>12. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with the applicable laws, without regard to conflict of law principles. Any disputes arising under these Terms shall be resolved in the appropriate courts of the applicable jurisdiction.</p>
        </div>

        <div class="card">
            <h2>13. Changes to These Terms</h2>
            <p>We reserve the right to update these Terms at any time. Changes will be effective immediately upon posting. Your continued use of the Services after changes are posted constitutes acceptance of the revised Terms. We encourage you to review these Terms periodically.</p>
        </div>

        <div class="card">
            <h2>14. Contact Us</h2>
            <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
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
