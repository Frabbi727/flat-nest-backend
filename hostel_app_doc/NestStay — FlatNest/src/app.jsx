// Root App — page routing and shared state
function App() {
  const [page, setPage] = useState('search'); // 'search' | 'detail' | 'post' | 'bookings'
  const [hostelId, setHostelId] = useState(null);
  const [saved, setSaved] = useState({ asha: true });
  const [search, setSearch] = useState('');
  const [division, setDivision] = useState('Dhaka');

  const toggleSave = (id) => setSaved(s => ({ ...s, [id]: !s[id] }));
  const open = (id) => { setHostelId(id); setPage('detail'); window.scrollTo(0, 0); };

  return (
    <div className="app">
      <Navbar
        page={page}
        setPage={(p) => { setPage(p); window.scrollTo(0, 0); }}
        search={search}
        setSearch={setSearch}
        division={division}
        setDivision={setDivision}
      />

      {page === 'search' && (
        <SearchPage onOpen={open} saved={saved} toggleSave={toggleSave} />
      )}
      {page === 'detail' && (
        <DetailPage hostelId={hostelId} onBack={() => { setPage('search'); window.scrollTo(0, 0); }} />
      )}
      {page === 'post' && (
        <PostPage onDone={() => { setPage('search'); window.scrollTo(0, 0); }} />
      )}
      {page === 'bookings' && <BookingsPage />}
    </div>
  );
}

function BookingsPage() {
  return (
    <div className="page">
      <div className="card" style={{ padding: 40, textAlign: 'center', maxWidth: 560, margin: '40px auto' }}>
        <div style={{
          width: 60, height: 60, borderRadius: 99, margin: '0 auto 14px',
          background: 'var(--primary-soft)', color: 'var(--primary-dark)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          <Icons.Calendar size={28} />
        </div>
        <h2 style={{ margin: '0 0 6px' }}>No bookings yet</h2>
        <p className="muted" style={{ margin: '0 0 18px' }}>
          When you book a seat, it'll show up here. Browse hostels to get started.
        </p>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
