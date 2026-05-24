// Page 1 — Hostel Search & Listing
// Globals: Icons, HOSTELS, HOSTEL_TYPES, TYPE_PILL_COLOR, AMENITIES, DIVISIONS,
// DISTRICTS, UPAZILAS, fmtBDT, Button, Checkbox, Radio, Toggle, Stepper, RangeSlider, StarRow

function HostelCard({ h, onOpen, saved, onToggleSave }) {
  const vacPct = (h.seats.vacant / h.seats.total) * 100;
  return (
    <article className="hostel-card">
      <div className="hostel-photo" style={{ background: h.grad }}>
        <div className="ph-overlay">
          {h.verified ? (
            <span className="badge badge-verified">
              <Icons.Shield size={11} /> VERIFIED
            </span>
          ) : <span/>}
          <span className={'pill ' + TYPE_PILL_COLOR[h.type]}>{h.typeLabel}</span>
        </div>
        <div className="ph-icon"><Icons.Building2 size={48} strokeWidth={1.4} /></div>
      </div>
      <div className="hostel-body">
        <h3 className="hostel-title">{h.name}</h3>
        <div className="hostel-loc">
          <Icons.MapPin size={12} />
          <span>{h.location}{h.landmark ? ' · ' + h.landmark : ''}</span>
        </div>
        <div className="row gap-3" style={{ alignItems: 'center' }}>
          <StarRow value={h.rating} reviews={h.reviews} />
        </div>
        <div>
          <div className="row" style={{ justifyContent: 'space-between', fontSize: 12, color: 'var(--text-2)', marginBottom: 4 }}>
            <span>{h.seats.vacant} / {h.seats.total} seats vacant</span>
            <span>{Math.round(vacPct)}%</span>
          </div>
          <div className="vac-bar"><div style={{ width: vacPct + '%' }}></div></div>
        </div>
        <div className="tags-row">
          <span className={'pill ' + (h.gender === 'female' ? 'pill-pink' : h.gender === 'male' ? 'pill-blue' : 'pill-teal')}>
            {h.gender === 'female' ? '♀ Female only' : h.gender === 'male' ? '♂ Male only' : '⚥ Mixed'}
          </span>
          {h.meal.included ? (
            <span className="pill pill-green"><Icons.Utensils size={11} /> Meal: {fmtBDT(h.meal.price)}/mo</span>
          ) : (
            <span className="pill">No meal</span>
          )}
          {h.curfew ? (
            <span className="pill pill-amber"><Icons.Clock size={11} /> Curfew {h.curfew}</span>
          ) : null}
        </div>
        <div className="price-row">
          <span className="price">{fmtBDT(h.price)}</span>
          <span className="unit">/ {h.priceUnit}</span>
        </div>
        <div className="tiny dim">Advance: {fmtBDT(h.advance)}</div>
      </div>
      <div className="hostel-footer">
        <Button variant="outline" className={saved ? 'heart-active' : ''} onClick={onToggleSave}>
          {saved ? <Icons.HeartFill size={14} /> : <Icons.Heart size={14} />}
          {saved ? 'Saved' : 'Save'}
        </Button>
        <Button variant="primary" onClick={onOpen}>
          View Details <Icons.ChevronRight size={14} />
        </Button>
      </div>
    </article>
  );
}

