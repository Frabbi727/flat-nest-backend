// Root App — page routing and shared state
function App() {
  const [page, setPage]       = React.useState('search');
  const [hostelId, setHostelId] = React.useState(null);
  const [saved, setSaved]     = React.useState({});
  const [navSearch, setNavSearch] = React.useState('');
  const [navDivisionId, setNavDivisionId] = React.useState('');
  const [divisions, setDivisions] = React.useState([]);
  const [user, setUser]       = React.useState(() => getUser());

  React.useEffect(() => {
    getDivisions().then(setDivisions).catch(() => {});
  }, []);

  const toggleSave = (id) => setSaved(s => ({ ...s, [id]: !s[id] }));

  const open = (id) => {
    setHostelId(id);
    setPage('detail');
    window.scrollTo(0, 0);
  };

  const goTo = (p) => {
    if ((p === 'post' || p === 'my-listings') && !user) {
      setPage('auth');
    } else {
      setPage(p);
    }
    window.scrollTo(0, 0);
  };

  const handleLogout = async () => {
    await logout();
    setUser(null);
    setPage('search');
  };

  const handleAuthSuccess = (u) => {
    setUser(u);
    setPage('post');
    window.scrollTo(0, 0);
  };

  return (
    <div className="app">
      <Navbar
        page={page}
        setPage={goTo}
        search={navSearch}
        setSearch={setNavSearch}
        divisions={divisions}
        divisionId={navDivisionId}
        setDivisionId={setNavDivisionId}
        user={user}
        onLogout={handleLogout}
      />

      {page === 'search' && (
        <SearchPage
          onOpen={open}
          saved={saved}
          toggleSave={toggleSave}
          navSearch={navSearch}
          navDivisionId={navDivisionId}
        />
      )}
      {page === 'detail' && (
        <DetailPage
          hostelId={hostelId}
          onBack={() => { setPage('search'); window.scrollTo(0, 0); }}
        />
      )}
      {page === 'post' && (
        <PostPage
          user={user}
          onDone={() => { setPage('search'); window.scrollTo(0, 0); }}
        />
      )}
      {page === 'auth' && (
        <AuthPage
          onSuccess={handleAuthSuccess}
          onBack={() => { setPage('search'); window.scrollTo(0, 0); }}
        />
      )}
      {page === 'my-listings' && (
        <OwnerDashboardPage
          user={user}
          onPostHostel={() => { setPage('post'); window.scrollTo(0, 0); }}
          onPostListing={() => { setPage('post'); window.scrollTo(0, 0); }}
        />
      )}
      {page === 'create-owner' && (
        <CreateOwnerPage
          onBack={() => { setPage('search'); window.scrollTo(0, 0); }}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
