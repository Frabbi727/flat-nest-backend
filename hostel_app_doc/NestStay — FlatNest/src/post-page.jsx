// Page 3 — Post a Hostel (4-step wizard)
// Globals: Icons, AMENITIES, DIVISIONS, DISTRICTS, UPAZILAS, HOSTEL_TYPES,
// Button, Checkbox, Radio, Toggle, Stepper, fmtBDT

function StepIndicator({ step }) {
  const STEPS = [
    { n: 1, label: 'Hostel info' },
    { n: 2, label: 'Seat builder' },
    { n: 3, label: 'Location' },
    { n: 4, label: 'Amenities & photos' },
  ];
  return (
    <div className="step-ind">
      {STEPS.map((s, i) => {
        const state = step === s.n ? 'active' : (step > s.n ? 'done' : '');
        return (
          <React.Fragment key={s.n}>
            <div className={'step ' + state}>
              <span className="num">{step > s.n ? <Icons.Check size={14} strokeWidth={3} /> : s.n}</span>
              <span className="lbl">{s.label}</span>
            </div>
            {i < STEPS.length - 1 ? <span className="line"></span> : null}
          </React.Fragment>
        );
      })}
    </div>
  );
}

function Step1({ form, setForm }) {
  const set = (k, v) => setForm({ ...form, [k]: v });
  const TYPES = HOSTEL_TYPES.slice(1);
  return (
    <div className="col gap-4">
      <label className="field">
        <span className="lbl">Hostel name</span>
        <input className="input" placeholder="e.g. Asha Women's Hostel" value={form.name} onChange={(e) => set('name', e.target.value)} />
      </label>

      <label className="field">
        <span className="lbl">Hostel type</span>
        <div className="chip-grid">
          {TYPES.map(t => (
            <button key={t.id} className={'chip ' + (form.type === t.id ? 'active' : '')} onClick={() => set('type', t.id)}>
              {t.label}
            </button>
          ))}
        </div>
      </label>

      <div className="wiz-grid-2">
        <label className="field">
          <span className="lbl">Total seats</span>
          <input type="number" className="input" placeholder="20" value={form.totalSeats} onChange={(e) => set('totalSeats', e.target.value)} />
        </label>
        <div>
          <span className="lbl">Gender policy</span>
          <div className="row gap-4" style={{ paddingTop: 6 }}>
            {[
              { id: 'male', label: 'Male' },
              { id: 'female', label: 'Female' },
              { id: 'mixed', label: 'Mixed' },
            ].map(g => (
              <Radio key={g.id} name="gp" label={g.label} checked={form.gender === g.id} onChange={() => set('gender', g.id)} />
            ))}
          </div>
        </div>
      </div>

      <div>
        <span className="lbl">Pricing unit</span>
        <div className="segmented" style={{ marginTop: 4 }}>
          {[
            { id: 'seat-month', label: 'Per seat / month' },
            { id: 'room-month', label: 'Per room / month' },
            { id: 'day', label: 'Per day' },
          ].map(p => (
            <button key={p.id} className={form.priceUnit === p.id ? 'active' : ''} onClick={() => set('priceUnit', p.id)}>
              {p.label}
            </button>
          ))}
        </div>
      </div>

      <div className="wiz-grid-3">
        <label className="field">
          <span className="lbl">Rent per unit (BDT)</span>
          <input type="number" className="input" placeholder="4500" value={form.rent} onChange={(e) => set('rent', e.target.value)} />
        </label>
        <label className="field">
          <span className="lbl">Security deposit (BDT)</span>
          <input type="number" className="input" placeholder="2000" value={form.deposit} onChange={(e) => set('deposit', e.target.value)} />
        </label>
        <label className="field">
          <span className="lbl">Advance amount (BDT)</span>
          <input type="number" className="input" placeholder="2000" value={form.advance} onChange={(e) => set('advance', e.target.value)} />
        </label>
      </div>

      <div className="row gap-4" style={{ alignItems: 'flex-end' }}>
        <div>
          <div className="row" style={{ justifyContent: 'space-between' }}>
            <span className="lbl">Meal included?</span>
          </div>
          <Toggle checked={form.mealOn} onChange={(v) => set('mealOn', v)} label={form.mealOn ? 'Yes' : 'No'} />
        </div>
        {form.mealOn ? (
          <label className="field flex-1">
            <span className="lbl">Meal price per month (BDT)</span>
            <input type="number" className="input" placeholder="1500" value={form.mealPrice} onChange={(e) => set('mealPrice', e.target.value)} />
          </label>
        ) : null}
        <label className="field flex-1">
          <span className="lbl">Curfew time (optional)</span>
          <input type="time" className="input" value={form.curfew} onChange={(e) => set('curfew', e.target.value)} />
        </label>
      </div>

      <label className="field">
        <span className="lbl">Description</span>
        <textarea className="textarea" placeholder="Briefly describe the hostel, neighborhood, food, vibe…" value={form.desc} onChange={(e) => set('desc', e.target.value)} />
      </label>
    </div>
  );
}

