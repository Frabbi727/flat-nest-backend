// Page 2 — Hostel Detail
// Globals: Icons, HOSTELS, AMENITIES, TYPE_PILL_COLOR, fmtBDT, Button, Toggle, StarRow, PhotoPH

function SeatGrid({ rooms, selectedSeat, setSelectedSeat }) {
  return (
    <>
      {rooms.map((room, idx) => (
        <div className="seat-section" key={idx}>
          <h4 className="seat-room-title">
            <Icons.Grid size={14} style={{ color: 'var(--text-2)' }} />
            {room.name}
            <span className="tiny muted" style={{ fontWeight: 400 }}>
              ({room.seats.filter(s => s.status === 'vacant').length} vacant of {room.seats.length})
            </span>
          </h4>
          <div className="seat-grid">
            {room.seats.map(seat => {
              const sel = selectedSeat === seat.id;
              return (
                <div
                  key={seat.id}
                  className={'seat ' + seat.status + (sel ? ' selected' : '')}
                  onClick={() => seat.status === 'vacant' ? setSelectedSeat(sel ? null : seat.id) : null}
                  title={seat.id + ' · ' + seat.status}
                >
                  {seat.id}
                </div>
              );
            })}
          </div>
        </div>
      ))}
      <div className="seat-legend">
        <span><span className="dot" style={{ background: 'var(--success-bg)' }}></span>Vacant</span>
        <span><span className="dot" style={{ background: '#EDE7FD' }}></span>Taken</span>
        <span><span className="dot" style={{ background: 'var(--warning-bg)' }}></span>Reserved</span>
        <span><span className="dot" style={{ background: '#C8F0D2', border: '2px solid var(--success)', width: 8, height: 8 }}></span>Selected</span>
      </div>
    </>
  );
}

function ReviewItem({ r }) {
  const initials = r.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
  return (
    <div className="review">
      <div className="av">{initials}</div>
      <div className="flex-1">
        <div className="head">
          <span className="name">{r.name}</span>
          <StarRow value={r.rating} size={12} showVal={false} />
          <span className="date">{r.date}</span>
        </div>
        <div className="body">{r.body}</div>
      </div>
    </div>
  );
}

function BookingCard({ h, selectedSeat, setSelectedSeat, mealOn, setMealOn, moveInDate, setMoveInDate }) {
  const isPerDay = h.priceUnit === 'day';
  const rent = h.price;
  const meal = mealOn && h.meal.included ? h.meal.price : 0;
  const advance = h.advance;
  const subtotal = rent + meal + advance;
  const fee = Math.round(subtotal * 0.05);
  const total = rent + meal + advance + fee;

  const vacantSeats = h.rooms.flatMap(r => r.seats.filter(s => s.status === 'vacant').map(s => s.id));

  return (
    <div className="card book-card">
      <div className="big-price">
        {fmtBDT(h.price)} <span className="unit">/ {h.priceUnit}</span>
      </div>
      {h.meal.included ? (
        <div className="tiny muted" style={{ marginTop: 4 }}>
          + {fmtBDT(h.meal.price)}/mo with food
        </div>
      ) : null}
      <div className="tiny muted">
        {fmtBDT(h.advance)} advance to reserve
      </div>

      <div style={{ marginTop: 14, display: 'flex', flexDirection: 'column', gap: 12 }}>
        <label className="field">
          <span className="lbl">Select seat</span>
          <select className="select" value={selectedSeat || ''} onChange={(e) => setSelectedSeat(e.target.value || null)}>
            <option value="">Choose from {vacantSeats.length} vacant…</option>
            {vacantSeats.map(s => <option key={s} value={s}>{s}</option>)}
          </select>
        </label>

        <label className="field">
          <span className="lbl">Move-in date</span>
          <input
            type="date"
            className="input"
            value={moveInDate}
            onChange={(e) => setMoveInDate(e.target.value)}
          />
        </label>

        {h.meal.included ? (
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span style={{ fontSize: 13 }}>Add meal plan</span>
            <Toggle checked={mealOn} onChange={setMealOn} />
          </div>
        ) : null}
      </div>

      <div className="divider-h" style={{ marginTop: 14 }}></div>

      <div>
        <div className="row-line">
          <span className="muted">First {isPerDay ? 'day' : 'month'} rent</span>
          <span>{fmtBDT(rent)}</span>
        </div>
        {h.meal.included ? (
          <div className="row-line" style={{ opacity: mealOn ? 1 : 0.45 }}>
            <span className="muted">Meal plan {isPerDay ? '(1 day)' : '(1 mo)'}</span>
            <span>{fmtBDT(meal)}</span>
          </div>
        ) : null}
        <div className="row-line">
          <span className="muted">Advance</span>
          <span>{fmtBDT(advance)}</span>
        </div>
        <div className="row-line">
          <span className="muted">Platform fee (5%)</span>
          <span>{fmtBDT(fee)}</span>
        </div>
        <div className="divider-h"></div>
        <div className="row-line total">
          <span>Total due today</span>
          <span>{fmtBDT(total)}</span>
        </div>
      </div>

      <div style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 8 }}>
        <Button variant="primary" block disabled={!selectedSeat}>
          Book &amp; Pay via bKash
        </Button>
        <Button variant="outline" block>
          <Icons.Phone size={14} /> Contact Owner
        </Button>
      </div>

      <div className="owner-row">
        <div className="av" style={{
          width: 38, height: 38, borderRadius: 99,
          background: 'var(--primary-soft)', color: 'var(--primary-dark)',
          display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
          fontWeight: 600, flex: 'none', fontSize: 13,
        }}>
          {h.owner.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
        </div>
        <div className="flex-1">
          <div style={{ fontSize: 13, fontWeight: 600 }}>Managed by {h.owner.name}</div>
          <div className="tiny muted">{h.owner.phone} · WhatsApp · Phone</div>
        </div>
      </div>
    </div>
  );
}

