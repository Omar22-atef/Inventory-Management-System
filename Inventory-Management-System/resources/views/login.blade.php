<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login — Inventra</title>

    <!-- Load CSS & JS via Vite -->
    @vite(['resources/css/login.css', 'resources/js/login.js'])
</head>
<body>

<header class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="{{ asset('Images/Screenshot 2025-11-21 145929.png') }}" alt="Inventra Logo">
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a class="active" href="{{ route('login') }}">User Login</a></li>
                <li><a href="#">View Inventory</a></li>
                <li><a href="#">Supplier Management</a></li>
                <li><a href="#">Reports</a></li>
            </ul>
        </nav>

        <div class="auth-buttons">
            <a href="{{ route('register') }}" class="register-btn">Register</a>
            <a href="{{ route('login') }}" class="login-btn">Log In</a>
        </div>
    </div>
</header>

<main>
    <div class="login-card">

        <div class="card-logo">
            <img src="{{ asset('Images/Screenshot 2025-11-21 145929.png') }}" alt="Inventra">
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="input-group">
                <input type="email" name="email" placeholder="Email">
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password">
            </div>

            <button type="submit" class="btn-primary">Log in</button>
        </form>

        <!-- ONLY THIS PART CHANGED – NOW BOTH LINKS ARE TOGETHER UNDER THE BUTTON -->
        <div class="text-center mt-5 space-y-2">
            <div class="lost-password">
                <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
            </div>
            <p>
                <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline text-sm">
                    Forgot Your Password?
                </a>
            </p>
        </div>

        <div class="divider">
            <span>Or continue with</span>
        </div>

        <button class="google-btn">
            <svg width="20" height="20" viewBox="0 0 46 46" xmlns="http://www.w3.org/2000/svg">
                <path d="M46 24.881c0-1.56-.14-3.06-.4-4.5H23.5v8.51h12.62c-.55 2.95-2.21 5.44-4.63 7.1v5.97h7.48c4.38-4.03 6.9-9.97 6.9-17.08z" fill="#4285F4"/>
                <path d="M23.5 46.5c6.24 0 11.47-2.07 15.3-5.6l-7.48-5.97c-2.07 1.39-4.73 2.21-7.82 2.21-6.01 0-11.1-4.05-12.92-9.5H4.88v6c3.8 7.5 11.62 12.86 20.Label 20.62 12.86z" fill="#34A853"/>
                <path d="M10.58 27.66c-.93-2.74-.93-5.66 0-8.4v-6h-7.7c-2.8 5.6-2.8 12.2 0 17.8l7.7-6z" fill="#FBBC05"/>
                <path d="M23.5 9.24c3.39-.04 6.62 1.24 9.08 3.57l6.8-6.8C34.76 1.82 29.53-.42 23.5.08 14.5.08 6.68 5.44 2.88 13l7.7 6c1.82-5.45 6.91-9.5 12.92-9.5z" fill="#EA4335"/>
            </svg>
            Google
        </button>

    </div>
</main>

</body>
</html>
