<?php
require __DIR__ . '/lib.php';
start_session();
$AMZ = [
    'user'        => public_user(current_user()),
    'csrf'        => $_SESSION['csrf'],
    'cars'        => cars(),
    'locations'   => LOCATIONS,
    'deliveryFee' => DELIVERY_FEE,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Automigz — Quality Cars</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ============ HEADER ============ -->
<header class="topbar">
  <a class="logo" href="#/home">
    <!-- Replace images/logo.png with your own logo file (same name) to change it -->
    <img class="logo-img" src="images/logo.png" alt="Automigz Car Exchange"
         onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
    <span class="logo-word" style="display:none">AUTOMIGZ</span>
  </a>
  <nav class="nav-links">
    <a href="#/browse">Inventory</a>
    <a href="#/about">About</a>
    <a href="#/services">Services</a>
    <a href="#/contact">Contact</a>
  </nav>
  <div class="top-actions">
    <button class="icon-btn" id="menuBtn" title="Menu">☰</button>
    <div class="user-area">
      <button class="icon-btn" id="userBtn" title="Account">👤</button>
      <div class="user-dropdown" id="userDropdown">
        <a href="#" data-goto="bookings">My Bookings</a>
        <a href="#/services">Services</a>
        <a href="#/browse">Offers</a>
        <a href="#/browse">Cars</a>
        <a href="#/contact">Help / Contact</a>
        <a href="#" id="logoutLink">Log out</a>
      </div>
    </div>
  </div>
</header>

<!-- ============ SIDEBAR ============ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="side-head"><span class="logo-word">AUTOMIGZ</span><button class="icon-btn" id="sideClose">✕</button></div>
  <a href="#/about">About</a>
  <a href="#/services">Services</a>
  <a href="#" data-goto="bookings">My Bookings</a>
  <a href="#/browse">Inventory</a>
  <a href="#/contact">Help / Contact</a>
  <a href="#" id="sideLogout">Log out</a>
</aside>

<main id="app">

  <!-- ============ HOME ============ -->
  <section class="view" id="view-home">
    <div class="home-hero">
      <div class="home-hero-copy">
        <p class="home-kicker">AUTOMIGZ CAR EXCHANGE</p>
        <h1>Good cars for<br><em>great journeys.</em></h1>
        <p class="home-lede">Find a dependable vehicle for the trip ahead. Browse our fleet, compare clear daily rates, and choose the pick-up option that works for you.</p>
        <div class="home-actions">
          <a href="#/browse" class="btn btn-red">Explore inventory <span aria-hidden="true">&rarr;</span></a>
          <a href="tel:09266242649" class="home-call">Call 0926-624-2649</a>
        </div>
        <div class="home-proof">
          <div><strong>8</strong><span>vehicles in our fleet</span></div>
          <div><strong>6</strong><span>convenient pick-up points</span></div>
          <div><strong>₱4,000</strong><span>daily rates from</span></div>
        </div>
      </div>
      <div class="home-featured">
        <div class="featured-stage">
          <span class="featured-label">A closer look</span>
          <img class="discover-img" id="discoverImg" src="images/image 33.png" alt="Toyota Land Cruiser">
          <div class="featured-caption">
            <div><span id="discoverType">SUV</span><h2 id="discoverName">Toyota Land Cruiser</h2><p id="discoverPrice">₱30,000 / day</p></div>
            <div class="featured-controls">
              <button class="arrow-btn" id="prevCar" type="button" aria-label="Previous vehicle" title="Previous vehicle">&#8592;</button>
              <button class="arrow-btn" id="nextCar" type="button" aria-label="Next vehicle" title="Next vehicle">&#8594;</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="home-inventory">
      <div class="home-section-head">
        <div><p class="home-kicker">THE AUTOMIGZ FLEET</p><h2>Find your next car</h2><p>Comfortable, capable vehicles for everyday drives and bigger plans.</p></div>
        <a href="#/browse" class="text-link">View all vehicles <span aria-hidden="true">&rarr;</span></a>
      </div>
      <div class="home-car-grid" id="homeCarGrid"></div>
    </section>

    <div class="home-service-strip">
      <div><span class="service-mark">01</span><strong>Clear daily rates</strong><span>Know the cost before you book.</span></div>
      <div><span class="service-mark">02</span><strong>Pick-up or delivery</strong><span>Choose how your trip begins.</span></div>
      <div><span class="service-mark">03</span><strong>Real local support</strong><span>Talk to our team when you need us.</span></div>
    </div>
  </section>

  <!-- ============ BROWSE ============ -->
  <section class="view" id="view-browse">
    <div class="browse-intro"><p class="home-kicker">THE AUTOMIGZ FLEET</p><h1>Find your next car</h1><p>Compare vehicles and daily rates, then choose the one that fits your plans.</p></div>
    <div class="browse-bar">
      <div class="tabs">
        <button class="tab active" data-type="all">All vehicles</button>
        <button class="tab" data-type="SUV">SUV</button>
        <button class="tab" data-type="Pick-up">Pick-up</button>
        <button class="tab" data-type="Van">Van</button>
      </div>
      <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Search cars...">
        <button class="icon-btn" id="searchBtn">🔍</button>
      </div>
    </div>
    <div class="car-grid" id="carGrid"></div>
  </section>

  <!-- ============ AUTH ============ -->
  <section class="view view-auth" id="view-auth">
    <div class="auth-card">
      <div id="loginPane">
        <h2>Welcome!</h2>
        <p class="auth-sub">Log in to your account and continue your car rental journey.</p>
        <div class="gate-msg" id="gateMsg">🔒 Please log in or sign up to view details and rent a car.</div>
        <form id="loginForm">
          <label>Email or Username</label>
          <input type="text" id="loginEmail" placeholder="email or username" autocomplete="username">
          <label>Password</label>
          <input type="password" id="loginPass" placeholder="password" autocomplete="current-password">
          <label class="remember-row"><input type="checkbox" id="rememberMe"> Remember me</label>
          <div class="form-error" id="loginError"></div>
          <button type="submit" class="btn btn-red btn-block">Login »</button>
        </form>
        <div class="auth-links"><a href="#" id="forgotUser">Forgot Username</a><a href="#" id="forgotPass">Forgot Password</a></div>
        <p class="auth-switch">Don't have an account? <a href="#" id="toSignup">Sign in Here</a></p>
      </div>
      <div id="signupPane" style="display:none">
        <h2>Welcome!</h2>
        <h3>Create Account</h3>
        <form id="signupForm">
          <label>First Name ( As it appears On your Driver's Licence)*</label>
          <input type="text" id="suFirst" placeholder="Enter your First Name">
          <label>Last Name ( As it appears On your Driver's Licence)*</label>
          <input type="text" id="suLast" placeholder="Enter your Last Name">
          <label>Mobile Number*</label>
          <input type="tel" id="suMobile" placeholder="+63 9XX-XXX-XXXX">
          <label>Email Address*</label>
          <input type="email" id="suEmail" placeholder="Enter your Email">
          <label>Password*</label>
          <input type="password" id="suPass" placeholder="Enter at least 8 characters">
          <div class="form-error" id="signupError"></div>
          <button type="submit" class="btn btn-dark btn-block">Sign Up</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="#" id="toLogin">Sign in Here</a></p>
      </div>
    </div>
  </section>

  <!-- ============ RENT ============ -->
  <section class="view" id="view-rent">
    <div class="rent-hero">
      <h1>AUTOMIGZ CAR EXCHANGE</h1>
      <p class="rent-sub">RENT WITH AUTOMIGZ</p>
      <div class="rent-car-info" id="rentCarInfo"></div>
      <form class="rent-form" id="rentForm">
        <div class="rent-row">
          <input type="text" id="rName" placeholder="Name" required>
          <input type="email" id="rEmail" placeholder="Email" required>
          <input type="tel" id="rPhone" placeholder="Phone Number" required>
        </div>
        <div class="rent-row">
          <div class="qty-wrap"><label>Number of vehicles:</label>
            <input type="number" id="rQty" min="1" max="10" value="1" required></div>
          <input type="date" id="rPickDate" required>
          <input type="time" id="rPickTime" value="12:00" required>
          <input type="date" id="rRetDate" required>
          <input type="time" id="rRetTime" value="12:00" required>
        </div>

        <!-- PICK-UP / DELIVERY OPTION -->
        <div class="option-row">
          <label class="option-pill"><input type="radio" name="handover" value="pickup" checked> 📍 I'll pick up the car</label>
          <label class="option-pill"><input type="radio" name="handover" value="deliver"> 🚚 Deliver the car to me</label>
        </div>
        <div class="rent-row">
          <select id="rLocation"></select>
        </div>
        <div class="rent-row" id="addressRow" style="display:none">
          <input type="text" id="rAddress" placeholder="Enter your delivery address (street, barangay, city)">
        </div>

        <div class="rent-summary-mini" id="rentMini"></div>
        <div class="form-error" id="rentError"></div>
        <button type="submit" class="btn btn-dark rent-btn">NEXT</button>
      </form>
    </div>
    <div class="contact-footer" id="homeContact">
      <h3>CONTACT US</h3>
      <div class="contact-cols">
        <div><strong>Reservations</strong><br><a href="tel:09266242649">0926-624-2649</a></div>
        <div><strong>Wizard &amp; Preferred Updates</strong><br><a href="mailto:automigz@gmail.com">Email Us</a></div>
        <div><strong>Customer Service</strong><br><a href="tel:09266242649">0926-624-2649</a></div>
      </div>
    </div>
  </section>

  <!-- ============ CONGRATS ============ -->
  <section class="view" id="view-congrats">
    <div class="congrats-hero">
      <h1>AUTOMIGZ CAR EXCHANGE</h1>
      <p class="rent-sub">RENT WITH AUTOMIGZ</p>
      <div class="congrats-box">
        <p class="congrats-msg">Rented Successfully!!</p>
        <p class="congrats-detail" id="congratsDetail"></p>
        <a href="#/browse" class="btn btn-red">Browse More Cars</a>
        <a href="#" data-goto="bookings" class="btn btn-dark">View My Bookings</a>
        <a href="#/home" class="btn btn-dark">Back to Home</a>
      </div>
    </div>
    <div class="contact-footer">
      <h3>CONTACT US</h3>
      <div class="contact-cols">
        <div><strong>Reservations</strong><br><a href="tel:09266242649">0926-624-2649</a></div>
        <div><strong>Wizard &amp; Preferred Updates</strong><br><a href="mailto:automigz@gmail.com">Email Us</a></div>
        <div><strong>Customer Service</strong><br><a href="tel:09266242649">0926-624-2649</a></div>
      </div>
    </div>
  </section>

  <!-- ============ MY BOOKINGS + TOTAL SUMMARY ============ -->
  <section class="view" id="view-bookings">
    <div class="bookings-wrap">
      <h2 class="bookings-title">My Bookings</h2>
      <div id="bookingsList"></div>
      <div class="summary-card" id="summaryCard">
        <h3>Total Summary</h3>
        <div class="summary-rows" id="summaryRows"></div>
        <div class="summary-total" id="summaryTotal"></div>
      </div>
      <div style="text-align:center;margin-top:20px">
        <a href="#/browse" class="btn btn-red">Rent Another Car</a>
      </div>
    </div>
  </section>


  <!-- ============ SERVICES ============ -->
  <section class="view" id="view-about">
    <div class="ab-hero">
      <div class="ab-hero-inner">
        <span class="eyebrow">About Automigz</span>
        <h1>Quality cars.<br><em>Honest service.</em></h1>
        <p>Automigz Car Exchange makes renting a car simple — clear prices, clean vehicles, and people who actually pick up the phone.</p>
      </div>
    </div>

    <div class="ab-stats reveal">
      <div class="ab-stats-card">
        <div class="ab-stat"><b id="statCars">8</b><span>Vehicles in our fleet</span></div>
        <div class="ab-stat"><b>4</b><span>Branches</span></div>
        <div class="ab-stat"><b>24/7</b><span>Roadside assistance</span></div>
        <div class="ab-stat"><b>6AM–10PM</b><span>Open daily</span></div>
      </div>
    </div>

    <div class="ab-story">
      <div class="reveal">
        <span class="eyebrow dark">Our story</span>
        <h2>Good cars shouldn’t come with complicated rentals.</h2>
        <p>Automigz Car Exchange is built around one idea: reliable vehicles and straightforward service. Whether you need a family SUV for the weekend, a van for the whole group, or a pick-up for heavy duty, you can browse, compare and book in minutes.</p>
        <p>Pick your car up at one of our branches or have it delivered to your door. Every vehicle is cleaned before it reaches you, and every rental comes with insurance and roadside assistance — so the only thing left to think about is where you’re headed.</p>
        <a href="#/contact" class="btn btn-dark" style="margin-top:8px">Talk to us</a>
      </div>
      <div class="ab-frame reveal">
        <div class="ab-frame-card"><img src="images/logo.png" alt="Automigz Car Exchange" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'ab-logo-text',textContent:'AUTOMIGZ'}))"></div>
      </div>
    </div>

    <div class="ab-values">
      <span class="eyebrow dark">What we stand for</span>
      <h2>Three promises, every rental</h2>
      <div class="ab-values-row">
        <div class="ab-value reveal"><div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg></div><h3>Quality first</h3><p>Clean, well-kept vehicles in every color and class — ready when you are.</p></div>
        <div class="ab-value reveal"><div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg></div><h3>Clear pricing</h3><p>See your full total before you confirm. Delivery is a flat ₱500, and pick-up is free.</p></div>
        <div class="ab-value reveal"><div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg></div><h3>Always convenient</h3><p>Four branches, delivery to your door, and easy cancellation from My Bookings.</p></div>
      </div>
    </div>

    <div class="sv-cta">
      <div class="sv-cta-box">
        <div><h3>Find your next ride</h3><p>Browse the fleet and book online in minutes.</p></div>
        <a href="#/browse" class="btn btn-red">Browse Cars</a>
      </div>
    </div>
  </section>

  <!-- ============ SERVICES ============ -->
  <section class="view" id="view-services">
    <div class="sv-hero">
      <div class="sv-hero-inner">
        <span class="eyebrow">What we offer</span>
        <h1>Drive more.<br><em>Worry less.</em></h1>
        <p>From a quick self-drive to a fully chauffeured trip, Automigz takes care of the details so you can enjoy the road.</p>
      </div>
      <div class="sv-watermark" aria-hidden="true">SERVICES</div>
    </div>

    <div class="sv-wrap">
      <div class="sv-bento">
        <article class="sv-card sv-feature reveal">
          <span class="sv-num">01</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg></div>
          <h3>Self-Drive Rental</h3>
          <p>Rent any car in our fleet and drive yourself — daily, weekly, or monthly rates available. Pick your color, pick your dates, and go.</p>
          <a href="#/browse" class="btn">Browse cars →</a>
        </article>
        <article class="sv-card reveal">
          <span class="sv-num">02</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg></div>
          <h3>Car Delivery</h3>
          <p>We deliver your rented vehicle to your doorstep anywhere in the city.</p>
          <span class="sv-chip">ONLY ₱500</span>
        </article>
        <article class="sv-card reveal">
          <span class="sv-num">03</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg></div>
          <h3>Chauffeur Service</h3>
          <p>Professional drivers for weddings, business trips, and out-of-town travel.</p>
        </article>
        <article class="sv-card reveal">
          <span class="sv-num">04</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg></div>
          <h3>Full Insurance</h3>
          <p>Every rental includes comprehensive insurance and 24/7 roadside assistance.</p>
          <span class="sv-chip">24/7 SUPPORT</span>
        </article>
        <article class="sv-card reveal">
          <span class="sv-num">05</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg></div>
          <h3>Sanitized Vehicles</h3>
          <p>Every car is deep-cleaned and disinfected before each rental.</p>
        </article>
        <article class="sv-card reveal">
          <span class="sv-num">06</span>
          <div class="sv-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <h3>Multiple Branches</h3>
          <p>Pick up or return at our Cabanatuan, Manila, Clark, or Tarlac branches.</p>
          <span class="sv-chip">4 BRANCHES</span>
        </article>
      </div>
    </div>

    <div class="sv-steps">
      <span class="eyebrow dark">Simple process</span>
      <h2>Rent in three easy steps</h2>
      <div class="sv-steps-row">
        <div class="sv-step reveal"><div class="dot">1</div><h4>Choose your car</h4><p>Browse SUVs, vans and pick-ups, then choose your favorite color.</p></div>
        <div class="sv-step reveal"><div class="dot">2</div><h4>Book online</h4><p>Pick your dates and decide: pick it up or have it delivered.</p></div>
        <div class="sv-step reveal"><div class="dot">3</div><h4>Hit the road</h4><p>Collect the keys, or wait at your door. Cancel anytime from My Bookings.</p></div>
      </div>
    </div>

    <div class="sv-cta">
      <div class="sv-cta-box">
        <div><h3>Ready to roll?</h3><p>Find the right car for your next trip in just a few clicks.</p></div>
        <a href="#/browse" class="btn btn-red">Browse Cars</a>
      </div>
    </div>
  </section>

  <section class="view" id="view-contact">
    <div class="page-head"><h2>Contact Us</h2><p>We're here to help — reach us anytime.</p></div>
    <div class="contact-grid">
      <a class="contact-card" href="tel:09266242649"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div><h3>Reservations</h3><p>0930-659-0249</p><span>Tap to call</span></a>
      <a class="contact-card" href="mailto:automigz@gmail.com"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></div><h3>Email Us</h3><p>automigz@gmail.com</p><span>Tap to email</span></a>
      <a class="contact-card" href="#" onclick="toast('We are open daily 6:00 AM – 10:00 PM');return false;"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><h3>Business Hours</h3><p>Daily 6:00 AM – 10:00 PM</p><span>All branches</span></a>
      <a class="contact-card" href="#" onclick="toast('Main Office: Cabanatuan City, Nueva Ecija');return false;"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/></svg></div><h3>Main Office</h3><p>Cabanatuan City, Nueva Ecija</p><span>Tap for location info</span></a>
      <a class="contact-card" href="#" onclick="toast('Branches: Manila · Clark · Tarlac');return false;"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div><h3>Branches</h3><p>Manila · Clark · Tarlac</p><span>Tap to see list</span></a>
      <a class="contact-card" href="#" onclick="toast('Follow us: Facebook / AutomigzCarExchange');return false;"><div class="s-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.42" x2="8.59" y1="6.51" y2="10.49"/></svg></div><h3>Social Media</h3><p>Facebook / AutomigzCarExchange</p><span>Tap to see page name</span></a>
    </div>
  </section>

</main>

<div class="modal-overlay" id="detailsModal">
  <div class="modal-card" id="detailsCard"></div>
</div>

<div class="modal-overlay" id="verifyModal"><div class="modal-card" id="verifyCard"></div></div>
<div class="toast" id="toast"></div>
<script>
window.AMZ = <?= json_encode($AMZ, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="app.js"></script>
</body>
</html>