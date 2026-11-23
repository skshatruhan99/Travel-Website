<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', 'Admin | NpAdventure')</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
	<link rel="stylesheet" href="{{ asset('css/roamvoyage.css') }}">
</head>
<body class="admin-body">
	<div class="admin-shell">
		<aside class="admin-sidebar">
			<a href="{{ route('admin.dashboard') }}" class="admin-brand">
				<img class="admin-brand-mark" src="{{ asset('npadventure-mark.svg') }}" alt="">
				<span>NpAdventure<small>ADMINISTRATION</small></span>
			</a>
			<p class="admin-nav-label">Workspace</p>
			<nav class="admin-nav" aria-label="Admin navigation">
				<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-chart-pie"></i><span>Overview</span></a>
				<a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings') ? 'active' : '' }}"><i class="fa-regular fa-calendar-check"></i><span>Bookings</span></a>
				<a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><i class="fa-regular fa-envelope"></i><span>Contact messages</span></a>
				<a href="{{ route('admin.newsletter-subscribers.index') }}" class="{{ request()->routeIs('admin.newsletter-subscribers.*') ? 'active' : '' }}"><i class="fa-regular fa-paper-plane"></i><span>Newsletter</span></a>
				<a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'active' : '' }}"><i class="fa-solid fa-credit-card"></i><span>Payments</span></a>
				<a href="{{ route('admin.reviews') }}" class="{{ request()->routeIs('admin.reviews*') ? 'active' : '' }}"><i class="fa-solid fa-star"></i><span>Reviews</span></a>
				<a href="{{ route('admin.journal.index') }}" class="{{ request()->routeIs('admin.journal.*') ? 'active' : '' }}"><i class="fa-solid fa-book-open"></i><span>Journal</span></a>
				<a href="{{ route('admin.faqs.index') }}" class="{{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"><i class="fa-regular fa-circle-question"></i><span>FAQs</span></a>
				<a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i><span>Coupons</span></a>
				<a href="{{ route('admin.tours.index') }}" class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}"><i class="fa-solid fa-route"></i><span>Tours</span></a>
				<a href="{{ route('admin.hotels.index') }}" class="{{ request()->routeIs('admin.hotels.*') ? 'active' : '' }}"><i class="fa-solid fa-hotel"></i><span>Hotels</span></a>
				<a href="{{ route('admin.destinations.index') }}" class="{{ request()->routeIs('admin.destinations.*') ? 'active' : '' }}"><i class="fa-solid fa-location-dot"></i><span>Destinations</span></a>
				<a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Customers</span></a>
				<a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
			</nav>
			<div class="admin-sidebar-bottom">
				<a href="{{ route('home') }}"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>View website</span></a>
				<div class="admin-user">
					<span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
					<span class="admin-user-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span>
					<form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" title="Log out" aria-label="Log out"><i class="fa-solid fa-arrow-right-from-bracket"></i></button></form>
				</div>
			</div>
		</aside>
		<main class="admin-main">
			<header class="admin-topbar">
				<div><span class="admin-eyebrow">TRAVEL OPERATIONS</span><p>Good day, {{ auth()->user()->name }}</p></div>
				<div class="admin-topbar-actions">
					<a class="admin-view-site" href="{{ route('home') }}"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a>
					<form action="{{ route('logout') }}" method="POST">@csrf<button class="admin-mobile-logout" type="submit" aria-label="Log out" title="Log out"><i class="fa-solid fa-arrow-right-from-bracket"></i></button></form>
				</div>
			</header>
			@if(session('success'))
				<div class="admin-alert" role="status"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>
			@endif
			@yield('admin-content')
		</main>
	</div>
</body>
</html>
