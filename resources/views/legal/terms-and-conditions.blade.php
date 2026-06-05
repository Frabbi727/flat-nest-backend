<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - FlatNest</title>
    <meta name="description" content="FlatNest Terms and Conditions — Read the terms governing the use of our flat rental marketplace services in Bangladesh.">
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
                <img src="/flatnest_icon_v4.png" alt="FlatNest" class="logo-icon" style="border-radius:12px;object-fit:contain;background:transparent;"/>
                FlatNest
            </a>
            <h1>Terms & Conditions</h1>
            <p class="subtitle">Last updated: {{ now()->format('F d, Y') }}</p>
        </div>

        <div class="card">
            <h2>1. Acceptance of Terms</h2>
            <p>By downloading, installing, or using the FlatNest mobile application and related services ("Services"), you agree to be legally bound by these Terms and Conditions ("Terms"). These Terms apply to all users, including property owners who post listings ("Owners") and individuals seeking rental accommodation ("Renters").</p>
            <p>If you do not agree to these Terms, please do not use our Services. We reserve the right to update these Terms at any time, and your continued use of the Services constitutes acceptance of any changes.</p>
        </div>

        <div class="card">
            <h2>2. About FlatNest</h2>
            <p>FlatNest is an online marketplace platform that connects property owners with individuals looking to rent residential flats, houses, rooms, and other accommodation in Bangladesh. FlatNest acts solely as a platform to facilitate connections between Owners and Renters — we are <strong>not</strong> a party to any rental agreement, and we do not own, manage, or control any of the listed properties.</p>
            <p>FlatNest does not guarantee the availability, accuracy, quality, safety, or legality of any listing or property. All transactions and agreements are strictly between the Owner and the Renter.</p>
        </div>

        <div class="card">
            <h2>3. Eligibility & Account Registration</h2>
            <ul>
                <li>You must be at least 18 years old to create an account and use our Services.</li>
                <li>You must provide accurate, complete, and current information during registration, including your real name, valid email address, and phone number.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</li>
                <li>You must notify us immediately at <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a> if you suspect any unauthorized use of your account.</li>
                <li>We reserve the right to suspend or permanently terminate accounts that violate these Terms, provide false information, or engage in fraudulent or harmful activity.</li>
            </ul>
        </div>

        <div class="card">
            <h2>4. User Roles</h2>
            <p><strong>Owners</strong> may post rental listings for residential properties located in Bangladesh. By posting a listing, Owners represent that:</p>
            <ul>
                <li>They are the legal owner or authorized representative of the property.</li>
                <li>All information provided in the listing — including price, size, address, amenities, photos, and availability — is accurate and up to date.</li>
                <li>The property is legally available for rent under the laws of Bangladesh.</li>
                <li>They will not discriminate against Renters based on religion, gender, ethnicity, or any other protected characteristic.</li>
            </ul>
            <p><strong>Renters</strong> may browse, save, and contact Owners regarding listed properties. Renters are responsible for conducting their own due diligence before entering into any rental agreement.</p>
        </div>

        <div class="card">
            <h2>5. Listing Content & Accuracy</h2>
            <p>Owners are solely responsible for the accuracy and completeness of their listings. FlatNest does not verify listing details, property ownership, or any claims made by Owners. You agree not to post listings that:</p>
            <ul>
                <li>Are fraudulent, misleading, or inaccurate.</li>
                <li>Advertise properties you do not own or have no authority to rent.</li>
                <li>Include offensive, illegal, or inappropriate content or images.</li>
                <li>Duplicate existing listings for the same property.</li>
                <li>Violate the rights of any third party, including intellectual property rights.</li>
            </ul>
            <p>FlatNest reserves the right to review, moderate, approve, reject, edit, or remove any listing at our sole discretion without prior notice.</p>
        </div>

        <div class="card">
            <h2>6. Platform Role & No Guarantee</h2>
            <p>FlatNest provides a platform to help Owners and Renters find each other. We make no representations or warranties regarding:</p>
            <ul>
                <li>The accuracy, completeness, or quality of any listing.</li>
                <li>The identity, creditworthiness, or good faith of any Owner or Renter.</li>
                <li>The condition, safety, habitability, or legal status of any property.</li>
                <li>Whether any rental transaction will be completed successfully.</li>
            </ul>
            <p>Any rental agreement entered into as a result of using FlatNest is solely between the Owner and the Renter. FlatNest is not liable for any disputes, losses, damages, or legal issues arising from such agreements.</p>
        </div>

        <div class="card">
            <h2>7. User Conduct</h2>
            <p>By using our Services, you agree not to:</p>
            <ul>
                <li>Use the Services for any unlawful purpose or in violation of any applicable laws of Bangladesh.</li>
                <li>Harass, threaten, or harm other users through the in-app chat or any other feature.</li>
                <li>Post false, misleading, or defamatory content.</li>
                <li>Attempt to gain unauthorized access to any part of the Services or our servers.</li>
                <li>Use automated tools, bots, or scrapers to access the Services.</li>
                <li>Reverse-engineer, decompile, or tamper with any part of the application.</li>
                <li>Use another user's account or impersonate any person or entity.</li>
                <li>Engage in any activity that could disrupt, damage, or overburden the Services.</li>
            </ul>
        </div>

        <div class="card">
            <h2>8. In-App Chat & Communications</h2>
            <p>FlatNest provides an in-app messaging feature to allow Renters and Owners to communicate about listings. You are solely responsible for the content of your messages. FlatNest does not monitor messages in real time but reserves the right to review communications if required to enforce these Terms, comply with applicable law, or investigate reports of abuse.</p>
            <p>Do not share sensitive financial information (such as bank account numbers) through the in-app chat. FlatNest is not responsible for any loss resulting from information exchanged via the chat feature.</p>
        </div>

        <div class="card">
            <h2>9. Push Notifications</h2>
            <p>FlatNest uses Firebase Cloud Messaging (FCM) to send push notifications related to your account activity, such as listing status updates (approved, rejected, under review) and other relevant alerts. You can manage notification preferences in your device settings. Disabling notifications may result in you missing important updates about your listings or account.</p>
        </div>

        <div class="card">
            <h2>10. Privacy & Data</h2>
            <p>Your use of our Services is also governed by our <a href="/privacy-policy">Privacy Policy</a>, which explains what personal data we collect (including your name, phone number, location, device token, and listing information), how we use it, and your rights regarding your data. By using FlatNest, you consent to the data practices described in the Privacy Policy.</p>
        </div>

        <div class="card">
            <h2>11. Intellectual Property</h2>
            <p>All content, features, and functionality of the FlatNest application — including the design, logo, user interface, software, and text created by FlatNest — are the exclusive property of FlatNest and are protected under applicable intellectual property laws. You may not reproduce, distribute, modify, or create derivative works of our proprietary content without our express written permission.</p>
            <p>By posting listings, photos, or other content on FlatNest, you grant us a non-exclusive, royalty-free, worldwide license to use, display, and distribute that content within the platform for the purpose of operating the Services.</p>
        </div>

        <div class="card">
            <h2>12. Account Deletion & Data Removal</h2>
            <p>You may delete your account at any time through the app (Settings → Delete Account) or by contacting us at <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a>. Upon deletion:</p>
            <ul>
                <li>Your account, profile, and all associated listings will be removed from the platform.</li>
                <li>Your personal data will be permanently deleted from our active systems within 30 days.</li>
                <li>Backup copies may be retained for up to 90 days before final purge.</li>
                <li>Anonymized, aggregated data may be retained for analytics purposes.</li>
            </ul>
            <p>Note: Messages you sent to other users may remain visible to those users until their accounts are also deleted.</p>
        </div>

        <div class="card">
            <h2>13. Disclaimers</h2>
            <p>Our Services are provided on an "AS IS" and "AS AVAILABLE" basis. FlatNest makes no warranties — express or implied — including warranties of merchantability, fitness for a particular purpose, or non-infringement. We do not warrant that the Services will be uninterrupted, error-free, or free from harmful components. You use the Services at your own risk.</p>
        </div>

        <div class="card">
            <h2>14. Limitation of Liability</h2>
            <p>To the fullest extent permitted under the laws of Bangladesh, FlatNest and its founders, employees, and agents shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from:</p>
            <ul>
                <li>Your use of or inability to use the Services.</li>
                <li>Any rental transaction, dispute, or agreement between Owners and Renters.</li>
                <li>Inaccurate listing information provided by Owners.</li>
                <li>Unauthorized access to your account or data.</li>
                <li>Any loss of data, profits, or business opportunities.</li>
            </ul>
        </div>

        <div class="card">
            <h2>15. Indemnification</h2>
            <p>You agree to indemnify and hold harmless FlatNest and its team from any claims, damages, losses, costs, and expenses (including reasonable legal fees) arising from your use of the Services, your listings, your communications with other users, or your violation of these Terms.</p>
        </div>

        <div class="card">
            <h2>16. Termination</h2>
            <p>FlatNest may suspend or terminate your access to the Services at any time, with or without notice, for any reason including — but not limited to — violation of these Terms, fraudulent activity, or extended inactivity. Upon termination, your right to use the Services ceases immediately. Clauses that by their nature survive termination (such as intellectual property, liability, and indemnification) shall remain in effect.</p>
        </div>

        <div class="card">
            <h2>17. Governing Law & Disputes</h2>
            <p>These Terms shall be governed by and construed in accordance with the laws of the People's Republic of Bangladesh. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the competent courts of Bangladesh. We encourage you to contact us first at <a href="mailto:flatnesthelp@gmail.com">flatnesthelp@gmail.com</a> to resolve any concern informally before initiating formal proceedings.</p>
        </div>

        <div class="card">
            <h2>18. Changes to These Terms</h2>
            <p>We reserve the right to update these Terms at any time. When we make material changes, we will update the "Last updated" date at the top of this page and may notify you via a push notification or in-app message. Your continued use of the Services after changes are posted constitutes your acceptance of the revised Terms. We encourage you to review these Terms periodically.</p>
        </div>

        <div class="card">
            <h2>19. Contact Us</h2>
            <p>If you have any questions, concerns, or requests regarding these Terms and Conditions, please contact us at:</p>
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
