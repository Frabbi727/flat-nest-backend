// Owner Dashboard — shows all flat/mess listings and NestStay hostels
const { useState, useEffect, useCallback } = React;

const STATUS_STYLE = {
  draft:    { bg: '#F3F4F6', color: '#6B7280',  label: 'Draft'    },
  pending:  { bg: '#FFF3CD', color: '#92600A',  label: 'Pending'  },
  active:   { bg: '#D1FAE5', color: '#065F46',  label: 'Active'   },
  rejected: { bg: '#FFE4E4', color: '#991B1B',  label: 'Rejected' },
  rented:   { bg: '#DBEAFE', color: '#1E40AF',  label: 'Rented'   },
};

function StatusPill({ status }) {
  const s = STATUS_STYLE[status] || { bg: '#F3F4F6', color: '#6B7280', label: status };
  return (
    <span style={{
      display: 'inline-block',
      padding: '2px 10px',
      borderRadius: 99,
      fontSize: 11.5,
      fontWeight: 600,
      background: s.bg,
      color: s.color,
    }}>{s.label}</span>
  );
}

function CategoryPill({ category }) {
  const isFlat = !category || category === 'flat';
  return (
    <span style={{
      display: 'inline-block',
      padding: '1px 8px',
      borderRadius: 99,
      fontSize: 10.5,
      fontWeight: 700,
      letterSpacing: 0.4,
      textTransform: 'uppercase',
      background: isFlat ? '#DBEAFE' : '#FFF3E0',
      color: isFlat ? '#1E40AF' : '#C2410C',
    }}>{isFlat ? 'Flat' : 'Mess'}</span>
  );
}

function EmptyState({ icon, title, subtitle, action }) {
  return (
    <div style={{ textAlign: 'center', padding: '60px 24px' }}>
      <div style={{
        width: 56, height: 56, borderRadius: 16,
        background: 'var(--primary-soft)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        margin: '0 auto 16px',
      }}>
        {icon}
      </div>
      <div style={{ fontSize: 15, fontWeight: 600, color: 'var(--text-1)', marginBottom: 6 }}>{title}</div>
      <div style={{ fontSize: 13.5, color: 'var(--text-3)', marginBottom: 20 }}>{subtitle}</div>
      {action}
    </div>
  );
}

// ── Flat / Mess Listings Tab ────────────────────────────────────────────────

