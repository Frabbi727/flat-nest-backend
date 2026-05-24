// Page 3 — Post a Hostel (4-step wizard, API-driven)
const { useState: useStateP, useEffect: useEffectP, useRef: useRefP } = React;

function StepIndicator({ step }) {
  const STEPS = [
    { n: 1, label: 'Hostel info' },
    { n: 2, label: 'Seat builder' },
    { n: 3, label: 'Location' },
    { n: 4, label: 'Amenities & Rules' },
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

function Step1({ form, setForm, hostelTypes }) {
  const set = (k, v) => setForm({ ...form, [k]: v });
  return (
    <div className="col gap-4">
      <label className="field">
        <span className="lbl">Hostel name</span>
        <input className="input" placeholder="e.g. Asha Women's Hostel" value={form.name} onChange={(e) => set('name', e.target.value)} />
      </label>

      <label className="field">
        <span className="lbl">Hostel type</span>
        <div className="chip-grid">
          {hostelTypes.map(t => (
            <button key={t.id} className={'chip ' + (form.hostelTypeId == t.id ? 'active' : '')} onClick={() => set('hostelTypeId', t.id)}>
              {t.label}
            </button>
          ))}
        </div>
      </label>

      <div>
        <span className="lbl">Gender policy</span>
        <div className="row gap-4" style={{ paddingTop: 6 }}>
          {[{ id: 'male', label: 'Male' }, { id: 'female', label: 'Female' }, { id: 'mixed', label: 'Mixed' }].map(g => (
            <Radio key={g.id} name="gp" label={g.label} checked={form.gender === g.id} onChange={() => set('gender', g.id)} />
          ))}
        </div>
      </div>

      <div>
        <span className="lbl">Pricing unit</span>
        <div className="segmented" style={{ marginTop: 4 }}>
          {[{ id: 'seat/month', label: 'Per seat / month' }, { id: 'day', label: 'Per day' }].map(p => (
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
          <span className="lbl">Advance amount (BDT)</span>
          <input type="number" className="input" placeholder="2000" value={form.advance} onChange={(e) => set('advance', e.target.value)} />
        </label>
        <label className="field">
          <span className="lbl">Curfew time (optional)</span>
          <input type="time" className="input" value={form.curfew} onChange={(e) => set('curfew', e.target.value)} />
        </label>
      </div>

      <div className="row gap-4" style={{ alignItems: 'flex-end' }}>
        <div>
          <span className="lbl">Meal included?</span>
          <Toggle checked={form.mealOn} onChange={(v) => set('mealOn', v)} label={form.mealOn ? 'Yes' : 'No'} />
        </div>
        {form.mealOn ? (
          <label className="field flex-1">
            <span className="lbl">Meal price per month (BDT)</span>
            <input type="number" className="input" placeholder="1500" value={form.mealPrice} onChange={(e) => set('mealPrice', e.target.value)} />
          </label>
        ) : null}
      </div>

      <label className="field">
        <span className="lbl">Description</span>
        <textarea className="textarea" placeholder="Briefly describe the hostel, neighborhood, food, vibe…" value={form.desc} onChange={(e) => set('desc', e.target.value)} />
      </label>

      <div className="wiz-grid-2">
        <label className="field">
          <span className="lbl">Floor</span>
          <input className="input" placeholder="e.g. 3rd floor" value={form.floor} onChange={(e) => set('floor', e.target.value)} />
        </label>
        <label className="field">
          <span className="lbl">Building type</span>
          <input className="input" placeholder="e.g. Apartment" value={form.building} onChange={(e) => set('building', e.target.value)} />
        </label>
      </div>

      <label className="field">
        <span className="lbl">Your name (as owner)</span>
        <input className="input" placeholder="e.g. Asha Begum" value={form.ownerName} onChange={(e) => set('ownerName', e.target.value)} />
      </label>
      <label className="field">
        <span className="lbl">Your phone number</span>
        <input className="input" placeholder="01XXXXXXXXX" value={form.ownerPhone} onChange={(e) => set('ownerPhone', e.target.value)} />
      </label>
    </div>
  );
}

function Step2({ form, setForm }) {
  const rooms = form.rooms;

  const updateRoom = (idx, room) => {
    const next = rooms.slice(); next[idx] = room;
    setForm({ ...form, rooms: next });
  };
  const removeRoom = (idx) => setForm({ ...form, rooms: rooms.filter((_, i) => i !== idx) });
  const addRoom = () => setForm({
    ...form,
    rooms: [...rooms, {
      name: 'Room ' + (rooms.length + 1),
      seats: Array.from({ length: 4 }).map((_, i) => ({ seatNumber: String.fromCharCode(65 + i), status: 'vacant' })),
    }],
  });
  const setSeatCount = (rIdx, n) => {
    n = Math.max(1, Math.min(10, n));
    const room = rooms[rIdx];
    let next;
    if (n > room.seats.length) {
      const extra = Array.from({ length: n - room.seats.length }).map((_, i) => ({
        seatNumber: String.fromCharCode(65 + room.seats.length + i), status: 'vacant',
      }));
      next = room.seats.concat(extra);
    } else {
      next = room.seats.slice(0, n);
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
                  value={seat.seatNumber}
                  onChange={(e) => {
                    const next = { ...room, seats: room.seats.slice() };
                    next.seats[si] = { ...seat, seatNumber: e.target.value };
                    updateRoom(idx, next);
                  }}
                  onClick={(e) => e.stopPropagation()}
                />
              </div>
            ))}
          </div>
        </div>
      ))}

      <div className="note" style={{ marginTop: 8 }}>
        <Icons.Info size={14} />
        Total: <strong>{rooms.reduce((s, r) => s + r.seats.length, 0)} seats</strong> ·{' '}
        <strong>{rooms.reduce((s, r) => s + r.seats.filter(x => x.status === 'vacant').length, 0)} vacant</strong>
      </div>
    </div>
  );
}

