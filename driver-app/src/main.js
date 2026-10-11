import './style.css'
import { Capacitor, CapacitorHttp, registerPlugin } from '@capacitor/core'
import { Preferences } from '@capacitor/preferences'
import { SecureStorage } from '@aparajita/capacitor-secure-storage'
import { Network } from '@capacitor/network'
import { LocalNotifications } from '@capacitor/local-notifications'

const BackgroundGeolocation = registerPlugin('BackgroundGeolocation')
const state = { token: null, user: null, watcherId: null, online: false, apiBase: 'http://10.0.2.2:8000/api/v1', lastLocation: null, home: null, homeTimer: null }
const app = document.querySelector('#app')

const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]))
const normalizeBase = value => value.trim().replace(/\/$/, '')

async function api(path, options = {}) {
  const response = await CapacitorHttp.request({
    url: `${state.apiBase}${path}`,
    method: options.method || 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(state.token ? { Authorization: `Bearer ${state.token}` } : {}) },
    data: options.data,
    connectTimeout: 12000,
    readTimeout: 15000,
  })
  if (response.status === 401) { await clearSession(); renderLogin('Tu sesión venció. Ingresa nuevamente.'); throw new Error('Sesión vencida') }
  if (response.status < 200 || response.status >= 300) throw new Error(response.data?.message || Object.values(response.data?.errors || {})?.flat()?.[0] || 'No fue posible completar la operación.')
  return response.data
}

function shell(content) {
  app.innerHTML = `<main class="shell"><header><div class="brand-mark">L</div><div><strong>Llegamos</strong><small>Aplicación del conductor</small></div></header>${content}<footer>Tu ubicación solo se comparte cuando estás conectado.</footer></main>`
}

function renderLogin(message = '') {
  shell(`<section class="card hero"><span class="eyebrow">ACCESO SEGURO</span><h1>Conduce con confianza</h1><p>Ingresa con tu cuenta de conductor verificada.</p></section>
  ${message ? `<div class="alert">${escapeHtml(message)}</div>` : ''}
  <form id="login-form" class="card form">
    <label>Servidor de Llegamos<input id="api-base" type="url" value="${escapeHtml(state.apiBase)}" required></label>
    <label>Correo electrónico<input id="email" type="email" autocomplete="email" required></label>
    <label>Contraseña<input id="password" type="password" autocomplete="current-password" minlength="8" required></label>
    <button class="primary" type="submit">Ingresar</button><p id="form-status" class="status"></p>
  </form>`)
  document.querySelector('#login-form').addEventListener('submit', login)
}

async function login(event) {
  event.preventDefault(); const button = event.submitter; const status = document.querySelector('#form-status')
  button.disabled = true; status.textContent = 'Verificando cuenta…'
  try {
    state.apiBase = normalizeBase(document.querySelector('#api-base').value)
    const result = await api('/auth/login', { method: 'POST', data: { email: document.querySelector('#email').value, password: document.querySelector('#password').value, device_name: `${Capacitor.getPlatform()}-${navigator.userAgent.slice(0, 40)}` } })
    if (result.user.role !== 'conductor') throw new Error('Esta aplicación es exclusiva para conductores.')
    state.token = result.token; state.user = result.user
    await Promise.all([Preferences.set({ key: 'api_base', value: state.apiBase }), SecureStorage.set('auth_token', state.token)])
    await loadHome()
  } catch (error) { status.textContent = error.message } finally { button.disabled = false }
}

function money(value) { return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0) }
function tripCard(trip, actions = '') { return `<article class="trip-card"><div class="trip-head"><span>Viaje #${trip.id}</span><strong>${money(trip.estimated_cost)}</strong></div><div class="route"><div><i class="pickup"></i><p><small>Recogida</small>${escapeHtml(trip.origin)}</p></div><div><i class="destination"></i><p><small>Destino</small>${escapeHtml(trip.destination)}</p></div></div>${trip.passenger_name ? `<p class="passenger">Pasajero: <strong>${escapeHtml(trip.passenger_name)}</strong></p>` : ''}${actions}</article>` }

function renderDashboard() {
  const active = state.home?.active_trip
  const offers = state.home?.offers || []
  const operations = active ? tripCard(active, active.status === 'accepted'
    ? `<button class="primary trip-action" data-action="start" data-trip="${active.id}">Iniciar con PIN</button>`
    : `<button class="primary trip-action" data-action="complete" data-trip="${active.id}">Finalizar viaje</button>`)
    : `<section><div class="section-heading"><h2>Solicitudes cercanas</h2><span>${offers.length}</span></div>${offers.length ? offers.map(offer => tripCard(offer.trip, `<p class="distance">A ${offer.distance_to_pickup_km.toFixed(1)} km de la recogida</p><div class="offer-actions"><button class="secondary trip-action" data-action="reject" data-trip="${offer.trip.id}">Rechazar</button><button class="primary trip-action" data-action="accept" data-trip="${offer.trip.id}">Aceptar</button></div>`)).join('') : `<div class="empty">${state.online ? 'Buscando solicitudes cercanas…' : 'Conéctate para recibir viajes.'}</div>`}</section>`
  shell(`<section class="card profile"><div><span class="eyebrow">CONDUCTOR</span><h1>${escapeHtml(state.user?.name)}</h1><p>${escapeHtml(state.user?.email)}</p></div><span id="connection-pill" class="pill">Comprobando red</span></section>
  <section class="availability ${state.online ? 'online' : ''}"><div class="radar"><span>🚘</span></div><h2 id="availability-title">${state.online ? 'Estás conectado' : 'Estás desconectado'}</h2><p id="tracking-message">${state.online ? 'Compartiendo ubicación de forma segura.' : 'Conéctate para recibir solicitudes cercanas.'}</p><button id="toggle-online" class="${state.online ? 'danger' : 'primary'}">${state.online ? 'Desconectarme' : 'Conectarme'}</button></section>
  <section class="metrics"><article><small>Precisión GPS</small><strong id="accuracy">${state.lastLocation ? `${Math.round(state.lastLocation.accuracy || 0)} m` : '—'}</strong></article><article><small>Última señal</small><strong id="last-signal">${state.lastLocation ? new Date(state.lastLocation.time).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '—'}</strong></article></section>
  ${operations}<button id="refresh-home" class="secondary">Actualizar solicitudes</button><button id="logout" class="secondary">Cerrar sesión en este dispositivo</button>`)
  document.querySelector('#toggle-online').addEventListener('click', () => state.online ? stopTracking() : startTracking())
  document.querySelector('#refresh-home').addEventListener('click', loadHome)
  document.querySelector('#logout').addEventListener('click', logout)
  document.querySelectorAll('.trip-action').forEach(button => button.addEventListener('click', operateTrip))
  refreshNetwork()
}

