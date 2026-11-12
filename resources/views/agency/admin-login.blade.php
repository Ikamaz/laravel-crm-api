<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agency CRM — Sign in</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b1220;color:#f4f7fb;font:15px/1.5 system-ui,sans-serif;padding:20px}.panel{width:min(420px,100%);background:#111c2e;border:1px solid #293b53;border-radius:18px;padding:30px}.brand{font-weight:800;letter-spacing:-.04em;font-size:19px}.brand span{color:#67a8ff}h1{font-size:28px;letter-spacing:-.04em;margin:28px 0 5px}.muted{color:#9aabc1;font-size:13px;margin:0 0 22px}label{display:block;font-size:12px;font-weight:700;margin:15px 0 6px}input{width:100%;border:1px solid #334760;border-radius:9px;background:#0b1626;color:white;padding:12px;font:inherit}.remember{display:flex;gap:8px;align-items:center;color:#aab9cb;font-size:12px;margin:15px 0}.remember input{width:auto}.btn{width:100%;border:0;border-radius:9px;background:#67a8ff;color:#07111f;font-weight:800;padding:13px;font:inherit;cursor:pointer}.error{color:#ffb4b4;font-size:12px;margin-top:12px}.back{display:inline-block;margin-top:20px;color:#aab9cb;font-size:12px}
    </style>
</head>
<body>
    <main class="panel">
        <div class="brand">DIGITAL<span>STUDIO</span></div>
        <h1>Agency CRM</h1>
        <p class="muted">Sign in with an administrator account to manage enquiries.</p>
        <form method="POST" action="{{ route('agency.admin.login.submit') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="btn" type="submit">Sign in</button>
            @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
        </form>
        <a class="back" href="{{ route('agency.home.ka') }}">← Back to website</a>
    </main>
</body>
</html>
