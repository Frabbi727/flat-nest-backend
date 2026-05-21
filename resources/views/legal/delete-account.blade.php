<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delete Your FlatNest Account</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f7f8fa;
      color: #1a1a2e;
      min-height: 100vh;
      padding: 0;
    }

    header {
      background: #ffffff;
      border-bottom: 1px solid #e8eaf0;
      padding: 18px 32px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-icon {
      width: 36px;
      height: 36px;
      background: #e53935;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-icon svg { fill: white; }

    .logo-text {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a2e;
      letter-spacing: -0.3px;
    }

    main {
      max-width: 680px;
      margin: 48px auto;
      padding: 0 20px 60px;
    }

    .page-title {
      font-size: 28px;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 8px;
    }

    .page-sub {
      font-size: 15px;
      color: #6b7280;
      margin-bottom: 36px;
      line-height: 1.6;
    }

    .card {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e8eaf0;
      padding: 28px 32px;
      margin-bottom: 20px;
    }

    .card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
    }

    .step-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .icon-red { background: #fef2f2; }
    .icon-blue { background: #eff6ff; }
    .icon-amber { background: #fffbeb; }

    .card-title {
      font-size: 16px;
      font-weight: 600;
      color: #1a1a2e;
    }

    .card-desc {
      font-size: 14px;
      color: #6b7280;
      margin-top: 2px;
    }

    .steps {
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    .step-row {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 12px 0;
      position: relative;
    }

    .step-row:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 15px;
      top: 42px;
      bottom: 0;
      width: 1.5px;
      background: #e8eaf0;
    }

    .step-num {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #fef2f2;
      border: 1.5px solid #fca5a5;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 600;
      color: #e53935;
      flex-shrink: 0;
      z-index: 1;
    }

    .step-content { padding-top: 5px; }

    .step-label {
      font-size: 14px;
      font-weight: 500;
      color: #1a1a2e;
      margin-bottom: 2px;
    }

    .step-note {
      font-size: 13px;
      color: #9ca3af;
    }

    .role-tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 20px;
    }

    .tab {
      padding: 7px 18px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      border: 1.5px solid #e8eaf0;
      cursor: default;
      color: #6b7280;
      background: #f7f8fa;
    }

    .tab.active {
      background: #1a1a2e;
      color: #ffffff;
      border-color: #1a1a2e;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13.5px;
    }

    .data-table th {
      text-align: left;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9ca3af;
      padding: 0 12px 10px 0;
      border-bottom: 1px solid #e8eaf0;
    }

    .data-table td {
      padding: 10px 12px 10px 0;
      border-bottom: 1px solid #f3f4f6;
      color: #374151;
      vertical-align: top;
    }

    .data-table tr:last-child td { border-bottom: none; }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
    }

    .badge-red { background: #fef2f2; color: #e53935; }
    .badge-green { background: #f0fdf4; color: #16a34a; }
    .badge-amber { background: #fffbeb; color: #d97706; }

    .warning-box {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 12px;
      padding: 16px 20px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
      margin-top: 20px;
    }

    .warning-box svg { flex-shrink: 0; margin-top: 1px; }

    .warning-text {
      font-size: 13.5px;
      color: #78350f;
      line-height: 1.6;
    }

    .contact-row {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-top: 8px;
    }

    .contact-link {
      font-size: 14px;
      color: #e53935;
      text-decoration: none;
      font-weight: 500;
    }

    .contact-link:hover { text-decoration: underline; }

    .divider { height: 1px; background: #f3f4f6; margin: 16px 0; }

    footer {
      text-align: center;
      font-size: 12px;
      color: #9ca3af;
      padding: 24px;
    }
  </style>
</head>
<body>

<header>
  <div class="logo-icon">
    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V21H3V9.5z"/></svg>
  </div>
  <span class="logo-text">FlatNest</span>
</header>

<main>

  <h1 class="page-title">Delete Your Account</h1>
  <p class="page-sub">
    You can delete your FlatNest account directly from the app. Deletion is permanent and all associated data will be removed. Please read the information below before proceeding.
  </p>

  <!-- In-App Deletion -->
  <div class="card">
    <div class="card-header">
      <div class="step-icon icon-red">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e53935" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9.5L12 3l9 6.5V21H3V9.5z"/>
          <rect x="9" y="14" width="6" height="7"/>
        </svg>
      </div>
      <div>
        <p class="card-title">Delete account from the app</p>
        <p class="card-desc">Supported for both Owner and Renter accounts</p>
      </div>
    </div>

    <div class="role-tabs">
      <div class="tab active">Owner</div>
      <div class="tab">Renter</div>
    </div>

    <div class="steps">
      <div class="step-row">
        <div class="step-num">1</div>
        <div class="step-content">
          <p class="step-label">Open FlatNest</p>
          <p class="step-note">Make sure you are logged in to your account</p>
        </div>
      </div>
      <div class="step-row">
        <div class="step-num">2</div>
        <div class="step-content">
          <p class="step-label">Go to Profile tab</p>
          <p class="step-note">Tap the Profile icon in the bottom navigation</p>
        </div>
      </div>
      <div class="step-row">
        <div class="step-num">3</div>
        <div class="step-content">
          <p class="step-label">Tap "Delete Account"</p>
          <p class="step-note">Shown in red at the bottom of your profile settings</p>
        </div>
      </div>
      <div class="step-row">
        <div class="step-num">4</div>
        <div class="step-content">
          <p class="step-label">Confirm deletion</p>
          <p class="step-note">A confirmation dialog will appear — tap Confirm to proceed</p>
        </div>
      </div>
    </div>

    <div class="warning-box">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
      <p class="warning-text">
        <strong>This action is permanent and cannot be undone.</strong> Your account, listings, booking history, and all associated data will be deleted immediately.
      </p>
    </div>
  </div>

  <!-- Data Deletion Info -->
  <div class="card">
    <div class="card-header">
      <div class="step-icon icon-blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
        </svg>
      </div>
      <div>
        <p class="card-title">What data is deleted or kept</p>
        <p class="card-desc">Overview of data handling upon account deletion</p>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Data type</th>
          <th>Action</th>
          <th>Retention</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Profile information (name, photo, bio)</td>
          <td><span class="badge badge-red">Deleted</span></td>
          <td>Immediately</td>
        </tr>
        <tr>
          <td>Property listings</td>
          <td><span class="badge badge-red">Deleted</span></td>
          <td>Immediately</td>
        </tr>
        <tr>
          <td>Booking history</td>
          <td><span class="badge badge-red">Deleted</span></td>
          <td>Immediately</td>
        </tr>
        <tr>
          <td>Messages &amp; conversations</td>
          <td><span class="badge badge-red">Deleted</span></td>
          <td>Immediately</td>
        </tr>
        <tr>
          <td>Authentication data (Google Sign-In)</td>
          <td><span class="badge badge-red">Cleared</span></td>
          <td>Immediately</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Need help -->
  <div class="card">
    <div class="card-header">
      <div class="step-icon icon-amber">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
        </svg>
      </div>
      <div>
        <p class="card-title">Need assistance?</p>
        <p class="card-desc">Unable to delete from the app? Contact our support team</p>
      </div>
    </div>

    <p style="font-size:14px; color:#6b7280; line-height:1.6; margin-bottom:14px;">
      If you are unable to access your account or are experiencing issues with in-app deletion, email us and we will process the request manually within <strong style="color:#1a1a2e;">3 business days</strong>.
    </p>

    <div class="contact-row">
      <a href="mailto:flatnesthelp@gmail.com" class="contact-link">&#9993; flatnesthelp@gmail.com</a>
    </div>

    <div class="divider"></div>

    <p style="font-size:12.5px; color:#9ca3af; line-height:1.6;">
      Please include your registered email address and reason for deletion in your message. Requests are processed within 3 business days.
    </p>
  </div>

</main>

<footer>
  &copy; 2025 FlatNest &middot; <a href="mailto:flatnesthelp@gmail.com" style="color:#9ca3af;">flatnesthelp@gmail.com</a>
</footer>

</body>
</html>