function DetailPage({ hostelId, onBack }) {
  const h = HOSTELS.find(x => x.id === hostelId) || HOSTELS[0];
  const [tab, setTab] = useState('overview');
  const [tabKey, setTabKey] = useState(0);
  const [galleryIdx, setGalleryIdx] = useState(0);
  const [selectedSeat, setSelectedSeat] = useState(null);
  const [mealOn, setMealOn] = useState(h.meal.included);
  const [moveInDate, setMoveInDate] = useState('2026-06-01');

  const switchTab = (t) => { setTab(t); setTabKey(k => k + 1); };

  const galleryGrads = [
    h.grad,
    'linear-gradient(135deg, #0E484D 0%, #34A6A6 100%)',
    'linear-gradient(135deg, #FF9500 0%, #FFB74D 100%)',
    'linear-gradient(135deg, #6B47C0 0%, #B189F2 100%)',
  ];

  const TABS = [
    { id: 'overview', label: 'Overview' },
    { id: 'seats', label: 'Seats' },
    { id: 'amenities', label: 'Amenities' },
    { id: 'rules', label: 'Rules' },
    { id: 'reviews', label: 'Reviews' },
  ];

  return (
    <div className="page">
      <div className="breadcrumb">
        <Button variant="ghost" size="sm" onClick={onBack}>
          <Icons.ArrowLeft size={14} /> Back
        </Button>
        <span className="dim">/</span>
        <span className="crumb">Bangladesh</span>
        <span className="dim">/</span>
        <span className="crumb">{h.location}</span>
        <span className="dim">/</span>
        <span className="crumb active">{h.name}</span>
      </div>

      <div className="detail-grid">
        {/* Left */}
        <div>
          <div className="gallery">
            <div className="gallery-main">
              <PhotoPH
                grad={galleryGrads[galleryIdx]}
                label={['Main view', 'Common area', 'Room', 'Exterior'][galleryIdx]}
                icon={<Icons.Building2 size={68} strokeWidth={1.2} />}
                height="100%"
              />
            </div>
            <div className="gallery-thumbs">
              {galleryGrads.map((g, i) => (
                <div
                  key={i}
                  className={'thumb ' + (galleryIdx === i ? 'active' : '')}
                  onClick={() => setGalleryIdx(i)}
                  style={{ background: g }}
                />
              ))}
            </div>
          </div>

          <h1 className="h-title">{h.name}</h1>
          <div className="h-meta">
            <span className="row gap-2" style={{ gap: 4 }}>
              <Icons.MapPin size={14} style={{ color: 'var(--text-2)' }} />
              {h.location} · {h.landmarkDist}
            </span>
            <StarRow value={h.rating} reviews={h.reviews} />
            {h.verified ? (
              <span className="badge"><Icons.Shield size={11} /> VERIFIED</span>
            ) : null}
            <span className={'pill ' + TYPE_PILL_COLOR[h.type]}>{h.typeLabel}</span>
          </div>

          <div className="tabs">
            {TABS.map(t => (
              <button key={t.id} className={'tab ' + (tab === t.id ? 'active' : '')} onClick={() => switchTab(t.id)}>
                {t.label}
              </button>
            ))}
          </div>

          <div className="tab-body" key={tabKey}>
            {tab === 'overview' && (
              <>
                <p style={{ color: 'var(--text-2)', lineHeight: 1.6, margin: '0 0 12px' }}>
                  {h.description}
                </p>
                <div className="stat-row">
                  <div className="stat-cell"><div className="lbl">Total seats</div><div className="val">{h.seats.total}</div></div>
                  <div className="stat-cell"><div className="lbl">Vacant</div><div className="val" style={{ color: 'var(--success)' }}>{h.seats.vacant}</div></div>
                  <div className="stat-cell"><div className="lbl">Floor</div><div className="val" style={{ fontSize: 14 }}>{h.floor}</div></div>
                  <div className="stat-cell"><div className="lbl">Building</div><div className="val" style={{ fontSize: 14 }}>{h.building}</div></div>
                </div>
                <div className={'policy-banner ' + h.gender}>
                  {h.gender === 'female' ? <Icons.Users size={16} /> : h.gender === 'male' ? <Icons.Users size={16} /> : <Icons.Users size={16} />}
                  <span>
                    {h.gender === 'female' && 'Women only — strict gender policy enforced'}
                    {h.gender === 'male' && 'Bachelor men only — no female visitors'}
                    {h.gender === 'mixed' && 'Mixed gender — separate floors/rooms'}
                  </span>
                </div>
                <span className="pill pill-teal" style={{ marginTop: 4 }}>
                  <Icons.MapPin size={11} /> {h.landmarkDist}
                </span>
              </>
            )}

            {tab === 'seats' && (
              <>
                <div className="row" style={{ justifyContent: 'space-between', marginBottom: 8 }}>
                  <div>
                    <div style={{ fontWeight: 600 }}>Select a seat to book</div>
                    <div className="tiny muted">Click a green seat to reserve it. The booking card on the right updates live.</div>
                  </div>
                </div>
                <SeatGrid rooms={h.rooms} selectedSeat={selectedSeat} setSelectedSeat={setSelectedSeat} />
                {selectedSeat ? (
                  <div className="book-btn-row">
                    <Button variant="primary">
                      <Icons.Check size={14} /> Book seat {selectedSeat}
                    </Button>
                  </div>
                ) : null}
              </>
            )}

            {tab === 'amenities' && (
              <div className="am-grid">
                {h.amenities.map(id => {
                  const a = AMENITIES.find(x => x.id === id);
                  if (!a) return null;
                  const Ico = Icons[a.icon];
                  return (
                    <div key={id} className="am-item">
                      {Ico ? <Ico size={16} /> : <Icons.Check size={16} />}
                      <span>{a.label}</span>
                    </div>
                  );
                })}
                {AMENITIES.filter(a => !h.amenities.includes(a.id)).slice(0, 4).map(a => {
                  const Ico = Icons[a.icon];
                  return (
                    <div key={a.id} className="am-item" style={{ opacity: 0.4, textDecoration: 'line-through' }}>
                      {Ico ? <Ico size={16} /> : null}
                      <span>{a.label}</span>
                    </div>
                  );
                })}
              </div>
            )}

            {tab === 'rules' && (
              <ol className="rules-list">
                {h.rules.map((r, i) => <li key={i}>{r}</li>)}
              </ol>
            )}

            {tab === 'reviews' && (
              <div>
                <div className="row gap-3" style={{ marginBottom: 10 }}>
                  <div style={{ fontSize: 32, fontWeight: 700 }}>{h.rating.toFixed(1)}</div>
                  <div>
                    <StarRow value={h.rating} showVal={false} />
                    <div className="tiny muted">{h.reviews} reviews</div>
                  </div>
                </div>
                <div>
                  {h.reviewsList.map((r, i) => <ReviewItem key={i} r={r} />)}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right sticky booking card */}
        <div style={{ position: 'sticky', top: 'calc(var(--nav-h) + 16px)' }}>
          <BookingCard
            h={h}
            selectedSeat={selectedSeat}
            setSelectedSeat={setSelectedSeat}
            mealOn={mealOn}
            setMealOn={setMealOn}
            moveInDate={moveInDate}
            setMoveInDate={setMoveInDate}
          />
        </div>
      </div>
    </div>
  );
}

window.DetailPage = DetailPage;