function SearchPage({ onOpen, saved, toggleSave }) {
  const [quickType, setQuickType] = useState('all');
  const [priceRange, setPriceRange] = useState([3000, 8000]);
  const [typesChecked, setTypesChecked] = useState({});
  const [gender, setGender] = useState('any');
  const [division, setDivision] = useState('Dhaka');
  const [district, setDistrict] = useState('');
  const [upazila, setUpazila] = useState('');
  const [amen, setAmen] = useState({ wifi: true });
  const [minVac, setMinVac] = useState(1);
  const [verifiedOnly, setVerifiedOnly] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const [heroLocation, setHeroLocation] = useState('Dhaka');
  const [heroType, setHeroType] = useState('all');

  const filtered = useMemo(() => {
    return HOSTELS.filter(h => {
      if (quickType !== 'all' && h.type !== quickType) return false;
      if (verifiedOnly && !h.verified) return false;
      if (gender !== 'any' && h.gender !== gender && h.gender !== 'mixed') return false;
      const tChecked = Object.keys(typesChecked).filter(k => typesChecked[k]);
      if (tChecked.length && !tChecked.includes(h.type)) return false;
      if (h.seats.vacant < minVac) return false;
      // price filter only applies to per-month entries
      if (h.priceUnit.includes('month')) {
        if (h.price < priceRange[0] || h.price > priceRange[1]) return false;
      }
      return true;
    });
  }, [quickType, typesChecked, gender, verifiedOnly, priceRange, minVac]);

  const resetFilters = () => {
    setPriceRange([3000, 8000]); setTypesChecked({}); setGender('any');
    setDistrict(''); setUpazila(''); setAmen({}); setMinVac(1); setVerifiedOnly(false);
    setQuickType('all');
  };

  return (
    <>
      {/* Hero */}
      <section className="hero">
        <div className="hero-inner">
          <h1>Find your perfect stay in Bangladesh</h1>
          <p>Hostels, messes &amp; shared rooms — verified &amp; affordable</p>

          <div className="hero-search">
            <div className="field-cell">
              <label>Location</label>
              <select value={heroLocation} onChange={(e) => setHeroLocation(e.target.value)}>
                {DIVISIONS.map(d => <option key={d} value={d}>{d}</option>)}
              </select>
            </div>
            <div className="field-cell">
              <label>Hostel type</label>
              <select value={heroType} onChange={(e) => { setHeroType(e.target.value); setQuickType(e.target.value); }}>
                {HOSTEL_TYPES.map(t => <option key={t.id} value={t.id}>{t.label}</option>)}
              </select>
            </div>
            <Button variant="primary" size="lg" style={{ margin: 4 }}>
              <Icons.Search size={16} /> Search
            </Button>
          </div>

          <div className="hero-quick">
            {HOSTEL_TYPES.map(t => (
              <button
                key={t.id}
                className={'qpill ' + (quickType === t.id ? 'active' : '')}
                onClick={() => setQuickType(t.id)}
              >
                {t.label}
              </button>
            ))}
          </div>
        </div>
      </section>

      <div className="page">
        <div className="row gap-3" style={{ justifyContent: 'space-between', marginBottom: 18 }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 700 }}>{filtered.length} stays found</div>
            <div className="muted tiny">in {heroLocation} · {quickType === 'all' ? 'All types' : HOSTEL_TYPES.find(t => t.id === quickType)?.label}</div>
          </div>
          <div className="row gap-2">
            <Button variant="ghost" size="sm" className="filters-toggle" onClick={() => setSidebarOpen(!sidebarOpen)}>
              <Icons.Filter size={14} /> Filters
            </Button>
            <select className="select" style={{ width: 'auto' }}>
              <option>Sort: Recommended</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Most vacancies</option>
              <option>Highest rated</option>
            </select>
          </div>
        </div>

        <div className="split">
          <aside className={'sidebar ' + (sidebarOpen ? '' : 'collapsed')}>
            <div className="sb-section">
              <h4 className="sb-title">Price per seat/month</h4>
              <RangeSlider min={2000} max={20000} step={100} value={priceRange} onChange={setPriceRange} />
              <div className="tiny muted" style={{ marginTop: 4 }}>
                Selected: {fmtBDT(priceRange[0])} – {fmtBDT(priceRange[1])}
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Hostel type</h4>
              <div className="col gap-2">
                {HOSTEL_TYPES.slice(1).map(t => (
                  <Checkbox
                    key={t.id}
                    label={t.label}
                    checked={!!typesChecked[t.id]}
                    onChange={(v) => setTypesChecked({ ...typesChecked, [t.id]: v })}
                  />
                ))}
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Gender policy</h4>
              <div className="col gap-2">
                {[
                  { id: 'any', label: 'Any' },
                  { id: 'male', label: 'Male only' },
                  { id: 'female', label: 'Female only' },
                  { id: 'mixed', label: 'Mixed' },
                ].map(g => (
                  <Radio key={g.id} name="gender" label={g.label} checked={gender === g.id} onChange={() => setGender(g.id)} />
                ))}
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Location</h4>
              <div className="col gap-2">
                <select className="select" value={division} onChange={(e) => { setDivision(e.target.value); setDistrict(''); setUpazila(''); }}>
                  <option value="">Division</option>
                  {DIVISIONS.map(d => <option key={d} value={d}>{d}</option>)}
                </select>
                <select className="select" value={district} onChange={(e) => { setDistrict(e.target.value); setUpazila(''); }} disabled={!division}>
                  <option value="">District</option>
                  {(DISTRICTS[division] || []).map(d => <option key={d} value={d}>{d}</option>)}
                </select>
                <select className="select" value={upazila} onChange={(e) => setUpazila(e.target.value)} disabled={!district}>
                  <option value="">Upazila</option>
                  {(UPAZILAS[division] || []).map(u => <option key={u} value={u}>{u}</option>)}
                </select>
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Amenities</h4>
              <div className="chip-grid">
                {AMENITIES.map(a => (
                  <button
                    key={a.id}
                    className={'chip ' + (amen[a.id] ? 'active' : '')}
                    onClick={() => setAmen({ ...amen, [a.id]: !amen[a.id] })}
                  >
                    {Icons[a.icon] ? React.createElement(Icons[a.icon], { size: 12 }) : null}
                    {a.label}
                  </button>
                ))}
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Vacancies</h4>
              <div className="row gap-2" style={{ alignItems: 'center' }}>
                <span className="tiny muted">At least</span>
                <Stepper value={minVac} onChange={setMinVac} min={0} max={50} />
                <span className="tiny muted">seat vacant</span>
              </div>
            </div>

            <div className="sb-section">
              <div className="row" style={{ justifyContent: 'space-between' }}>
                <div>
                  <div style={{ fontSize: 13, fontWeight: 600 }}>Verified only</div>
                  <div className="tiny muted">FlatNest-verified listings</div>
                </div>
                <Toggle checked={verifiedOnly} onChange={setVerifiedOnly} />
              </div>
            </div>

            <div className="sb-actions">
              <Button variant="ghost" size="sm" block onClick={resetFilters}>Reset filters</Button>
              <Button variant="primary" size="sm" block>Show results</Button>
            </div>
          </aside>

          <section>
            <div className="grid-cards">
              {filtered.map(h => (
                <HostelCard
                  key={h.id}
                  h={h}
                  onOpen={() => onOpen(h.id)}
                  saved={!!saved[h.id]}
                  onToggleSave={() => toggleSave(h.id)}
                />
              ))}
            </div>
            {filtered.length === 0 ? (
              <div className="card" style={{ padding: 32, textAlign: 'center', color: 'var(--text-2)' }}>
                <Icons.Search size={28} style={{ color: 'var(--text-3)' }} />
                <div style={{ marginTop: 10, fontWeight: 600, color: 'var(--text-1)' }}>No matches</div>
                <div className="tiny">Try widening your filters or resetting.</div>
              </div>
            ) : null}
          </section>
        </div>
      </div>
    </>
  );
}

window.SearchPage = SearchPage;