function ListingsTab({ onPostListing }) {
  const [statusFilter, setStatusFilter] = useState('all');
  const [catFilter, setCatFilter]       = useState('all');
  const [rows, setRows]                 = useState([]);
  const [meta, setMeta]                 = useState(null);
  const [page, setPage]                 = useState(1);
  const [loading, setLoading]           = useState(true);
  const [error, setError]               = useState(null);

  const load = useCallback((pg = 1) => {
    setLoading(true);
    setError(null);
    const filters = { page: pg };
    if (statusFilter !== 'all') filters.status = statusFilter;
    if (catFilter    !== 'all') filters.category = catFilter;
    getOwnerListings(filters)
      .then(res => { setRows(res.data || []); setMeta(res.meta || null); setLoading(false); })
      .catch(e  => { setError(e.message); setLoading(false); });
  }, [statusFilter, catFilter]);

  useEffect(() => { setPage(1); load(1); }, [statusFilter, catFilter]);
  useEffect(() => { load(page); }, [page]);

  const STATUS_TABS = ['all', 'draft', 'pending', 'active', 'rejected', 'rented'];

  return (
    <div>
      {/* Filters row */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap', marginBottom: 20 }}>
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
          {STATUS_TABS.map(s => (
            <button
              key={s}
              onClick={() => setStatusFilter(s)}
              style={{
                padding: '5px 14px',
                borderRadius: 99,
                border: '1.5px solid',
                fontSize: 12.5,
                fontWeight: 500,
                cursor: 'pointer',
                transition: 'all 120ms',
                borderColor: statusFilter === s ? 'var(--primary)' : 'var(--border)',
                background:  statusFilter === s ? 'var(--primary)' : '#fff',
                color:       statusFilter === s ? '#fff' : 'var(--text-2)',
              }}
            >
              {s === 'all' ? 'All' : STATUS_STYLE[s]?.label || s}
            </button>
          ))}
        </div>
        <div style={{ flex: 1 }} />
        <select
          value={catFilter}
          onChange={e => setCatFilter(e.target.value)}
          className="select"
          style={{ width: 'auto', padding: '6px 12px', fontSize: 13 }}
        >
          <option value="all">All categories</option>
          <option value="flat">Flat only</option>
          <option value="mess">Mess only</option>
        </select>
      </div>

      {error && (
        <div style={{ padding: 16, background: 'var(--error-bg)', borderRadius: 8, color: 'var(--error)', marginBottom: 16, fontSize: 13.5 }}>
          {error}
        </div>
      )}

      {loading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {[1,2,3].map(i => (
            <div key={i} style={{ background: '#fff', borderRadius: 12, padding: 20, border: '1px solid var(--border-soft)', height: 90, opacity: 0.5 }} />
          ))}
        </div>
      ) : rows.length === 0 ? (
        <EmptyState
          icon={<Icons.Home size={26} style={{ color: 'var(--primary)' }} />}
          title="No listings yet"
          subtitle={statusFilter !== 'all' ? 'No listings with this status.' : 'Post your first flat or mess listing.'}
          action={statusFilter === 'all' && (
            <Button onClick={onPostListing} size="sm">
              <Icons.Plus size={14} /> Post a Listing
            </Button>
          )}
        />
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {rows.map(l => (
            <ListingCard key={l.id} listing={l} onReload={() => load(page)} />
          ))}
        </div>
      )}

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 24 }}>
          <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(p => p - 1)}>
            Previous
          </Button>
          <span style={{ padding: '7px 12px', fontSize: 13, color: 'var(--text-2)' }}>
            {page} / {meta.last_page}
          </span>
          <Button variant="outline" size="sm" disabled={page >= meta.last_page} onClick={() => setPage(p => p + 1)}>
            Next
          </Button>
        </div>
      )}
    </div>
  );
}

