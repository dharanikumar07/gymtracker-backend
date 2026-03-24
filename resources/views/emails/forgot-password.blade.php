<h1>Forget Password Email</h1>
<p>You can reset password from bellow link:</p>
<a href="{{ env('FRONTEND_URL', 'http://localhost:6500') }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}">Reset Password</a>
