<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register — Inventra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/register.css', 'resources/js/register.js'])
</head>

<body>

<header class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="{{ asset('images/Screenshot 2025-11-21 145929.png') }}" alt="Inventra Logo">
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ route('login') }}">User Login</a></li>
                <li><a href="{{ url('/inventory') }}">View Inventory</a></li>
                <li><a href="{{ url('/suppliers') }}">Supplier Management</a></li>
                <li><a href="{{ url('/reports') }}">Reports</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <a href="{{ route('register') }}" class="register-btn active">Register</a>
            <a href="{{ route('login') }}" class="login-btn">Log In</a>
        </div>
    </div>
</header>

<main>
    <div class="login-card">

        <div class="card-logo">
            <img src="{{ asset('images/Screenshot 2025-11-21 145929.png') }}" alt="Inventra">
        </div>

        <form novalidate action="{{ route('register') }}" method="POST">
            @csrf

            <div class="input-group">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name"/>
            </div>

            <div class="input-group">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"/>
            </div>

            <!-- PASSWORD -->
            <div class="input-group">
                <input 
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    autocomplete="new-password"
                />
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="input-group">
                <input 
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Confirm Password"
                />

                <!-- Confirmation error goes right here -->
                <span id="confirm-error" class="text-red-600 text-sm block mt-2"></span>
            </div>

            <!-- PASSWORD REQUIREMENTS -->
            <div id="password-requirements" class="mt-3 text-sm text-left space-y-2">
                <div class="flex items-center gap-3 password-condition">
                    <span id="length" class="text-red-600 text-lg font-bold">✘</span>
                    <span>At least 8 characters</span>
                </div>

                <div class="flex items-center gap-3 password-condition">
                    <span id="uppercase" class="text-red-600 text-lg font-bold">✘</span>
                    <span>One capital letter (A–Z)</span>
                </div>

                <div class="flex items-center gap-3 password-condition">
                    <span id="special" class="text-red-600 text-lg font-bold">✘</span>
                    <span>One special character #, $, @, !, etc.</span>
                </div>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form novalidate>

        <div class="lost-password">
            <p>Already have an account? <a href="{{ route('login') }}">Log In</a></p>
        </div>

        <div class="divider"><span>Or continue with</span></div>

        <button class="google-btn" onclick="window.location.href='{{ url('/auth/google') }}'">
            Continue with Google
        </button>

    </div>
</main>

</body>
</html>