function ListingCard({ listing: l }) {
  const [expanded, setExpanded] = useState(false);
  return (
    <div style={{
      background: '#fff',
      borderRadius: 12,
      border: '1px solid var(--border-soft)',
      padding: '16px 20px',
      boxShadow: '0 1px 4px rgba(0,0,0,0.04)',
    }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
        {/* Left: icon */}
        <div style={{
          width: 44, height: 44, borderRadius: 10, flexShrink: 0,
          background: l.category === 'mess' ? '#FFF3E0' : 'var(--primary-soft)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          <Icons.Home size={20} style={{ color: l.category === 'mess' ? '#C2410C' : 'var(--primary)' }} />
        </div>

        {/* Middle: info */}
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginBottom: 4 }}>
            <span style={{ fontSize: 14.5, fontWeight: 600, color: 'var(--text-1)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
              {l.title || '(untitled)'}
            </span>
            <CategoryPill category={l.category} />
            <StatusPill status={l.status} />
          </div>
          <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap', fontSize: 12.5, color: 'var(--text-3)' }}>
            {l.price > 0 && <span style={{ fontWeight: 600, color: 'var(--primary)' }}>{fmtBDT(l.price)}/mo</span>}
            {l.category !== 'mess' && l.beds != null && <span>{l.beds} bed · {l.baths} bath</span>}
            {l.listing_type?.label && <span>{l.listing_type.label}</span>}
            {(l.area || l.district?.name || l.division?.name) && (
              <span style={{ display: 'flex', alignItems: 'center', gap: 3 }}>
                <Icons.MapPin size={11} />
                {l.area || l.district?.name || l.division?.name}
              </span>
            )}
            {l.created_at && <span>{new Date(l.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</span>}
          </div>
          {l.status === 'rejected' && l.rejection_reason && (
            <div style={{ marginTop: 8, padding: '6px 10px', background: '#FEF2F2', borderRadius: 6, fontSize: 12.5, color: '#991B1B', display: 'flex', gap: 6 }}>
              <Icons.AlertTriangle size={13} style={{ flexShrink: 0, marginTop: 1 }} />
              <span>{l.rejection_reason}</span>
            </div>
          )}
        </div>

        {/* Right: toggle details */}
        <button
          onClick={() => setExpanded(e => !e)}
          style={{ background: 'none', border: 'none', padding: 4, color: 'var(--text-3)', cursor: 'pointer' }}
        >
          <Icons.ChevronDown size={16} style={{ transform: expanded ? 'rotate(180deg)' : 'none', transition: 'transform 150ms' }} />
        </button>
      </div>

      {expanded && (
        <div style={{ marginTop: 14, paddingTop: 14, borderTop: '1px solid var(--border-soft)', display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))', gap: 10, fontSize: 12.5, color: 'var(--text-2)' }}>
          {l.deposit != null   && <div><span style={{ color: 'var(--text-3)' }}>Deposit</span><br /><strong>{fmtBDT(l.deposit)}</strong></div>}
          {l.size     != null  && <div><span style={{ color: 'var(--text-3)' }}>Size</span><br /><strong>{l.size} sqft</strong></div>}
          {l.floor_no != null  && <div><span style={{ color: 'var(--text-3)' }}>Floor</span><br /><strong>{l.floor_no}</strong></div>}
          {l.views    != null  && <div><span style={{ color: 'var(--text-3)' }}>Views</span><br /><strong>{l.views}</strong></div>}
          {l.available_from    && <div><span style={{ color: 'var(--text-3)' }}>Available from</span><br /><strong>{l.available_from}</strong></div>}
          <div><span style={{ color: 'var(--text-3)' }}>Status</span><br /><StatusPill status={l.status} /></div>
        </div>
      )}
    </div>
  );
}

// ── NestStay Hostels Tab ────────────────────────────────────────────────────

function HostelsTab({ onPostHostel }) {
  const [statusFilter, setStatusFilter] = useState('all');
  const [rows, setRows]                 = useState([]);
  const [meta, setMeta]                 = useState(null);
  const [page, setPage]                 = useState(1);
  const [loading, setLoading]           = useState(true);
  const [error, setError]               = useState(null);

  const load = useCallback((pg = 1) => {
    setLoading(true);
    setError(null);
    const filters = { page: pg };
    if (statusFilter !== 'all') filters.status = statusFilter;
    getOwnerHostels(filters)
      .then(res => { setRows(res.data || []); setMeta(res.meta || null); setLoading(false); })
      .catch(e  => { setError(e.message); setLoading(false); });
  }, [statusFilter]);

  useEffect(() => { setPage(1); load(1); }, [statusFilter]);
  useEffect(() => { load(page); }, [page]);

  const STATUS_TABS = ['all', 'draft', 'pending', 'active', 'rejected'];

  return (
    <div>
      <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 20 }}>
        {STATUS_TABS.map(s => (
          <button
            key={s}
            onClick={() => setStatusFilter(s)}
            style={{
              padding: '5px 14px',
              borderRadius: 99,
              border: '1.5px solid',
              fontSize: 12.5,
              fontWeight: 500,
              cursor: 'pointer',
              transition: 'all 120ms',
              borderColor: statusFilter === s ? 'var(--primary)' : 'var(--border)',
              background:  statusFilter === s ? 'var(--primary)' : '#fff',
              color:       statusFilter === s ? '#fff' : 'var(--text-2)',
            }}
          >
            {s === 'all' ? 'All' : STATUS_STYLE[s]?.label || s}
          </button>
        ))}
      </div>

      {error && (
        <div style={{ padding: 16, background: 'var(--error-bg)', borderRadius: 8, color: 'var(--error)', marginBottom: 16, fontSize: 13.5 }}>
          {error}
        </div>
      )}

      {loading ? (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {[1,2,3].map(i => (
            <div key={i} style={{ background: '#fff', borderRadius: 12, padding: 20, border: '1px solid var(--border-soft)', height: 90, opacity: 0.5 }} />
          ))}
        </div>
      ) : rows.length === 0 ? (
        <EmptyState
          icon={<Icons.Building2 size={26} style={{ color: 'var(--primary)' }} />}
          title="No hostels yet"
          subtitle={statusFilter !== 'all' ? 'No hostels with this status.' : 'Post your first hostel or mess.'}
          action={statusFilter === 'all' && (
            <Button onClick={onPostHostel} size="sm">
              <Icons.Plus size={14} /> Post a Hostel
            </Button>
          )}
        />
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {rows.map(h => <HostelCard2 key={h.id} hostel={h} />)}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 24 }}>
          <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(p => p - 1)}>
            Previous
          </Button>
          <span style={{ padding: '7px 12px', fontSize: 13, color: 'var(--text-2)' }}>
            {page} / {meta.last_page}
          </span>
          <Button variant="outline" size="sm" disabled={page >= meta.last_page} onClick={() => setPage(p => p + 1)}>
            Next
          </Button>
        </div>
      )}
    </div>
  );
}

