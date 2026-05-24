// Page 2 — Hostel Detail (API-driven)

function SeatGrid({ rooms }) {
  const [selectedSeat, setSelectedSeat] = React.useState(null);
  return (
    <>
      {(rooms || []).map((room) => (
        <div className="seat-section" key={room.id}>
          <h4 className="seat-room-title">
            <Icons.Grid size={14} style={{ color: 'var(--text-2)' }} />
            {room.name}
            <span className="tiny muted" style={{ fontWeight: 400 }}>
              ({(room.seats || []).filter(s => s.status === 'vacant').length} vacant of {(room.seats || []).length})
            </span>
          </h4>
          <div className="seat-grid">
            {(room.seats || []).map(seat => {
              const sel = selectedSeat === seat.id;
              return (
                <div
                  key={seat.id}
                  className={'seat ' + seat.status + (sel ? ' selected' : '')}
                  onClick={() => seat.status === 'vacant' ? setSelectedSeat(sel ? null : seat.id) : null}
                  title={seat.seat_number + ' · ' + seat.status}
                >
                  {seat.seat_number}
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
  const name     = r.user?.name || 'Anonymous';
  const initials = name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
  const date     = r.created_at ? new Date(r.created_at).toLocaleDateString('en-BD', { month: 'short', year: 'numeric' }) : '';
  return (
    <div className="review">
      <div className="av">{initials}</div>
      <div className="flex-1">
        <div className="head">
          <span className="name">{name}</span>
          <StarRow value={r.rating} size={12} showVal={false} />
          <span className="date">{date}</span>
        </div>
        <div className="body">{r.comment}</div>
      </div>
    </div>
  );
}

function ContactCard({ h }) {
  const vacantSeats = h.vacant_seats || 0;

  return (
    <div className="card book-card">
      <div className="big-price">
        {fmtBDT(h.price)} <span className="unit">/ {h.price_unit || 'seat/month'}</span>
      </div>
      {h.meal_included ? (
        <div className="tiny muted" style={{ marginTop: 4 }}>
          + {fmtBDT(h.meal_price)}/mo with food
        </div>
      ) : null}
      {h.advance ? (
        <div className="tiny muted">{fmtBDT(h.advance)} advance to reserve</div>
      ) : null}

      <div style={{ marginTop: 14, display: 'flex', flexDirection: 'column', gap: 8 }}>
        <div className="row-line">
          <span className="muted">Vacant seats</span>
          <span style={{ color: 'var(--success)', fontWeight: 600 }}>{vacantSeats}</span>
        </div>
        <div className="row-line">
          <span className="muted">Total seats</span>
          <span>{h.total_seats || 0}</span>
        </div>
        {h.curfew_time ? (
          <div className="row-line">
            <span className="muted">Curfew</span>
            <span>{h.curfew_time}</span>
          </div>
        ) : null}
      </div>

      <div className="divider-h" style={{ marginTop: 14 }}></div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 12 }}>
        <Button variant="primary" block disabled={vacantSeats === 0}>
          {vacantSeats > 0 ? 'Enquire About a Seat' : 'No Vacancies'}
        </Button>
        <Button variant="outline" block>
          <Icons.Phone size={14} /> {h.owner_phone || 'Contact Owner'}
        </Button>
      </div>

      {(h.owner_name || h.owner_phone) && (
        <div className="owner-row">
          <div className="av" style={{
            width: 38, height: 38, borderRadius: 99,
            background: 'var(--primary-soft)', color: 'var(--primary-dark)',
            display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
            fontWeight: 600, flex: 'none', fontSize: 13,
          }}>
            {(h.owner_name || 'O').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
          </div>
          <div className="flex-1">
            <div style={{ fontSize: 13, fontWeight: 600 }}>
              Managed by {h.owner_name || 'Owner'}
            </div>
            {h.owner_phone && <div className="tiny muted">{h.owner_phone}</div>}
          </div>
        </div>
      )}
    </div>
  );
}

function DetailPage({ hostelId, onBack }) {
  const [h, setH]           = React.useState(null);
  const [loading, setLoading] = React.useState(true);
  const [tab, setTab]       = React.useState('overview');
  const [tabKey, setTabKey] = React.useState(0);
  const [galleryIdx, setGalleryIdx] = React.useState(0);

  React.useEffect(() => {
    setLoading(true);
    getHostel(hostelId)
      .then(setH)
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [hostelId]);

  if (loading) {
    return (
      <div className="page" style={{ textAlign: 'center', paddingTop: 80 }}>
        <div style={{ color: 'var(--text-2)' }}>Loading hostel details…</div>
      </div>
    );
  }

  if (!h) {
    return (
      <div className="page">
        <Button variant="ghost" size="sm" onClick={onBack}><Icons.ArrowLeft size={14} /> Back</Button>
        <div className="card" style={{ padding: 32, textAlign: 'center', marginTop: 16 }}>Hostel not found.</div>
      </div>
    );
  }

  const typeSlug = h.type?.name || '';
  const grad = h.photos?.length
    ? null
    : PHOTO_GRADS[hostelId?.charCodeAt(0) % PHOTO_GRADS.length] || PHOTO_GRADS[0];

  const galleryItems = h.photos?.length
    ? h.photos.map(p => ({ url: p.url, label: 'Photo' }))
    : [
        { grad, label: 'Main view' },
        { grad: PHOTO_GRADS[1], label: 'Common area' },
        { grad: PHOTO_GRADS[2], label: 'Room' },
        { grad: PHOTO_GRADS[3], label: 'Exterior' },
      ];

  const switchTab = (t) => { setTab(t); setTabKey(k => k + 1); };

  const TABS = [
    { id: 'overview',  label: 'Overview'  },
    { id: 'seats',     label: 'Seats'     },
    { id: 'amenities', label: 'Amenities' },
    { id: 'rules',     label: 'Rules'     },
    { id: 'reviews',   label: 'Reviews'   },
  ];

  const locationParts = [
    h.upazila?.name,
    h.district?.name,
    h.division?.name,
  ].filter(Boolean).join(', ');

  return (
    <div className="page">
      <div className="breadcrumb">
        <Button variant="ghost" size="sm" onClick={onBack}>
          <Icons.ArrowLeft size={14} /> Back
        </Button>
        <span className="dim">/</span>
        <span className="crumb">Bangladesh</span>
        {locationParts && <><span className="dim">/</span><span className="crumb">{locationParts}</span></>}
        <span className="dim">/</span>
        <span className="crumb active">{h.name}</span>
      </div>

      <div className="detail-grid">
        {/* Left */}
        <div>
          <div className="gallery">
            <div className="gallery-main">
              {galleryItems[galleryIdx]?.url ? (
                <img src={galleryItems[galleryIdx].url} alt={h.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
              ) : (
                <PhotoPH
                  grad={galleryItems[galleryIdx]?.grad}
                  label={galleryItems[galleryIdx]?.label}
                  icon={<Icons.Building2 size={68} strokeWidth={1.2} />}
                  height="100%"
                />
              )}
            </div>
            <div className="gallery-thumbs">
              {galleryItems.map((g, i) => (
                <div
                  key={i}
                  className={'thumb ' + (galleryIdx === i ? 'active' : '')}
                  onClick={() => setGalleryIdx(i)}
                  style={{ background: g.grad || 'var(--bg-2)', backgroundImage: g.url ? `url(${g.url})` : undefined, backgroundSize: 'cover' }}
                />
              ))}
            </div>
          </div>

          <h1 className="h-title">{h.name}</h1>
          <div className="h-meta">
            <span className="row gap-2" style={{ gap: 4 }}>
              <Icons.MapPin size={14} style={{ color: 'var(--text-2)' }} />
              {locationParts || h.address || '—'}
              {h.landmark_distance ? ' · ' + h.landmark_distance : ''}
            </span>
            <StarRow value={h.avg_rating || 0} reviews={(h.reviews || []).length} />
            {h.is_verified && <span className="badge"><Icons.Shield size={11} /> VERIFIED</span>}
            <span className={'pill ' + (TYPE_PILL_COLOR[typeSlug] || 'pill-teal')}>{h.type?.label || 'Hostel'}</span>
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
                  {h.description || 'No description provided.'}
                </p>
                <div className="stat-row">
                  <div className="stat-cell"><div className="lbl">Total seats</div><div className="val">{h.total_seats}</div></div>
                  <div className="stat-cell"><div className="lbl">Vacant</div><div className="val" style={{ color: 'var(--success)' }}>{h.vacant_seats}</div></div>
                  {h.floor && <div className="stat-cell"><div className="lbl">Floor</div><div className="val" style={{ fontSize: 14 }}>{h.floor}</div></div>}
                  {h.building && <div className="stat-cell"><div className="lbl">Building</div><div className="val" style={{ fontSize: 14 }}>{h.building}</div></div>}
                </div>
                <div className={'policy-banner ' + (h.gender_policy || 'mixed')}>
                  <Icons.Users size={16} />
                  <span>
                    {h.gender_policy === 'female' && 'Women only — strict gender policy enforced'}
                    {h.gender_policy === 'male'   && 'Bachelor men only — no female visitors'}
                    {(!h.gender_policy || h.gender_policy === 'mixed') && 'Mixed gender — separate floors/rooms'}
                  </span>
                </div>
                {h.landmark_distance && (
                  <span className="pill pill-teal" style={{ marginTop: 4 }}>
                    <Icons.MapPin size={11} /> {h.landmark_distance}
                  </span>
                )}
              </>
            )}

            {tab === 'seats' && (
              <>
                <div className="row" style={{ justifyContent: 'space-between', marginBottom: 8 }}>
                  <div>
                    <div style={{ fontWeight: 600 }}>Seat layout</div>
                    <div className="tiny muted">Click a green seat to select it.</div>
                  </div>
                </div>
                <SeatGrid rooms={h.rooms || []} />
              </>
            )}

            {tab === 'amenities' && (
              <div className="am-grid">
                {(h.amenities || []).map(a => (
                  <div key={a.id} className="am-item">
                    <Icons.Check size={16} />
                    <span>{a.label}</span>
                  </div>
                ))}
                {(h.amenities || []).length === 0 && (
                  <div className="tiny muted">No amenities listed.</div>
                )}
              </div>
            )}

            {tab === 'rules' && (
              (h.rules || []).length > 0 ? (
                <ol className="rules-list">
                  {(h.rules || []).map((r, i) => <li key={i}>{r}</li>)}
                </ol>
              ) : (
                <div className="tiny muted">No rules listed.</div>
              )
            )}

            {tab === 'reviews' && (
              <div>
                <div className="row gap-3" style={{ marginBottom: 10 }}>
                  <div style={{ fontSize: 32, fontWeight: 700 }}>{(h.avg_rating || 0).toFixed(1)}</div>
                  <div>
                    <StarRow value={h.avg_rating || 0} showVal={false} />
                    <div className="tiny muted">{(h.reviews || []).length} reviews</div>
                  </div>
                </div>
                {(h.reviews || []).length === 0 && (
                  <div className="tiny muted">No reviews yet.</div>
                )}
                <div>
                  {(h.reviews || []).map(r => <ReviewItem key={r.id} r={r} />)}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right sticky contact card */}
        <div style={{ position: 'sticky', top: 'calc(var(--nav-h) + 16px)' }}>
          <ContactCard h={h} />
        </div>
      </div>
    </div>
  );
}

window.DetailPage = DetailPage;
