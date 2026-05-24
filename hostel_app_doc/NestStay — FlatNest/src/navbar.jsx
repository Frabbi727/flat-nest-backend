// Top navigation bar
function Navbar({ page, setPage, search, setSearch, division, setDivision }) {
  const NAV = [
    { id: 'search', label: 'Search' },
    { id: 'post', label: 'Post Hostel' },
    { id: 'bookings', label: 'My Bookings' },
  ];
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
          <select value={division} onChange={(e) => setDivision(e.target.value)}>
            <option value="">All divisions</option>
            {DIVISIONS.map(d => <option key={d} value={d}>{d}</option>)}
          </select>
        </div>

        <div className="nav-right">
          <Button variant="outline" size="sm" onClick={() => setPage('post')}>
            <Icons.Plus size={14} /> Post a Hostel
          </Button>
          <div className="avatar" title="Account">RA</div>
        </div>
      </div>
    </header>
  );
}

window.Navbar = Navbar;
