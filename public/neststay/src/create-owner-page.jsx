// Admin — Create Owner Account page
function CreateOwnerPage({ onBack }) {
  const [form, setForm] = React.useState({ name: '', email: '', phone: '', password: '' });
  const [loading, setLoading] = React.useState(false);
  const [error, setError]     = React.useState('');
  const [created, setCreated] = React.useState(null);

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    if (!form.name || !form.email || !form.phone || !form.password) {
      setError('All fields are required.');
      return;
    }
    setLoading(true);
    try {
      const owner = await adminCreateOwner(form);
      setCreated(owner);
      setForm({ name: '', email: '', phone: '', password: '' });
    } catch (err) {
      setError(err.message || 'Failed to create owner account.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="page" style={{ display: 'flex', justifyContent: 'center', paddingTop: 32 }}>
      <div style={{ width: '100%', maxWidth: 500 }}>
        <div style={{ marginBottom: 20 }}>
          <Button variant="ghost" size="sm" onClick={onBack}>
            <Icons.ArrowLeft size={14} /> Back
          </Button>
        </div>

        <div className="card" style={{ padding: 32 }}>
          <div style={{ marginBottom: 24 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
              <div style={{
                width: 40, height: 40, borderRadius: 99,
                background: 'var(--primary-soft)', color: 'var(--primary-dark)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
              }}>
                <Icons.Users size={20} />
              </div>
              <div>
                <div style={{ fontSize: 18, fontWeight: 700 }}>Create Owner Account</div>
                <div className="tiny muted">The owner will use these credentials to log in and post hostels</div>
              </div>
            </div>
          </div>

          {created && (
            <div className="note" style={{ background: 'var(--success-bg)', color: 'var(--success)', marginBottom: 20 }}>
              <Icons.Check size={14} />
              <span>
                Owner account created for <strong>{created.name}</strong>.
                Share the credentials: <strong>{created.email}</strong> / their password.
              </span>
            </div>
          )}

          {error && (
            <div className="note" style={{ background: 'var(--danger-bg)', color: 'var(--danger)', marginBottom: 16 }}>
              <Icons.Info size={14} /> {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="col gap-4">
            <label className="field">
              <span className="lbl">Full name</span>
              <input
                className="input"
                placeholder="e.g. Asha Begum"
                value={form.name}
                onChange={(e) => set('name', e.target.value)}
              />
            </label>

            <label className="field">
              <span className="lbl">Email address</span>
              <input
                className="input"
                type="email"
                placeholder="owner@example.com"
                value={form.email}
                onChange={(e) => set('email', e.target.value)}
              />
            </label>

            <label className="field">
              <span className="lbl">Phone number</span>
              <input
                className="input"
                type="tel"
                placeholder="01712345678"
                value={form.phone}
                onChange={(e) => set('phone', e.target.value)}
              />
            </label>

            <label className="field">
              <span className="lbl">Password</span>
              <input
                className="input"
                type="password"
                placeholder="Minimum 8 characters"
                value={form.password}
                onChange={(e) => set('password', e.target.value)}
              />
            </label>

            <div className="note">
              <Icons.Info size={14} />
              The account will be created with <strong>Owner</strong> role and ready to post hostels immediately.
            </div>

            <Button variant="primary" block type="submit" disabled={loading}>
              {loading ? 'Creating…' : 'Create Owner Account'}
            </Button>
          </form>
        </div>
      </div>
    </div>
  );
}

window.CreateOwnerPage = CreateOwnerPage;
