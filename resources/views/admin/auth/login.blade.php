<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Gram Panchayat</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f5f9;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: #1a365d;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        
        .login-logo i {
            font-size: 28px;
            color: #ffffff;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 14px;
            color: #64748b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font-size: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1a365d;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #475569;
            cursor: pointer;
        }
        
        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: #1a365d;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background: #1a365d;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-login:hover {
            background: #0f2440;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #64748b;
        }
        
        .back-link a {
            color: #1a365d;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1>Admin Login</h1>
                <p>Enter your credentials to access the admin panel</p>
            </div>
            
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            
            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>
            
            <p class="back-link">
                <a href="{{ route('home') }}"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </p>
        </div>
    </div>
</body>
</html>
