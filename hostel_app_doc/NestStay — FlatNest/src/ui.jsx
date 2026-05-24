// Shared small UI primitives. Uses globals: Icons.
const { useState, useEffect, useRef, useMemo, useCallback } = React;

function Button({ variant = 'primary', size, block, icon, iconRight, children, className = '', ...props }) {
  const cls = [
    'btn',
    'btn-' + variant,
    size === 'sm' ? 'btn-sm' : size === 'lg' ? 'btn-lg' : '',
    block ? 'btn-block' : '',
    className,
  ].filter(Boolean).join(' ');
  return (
    <button className={cls} {...props}>
      {icon}
      {children}
      {iconRight}
    </button>
  );
}

function Checkbox({ checked, onChange, label }) {
  return (
    <label className="check">
      <input type="checkbox" checked={checked} onChange={(e) => onChange?.(e.target.checked)} />
      <span className="check-box">
        <Icons.Check size={12} strokeWidth={3} />
      </span>
      <span>{label}</span>
    </label>
  );
}

function Radio({ checked, onChange, label, name }) {
  return (
    <label className="check">
      <input type="radio" name={name} checked={checked} onChange={() => onChange?.()} />
      <span className="radio-box"></span>
      <span>{label}</span>
    </label>
  );
}

function Toggle({ checked, onChange, label }) {
  return (
    <label className="toggle">
      <input type="checkbox" checked={checked} onChange={(e) => onChange?.(e.target.checked)} />
      <span className="toggle-track"></span>
      {label ? <span>{label}</span> : null}
    </label>
  );
}

function Stepper({ value, onChange, min = 0, max = 99 }) {
  return (
    <div className="stepper">
      <button onClick={() => onChange?.(Math.max(min, value - 1))} aria-label="decrease"><Icons.Minus size={14} /></button>
      <span className="val">{value}</span>
      <button onClick={() => onChange?.(Math.min(max, value + 1))} aria-label="increase"><Icons.Plus size={14} /></button>
    </div>
  );
}

// Dual-handle range slider
function RangeSlider({ min = 0, max = 100, step = 1, value, onChange }) {
  const [lo, hi] = value;
  const pctLo = ((lo - min) / (max - min)) * 100;
  const pctHi = ((hi - min) / (max - min)) * 100;
  return (
    <div>
      <div className="range-wrap">
        <div className="range-track"></div>
        <div className="range-fill" style={{ left: pctLo + '%', right: (100 - pctHi) + '%' }}></div>
        <input
          type="range" min={min} max={max} step={step} value={lo}
          onChange={(e) => onChange?.([Math.min(+e.target.value, hi - step), hi])}
        />
        <input
          type="range" min={min} max={max} step={step} value={hi}
          onChange={(e) => onChange?.([lo, Math.max(+e.target.value, lo + step)])}
        />
      </div>
      <div className="range-vals">
        <span>{fmtBDT(lo)}</span>
        <span>{fmtBDT(hi)}</span>
      </div>
    </div>
  );
}

function StarRow({ value, size = 14, showVal = true, reviews }) {
  return (
    <span className="rating">
      <Icons.StarFill size={size} style={{ color: '#FFB400' }} />
      {showVal ? <span>{value.toFixed(1)}</span> : null}
      {reviews != null ? <span className="rev">({reviews})</span> : null}
    </span>
  );
}

function PhotoPH({ grad, label, icon, height, style }) {
  return (
    <div style={{
      width: '100%', height: height || '100%',
      background: grad, position: 'relative', overflow: 'hidden',
      ...style,
    }}>
      <div style={{
        position: 'absolute', inset: 0,
        background: 'radial-gradient(circle at 80% 20%, rgba(255,255,255,0.18), transparent 60%)',
      }}/>
      {label ? (
        <div style={{
          position: 'absolute', left: 14, bottom: 12,
          color: 'rgba(255,255,255,0.92)', fontSize: 12, fontWeight: 500,
        }}>{label}</div>
      ) : null}
      {icon ? (
        <div style={{
          position: 'absolute', right: 14, bottom: 12, color: 'rgba(255,255,255,0.55)',
        }}>{icon}</div>
      ) : null}
    </div>
  );
}

Object.assign(window, { Button, Checkbox, Radio, Toggle, Stepper, RangeSlider, StarRow, PhotoPH });