function Step3({ form, setForm, divisions }) {
  const [districts, setDistricts] = useStateP([]);
  const [upazilas, setUpazilas]   = useStateP([]);
  const [unions, setUnions]       = useStateP([]);
  const [locating, setLocating]   = useStateP(false);
  const mapRef        = useRefP(null);
  const leafletMapRef = useRefP(null);
  const markerRef     = useRefP(null);

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  // Division → District
  useEffectP(() => {
    setDistricts([]); setUpazilas([]); setUnions([]);
    setForm(f => ({ ...f, districtId: '', upazilaId: '', unionId: '' }));
    if (form.divisionId) getDistricts(form.divisionId).then(setDistricts).catch(() => {});
  }, [form.divisionId]);

  // District → Upazila
  useEffectP(() => {
    setUpazilas([]); setUnions([]);
    setForm(f => ({ ...f, upazilaId: '', unionId: '' }));
    if (form.districtId) getUpazilas(form.districtId).then(setUpazilas).catch(() => {});
  }, [form.districtId]);

  // Upazila → Union
  useEffectP(() => {
    setUnions([]);
    setForm(f => ({ ...f, unionId: '' }));
    if (form.upazilaId) getUnions(form.upazilaId).then(setUnions).catch(() => {});
  }, [form.upazilaId]);

  // Init Leaflet map once
  useEffectP(() => {
    if (!mapRef.current || !window.L || leafletMapRef.current) return;

    const map = L.map(mapRef.current).setView([23.6850, 90.3563], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(map);

    map.on('click', (e) => {
      const lat = e.latlng.lat;
      const lng = e.latlng.lng;
      setForm(f => ({ ...f, coordY: lat, coordX: lng }));
      if (markerRef.current) {
        markerRef.current.setLatLng([lat, lng]);
      } else {
        markerRef.current = L.marker([lat, lng]).addTo(map);
      }
    });

    leafletMapRef.current = map;
    return () => {
      map.remove();
      leafletMapRef.current = null;
      markerRef.current     = null;
    };
  }, []);

  // Fly to pin when coords set externally (GPS)
  useEffectP(() => {
    if (!leafletMapRef.current || !form.coordY || !form.coordX) return;
    const lat = form.coordY, lng = form.coordX;
    if (markerRef.current) {
      markerRef.current.setLatLng([lat, lng]);
    } else {
      markerRef.current = L.marker([lat, lng]).addTo(leafletMapRef.current);
    }
    leafletMapRef.current.setView([lat, lng], 16);
  }, [form.coordY, form.coordX]);

  const useGPS = () => {
    if (!navigator.geolocation) return alert('Geolocation is not supported by your browser.');
    setLocating(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        setForm(f => ({ ...f, coordY: pos.coords.latitude, coordX: pos.coords.longitude }));
        setLocating(false);
      },
      () => { alert('Could not get your location. Please allow location access.'); setLocating(false); }
    );
  };

  return (
    <div className="col gap-4">
      {/* 4-level geo cascade */}
      <div className="wiz-grid-2">
        <label className="field">
          <span className="lbl">Division</span>
          <select className="select" value={form.divisionId} onChange={(e) => set('divisionId', e.target.value)}>
            <option value="">Select division…</option>
            {divisions.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">District</span>
          <select className="select" value={form.districtId} onChange={(e) => set('districtId', e.target.value)} disabled={!form.divisionId}>
            <option value="">Select district…</option>
            {districts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">Upazila / Thana</span>
          <select className="select" value={form.upazilaId} onChange={(e) => set('upazilaId', e.target.value)} disabled={!form.districtId}>
            <option value="">Select upazila…</option>
            {upazilas.map(u => <option key={u.id} value={u.id}>{u.name}</option>)}
          </select>
        </label>
        <label className="field">
          <span className="lbl">Union</span>
          <select className="select" value={form.unionId} onChange={(e) => set('unionId', e.target.value)} disabled={!form.upazilaId}>
            <option value="">Select union…</option>
            {unions.map(u => <option key={u.id} value={u.id}>{u.name}</option>)}
          </select>
        </label>
      </div>

      <label className="field">
        <span className="lbl">Street address</span>
        <input className="input" placeholder="e.g. Road 7, House 12, Block A" value={form.address} onChange={(e) => set('address', e.target.value)} />
      </label>
      <label className="field">
        <span className="lbl">Nearby landmark</span>
        <input className="input" placeholder="e.g. 500m from BUET main gate" value={form.landmark} onChange={(e) => set('landmark', e.target.value)} />
      </label>

      {/* Map pin */}
      <div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
          <span className="lbl">Pin exact location on map</span>
          <Button variant="outline" size="sm" onClick={useGPS} disabled={locating}>
            {locating ? 'Locating…' : <><Icons.MapPin size={13} /> Use my location</>}
          </Button>
        </div>
        <div
          ref={mapRef}
          style={{ height: 300, borderRadius: 10, border: '1px solid var(--border)', overflow: 'hidden', zIndex: 0 }}
        />
        {form.coordY && form.coordX ? (
          <div className="tiny muted" style={{ marginTop: 6, display: 'flex', alignItems: 'center', gap: 4 }}>
            <Icons.MapPin size={11} />
            Pinned: {Number(form.coordY).toFixed(6)}, {Number(form.coordX).toFixed(6)}
            <button
              onClick={() => setForm(f => ({ ...f, coordY: null, coordX: null }))}
              style={{ marginLeft: 6, background: 'none', border: 'none', cursor: 'pointer', color: 'var(--error)', fontSize: 11 }}
            >
              Clear
            </button>
          </div>
        ) : (
          <div className="tiny muted" style={{ marginTop: 6 }}>Click anywhere on the map to pin the location.</div>
        )}
      </div>
    </div>
  );
}

function Step4({ form, setForm, amenities }) {
  const set = (k, v) => setForm({ ...form, [k]: v });
  const toggleAm = (id) => set('amenities', { ...form.amenities, [id]: !form.amenities[id] });
  const updateRule = (i, v) => { const next = form.rules.slice(); next[i] = v; set('rules', next); };
  const addRule    = () => set('rules', [...form.rules, '']);
  const removeRule = (i) => set('rules', form.rules.filter((_, x) => x !== i));

  return (
    <div className="col gap-4">
      <div>
        <span className="lbl">Amenities</span>
        <div className="chip-grid" style={{ marginTop: 6 }}>
          {amenities.map(a => (
            <button key={a.id} className={'chip ' + (form.amenities[a.id] ? 'active' : '')} onClick={() => toggleAm(a.id)}>
              {a.label}
            </button>
          ))}
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
      </div>

      <div className="note">
        <Icons.Info size={14} />
        Your listing will be reviewed within 24 hours. You'll receive a confirmation once approved.
      </div>
    </div>
  );
}

function PostPage({ onDone, user }) {
  const [step, setStep]         = useStateP(1);
  const [submitted, setSubmitted] = useStateP(false);
  const [submitting, setSubmitting] = useStateP(false);
  const [error, setError]       = useStateP('');
  const [hostelTypes, setHostelTypes] = useStateP([]);
  const [divisions, setDivisions] = useStateP([]);
  const [amenities, setAmenities] = useStateP([]);
  const [form, setForm]         = useStateP({
    name: '', hostelTypeId: '', gender: 'mixed',
    priceUnit: 'seat/month', rent: '', advance: '',
    mealOn: false, mealPrice: '', curfew: '',
    desc: '', floor: '', building: '',
    ownerName: user?.name || '', ownerPhone: user?.phone || '',
    rooms: [
      { name: 'Room 1', seats: [
        { seatNumber: 'A', status: 'vacant' }, { seatNumber: 'B', status: 'vacant' },
        { seatNumber: 'C', status: 'vacant' }, { seatNumber: 'D', status: 'vacant' },
      ]},
    ],
    divisionId: '', districtId: '', upazilaId: '', unionId: '', address: '', landmark: '',
    coordX: null, coordY: null,
    amenities: {},
    rules: [],
  });

  useEffectP(() => {
    getHostelTypes().then(ts => { setHostelTypes(ts); if (ts[0]) setForm(f => ({ ...f, hostelTypeId: ts[0].id })); }).catch(() => {});
    getDivisions().then(setDivisions).catch(() => {});
    getAmenities().then(setAmenities).catch(() => {});
  }, []);

  const handleSubmit = async () => {
    setSubmitting(true);
    setError('');
    try {
      // 1. Create hostel draft
      const amenityIds = amenities.filter(a => form.amenities[a.id]).map(a => a.id);
      const hostel = await createHostel({
        hostel_type_id: form.hostelTypeId,
        name:           form.name,
        description:    form.desc || null,
        gender_policy:  form.gender,
        price:          parseInt(form.rent) || 0,
        price_unit:     form.priceUnit,
        advance:        form.advance ? parseInt(form.advance) : null,
        meal_included:  form.mealOn,
        meal_price:     form.mealOn && form.mealPrice ? parseInt(form.mealPrice) : null,
        curfew_time:    form.curfew || null,
        floor:          form.floor || null,
        building:       form.building || null,
        owner_name:     form.ownerName || null,
        owner_phone:    form.ownerPhone || null,
        rules:          form.rules.filter(r => r.trim()),
        amenities:      amenityIds,
      });

      // 2. Add rooms
      for (const room of form.rooms) {
        await addRoom(hostel.id, {
          name:  room.name,
          seats: room.seats.map(s => ({ seat_number: s.seatNumber, status: s.status })),
        });
      }

      // 3. Location
      if (form.divisionId || form.coordY) {
        await updateHostelLocation(hostel.id, {
          address:     form.address || null,
          landmark:    form.landmark || null,
          division_id: form.divisionId || null,
          district_id: form.districtId || null,
          upazila_id:  form.upazilaId  || null,
          union_id:    form.unionId    || null,
          coord_x:     form.coordX     || null,
          coord_y:     form.coordY     || null,
        });
      }

      // 4. Submit for review
      await submitHostel(hostel.id);

      setSubmitted(true);
    } catch (err) {
      setError(err.message || 'Submission failed. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

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
            We'll review <strong>{form.name || 'your hostel'}</strong> within 24 hours.
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

        {error && (
          <div className="note" style={{ background: 'var(--danger-bg)', color: 'var(--danger)', marginBottom: 12 }}>
            <Icons.Info size={14} /> {error}
          </div>
        )}

        <div className="card wiz-card">
          {step === 1 && <Step1 form={form} setForm={setForm} hostelTypes={hostelTypes} />}
          {step === 2 && <Step2 form={form} setForm={setForm} />}
          {step === 3 && <Step3 form={form} setForm={setForm} divisions={divisions} />}
          {step === 4 && <Step4 form={form} setForm={setForm} amenities={amenities} />}

          <div className="wiz-actions">
            <Button variant="ghost" onClick={() => setStep(Math.max(1, step - 1))} disabled={step === 1 || submitting}>
              <Icons.ChevronLeft size={14} /> Back
            </Button>
            {step < 4 ? (
              <Button variant="primary" onClick={() => setStep(step + 1)}>
                Next <Icons.ChevronRight size={14} />
              </Button>
            ) : (
              <Button variant="primary" onClick={handleSubmit} disabled={submitting}>
                {submitting ? 'Submitting…' : 'Submit for Review'} <Icons.Check size={14} />
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

window.PostPage = PostPage;
