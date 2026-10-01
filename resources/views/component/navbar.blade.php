<nav class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="logo">
            Student<span>Manage</span>
        </a>

        <!-- Navigation -->
        <div class="nav-links">

            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home', 'students.*') ? 'active' : '' }}">
                Home
            </a>

            <a href="{{ route('about') }}"
               class="{{ request()->routeIs('about') ? 'active' : '' }}">
                About Us
            </a>

            <a href="{{ route('contact') }}"
               class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                Contact Us
            </a>

        </div>

    </div>
</nav>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f7fb;
    }

    .navbar {
        background: #198754;
        width: 100%;
    }

    .nav-container {
        max-width: 1200px;
        height: 65px;
        margin: auto;
        padding: 0 25px;

        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        color: white;
        text-decoration: none;
        font-size: 23px;
        font-weight: bold;
    }

    .logo span {
        color: #d1fae5;
    }

    .nav-links {
        display: flex;
        gap: 30px;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        font-size: 16px;
    }

    .nav-links a:hover,
    .nav-links a.active {
        color: #d1fae5;
    }
</style>
