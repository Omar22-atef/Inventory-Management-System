
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Inventra</title>

    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* EXACT SAME PREMIUM CSS AS YOUR LOGIN PAGE */
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family:'Inter',sans-serif;
            background:#f8faff;
            color:#1e293b;
            min-height:100vh;
            display:flex;
            flex-direction:column;
            line-height:1.6;
        }

        main {
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:100px 20px 80px;
        }

        .login-card {
            background:white;
            padding:55px;
            border-radius:18px;
            box-shadow:0 15px 50px rgba(67,97,238,.15);
            width:100%;
            max-width:420px;
            text-align:center;
        }

        .card-logo img {
            height:85px;
            margin-bottom:28px;
            object-fit:contain;
        }

        .login-card h2 {
            font-size:26px;
            margin-bottom:30px;
            color:#1e293b;
            font-weight:600;
        }

        /* PERFECT SPACING — EXACTLY LIKE LOGIN */
        .login-card > * + * { margin-top:20px; }

        .input-group {
            margin-bottom:20px;
            text-align:left;
        }

        .input-group input {
            width:100%;
            padding:16px 18px;
            border:2px solid #e2e8f0;
            border-radius:12px;
            font-size:16px;
            transition:all .3s ease;
            background:white;
        }

        .input-group input:focus {
            outline:none;
            border-color:#4361ee;
            box-shadow:0 0 0 4px rgba(67,97,238,.12);
        }

        .btn-primary {
            width:100%;
            padding:17px;
            background:#4361ee;
            color:white;
            border:none;
            border-radius:12px;
            font-size:16.5px;
            font-weight:600;
            cursor:pointer;
            transition:all .35s ease;
            margin:10px 0 20px;
        }

        .btn-primary:hover {
            background:#364fc7;
            transform:translateY(-3px);
            box-shadow:0 10px 25px rgba(67,97,238,.35);
        }

        .text-center a {
            color:#4361ee;
            font-weight:600;
            text-decoration:none;
            font-size:14.5px;
        }

        .text-center a:hover {
            text-decoration:underline;
        }

        /* Success & Error Messages — Beautiful */
        .alert-success {
            background:#dcfce7;
            color:#166534;
            padding:14px 18px;
            border-radius:12px;
            border:1px solid #86b89a;
            font-size:15px;
            margin-bottom:20px;
        }

        .alert-error {
            color:#dc2626;
            font-size:14px;
            margin-top:8px;
            display:block;
        }

        @media (max-width:480px) {
            .login-card { padding:40px 30px; }
            .card-logo img { height:70px; }
        }
    </style>
</head>
<body>

<main>
    <div class="login-card">

        <!-- Logo -->
        <div class="card-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Inventra">
        </div>

        <h2>Forgot Password</h2>

        <!-- Success Message -->
        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="input-group">
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter your email address" required autofocus>

                @error('email')
                    <span class="alert-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                Send Password Reset Link
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}">← Back to Login</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
