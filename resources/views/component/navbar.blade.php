<nav class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="{{ route('admin.dashboard') }}" class="logo">
            Student<span>Manage</span>
        </a>

        <!-- Navigation -->
        <ul class="nav-links">

            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    Home
                </a>
            </li>

            <li>
                <a href="{{ route('about') }}"
                   class="{{ request()->routeIs('about') ? 'active' : '' }}">
                    About Us
                </a>
            </li>

            <li>
                <a href="{{ route('contact') }}"
                   class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                    Contact
                </a>
            </li>

        </ul>

        <!-- Login -->
        <a href="{{ route('login') }}" class="login-btn">
            Login
        </a>

    </div>
</nav>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f7fb;
    }

    .navbar {
        width: 100%;
        background: #111827;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
    }

    .nav-container {
        max-width: 1200px;
        margin: auto;
        padding: 0 30px;
        height: 70px;

        display: flex;
        align-items: center;
    }

    .logo {
        font-size: 24px;
        font-weight: 700;
        text-decoration: none;
        color: white;
    }

    .logo span {
        color: #38bdf8;
    }

    .nav-links {
        display: flex;
        list-style: none;
        gap: 35px;
        margin-left: auto;
        margin-right: 35px;
    }

    .nav-links a {
        color: #d1d5db;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
        padding: 8px 0;
        transition: 0.3s;
    }

    .nav-links a:hover {
        color: #38bdf8;
    }

    .nav-links a.active {
        color: #38bdf8;
    }

    .login-btn {
        text-decoration: none;
        background: #38bdf8;
        color: #0f172a;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }

    .login-btn:hover {
        background: #0ea5e9;
        color: white;
    }

    @media (max-width: 768px) {
        .nav-container {
            height: auto;
            padding: 20px;
            flex-direction: column;
            gap: 20px;
        }

        .nav-links {
            margin: 0;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .login-btn {
            margin-bottom: 5px;
        }
    }
</style>