function HostelCard2({ hostel: h }) {
  const [expanded, setExpanded] = useState(false);
  const typeSlug = h.type?.name || '';
  return (
    <div style={{
      background: '#fff',
      borderRadius: 12,
      border: '1px solid var(--border-soft)',
      padding: '16px 20px',
      boxShadow: '0 1px 4px rgba(0,0,0,0.04)',
    }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
        <div style={{
          width: 44, height: 44, borderRadius: 10, flexShrink: 0,
          background: 'var(--primary-soft)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          <Icons.Building2 size={20} style={{ color: 'var(--primary)' }} />
        </div>

        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginBottom: 4 }}>
            <span style={{ fontSize: 14.5, fontWeight: 600, color: 'var(--text-1)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
              {h.name || '(untitled)'}
            </span>
            {h.type?.label && (
              <span className={'pill ' + (TYPE_PILL_COLOR[typeSlug] || 'pill-teal')} style={{ fontSize: 10.5 }}>
                {h.type.label}
              </span>
            )}
            <StatusPill status={h.status} />
          </div>
          <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap', fontSize: 12.5, color: 'var(--text-3)' }}>
            {h.price > 0 && <span style={{ fontWeight: 600, color: 'var(--primary)' }}>{fmtBDT(h.price)}/{h.price_unit || 'seat/month'}</span>}
            {h.total_seats != null && <span>{h.total_seats} total seats</span>}
            {h.vacant_seats != null && <span style={{ color: 'var(--success)' }}>{h.vacant_seats} vacant</span>}
            {h.gender_policy && (
              <span>{h.gender_policy === 'female' ? '♀ Female only' : h.gender_policy === 'male' ? '♂ Male only' : '⚥ Mixed'}</span>
            )}
            {(h.address || h.district?.name) && (
              <span style={{ display: 'flex', alignItems: 'center', gap: 3 }}>
                <Icons.MapPin size={11} />
                {h.address || h.district?.name}
              </span>
            )}
          </div>
          {h.status === 'rejected' && h.rejection_reason && (
            <div style={{ marginTop: 8, padding: '6px 10px', background: '#FEF2F2', borderRadius: 6, fontSize: 12.5, color: '#991B1B', display: 'flex', gap: 6 }}>
              <Icons.AlertTriangle size={13} style={{ flexShrink: 0, marginTop: 1 }} />
              <span>{h.rejection_reason}</span>
            </div>
          )}
        </div>

        <button
          onClick={() => setExpanded(e => !e)}
          style={{ background: 'none', border: 'none', padding: 4, color: 'var(--text-3)', cursor: 'pointer' }}
        >
          <Icons.ChevronDown size={16} style={{ transform: expanded ? 'rotate(180deg)' : 'none', transition: 'transform 150ms' }} />
        </button>
      </div>

      {expanded && (
        <div style={{ marginTop: 14, paddingTop: 14, borderTop: '1px solid var(--border-soft)', display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))', gap: 10, fontSize: 12.5, color: 'var(--text-2)' }}>
          {h.advance   != null && <div><span style={{ color: 'var(--text-3)' }}>Advance</span><br /><strong>{fmtBDT(h.advance)}</strong></div>}
          {h.meal_included      && <div><span style={{ color: 'var(--text-3)' }}>Meal</span><br /><strong>{h.meal_price ? fmtBDT(h.meal_price) + '/mo' : 'Included'}</strong></div>}
          {h.curfew_time        && <div><span style={{ color: 'var(--text-3)' }}>Curfew</span><br /><strong>{h.curfew_time}</strong></div>}
          {h.is_verified != null && <div><span style={{ color: 'var(--text-3)' }}>Verified</span><br /><strong>{h.is_verified ? 'Yes' : 'No'}</strong></div>}
          <div><span style={{ color: 'var(--text-3)' }}>Status</span><br /><StatusPill status={h.status} /></div>
        </div>
      )}
    </div>
  );
}

// ── Main Page ───────────────────────────────────────────────────────────────

function OwnerDashboardPage({ user, onPostHostel, onPostListing }) {
  const [tab, setTab] = useState('listings');

  const TABS = [
    { id: 'listings', label: 'Flat & Mess Listings', icon: <Icons.Home size={15} /> },
    { id: 'hostels',  label: 'NestStay Hostels',     icon: <Icons.Building2 size={15} /> },
  ];

  return (
    <div style={{ maxWidth: 860, margin: '0 auto', padding: '32px 20px 60px' }}>
      {/* Header */}
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ margin: 0, fontSize: 22, fontWeight: 700, color: 'var(--text-1)' }}>
          My Listings
        </h1>
        <p style={{ margin: '4px 0 0', fontSize: 14, color: 'var(--text-3)' }}>
          Manage all your flat, mess, and hostel listings in one place.
        </p>
      </div>

      {/* Quick action buttons */}
      <div style={{ display: 'flex', gap: 10, marginBottom: 28, flexWrap: 'wrap' }}>
        <Button onClick={onPostHostel} size="sm">
          <Icons.Plus size={14} /> Post a Hostel / Mess (NestStay)
        </Button>
        <Button variant="outline" onClick={onPostListing} size="sm">
          <Icons.Plus size={14} /> Post a Flat / Mess Listing
        </Button>
      </div>

      {/* Tab switcher */}
      <div style={{
        display: 'flex',
        borderBottom: '2px solid var(--border-soft)',
        marginBottom: 24,
        gap: 0,
      }}>
        {TABS.map(t => (
          <button
            key={t.id}
            onClick={() => setTab(t.id)}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 7,
              padding: '10px 20px',
              border: 'none',
              background: 'none',
              fontSize: 14,
              fontWeight: tab === t.id ? 600 : 400,
              color: tab === t.id ? 'var(--primary)' : 'var(--text-2)',
              borderBottom: tab === t.id ? '2px solid var(--primary)' : '2px solid transparent',
              marginBottom: -2,
              cursor: 'pointer',
              transition: 'color 120ms',
            }}
          >
            {t.icon}
            {t.label}
          </button>
        ))}
      </div>

      {/* Tab content */}
      {tab === 'listings' && (
        <ListingsTab onPostListing={onPostListing} />
      )}
      {tab === 'hostels' && (
        <HostelsTab onPostHostel={onPostHostel} />
      )}
    </div>
  );
}

window.OwnerDashboardPage = OwnerDashboardPage;
