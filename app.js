/* ===== AUTOMIGZ — app.js ===== */

/* ---------- CAR DATA (comes from PHP: lib.php) ---------- */
const CARS = window.AMZ.cars;

/* ---------- LOCATIONS & FEES ---------- */
const LOCATIONS = window.AMZ.locations;
const DELIVERY_FEE = window.AMZ.deliveryFee;
const peso = n => "\u20B1" + n.toLocaleString();

/* ---------- BROKEN PICTURE FALLBACK ---------- */
// If a car picture file is missing, show a neat placeholder instead of a broken image.
document.addEventListener("error", e => {
  const img = e.target;
  if (!img || img.tagName !== "IMG" || img.dataset.fallback) return;
  if (img.matches(".logo-img, .hero-img")) return;   // these have their own fallback
  img.dataset.fallback = "1";
  img.src = "images/placeholder.svg";
}, true);

/* ---------- SESSION + API (talks to api.php) ---------- */
let currentUser = window.AMZ.user;   // {first,last,mobile,email} or null — set by PHP
const store = {
  get session(){ return currentUser ? currentUser.email : null; },
  // only the email is remembered in the browser — never the password
  get remembered(){ try { return JSON.parse(localStorage.getItem("amz_remembered")) || null; } catch(e){ return null; } },
  set remembered(v){ v ? localStorage.setItem("amz_remembered", JSON.stringify(v)) : localStorage.removeItem("amz_remembered"); },
};
const esc = v => String(v == null ? "" : v).replace(/[&<>"']/g, ch => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[ch]));

async function api(action, data){
  try {
    const opts = data === undefined
      ? { credentials:"same-origin" }
      : { method:"POST", credentials:"same-origin",
          headers:{ "Content-Type":"application/json", "X-CSRF-Token": window.AMZ.csrf },
          body: JSON.stringify(data) };
    const res = await fetch("api.php?action=" + action, opts);
    const json = await res.json().catch(() => ({ ok:false, error:"Unexpected server response." }));
    if (res.status === 401 && currentUser){          // session expired
      currentUser = null; updateUserUI(); pendingAction = null;
      showPane("login"); location.hash = "#/auth";
    }
    return json;
  } catch(e){
    return { ok:false, error:"Cannot reach the server. Is PHP running?" };
  }
}

/* ---------- STATE ---------- */
let currentType = "all";
let searchTerm  = "";
let selectedCar = null;
let chosenColor = null;      // {n, h} chosen for the rent flow
let discoverIdx = 3; // Land Cruiser, like your design
let pendingAction = null;

/* ---------- ROUTER ---------- */
const routes = ["home","browse","auth","rent","congrats","bookings","services","about","contact"];
function router(){
  let hash = location.hash.replace("#/","") || "home";
  if (!routes.includes(hash)) hash = "home";
  if (hash === "rent" && !selectedCar) hash = "browse";
  if (hash === "bookings" && !store.session){ pendingAction = null; showPane("login"); location.hash = "#/auth"; return; }
  showView(hash);
}
function showView(name){
  document.querySelectorAll(".view").forEach(v => v.classList.remove("active"));
  const el = document.getElementById("view-" + name);
  if (el) el.classList.add("active");
  window.scrollTo(0,0);
  document.querySelectorAll(".nav-links a").forEach(a => a.classList.toggle("active", a.getAttribute("href") === "#/" + name));
  if (name === "about"){ const s = document.getElementById("statCars"); if (s) s.textContent = CARS.length; }
  if (name === "browse") renderCars();
  if (name === "bookings") renderBookings();
}
window.addEventListener("hashchange", router);

/* ---------- AUTH GATE ---------- */
function requireAuth(action, carId){
  if (store.session){ action(carId); return; }
  pendingAction = { action, carId };
  document.getElementById("gateMsg").classList.add("show");
  showPane("login");
  location.hash = "#/auth";
}
window.requireAuth = requireAuth;

/* ---------- COLOR HELPERS ---------- */
function swatchesHtml(car, selected){
  return `<div class="color-row"><span>Color:</span>` + car.colors.map((c,i) =>
    `<div class="swatch${(selected && selected.n===c.n)?" selected":""}" style="background:${c.h}" title="${c.n}" onclick="pickColor(${car.id},${i},event)"></div>`
  ).join("") + `<span class="color-name">${selected ? selected.n : car.colors[0].n}</span></div>`;
}
const carColor = {};  // carId -> chosen color
function pickColor(carId, idx, ev){
  if (ev) ev.stopPropagation();
  carColor[carId] = CARS.find(c => c.id === carId).colors[idx];
  renderCars();
  renderHomeCars();
}
window.pickColor = pickColor;

function carCardMarkup(c){
  return `
    <article class="car-card">
      <div class="car-photo"><img class="car-img" src="${c.img}" alt="${c.name}"></div>
      <div class="car-card-body">
        <div class="car-price">${peso(c.price)}<span> / day</span></div>
        <h3 class="car-name">${c.name}</h3>
        <div class="car-meta"><span>${c.type}</span><span>${c.trans}</span></div>
        ${swatchesHtml(c, carColor[c.id])}
        <div class="car-actions">
          <button class="btn btn-light" onclick="openDetails(${c.id})">View details</button>
          <button class="btn-rentnow" onclick="requireAuth(startRent, ${c.id})">Rent now</button>
        </div>
      </div>
    </article>`;
}

/* ---------- RENDER CAR GRID ---------- */
function renderCars(){
  const grid = document.getElementById("carGrid");
  const list = CARS.filter(c => {
    const typeOk = currentType === "all" || c.type === currentType;
    const term = searchTerm.toLowerCase();
    return typeOk && (!term || c.name.toLowerCase().includes(term));
  });
  grid.innerHTML = list.length ? list.map(carCardMarkup).join("")
    : `<div class="no-results">No cars found. Try another search.</div>`;
}

function renderHomeCars(){
  const grid = document.getElementById("homeCarGrid");
  if (!grid) return;
  const featured = [CARS.find(c => c.id === 1), CARS.find(c => c.id === 2), CARS.find(c => c.id === 4)].filter(Boolean);
  grid.innerHTML = featured.map(carCardMarkup).join("");
}

/* ---------- DETAILS MODAL ---------- */
function openDetails(id){
  const c = CARS.find(x => x.id === id);
  const sel = carColor[c.id] || c.colors[0];
  document.getElementById("detailsCard").innerHTML = `
    <button class="modal-close" onclick="closeDetails()">✕</button>
    <img class="car-img" src="${c.img}" alt="${c.name}">
    <div class="car-price">${peso(c.price)}/Day</div>
    <div class="car-name" style="font-size:17px">${c.name}</div>
    <div class="spec-list">
      <div><span>Type</span><span>${c.type}</span></div>
      <div><span>Transmission</span><span>${c.trans}</span></div>
      <div><span>Details</span><span>${c.info}</span></div>
    </div>
    ${swatchesHtml(c, sel)}
    <button class="btn btn-red btn-block" style="margin-top:6px" onclick="closeDetails();requireAuth(startRent, ${c.id})">Rent This Car</button>`;
  document.getElementById("detailsModal").classList.add("show");
}
function closeDetails(){ document.getElementById("detailsModal").classList.remove("show"); }
window.openDetails = openDetails;
window.closeDetails = closeDetails;
document.getElementById("detailsModal").addEventListener("click", e => {
  if (e.target.id === "detailsModal") closeDetails();
});

/* ---------- RENT FLOW ---------- */
function startRent(id){
  selectedCar = CARS.find(x => x.id === id);
  chosenColor = carColor[id] || selectedCar.colors[0];
  document.getElementById("rentCarInfo").innerHTML =
    `<span>${selectedCar.name} · ${chosenColor.n} — ${peso(selectedCar.price)}/Day</span>`;
  const user = currentUser;
  if (user){
    document.getElementById("rName").value  = user.first + " " + user.last;
    document.getElementById("rEmail").value = user.email;
    document.getElementById("rPhone").value = user.mobile;
  }
  updateRentMini();
  location.hash = "#/rent";
}
window.startRent = startRent;

/* ---------- LOCATION SELECT ---------- */
(function initLocations(){
  const sel = document.getElementById("rLocation");
  sel.innerHTML = LOCATIONS.map(l => `<option>${l}</option>`).join("");
})();

/* pick-up vs delivery toggle */
document.querySelectorAll('input[name="handover"]').forEach(r => r.addEventListener("change", () => {
  const mode = document.querySelector('input[name="handover"]:checked').value;
  const row = document.getElementById("addressRow");
  row.style.display = mode === "deliver" ? "flex" : "none";
  updateRentMini();
}));
document.getElementById("rLocation").addEventListener("change", updateRentMini);

/* live mini-summary on rent form */
function calcRent(){
  const pD = document.getElementById("rPickDate").value, rD = document.getElementById("rRetDate").value;
  const mode = document.querySelector('input[name="handover"]:checked').value;
  const qty  = Math.max(1, parseInt(document.getElementById("rQty").value) || 1);
  const days = (pD && rD) ? Math.max(1, Math.round((new Date(rD) - new Date(pD)) / 86400000)) : 0;
  const base = days * qty * (selectedCar ? selectedCar.price : 0);
  const fee  = mode === "deliver" ? DELIVERY_FEE : 0;
  return { days, qty, base, fee, total: base + fee, mode };
}
function updateRentMini(){
  if (!selectedCar) return;
  const c = calcRent();
  const box = document.getElementById("rentMini");
  box.classList.add("show");
  box.innerHTML = `
    <div class="line"><span>${selectedCar.name} (${chosenColor.n})</span><span>${peso(selectedCar.price)}/day × ${c.qty} vehicle(s) × ${c.days || "…"} day(s)</span></div>
    <div class="line"><span>${c.mode === "deliver" ? "🚚 Delivery fee" : "📍 Pick-up (free)"}</span><span>${c.mode === "deliver" ? peso(DELIVERY_FEE) : "FREE"}</span></div>
    <div class="line grand"><span>Estimated Total</span><span>${c.days ? peso(c.total) : "—"}</span></div>`;
}
["rPickDate","rRetDate"].forEach(id => document.getElementById(id).addEventListener("change", updateRentMini));
document.getElementById("rQty").addEventListener("input", updateRentMini);

/* ---------- RENT SUBMIT ---------- */
document.getElementById("rentForm").addEventListener("submit", e => {
  e.preventDefault();
  const err = document.getElementById("rentError");
  const pD = document.getElementById("rPickDate").value, rD = document.getElementById("rRetDate").value;
  const mode = document.querySelector('input[name="handover"]:checked').value;
  const address = document.getElementById("rAddress").value.trim();
  if (!pD || !rD){ err.textContent = "Please fill in all fields."; return; }
  if (new Date(rD) < new Date(pD)){ err.textContent = "Return date must be after pick-up date."; return; }
  if (mode === "deliver" && !address){ err.textContent = "Please enter your delivery address."; return; }
  err.textContent = "";
  openVerify();
});

/* ---------- BOOKING VERIFICATION ---------- */
function openVerify(){
  const c = calcRent();
  const loc = document.getElementById("rLocation").value;
  const address = document.getElementById("rAddress").value.trim();
  const name = document.getElementById("rName").value.trim();
  const where = esc(c.mode === "deliver" ? "🚚 Deliver to: " + address : "📍 Pick-up: " + loc);
  document.getElementById("verifyCard").innerHTML = `
    <button class="modal-close" onclick="closeVerify()">✕</button>
    <div class="verify-title">✅ Verify Your Booking</div>
    <div class="verify-sub">Please confirm your rental details before submitting.</div>
    <div class="verify-list">
      <div><span>Renter</span><span>${esc(name)}</span></div>
      <div><span>Vehicle</span><span>${esc(selectedCar.name)}</span></div>
      <div><span>Color</span><span>${chosenColor.n}</span></div>
      <div><span>Quantity</span><span>${c.qty} vehicle(s)</span></div>
      <div><span>Rental Period</span><span>${document.getElementById("rPickDate").value} → ${document.getElementById("rRetDate").value} (${c.days} day(s))</span></div>
      <div><span>Handover</span><span>${where}</span></div>
      <div><span>Rate</span><span>${peso(selectedCar.price)}/day × ${c.qty} × ${c.days}</span></div>
      <div><span>${c.mode === "deliver" ? "Delivery fee" : "Pick-up fee"}</span><span>${c.mode === "deliver" ? peso(DELIVERY_FEE) : "FREE"}</span></div>
    </div>
    <div class="verify-total"><span>Total Payment</span><span>${peso(c.total)}</span></div>
    <div class="verify-actions">
      <button class="btn btn-dark" onclick="closeVerify()">Cancel</button>
      <button class="btn btn-red" onclick="confirmBooking()">✔ Confirm Booking</button>
    </div>`;
  document.getElementById("verifyModal").classList.add("show");
}
function closeVerify(){ document.getElementById("verifyModal").classList.remove("show"); }
window.closeVerify = closeVerify;

let bookingBusy = false;
async function confirmBooking(){
  if (bookingBusy) return;
  bookingBusy = true;
  const c = calcRent();
  const address = document.getElementById("rAddress").value.trim();
  const res = await api("book", {
    carId:    selectedCar.id,
    color:    chosenColor.n,
    qty:      c.qty,
    pickDate: document.getElementById("rPickDate").value,
    retDate:  document.getElementById("rRetDate").value,
    mode:     c.mode,
    location: document.getElementById("rLocation").value,
    address:  address,
    name:     document.getElementById("rName").value.trim(),
    phone:    document.getElementById("rPhone").value.trim(),
  });
  bookingBusy = false;
  closeVerify();
  if (!res.ok){ document.getElementById("rentError").textContent = res.error || "Could not save your booking."; return; }
  document.getElementById("rentError").textContent = "";
  const b = res.booking;   // totals below are the server's, not the browser's
  document.getElementById("congratsDetail").innerHTML =
    `${b.qty} × ${esc(b.car)} (${esc(b.color.n)}) · ${b.days} day(s) · ${b.mode === "deliver" ? "Delivered to " + esc(b.address) : "Pick-up: " + esc(b.location)} · Total: <b>${peso(b.total)}</b>`;
  location.hash = "#/congrats";
}
window.confirmBooking = confirmBooking;
document.getElementById("verifyModal").addEventListener("click", e => {
  if (e.target.id === "verifyModal") closeVerify();
});

/* ---------- BOOKINGS PAGE + TOTAL SUMMARY + CANCELLATION ---------- */
const bookingKey = b => b.id;
let bookingsCache = [];

function normalizeBooking(b){
  const qty   = b.qty   || 1;
  const days  = b.days  || 1;
  const price = b.price || 0;
  const base  = (b.base != null) ? b.base : price * qty * days;
  const fee   = (b.fee  != null) ? b.fee  : 0;
  const total = (b.total != null) ? b.total : base + fee;
  return { qty, days, price, base, fee, total, color: b.color || { n:"—", h:"#bbbbbb" },
           cancelled: b.status === "cancelled" };
}

async function renderBookings(){
  const listEl = document.getElementById("bookingsList");
  listEl.innerHTML = `<div class="empty-msg">Loading your bookings…</div>`;
  try {
    const res = await api("bookings");
    if (!res.ok) throw new Error(res.error || "load failed");
    const mine = bookingsCache = res.bookings;
    const user = currentUser;
    if (!mine.length){
      listEl.innerHTML = `<div class="empty-msg">No bookings yet. <a href="#/browse">Rent your first car →</a></div>`;
      document.getElementById("summaryRows").innerHTML = "";
      document.getElementById("summaryTotal").innerHTML = "<span>Grand Total</span><span>" + peso(0) + "</span>";
      return;
    }
    // newest first
    const sorted = mine.slice().sort((a,b) => (b.date || "").localeCompare(a.date || ""));
    listEl.innerHTML = sorted.map(b => {
      const n = normalizeBooking(b);
      const renter = b.renter || (user ? user.first + " " + user.last : "—");
      const where = esc(b.mode === "deliver"
        ? "🚚 Deliver to: " + (b.address || "—")
        : "📍 Pick-up: " + (b.location || "Automigz Office — Cabanatuan City"));
      const booked = b.date ? new Date(b.date).toLocaleString() : "—";
      const key = Number(bookingKey(b));
      return `
      <div class="booking-item${n.cancelled ? " is-cancelled" : ""}">
        <div class="booking-head">
          <img src="${esc(b.img || "images/fortuner.png")}" alt="${esc(b.car)}">
          <div class="booking-info">
            <div class="b-name"><span class="color-dot" style="background:${esc(n.color.h)}"></span>${n.qty} × ${esc(b.car)}
              <span class="status-badge ${n.cancelled ? "status-cancelled" : "status-active"}">${n.cancelled ? "Cancelled" : "Active"}</span></div>
            <div class="b-meta">${b.pickDate || "—"} → ${b.retDate || "—"} · ${n.days} day(s)</div>
          </div>
        </div>
        <div class="booking-detail">
          <div class="line"><span>Renter</span><span>${esc(renter)}</span></div>
          <div class="line"><span>Vehicle</span><span>${esc(b.car)}</span></div>
          <div class="line"><span>Color</span><span>${esc(n.color.n)}</span></div>
          <div class="line"><span>Quantity</span><span>${n.qty} vehicle(s)</span></div>
          <div class="line"><span>Rental period</span><span>${b.pickDate || "—"} → ${b.retDate || "—"} (${n.days} day(s))</span></div>
          <div class="line"><span>Handover</span><span>${where}</span></div>
          <div class="line"><span>Rate</span><span>${peso(n.price)}/day × ${n.qty} × ${n.days} = ${peso(n.base)}</span></div>
          <div class="line"><span>${b.mode === "deliver" ? "Delivery fee" : "Pick-up fee"}</span><span>${n.fee ? peso(n.fee) : "FREE"}</span></div>
          <div class="line"><span>Booked on</span><span>${booked}</span></div>
        </div>
        <div class="booking-foot">
          <div class="booking-total">${peso(n.total)}</div>
          ${n.cancelled
            ? `<span class="cancelled-note">This booking was cancelled</span>`
            : `<button class="btn btn-cancel" onclick="askCancel(${key})">✕ Cancel Booking</button>`}
        </div>
      </div>`;
    }).join("");

    // Total summary counts ACTIVE bookings only
    const active = mine.filter(b => b.status !== "cancelled");
    const cancelledCount = mine.length - active.length;
    const norm = active.map(normalizeBooking);
    const sumBase  = norm.reduce((s,n) => s + n.base, 0);
    const sumFee   = norm.reduce((s,n) => s + n.fee, 0);
    const sumAll   = norm.reduce((s,n) => s + n.total, 0);
    const daysAll  = norm.reduce((s,n) => s + n.days, 0);
    const unitsAll = norm.reduce((s,n) => s + n.qty, 0);
    document.getElementById("summaryRows").innerHTML = `
      <div class="line"><span>Active bookings</span><span>${active.length} booking(s) · ${unitsAll} vehicle(s) · ${daysAll} day(s)</span></div>
      <div class="line"><span>Rental costs</span><span>${peso(sumBase)}</span></div>
      <div class="line"><span>Delivery fees</span><span>${peso(sumFee)}</span></div>
      ${cancelledCount ? `<div class="line muted"><span>Cancelled (not charged)</span><span>${cancelledCount} booking(s)</span></div>` : ""}`;
    document.getElementById("summaryTotal").innerHTML = `<span>Grand Total Payment</span><span>${peso(sumAll)}</span>`;
  } catch(e){
    console.error(e);
    listEl.innerHTML = `<div class="empty-msg">Sorry, your saved bookings could not be loaded.</div>`;
  }
}

function askCancel(key){
  const b = bookingsCache.find(x => Number(bookingKey(x)) === Number(key));
  if (!b) return;
  const n = normalizeBooking(b);
  document.getElementById("verifyCard").innerHTML = `
    <button class="modal-close" onclick="closeVerify()">✕</button>
    <div class="verify-title">Cancel this booking?</div>
    <div class="verify-sub">This will cancel your reservation and remove it from your total payment.</div>
    <div class="verify-list">
      <div><span>Vehicle</span><span>${n.qty} × ${esc(b.car)} (${esc(n.color.n)})</span></div>
      <div><span>Rental period</span><span>${esc(b.pickDate || "—")} → ${esc(b.retDate || "—")}</span></div>
    </div>
    <div class="verify-total"><span>Amount</span><span>${peso(n.total)}</span></div>
    <div class="verify-actions">
      <button class="btn btn-dark" onclick="closeVerify()">Keep Booking</button>
      <button class="btn btn-red" onclick="confirmCancel(${Number(key)})">Yes, Cancel It</button>
    </div>`;
  document.getElementById("verifyModal").classList.add("show");
}
async function confirmCancel(key){
  const res = await api("cancel", { id: Number(key) });
  closeVerify();
  if (!res.ok){ toast(res.error || "Could not cancel the booking."); }
  else toast("Booking cancelled.");
  renderBookings();
}
window.askCancel = askCancel;
window.confirmCancel = confirmCancel;

/* ---------- CONTACT: EMAIL REVEAL / COPY ---------- */
const OUR_EMAIL = "automigz@gmail.com";
function copyEmail(e){
  e.preventDefault();
  const show = () => toast("📧 Our email: " + OUR_EMAIL + " (copied!)");
  if (navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(OUR_EMAIL).then(show).catch(show);
  } else show();
}
window.copyEmail = copyEmail;

/* ---------- PHONE VALIDATION — letters show "Invalid input" ---------- */
function bindPhoneValidation(inputId, errId){
  const input = document.getElementById(inputId);
  const err = document.getElementById(errId);
  input.addEventListener("input", () => {
    const cleaned = input.value.replace(/[^0-9+\s-]/g, "");
    if (input.value !== cleaned){
      input.value = cleaned;
      err.textContent = "Invalid input: numbers only.";
    } else {
      err.textContent = "";
    }
  });
}
bindPhoneValidation("suMobile", "signupError");
bindPhoneValidation("rPhone", "rentError");

/* ---------- TABS & SEARCH ---------- */
document.querySelectorAll(".tab").forEach(t => t.addEventListener("click", () => {
  document.querySelectorAll(".tab").forEach(x => x.classList.remove("active"));
  t.classList.add("active");
  currentType = t.dataset.type;
  renderCars();
}));
document.getElementById("searchBtn").addEventListener("click", () => {
  searchTerm = document.getElementById("searchInput").value.trim();
  renderCars();
});
document.getElementById("searchInput").addEventListener("input", e => {
  searchTerm = e.target.value.trim();
  renderCars();
});

/* ---------- DISCOVER CAROUSEL ---------- */
function renderDiscover(){
  const car = CARS[discoverIdx];
  document.getElementById("discoverImg").src = car.img;
  document.getElementById("discoverImg").alt = car.name;
  document.getElementById("discoverType").textContent = car.type;
  document.getElementById("discoverName").textContent = car.name;
  document.getElementById("discoverPrice").textContent = `${peso(car.price)} / day`;
}
document.getElementById("prevCar").addEventListener("click", () => {
  discoverIdx = (discoverIdx - 1 + CARS.length) % CARS.length; renderDiscover();
});
document.getElementById("nextCar").addEventListener("click", () => {
  discoverIdx = (discoverIdx + 1) % CARS.length; renderDiscover();
});

/* ---------- AUTH PANES ---------- */
function showPane(which){
  document.getElementById("loginPane").style.display  = which === "login" ? "block" : "none";
  document.getElementById("signupPane").style.display = which === "signup" ? "block" : "none";
  if (which === "login" && !pendingAction) document.getElementById("gateMsg").classList.remove("show");
}
document.getElementById("toSignup").addEventListener("click", e => { e.preventDefault(); showPane("signup"); });
document.getElementById("toLogin").addEventListener("click",  e => { e.preventDefault(); showPane("login"); });

/* ---------- SIGN UP ---------- */
document.getElementById("signupForm").addEventListener("submit", async e => {
  e.preventDefault();
  const err = document.getElementById("signupError");
  const first  = document.getElementById("suFirst").value.trim();
  const last   = document.getElementById("suLast").value.trim();
  const mobile = document.getElementById("suMobile").value.trim();
  const email  = document.getElementById("suEmail").value.trim().toLowerCase();
  const pass   = document.getElementById("suPass").value;
  if (!first || !last || !mobile || !email || !pass){ err.textContent = "Please fill in all required fields."; return; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ err.textContent = "Please enter a valid email address."; return; }
  if (pass.length < 8){ err.textContent = "Password must be at least 8 characters."; return; }
  const res = await api("signup", { first, last, mobile, email, pass });
  if (!res.ok){ err.textContent = res.error || "Could not create account."; return; }
  err.textContent = "";
  document.getElementById("signupForm").reset();
  toast("Account created! Please log in.");
  showPane("login");
});

/* ---------- LOGIN (remember me) ---------- */
(function initRemembered(){
  const r = store.remembered;
  if (r && r.email){
    document.getElementById("loginEmail").value = r.email;
    document.getElementById("rememberMe").checked = true;
  }
})();

document.getElementById("loginForm").addEventListener("submit", async e => {
  e.preventDefault();
  const err = document.getElementById("loginError");
  const id   = document.getElementById("loginEmail").value.trim();
  const pass = document.getElementById("loginPass").value;
  const remember = document.getElementById("rememberMe").checked;
  const res = await api("login", { id, pass, remember });
  if (!res.ok){ err.textContent = res.error || "Invalid email/username or password."; return; }
  err.textContent = "";
  currentUser = res.user;
  store.remembered = remember ? { email: res.user.email } : null;
  document.getElementById("loginPass").value = "";
  updateUserUI();
  document.getElementById("gateMsg").classList.remove("show");
  toast("Welcome back, " + res.user.first + "!");
  const p = pendingAction; pendingAction = null;
  if (p) p.action(p.carId);
  else location.hash = "#/browse";
});

document.getElementById("forgotUser").addEventListener("click", e => { e.preventDefault(); toast("Use the email you signed up with to log in."); });
document.getElementById("forgotPass").addEventListener("click", e => { e.preventDefault(); toast("Contact automigz@gmail.com to reset your password."); });

/* ---------- LOGOUT ---------- */
async function logout(){
  await api("logout", {});
  currentUser = null;
  pendingAction = null;
  updateUserUI();
  toast("Logged out. See you soon!");
  location.hash = "#/home";
}
document.getElementById("logoutLink").addEventListener("click", e => { e.preventDefault(); logout(); });
document.getElementById("sideLogout").addEventListener("click",  e => { e.preventDefault(); logout(); closeSidebar(); });

/* ---------- USER UI / MENUS ---------- */
function updateUserUI(){
  const btn = document.getElementById("userBtn");
  if (store.session){
    const user = currentUser;
    btn.innerHTML = `<span class="avatar-letter">${esc((user ? user.first[0] : "U").toUpperCase())}</span>`;
  } else btn.textContent = "\uD83D\uDC64";
}
const userDropdown = document.getElementById("userDropdown");
document.getElementById("userBtn").addEventListener("click", e => {
  e.stopPropagation();
  if (!store.session){ pendingAction = null; showPane("login"); location.hash = "#/auth"; return; }
  userDropdown.classList.toggle("show");
});
document.addEventListener("click", () => userDropdown.classList.remove("show"));

/* data-goto links (My Booking, etc.) — gated behind login */
document.querySelectorAll("[data-goto]").forEach(a => a.addEventListener("click", e => {
  e.preventDefault();
  closeSidebar();
  if (!store.session){ pendingAction = null; showPane("login"); location.hash = "#/auth"; return; }
  location.hash = "#/" + a.dataset.goto;
}));

const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("sidebarOverlay");
function closeSidebar(){ sidebar.classList.remove("show"); overlay.classList.remove("show"); }
document.getElementById("menuBtn").addEventListener("click", () => {
  sidebar.classList.add("show"); overlay.classList.add("show");
});
document.getElementById("sideClose").addEventListener("click", closeSidebar);
overlay.addEventListener("click", closeSidebar);
sidebar.querySelectorAll("a").forEach(a => a.addEventListener("click", closeSidebar));

function scrollToContact(){
  location.hash = "#/home";
  setTimeout(() => {
    const el = document.getElementById("homeContact");
    if (el) el.scrollIntoView({ behavior:"smooth" });
  }, 100);
}
window.scrollToContact = scrollToContact;

/* ---------- TOAST ---------- */
let toastTimer;
function toast(msg){
  const el = document.getElementById("toast");
  el.textContent = msg;
  el.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove("show"), 2800);
}

/* ---------- INIT ---------- */
updateUserUI();
renderDiscover();
renderHomeCars();
router();