function Step2({ form, setForm }) {
  const rooms = form.rooms;
  const totalSeats = rooms.reduce((s, r) => s + r.seats.length, 0);
  const vacant = rooms.reduce((s, r) => s + r.seats.filter(x => x.status === 'vacant').length, 0);

  const updateRoom = (idx, room) => {
    const next = rooms.slice(); next[idx] = room;
    setForm({ ...form, rooms: next });
  };
  const removeRoom = (idx) => {
    setForm({ ...form, rooms: rooms.filter((_, i) => i !== idx) });
  };
  const addRoom = () => {
    setForm({
      ...form,
      rooms: [...rooms, {
        name: 'Room ' + (rooms.length + 1),
        seats: Array.from({ length: 4 }).map((_, i) => ({
          id: 'R' + (rooms.length + 1) + '-' + String.fromCharCode(65 + i),
          status: 'vacant',
        })),
      }],
    });
  };
  const setSeatCount = (rIdx, n) => {
    n = Math.max(1, Math.min(10, n));
    const room = rooms[rIdx];
    const current = room.seats;
    let next;
    if (n > current.length) {
      next = current.concat(Array.from({ length: n - current.length }).map((_, i) => ({
        id: (room.name.replace(/[^A-Za-z0-9]/g, '') || 'R') + '-' + String.fromCharCode(65 + current.length + i),
        status: 'vacant',
      })));
    } else {
      next = current.slice(0, n);
    }
    updateRoom(rIdx, { ...room, seats: next });
  };

  return (
    <div>
      <div className="row" style={{ justifyContent: 'space-between', marginBottom: 16 }}>
        <div>
          <div style={{ fontWeight: 600, fontSize: 15 }}>Build out your seat layout</div>
          <div className="tiny muted">Add rooms, set seat counts. Tap a seat to toggle Vacant/Taken.</div>
        </div>
        <Button variant="primary" size="sm" onClick={addRoom}><Icons.Plus size={14} /> Add room</Button>
      </div>

      {rooms.map((room, idx) => (
        <div className="room-block" key={idx}>
          <div className="row gap-3" style={{ alignItems: 'flex-end' }}>
            <label className="field flex-1">
              <span className="lbl">Room name / number</span>
              <input className="input" value={room.name} onChange={(e) => updateRoom(idx, { ...room, name: e.target.value })} />
            </label>
            <div>
              <span className="lbl">Seats</span>
              <Stepper value={room.seats.length} onChange={(n) => setSeatCount(idx, n)} min={1} max={10} />
            </div>
            <Button variant="ghost" size="sm" onClick={() => removeRoom(idx)} aria-label="remove">
              <Icons.X size={14} /> Remove
            </Button>
          </div>

          <div className="seats-mini">
            {room.seats.map((seat, si) => (
              <div
                key={si}
                className={'s ' + (seat.status === 'taken' ? 'taken' : '')}
                onClick={() => {
                  const next = { ...room, seats: room.seats.slice() };
                  next.seats[si] = { ...seat, status: seat.status === 'vacant' ? 'taken' : 'vacant' };
                  updateRoom(idx, next);
                }}
                title="Click to toggle"
              >
                <input
                  style={{ width: 42, border: 'none', background: 'transparent', fontSize: 11, fontWeight: 500, textAlign: 'center', padding: 0, outline: 'none' }}
                  value={seat.id}
                  onChange={(e) => {
                    const next = { ...room, seats: room.seats.slice() };
                    next.seats[si] = { ...seat, id: e.target.value };
                    updateRoom(idx, next);
                  }}
                  onClick={(e) => e.stopPropagation()}
                />
              </div>
            ))}
          </div>
          <div className="tiny muted" style={{ marginTop: 8 }}>
            Click a seat box to toggle Vacant ↔ Taken
          </div>
        </div>
      ))}

      <div className="note" style={{ marginTop: 8 }}>
        <Icons.Info size={14} />
        Total: <strong>{totalSeats} seats</strong> · <strong>{vacant} vacant</strong>
      </div>
    </div>
  );
}

