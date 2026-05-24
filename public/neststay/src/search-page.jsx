// Page 1 — Hostel Search & Listing (API-driven)
const { useState, useEffect, useMemo, useCallback } = React;

function HostelCard({ h, onOpen, saved, onToggleSave }) {
  const totalSeats  = h.total_seats || 0;
  const vacantSeats = h.vacant_seats || 0;
  const vacPct      = totalSeats > 0 ? (vacantSeats / totalSeats) * 100 : 0;
  const typeSlug    = h.type?.name || '';
  const grad        = PHOTO_GRADS[h.id?.charCodeAt(0) % PHOTO_GRADS.length] || PHOTO_GRADS[0];

  return (
    <article className="hostel-card">
      <div className="hostel-photo" style={{ background: grad }}>
        <div className="ph-overlay">
          {h.is_verified ? (
            <span className="badge badge-verified"><Icons.Shield size={11} /> VERIFIED</span>
          ) : <span/>}
          <span className={'pill ' + (TYPE_PILL_COLOR[typeSlug] || 'pill-teal')}>{h.type?.label || 'Hostel'}</span>
        </div>
        <div className="ph-icon"><Icons.Building2 size={48} strokeWidth={1.4} /></div>
      </div>
      <div className="hostel-body">
        <h3 className="hostel-title">{h.name}</h3>
        <div className="hostel-loc">
          <Icons.MapPin size={12} />
          <span>
            {h.address || h.upazila?.name || h.district?.name || h.division?.name || '—'}
            {h.landmark ? ' · ' + h.landmark : ''}
          </span>
        </div>
        <div className="row gap-3" style={{ alignItems: 'center' }}>
          <StarRow value={h.avg_rating || 0} />
        </div>
        <div>
          <div className="row" style={{ justifyContent: 'space-between', fontSize: 12, color: 'var(--text-2)', marginBottom: 4 }}>
            <span>{vacantSeats} / {totalSeats} seats vacant</span>
            <span>{Math.round(vacPct)}%</span>
          </div>
          <div className="vac-bar"><div style={{ width: vacPct + '%' }}></div></div>
        </div>
        <div className="tags-row">
          <span className={'pill ' + (h.gender_policy === 'female' ? 'pill-pink' : h.gender_policy === 'male' ? 'pill-blue' : 'pill-teal')}>
            {h.gender_policy === 'female' ? '♀ Female only' : h.gender_policy === 'male' ? '♂ Male only' : '⚥ Mixed'}
          </span>
          {h.meal_included ? (
            <span className="pill pill-green"><Icons.Utensils size={11} /> Meal: {fmtBDT(h.meal_price || 0)}/mo</span>
          ) : (
            <span className="pill">No meal</span>
          )}
          {h.curfew_time ? (
            <span className="pill pill-amber"><Icons.Clock size={11} /> Curfew {h.curfew_time}</span>
          ) : null}
        </div>
        <div className="price-row">
          <span className="price">{fmtBDT(h.price)}</span>
          <span className="unit">/ {h.price_unit || 'seat/month'}</span>
        </div>
        {h.advance ? <div className="tiny dim">Advance: {fmtBDT(h.advance)}</div> : null}
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

function SearchPage({ onOpen, saved, toggleSave, navSearch, navDivisionId }) {
  const [hostelTypes, setHostelTypes] = useState([]);
  const [divisions, setDivisions]     = useState([]);
  const [districts, setDistricts]     = useState([]);
  const [upazilas, setUpazilas]       = useState([]);
  const [amenities, setAmenities]     = useState([]);

  const [quickType, setQuickType]     = useState('');
  const [priceRange, setPriceRange]   = useState([2000, 20000]);
  const [gender, setGender]           = useState('');
  const [divisionId, setDivisionId]   = useState(navDivisionId || '');
  const [districtId, setDistrictId]   = useState('');
  const [upazilaId, setUpazilaId]     = useState('');
  const [amenSel, setAmenSel]         = useState({});
  const [minVac, setMinVac]           = useState(0);
  const [verifiedOnly, setVerifiedOnly] = useState(false);
  const [sortBy, setSortBy]           = useState('');
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const [hostels, setHostels]         = useState([]);
  const [meta, setMeta]               = useState(null);
  const [loading, setLoading]         = useState(false);
  const [page, setPage]               = useState(1);
  const [typesChecked, setTypesChecked] = useState({});

  // Load static data on mount
  useEffect(() => {
    getHostelTypes().then(setHostelTypes).catch(() => {});
    getDivisions().then(setDivisions).catch(() => {});
    getAmenities().then(setAmenities).catch(() => {});
  }, []);

  // Cascade: division → districts
  useEffect(() => {
    setDistricts([]); setDistrictId('');
    setUpazilas([]); setUpazilaId('');
    if (divisionId) getDistricts(divisionId).then(setDistricts).catch(() => {});
  }, [divisionId]);

  // Cascade: district → upazilas
  useEffect(() => {
    setUpazilas([]); setUpazilaId('');
    if (districtId) getUpazilas(districtId).then(setUpazilas).catch(() => {});
  }, [districtId]);

  // Sync navbar division
  useEffect(() => { if (navDivisionId !== undefined) setDivisionId(navDivisionId); }, [navDivisionId]);

  const fetchHostels = useCallback(async (pg = 1) => {
    setLoading(true);
    try {
      const checkedTypes = Object.keys(typesChecked).filter(k => typesChecked[k]);
      const amenIds = Object.keys(amenSel).filter(k => amenSel[k]).join(',');
      const filters = {
        page: pg,
        ...(navSearch       ? { search: navSearch }           : {}),
        ...(quickType       ? { type_id: quickType }          : {}),
        ...(checkedTypes.length === 1 ? { type_id: checkedTypes[0] } : {}),
        ...(gender          ? { gender_policy: gender }       : {}),
        price_min: priceRange[0],
        price_max: priceRange[1],
        ...(divisionId      ? { division_id: divisionId }    : {}),
        ...(districtId      ? { district_id: districtId }    : {}),
        ...(upazilaId       ? { upazila_id: upazilaId }      : {}),
        ...(amenIds         ? { amenities: amenIds }          : {}),
        ...(minVac > 0      ? { min_vacant_seats: minVac }   : {}),
        ...(verifiedOnly    ? { is_verified: 1 }              : {}),
        ...(sortBy          ? { sort_by: sortBy }             : {}),
      };
      const res = await getHostels(filters);
      if (pg === 1) {
        setHostels(res.data || []);
      } else {
        setHostels(prev => [...prev, ...(res.data || [])]);
      }
      setMeta(res.meta || null);
      setPage(pg);
    } catch {}
    setLoading(false);
  }, [navSearch, quickType, typesChecked, gender, priceRange, divisionId, districtId, upazilaId, amenSel, minVac, verifiedOnly, sortBy]);

  // Re-fetch whenever filters change
  useEffect(() => { fetchHostels(1); }, [fetchHostels]);

  const resetFilters = () => {
    setPriceRange([2000, 20000]); setTypesChecked({}); setGender('');
    setDistrictId(''); setUpazilaId(''); setAmenSel({}); setMinVac(0);
    setVerifiedOnly(false); setQuickType(''); setSortBy('');
  };

  const divisionName = divisions.find(d => d.id == divisionId)?.name || 'All Bangladesh';

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
              <select value={divisionId} onChange={(e) => setDivisionId(e.target.value)}>
                <option value="">All divisions</option>
                {divisions.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>
            </div>
            <div className="field-cell">
              <label>Hostel type</label>
              <select value={quickType} onChange={(e) => setQuickType(e.target.value)}>
                <option value="">All types</option>
                {hostelTypes.map(t => <option key={t.id} value={t.id}>{t.label}</option>)}
              </select>
            </div>
            <Button variant="primary" size="lg" style={{ margin: 4 }} onClick={() => fetchHostels(1)}>
              <Icons.Search size={16} /> Search
            </Button>
          </div>

          <div className="hero-quick">
            <button className={'qpill ' + (quickType === '' ? 'active' : '')} onClick={() => setQuickType('')}>All</button>
            {hostelTypes.map(t => (
              <button key={t.id} className={'qpill ' + (quickType == t.id ? 'active' : '')} onClick={() => setQuickType(t.id)}>
                {t.label}
              </button>
            ))}
          </div>
        </div>
      </section>

      <div className="page">
        <div className="row gap-3" style={{ justifyContent: 'space-between', marginBottom: 18 }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 700 }}>
              {loading ? 'Loading…' : (meta?.total ?? hostels.length) + ' stays found'}
            </div>
            <div className="muted tiny">
              in {divisionName} · {quickType ? (hostelTypes.find(t => t.id == quickType)?.label || 'Filtered') : 'All types'}
            </div>
          </div>
          <div className="row gap-2">
            <Button variant="ghost" size="sm" className="filters-toggle" onClick={() => setSidebarOpen(!sidebarOpen)}>
              <Icons.Filter size={14} /> Filters
            </Button>
            <select className="select" style={{ width: 'auto' }} value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
              <option value="">Sort: Recommended</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="most_vacant">Most Vacancies</option>
              <option value="rating">Highest Rated</option>
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
                {hostelTypes.map(t => (
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
                  { id: '', label: 'Any' },
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
                <select className="select" value={divisionId} onChange={(e) => setDivisionId(e.target.value)}>
                  <option value="">Division</option>
                  {divisions.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                </select>
                <select className="select" value={districtId} onChange={(e) => setDistrictId(e.target.value)} disabled={!divisionId}>
                  <option value="">District</option>
                  {districts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                </select>
                <select className="select" value={upazilaId} onChange={(e) => setUpazilaId(e.target.value)} disabled={!districtId}>
                  <option value="">Upazila</option>
                  {upazilas.map(u => <option key={u.id} value={u.id}>{u.name}</option>)}
                </select>
              </div>
            </div>

            <div className="sb-section">
              <h4 className="sb-title">Amenities</h4>
              <div className="chip-grid">
                {amenities.map(a => (
                  <button
                    key={a.id}
                    className={'chip ' + (amenSel[a.id] ? 'active' : '')}
                    onClick={() => setAmenSel({ ...amenSel, [a.id]: !amenSel[a.id] })}
                  >
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
              <Button variant="primary" size="sm" block onClick={() => fetchHostels(1)}>Show results</Button>
            </div>
          </aside>

          <section>
            <div className="grid-cards">
              {hostels.map(h => (
                <HostelCard
                  key={h.id}
                  h={h}
                  onOpen={() => onOpen(h.id)}
                  saved={!!saved[h.id]}
                  onToggleSave={() => toggleSave(h.id)}
                />
              ))}
            </div>

            {loading && (
              <div style={{ textAlign: 'center', padding: 32, color: 'var(--text-2)' }}>
                Loading hostels…
              </div>
            )}

            {!loading && hostels.length === 0 && (
              <div className="card" style={{ padding: 32, textAlign: 'center', color: 'var(--text-2)' }}>
                <Icons.Search size={28} style={{ color: 'var(--text-3)' }} />
                <div style={{ marginTop: 10, fontWeight: 600, color: 'var(--text-1)' }}>No matches</div>
                <div className="tiny">Try widening your filters or resetting.</div>
              </div>
            )}

            {meta && page < meta.last_page && !loading && (
              <div style={{ textAlign: 'center', marginTop: 24 }}>
                <Button variant="outline" onClick={() => fetchHostels(page + 1)}>
                  Load more
                </Button>
              </div>
            )}
          </section>
        </div>
      </div>
    </>
  );
}

window.SearchPage = SearchPage;
