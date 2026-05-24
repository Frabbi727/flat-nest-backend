// Centralized API client for NestStay web UI
const BASE = '/api/v1';

const fmtBDT = (n) => '৳' + Number(n).toLocaleString('en-BD');
Object.assign(window, { fmtBDT });

const TYPE_PILL_COLOR = {
  student: 'pill-purple',
  women: 'pill-pink',
  mess: 'pill-blue',
  corporate: 'pill-teal',
  worker: 'pill-amber',
  medical: 'pill-coral',
};
window.TYPE_PILL_COLOR = TYPE_PILL_COLOR;

const PHOTO_GRADS = [
  'linear-gradient(135deg, #1A6B72 0%, #34A6A6 100%)',
  'linear-gradient(135deg, #FF8E8E 0%, #FF6B6B 60%, #C94F65 100%)',
  'linear-gradient(135deg, #6B47C0 0%, #9377DC 100%)',
  'linear-gradient(135deg, #1D6FD1 0%, #5BA0E8 100%)',
  'linear-gradient(135deg, #A65800 0%, #FF9500 100%)',
  'linear-gradient(135deg, #1E8C3F 0%, #34C759 100%)',
  'linear-gradient(135deg, #0E484D 0%, #1A6B72 50%, #FF6B6B 130%)',
  'linear-gradient(135deg, #B53D7C 0%, #FF8E8E 100%)',
];
window.PHOTO_GRADS = PHOTO_GRADS;

function getToken() {
  return localStorage.getItem('ns_token');
}

function getUser() {
  try { return JSON.parse(localStorage.getItem('ns_user')); } catch { return null; }
}

function setAuth(token, user) {
  localStorage.setItem('ns_token', token);
  localStorage.setItem('ns_user', JSON.stringify(user));
}

function clearAuth() {
  localStorage.removeItem('ns_token');
  localStorage.removeItem('ns_user');
}

async function apiFetch(path, opts = {}) {
  const token = getToken();
  const res = await fetch(BASE + path, {
    ...opts,
    headers: {
      'Accept': 'application/json',
      ...(opts.body && !(opts.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
      ...(token ? { Authorization: 'Bearer ' + token } : {}),
      ...(opts.headers || {}),
    },
  });
  const json = await res.json();
  if (!res.ok) throw new Error(json.message || 'Request failed');
  return json;
}

// Public
async function getHostelTypes() {
  const res = await apiFetch('/hostel-types');
  return res.data;
}

async function getDivisions() {
  const res = await apiFetch('/geo/divisions');
  return res.data;
}

async function getDistricts(divisionId) {
  if (!divisionId) return [];
  const res = await apiFetch('/geo/districts/' + divisionId);
  return res.data;
}

async function getUpazilas(districtId) {
  if (!districtId) return [];
  const res = await apiFetch('/geo/upazilas/' + districtId);
  return res.data;
}

async function getUnions(upazilaId) {
  if (!upazilaId) return [];
  const res = await apiFetch('/geo/unions/' + upazilaId);
  return res.data;
}

async function getAmenities() {
  const res = await apiFetch('/amenities');
  return res.data;
}

async function getHostels(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([k, v]) => {
    if (v !== undefined && v !== null && v !== '') params.append(k, v);
  });
  const qs = params.toString();
  const res = await apiFetch('/hostels' + (qs ? '?' + qs : ''));
  return res;
}

async function getHostel(id) {
  const res = await apiFetch('/hostels/' + id);
  return res.data;
}

// Auth
async function login(identifier, password) {
  const isEmail = identifier.includes('@');
  const body = isEmail
    ? { email: identifier, password }
    : { phone: identifier, password };
  const res = await apiFetch('/auth/login', {
    method: 'POST',
    body: JSON.stringify(body),
  });
  return res;
}

async function logout() {
  try { await apiFetch('/auth/logout', { method: 'POST' }); } catch {}
  clearAuth();
}

// Owner
async function createHostel(data) {
  const res = await apiFetch('/hostels', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  return res.data;
}

async function uploadHostelPhotos(hostelId, files) {
  const fd = new FormData();
  files.forEach(f => fd.append('photos[]', f));
  const res = await apiFetch('/hostels/' + hostelId + '/photos', { method: 'POST', body: fd });
  return res;
}

async function updateHostelLocation(hostelId, data) {
  const res = await apiFetch('/hostels/' + hostelId + '/location', {
    method: 'PATCH',
    body: JSON.stringify(data),
  });
  return res;
}

async function addRoom(hostelId, data) {
  const res = await apiFetch('/hostels/' + hostelId + '/rooms', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  return res;
}

async function submitHostel(hostelId) {
  const res = await apiFetch('/hostels/' + hostelId + '/submit', { method: 'POST' });
  return res;
}

// Owner dashboard
async function getOwnerListings(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([k, v]) => {
    if (v !== undefined && v !== null && v !== '') params.append(k, v);
  });
  const qs = params.toString();
  const res = await apiFetch('/owner/listings' + (qs ? '?' + qs : ''));
  return res;
}

async function getOwnerHostels(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([k, v]) => {
    if (v !== undefined && v !== null && v !== '') params.append(k, v);
  });
  const qs = params.toString();
  const res = await apiFetch('/owner/hostels' + (qs ? '?' + qs : ''));
  return res;
}

// Admin
async function adminCreateOwner(data) {
  const res = await apiFetch('/admin/owners', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  return res.data;
}

Object.assign(window, {
  getToken, getUser, setAuth, clearAuth,
  getHostelTypes, getDivisions, getDistricts, getUpazilas, getUnions, getAmenities,
  getHostels, getHostel,
  login, logout,
  createHostel, uploadHostelPhotos, updateHostelLocation, addRoom, submitHostel,
  getOwnerListings, getOwnerHostels,
  adminCreateOwner,
});
