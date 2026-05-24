// Top navigation bar
function Navbar({ page, setPage, search, setSearch, divisions, divisionId, setDivisionId, user, onLogout }) {
  const NAV = [
    { id: 'search', label: 'Search' },
    { id: 'post',   label: 'Post Hostel' },
    ...(user?.role === 'owner' || user?.role === 'admin' ? [{ id: 'my-listings', label: 'My Listings' }] : []),
    ...(user?.role === 'admin' ? [{ id: 'create-owner', label: 'Create Owner' }] : []),
  ];

  const initials = user
    ? (user.name || '').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase() || 'U'
    : null;

  return (
    <header className="nav">
      <div className="nav-inner">
        <div className="brand" onClick={() => setPage('search')} style={{ cursor: 'pointer' }}>
          <span className="brand-mark"><Icons.Home size={16} /></span>
          <span>FlatNest</span>
          <span className="nest-badge">NestStay</span>
        </div>

        <nav className="nav-links">
          {NAV.map(n => (
            <button
              key={n.id}
              className={'nav-link ' + ((page === n.id || (n.id === 'search' && page === 'detail')) ? 'active' : '')}
              onClick={() => setPage(n.id)}
            >
              {n.label}
            </button>
          ))}
        </nav>

        <div className="nav-search">
          <Icons.Search size={15} style={{ color: 'var(--text-3)' }} />
          <input
            placeholder="Search hostels, mess, location…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
          <span className="divider"></span>
          <select value={divisionId} onChange={(e) => setDivisionId(e.target.value)}>
            <option value="">All divisions</option>
            {(divisions || []).map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
        </div>

        <div className="nav-right">
          <Button variant="outline" size="sm" onClick={() => setPage('post')}>
            <Icons.Plus size={14} /> Post a Hostel
          </Button>
          {user ? (
            <div
              className="avatar"
              title={user.name + ' · Sign out'}
              onClick={onLogout}
              style={{ cursor: 'pointer' }}
            >
              {initials}
            </div>
          ) : (
            <Button variant="ghost" size="sm" onClick={() => setPage('auth')}>
              Sign In
            </Button>
          )}
        </div>
      </div>
    </header>
  );
}

window.Navbar = Navbar;