function Step3({ form, setForm }) {
  const set = (k, v) => setForm({ ...form, [k]: v });
  return (
    <div className="col gap-4">
      <div className="wiz-grid-2">
        <label className="field">
          <span className="lbl">Division</span>
          <select className="select" value={form.division} onChange={(e) => { set('division', e.target.value); set('district', ''); set('upazila', ''); }}>
            <option value="">Select…</option>
            {DIVISIONS.map(d => <option key={d} value={d}>{d}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">District</span>
          <select className="select" value={form.district} onChange={(e) => { set('district', e.target.value); set('upazila', ''); }} disabled={!form.division}>
            <option value="">Select…</option>
            {(DISTRICTS[form.division] || []).map(d => <option key={d} value={d}>{d}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">Upazila / Thana</span>
          <select className="select" value={form.upazila} onChange={(e) => set('upazila', e.target.value)} disabled={!form.district}>
            <option value="">Select…</option>
            {(UPAZILAS[form.division] || []).map(d => <option key={d} value={d}>{d}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">Union (optional)</span>
          <input className="input" placeholder="e.g. Ward 14" value={form.union} onChange={(e) => set('union', e.target.value)} />
        </label>
      </div>

      <div className="wiz-grid-2">
        <label className="field">
          <span className="lbl">Road / House name</span>
          <input className="input" placeholder="e.g. Road 7, House 12" value={form.road} onChange={(e) => set('road', e.target.value)} />
        </label>
        <label className="field">
          <span className="lbl">Block / Section</span>
          <input className="input" placeholder="e.g. Section 11, Block A" value={form.block} onChange={(e) => set('block', e.target.value)} />
        </label>
      </div>

      <label className="field">
        <span className="lbl">Nearby landmark</span>
        <input className="input" placeholder="e.g. 500m from BUET main gate" value={form.landmark} onChange={(e) => set('landmark', e.target.value)} />
      </label>

      <div>
        <span className="lbl">Pin on map</span>
        <div style={{
          background: '#EEF1F5',
          backgroundImage: 'repeating-linear-gradient(0deg, transparent, transparent 23px, rgba(0,0,0,0.04) 24px), repeating-linear-gradient(90deg, transparent, transparent 23px, rgba(0,0,0,0.04) 24px)',
          borderRadius: 'var(--r-card)',
          height: 200,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          flexDirection: 'column', gap: 6, color: 'var(--text-2)', cursor: 'pointer',
          border: '1px dashed var(--border)',
        }}>
          <Icons.MapPin size={32} style={{ color: 'var(--accent)' }} />
          <span className="tiny">Tap to pick on map</span>
        </div>
      </div>
    </div>
  );
}

function Step4({ form, setForm }) {
  const set = (k, v) => setForm({ ...form, [k]: v });
  const toggleAm = (id) => {
    set('amenities', { ...form.amenities, [id]: !form.amenities[id] });
  };
  const updateRule = (i, v) => {
    const next = form.rules.slice(); next[i] = v; set('rules', next);
  };
  const addRule = () => set('rules', [...form.rules, '']);
  const removeRule = (i) => set('rules', form.rules.filter((_, x) => x !== i));

  return (
    <div className="col gap-4">
      <div>
        <span className="lbl">Amenities</span>
        <div className="chip-grid" style={{ marginTop: 6 }}>
          {AMENITIES.map(a => {
            const Ico = Icons[a.icon];
            return (
              <button key={a.id} className={'chip ' + (form.amenities[a.id] ? 'active' : '')} onClick={() => toggleAm(a.id)}>
                {Ico ? <Ico size={12} /> : null}
                {a.label}
              </button>
            );
          })}
        </div>
      </div>

      <div>
        <div className="row" style={{ justifyContent: 'space-between', marginBottom: 6 }}>
          <span className="lbl">Hostel rules</span>
          <Button variant="ghost" size="sm" onClick={addRule}><Icons.Plus size={12} /> Add rule</Button>
        </div>
        <div className="col gap-2">
          {form.rules.map((r, i) => (
            <div className="row gap-2" key={i}>
              <span className="muted tiny" style={{ width: 22, textAlign: 'right' }}>{i + 1}.</span>
              <input className="input flex-1" value={r} onChange={(e) => updateRule(i, e.target.value)} />
              <Button variant="ghost" size="sm" onClick={() => removeRule(i)}><Icons.X size={14} /></Button>
            </div>
          ))}
        </div>
        <div className="tiny muted" style={{ marginTop: 6 }}>
          Suggestions: curfew time, visitor policy, cooking, notice period
        </div>
      </div>

      <div>
        <span className="lbl">Photos (upload at least 3)</span>
        <div className="wiz-grid-3" style={{ marginTop: 6 }}>
          {['Common Area', 'Room Photo', 'Exterior'].map(p => (
            <div className="photo-zone" key={p}>
              <Icons.Upload size={22} />
              <div className="ph-title">{p}</div>
              <div className="tiny" style={{ marginTop: 2 }}>PNG, JPG up to 5MB</div>
            </div>
          ))}
        </div>
      </div>

      <div className="note">
        <Icons.Info size={14} />
        Your listing will be reviewed within 24 hours. You'll receive a confirmation SMS when approved.
      </div>
    </div>
  );
}

function PostPage({ onDone }) {
  const [step, setStep] = useState(1);
  const [submitted, setSubmitted] = useState(false);
  const [form, setForm] = useState({
    name: '', type: 'student', totalSeats: '18', gender: 'female',
    priceUnit: 'seat-month', rent: '4500', deposit: '2000', advance: '2000',
    mealOn: true, mealPrice: '1500', curfew: '22:00', desc: '',
    rooms: [
      { name: 'Room 1', seats: [
        { id: 'R1-A', status: 'vacant' }, { id: 'R1-B', status: 'vacant' },
        { id: 'R1-C', status: 'taken' }, { id: 'R1-D', status: 'vacant' },
      ]},
      { name: 'Room 2', seats: [
        { id: 'R2-A', status: 'vacant' }, { id: 'R2-B', status: 'taken' },
        { id: 'R2-C', status: 'vacant' }, { id: 'R2-D', status: 'taken' },
      ]},
    ],
    division: 'Dhaka', district: 'Dhaka', upazila: 'Mirpur', union: '',
    road: '', block: '', landmark: '',
    amenities: { wifi: true, meal: true, cctv: true, study: true, bath: true, guard: true },
    rules: [
      'Curfew: 10:00 PM every night',
      'No male visitors above floor 1',
      'No cooking in rooms',
      'Advance: 1 month seat rent',
    ],
  });

  if (submitted) {
    return (
      <div className="page">
        <div className="card" style={{ maxWidth: 540, margin: '60px auto', padding: 40, textAlign: 'center' }}>
          <div style={{
            width: 72, height: 72, borderRadius: 99, margin: '0 auto 16px',
            background: 'var(--success-bg)', color: 'var(--success)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}>
            <Icons.Check size={36} strokeWidth={3} />
          </div>
          <h2 style={{ margin: '0 0 6px' }}>Listing submitted!</h2>
          <p className="muted" style={{ margin: '0 0 22px' }}>
            We'll review <strong>{form.name || 'your hostel'}</strong> within 24 hours and send you a confirmation SMS.
          </p>
          <div className="row gap-2" style={{ justifyContent: 'center' }}>
            <Button variant="primary" onClick={onDone}>Go to listings</Button>
            <Button variant="outline" onClick={() => { setSubmitted(false); setStep(1); }}>Post another</Button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="page">
      <div className="wizard">
        <div style={{ marginBottom: 18 }}>
          <div style={{ fontSize: 22, fontWeight: 700 }}>Post a hostel</div>
          <div className="muted tiny">Reach thousands of renters across Bangladesh. Free to list.</div>
        </div>

        <StepIndicator step={step} />

        <div className="card wiz-card">
          {step === 1 && <Step1 form={form} setForm={setForm} />}
          {step === 2 && <Step2 form={form} setForm={setForm} />}
          {step === 3 && <Step3 form={form} setForm={setForm} />}
          {step === 4 && <Step4 form={form} setForm={setForm} />}

          <div className="wiz-actions">
            <Button variant="ghost" onClick={() => setStep(Math.max(1, step - 1))} disabled={step === 1}>
              <Icons.ChevronLeft size={14} /> Back
            </Button>
            {step < 4 ? (
              <Button variant="primary" onClick={() => setStep(step + 1)}>
                Next <Icons.ChevronRight size={14} />
              </Button>
            ) : (
              <Button variant="primary" onClick={() => setSubmitted(true)}>
                Submit for Review <Icons.Check size={14} />
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

window.PostPage = PostPage;
