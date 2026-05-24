// Auth Page — Login for hostel owners
function AuthPage({ onSuccess, onBack }) {
  const [identifier, setIdentifier] = React.useState('');
  const [password, setPassword] = React.useState('');
  const [loading, setLoading] = React.useState(false);
  const [error, setError] = React.useState('');

  const handleLogin = async (e) => {
    e.preventDefault();
    if (!identifier || !password) { setError('Please enter phone/email and password.'); return; }
    setLoading(true);
    setError('');
    try {
      const res = await login(identifier, password);
      const token = res.data?.access_token || res.data?.token;
      const user  = res.data?.user;
      if (!token) throw new Error('No token received');
      setAuth(token, user);
      onSuccess(user);
    } catch (err) {
      setError(err.message || 'Login failed. Check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="page" style={{ display: 'flex', justifyContent: 'center', alignItems: 'flex-start', paddingTop: 40 }}>
      <div className="card" style={{ width: '100%', maxWidth: 420, padding: 36 }}>
        <div style={{ textAlign: 'center', marginBottom: 24 }}>
          <div style={{
            width: 52, height: 52, borderRadius: 99, margin: '0 auto 12px',
            background: 'var(--primary-soft)', color: 'var(--primary-dark)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}>
            <Icons.Home size={24} />
          </div>
          <h2 style={{ margin: '0 0 4px' }}>Sign in to FlatNest</h2>
          <div className="muted tiny">Owner account required to post a hostel</div>
        </div>

        {error && (
          <div className="note" style={{ background: 'var(--danger-bg)', color: 'var(--danger)', marginBottom: 16 }}>
            <Icons.Info size={14} /> {error}
          </div>
        )}

        <form onSubmit={handleLogin} className="col gap-4">
          <label className="field">
            <span className="lbl">Phone number or Email</span>
            <input
              className="input"
              type="text"
              placeholder="01XXXXXXXXX or email@example.com"
              value={identifier}
              onChange={(e) => setIdentifier(e.target.value)}
              autoFocus
            />
          </label>

          <label className="field">
            <span className="lbl">Password</span>
            <input
              className="input"
              type="password"
              placeholder="Your password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </label>

          <Button variant="primary" block type="submit" disabled={loading}>
            {loading ? 'Signing in…' : 'Sign In'}
          </Button>

          <Button variant="ghost" block type="button" onClick={onBack}>
            <Icons.ArrowLeft size={14} /> Back to Browse
          </Button>
        </form>
      </div>
    </div>
  );
}

window.AuthPage = AuthPage;
