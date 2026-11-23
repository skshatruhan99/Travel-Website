<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', \App\Models\Setting::value('meta_title', 'NpAdventure'))</title>

    <meta name="description"
          content="{{ \App\Models\Setting::value('meta_description', 'NpAdventure - Discover beautiful destinations, tours, hotels and travel experiences.') }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('npadventure-mark.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/roamvoyage.css') }}">

    @yield('head')
</head>

<body>

<header class="site-header">

    <div class="container nav-wrapper">

        <a href="{{ route('home') }}" class="logo">
            <img class="logo-icon" src="{{ asset('npadventure-mark.svg') }}" alt="">
            <span>{{ \App\Models\Setting::value('site_name', 'NpAdventure') }}</span>
        </a>

        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="main-nav" id="mainNav">

            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'active' : '' }}">
                Home
            </a>

            <a href="{{ route('destination') }}"
               class="{{ request()->routeIs('destination') ? 'active' : '' }}">
                Destinations
            </a>

            <a href="{{ route('tour') }}"
               class="{{ request()->routeIs('tour') ? 'active' : '' }}">
                Tours
            </a>

            <a href="{{ route('hotels') }}"
               class="{{ request()->routeIs('hotels') ? 'active' : '' }}">
                Hotels
            </a>
            <a href="{{ route('journal') }}"
               class="{{ request()->routeIs('journal') ? 'active' : '' }}">
                Journal
            </a>

            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'active' : '' }}">
                About
            </a>

            <a href="{{ route('contact') }}"
               class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                Contact
            </a>

        </nav>

        <div class="nav-actions">

            @auth
                <details class="account-menu">
                    <summary class="btn btn-outline" aria-label="Account options" title="Account">
                        <i class="fa-regular fa-user"></i>
                        Account
                    </summary>
                    <div class="account-menu-links">
                        <a href="{{ route('account') }}">My Account</a>
                        <a href="{{ route('profile') }}">Profile</a>
                        <form action="{{ route('logout') }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to logout?');">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    </div>
                </details>
            @else
                <details class="account-menu">
                    <summary class="btn btn-outline" aria-label="Account menu">
                        <i class="fa-regular fa-user"></i>
                        Account
                    </summary>
                    <div class="account-menu-links">
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                        <a href="{{ route('profile') }}">Profile</a>
                    </div>
                </details>
            @endauth

            @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline admin-access-link" aria-label="Open admin dashboard" title="Admin dashboard">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i><span>Admin</span>
                </a>
            @elseif(app()->environment('local') && !\App\Models\User::where('is_admin', true)->exists())
                <a href="{{ route('admin.setup') }}" class="btn btn-outline admin-access-link" aria-label="Set up admin access" title="Admin setup">
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i><span>Admin setup</span>
                </a>
            @endif

            <a href="{{ route('planner') }}" class="btn btn-primary">
                Plan Trip
            </a>

        </div>

    </div>

</header>


<main>
    @yield('content')
</main>


<footer class="site-footer">

    <div class="container footer-grid">

        <div class="footer-about">

            <a href="{{ route('home') }}" class="logo footer-logo">
                <img class="logo-icon" src="{{ asset('npadventure-mark.svg') }}" alt="">
                <span>{{ \App\Models\Setting::value('site_name', 'NpAdventure') }}</span>
            </a>

            <p>
                {{ \App\Models\Setting::value('meta_description', 'Discover amazing places, unforgettable experiences and journeys made for you.') }}
            </p>

            <div class="social-links">

                <a href="{{ route('contact') }}" aria-label="Contact us on Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="{{ route('contact') }}" aria-label="Contact us on Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a href="{{ route('contact') }}" aria-label="Contact us on X">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>

                <a href="{{ route('contact') }}" aria-label="Contact us on YouTube">
                    <i class="fa-brands fa-youtube"></i>
                </a>

            </div>

        </div>


        <div>
            <h4>Explore</h4>

            <a href="{{ route('destination') }}">Destinations</a>
            <a href="{{ route('tour') }}">Tours</a>
            <a href="{{ route('hotels') }}">Hotels</a>
            <a href="{{ route('activities') }}">Activities</a>
        </div>


        <div>
            <h4>Company</h4>

            <a href="{{ route('about') }}">About Us</a>
            <a href="{{ route('journal') }}">Travel Journal</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a href="{{ route('faq') }}">FAQ</a>
        </div>


        <div>
            <h4>Support</h4>

            <a href="{{ route('account') }}">My Account</a>
            <a href="{{ route('booking') }}">My Booking</a>
            <a href="{{ route('profile') }}">Profile</a>
            <a href="{{ route('contact') }}">Help Center</a>
        </div>

        <div class="footer-newsletter">
            <h4>Newsletter</h4>
            <p>Get travel inspiration and updates in your inbox.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="Your email address" maxlength="255" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
            @if(session('newsletter_success'))
                <p class="badge badge-green" role="status">{{ session('newsletter_success') }}</p>
            @endif
            @if($errors->has('email'))
                <p class="badge badge-red" role="alert">{{ $errors->first('email') }}</p>
            @endif
        </div>

    </div>


    <div class="container footer-bottom">

        <p>© {{ date('Y') }} {{ \App\Models\Setting::value('site_name', 'NpAdventure') }}. All rights reserved.</p>

        <div>
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms & Conditions</a>
            <a href="{{ route('photo-credits') }}">Photo credits</a>
        </div>

    </div>

</footer>

<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif>
        <i class="fa-solid fa-house" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('destination') }}" class="{{ request()->routeIs('destination*') ? 'active' : '' }}" @if(request()->routeIs('destination*')) aria-current="page" @endif>
        <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
        <span>Places</span>
    </a>
    <a href="{{ route('tour') }}" class="{{ request()->routeIs('tour*') ? 'active' : '' }}" @if(request()->routeIs('tour*')) aria-current="page" @endif>
        <i class="fa-solid fa-route" aria-hidden="true"></i>
        <span>Tours</span>
    </a>
    <a href="{{ route('hotels') }}" class="{{ request()->routeIs('hotels', 'hotel.*') ? 'active' : '' }}" @if(request()->routeIs('hotels', 'hotel.*')) aria-current="page" @endif>
        <i class="fa-solid fa-hotel" aria-hidden="true"></i>
        <span>Hotels</span>
    </a>
    <a href="{{ auth()->check() ? route('account') : route('login') }}" class="{{ request()->routeIs('account', 'profile') ? 'active' : '' }}" @if(request()->routeIs('account', 'profile')) aria-current="page" @endif>
        <i class="fa-regular fa-user" aria-hidden="true"></i>
        <span>Account</span>
    </a>
</nav>

<div id="toast" class="toast">
    <i class="fa-solid fa-circle-check"></i>
    <span>Action completed successfully.</span>
</div>


<script src="{{ asset('js/roamvoyage.js') }}"></script>

@yield('scripts')

</body>
</html>