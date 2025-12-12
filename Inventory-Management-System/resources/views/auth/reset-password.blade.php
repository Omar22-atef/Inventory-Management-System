<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8faff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background: white;
            padding: 50px 55px;
            border-radius: 18px;
            box-shadow: 0 15px 50px rgba(67, 97, 238, 0.15);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        h2 {
            font-size: 26px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 36px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group input {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.12);
        }

        .input-group input::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            padding: 16px;
            background: #4361ee;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.35s ease;
            margin-top: 8px;
        }

        button:hover {
            background: #364fc7;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.35);
        }

        button:active {
            transform: translateY(-1px);
        }

        /* Error messages (red text under inputs) */
        .text-danger {
            color: #ef4444;
            font-size: 14px;
            margin-top: 6px;
            display: block;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Reset Password</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div class="input-group">
            <input type="email" name="email" value="{{ request()->email }}" placeholder="Email address" required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- New Password -->
        <div class="input-group">
            <input type="password" name="password" placeholder="New Password" required>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        </div>

        <button type="submit">Reset Password</button>
    </form>
</div>

</body>
</html>