async function loadHome() {
  try { state.home = await api('/driver/home'); state.online = state.home.driver.is_online; renderDashboard(); if (!state.homeTimer) state.homeTimer = setInterval(() => { if (state.user) loadHome() }, 10000) }
  catch (error) { if (state.user) renderDashboard(); const message = document.querySelector('#tracking-message'); if (message) message.textContent = error.message }
}

async function operateTrip(event) {
  const button = event.currentTarget; const action = button.dataset.action; const tripId = button.dataset.trip; button.disabled = true
  try {
    let data = {}
    if (action === 'start') { const pin = window.prompt('Solicita al pasajero el PIN de 4 dígitos'); if (!pin) { button.disabled = false; return } data = { pickup_pin: pin } }
    if (action === 'complete' && !window.confirm('¿Confirmas que el pasajero llegó al destino?')) { button.disabled = false; return }
    await api(`/driver/trips/${tripId}/${action}`, { method: 'POST', data })
    await loadHome()
  } catch (error) { window.alert(error.message); button.disabled = false }
}

async function startTracking() {
  const button = document.querySelector('#toggle-online'); button.disabled = true
  try {
    if (Capacitor.getPlatform() === 'android') await LocalNotifications.requestPermissions()
    state.watcherId = await BackgroundGeolocation.addWatcher({ backgroundTitle: 'Llegamos Conductor activo', backgroundMessage: 'Compartiendo tu ubicación durante el servicio. Toca para volver a la aplicación.', requestPermissions: true, stale: false, distanceFilter: 10 }, handleLocation)
    document.querySelector('#tracking-message').textContent = 'Buscando señal GPS precisa…'
  } catch (error) { document.querySelector('#tracking-message').textContent = locationError(error); button.disabled = false }
}

async function handleLocation(location, error) {
  if (error) { document.querySelector('#tracking-message').textContent = locationError(error); return }
  if (!location || location.simulated) { document.querySelector('#tracking-message').textContent = location?.simulated ? 'Ubicación simulada detectada. No será enviada.' : 'Esperando ubicación…'; return }
  const payload = { latitude: location.latitude, longitude: location.longitude, accuracy: location.accuracy, heading: location.bearing, speed: location.speed }
  try {
    if (!state.online) { await api('/driver/availability', { method: 'POST', data: { is_online: true, ...payload } }); state.online = true; await loadHome() }
    await api('/driver/location', { method: 'POST', data: payload }); state.lastLocation = location
    const accuracy = document.querySelector('#accuracy'); const signal = document.querySelector('#last-signal'); const message = document.querySelector('#tracking-message')
    if (accuracy) accuracy.textContent = `${Math.round(location.accuracy || 0)} m`; if (signal) signal.textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); if (message) message.textContent = 'Ubicación protegida y actualizada.'
  } catch (requestError) { const message = document.querySelector('#tracking-message'); if (message) message.textContent = `Sin conexión: ${requestError.message}` }
}

async function stopTracking() {
  const button = document.querySelector('#toggle-online'); button.disabled = true
  try { if (state.watcherId) await BackgroundGeolocation.removeWatcher({ id: state.watcherId }); state.watcherId = null; await api('/driver/availability', { method: 'POST', data: { is_online: false } }); state.online = false; await loadHome() }
  catch (error) { button.disabled = false; document.querySelector('#tracking-message').textContent = error.message }
}

function locationError(error) { if (error?.code === 'NOT_AUTHORIZED') return 'Autoriza ubicación permanente en los ajustes del teléfono.'; return error?.message || 'No fue posible iniciar el GPS.' }
async function refreshNetwork() { const status = await Network.getStatus(); const pill = document.querySelector('#connection-pill'); if (pill) { pill.textContent = status.connected ? 'En línea' : 'Sin internet'; pill.classList.toggle('ok', status.connected) } }
async function logout() { try { if (state.online) await stopTracking(); await api('/auth/logout', { method: 'DELETE' }) } catch {} await clearSession(); renderLogin('Sesión cerrada correctamente.') }
async function clearSession() { state.token = null; state.user = null; state.online = false; await SecureStorage.remove('auth_token') }

async function boot() {
  const [token, { value: apiBase }] = await Promise.all([SecureStorage.get('auth_token'), Preferences.get({ key: 'api_base' })])
  if (apiBase) state.apiBase = apiBase
  Network.addListener('networkStatusChange', refreshNetwork)
  if (!token) return renderLogin()
  state.token = token
  try { const result = await api('/me'); if (result.user.role !== 'conductor') throw new Error(); state.user = result.user; await loadHome() } catch { await clearSession(); renderLogin('Debes iniciar sesión nuevamente.') }
}
boot()