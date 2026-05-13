<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Kasir — Pipindonuts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0d0f14;--surface:#161b27;--surface2:#1e2535;--border:#2a3145;--accent:#f97316;--text:#e8eaf0;--muted:#8892a4;--danger:#ef4444;--radius:14px}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}

/* Background orbs */
.orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.15;pointer-events:none}
.orb-1{width:400px;height:400px;background:#f97316;top:-100px;left:-100px}
.orb-2{width:300px;height:300px;background:#7c3aed;bottom:-80px;right:-80px}

.login-card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:40px;width:100%;max-width:400px;position:relative;box-shadow:0 25px 60px rgba(0,0,0,.4)}

/* Logo area */
.logo-area{text-align:center;margin-bottom:32px}
.logo-icon{font-size:52px;line-height:1;margin-bottom:12px;display:block;animation:float 3s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.logo-title{font-size:24px;font-weight:800;color:var(--text)}
.logo-sub{font-size:13px;color:var(--muted);margin-top:4px}

.badge{display:inline-flex;align-items:center;gap:6px;background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.3);color:var(--accent);border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;margin-top:10px}

/* Alert */
.alert{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
.alert-success{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#86efac}

/* Form */
.field{margin-bottom:18px}
.field-label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px}
.field-wrap{position:relative}
.field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none}
.field-input{width:100%;padding:13px 14px 13px 42px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-size:14px;outline:none;transition:.2s;font-family:inherit}
.field-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(249,115,22,.12)}
.field-input::placeholder{color:var(--muted)}
.field-error{font-size:12px;color:#fca5a5;margin-top:6px;display:flex;align-items:center;gap:4px}

/* Submit */
.btn-login{width:100%;padding:14px;background:var(--accent);color:#fff;font-size:15px;font-weight:700;border:none;border-radius:var(--radius);cursor:pointer;transition:.2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:4px}
.btn-login:hover{background:#ea6c0a;transform:translateY(-1px);box-shadow:0 8px 24px rgba(249,115,22,.3)}
.btn-login:active{transform:translateY(0)}

/* Footer */
.login-footer{text-align:center;margin-top:24px;font-size:12px;color:var(--muted)}
</style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="login-card">
    <div class="logo-area">
        <span class="logo-icon">🍩</span>
        <div class="logo-title">Pipindonuts</div>
        <div class="logo-sub">Sistem Point of Sale</div>
        <div class="badge">🖥️ Panel Kasir</div>
    </div>

    {{-- Error --}}
    @if($errors->any())
    <div class="alert">
        ⚠️ {{ $errors->first() }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('kasir.login.post') }}">
        @csrf

        <div class="field">
            <label class="field-label">Email</label>
            <div class="field-wrap">
                <span class="field-icon">✉️</span>
                <input type="email" name="email" class="field-input"
                    placeholder="kasir@pipindonuts.com"
                    value="{{ old('email') }}" autocomplete="email" required>
            </div>
        </div>

        <div class="field">
            <label class="field-label">Password</label>
            <div class="field-wrap">
                <span class="field-icon">🔒</span>
                <input type="password" name="password" class="field-input"
                    placeholder="••••••••" autocomplete="current-password" required>
            </div>
        </div>

        <button type="submit" class="btn-login">
            Masuk ke Panel Kasir →
        </button>
    </form>

    <div class="login-footer">
        Hanya akun dengan role <strong style="color:var(--accent)">Kasir</strong> yang dapat masuk.
    </div>
</div>
</body>
</html>
