<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Sticky Notes' }}</title>

  {{-- Bootstrap 5 (CDN) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background: #f8f9fa; }
    .note-card {
      min-height: 150px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: transform 0.1s ease;
      cursor: pointer;
    }
    .note-card:hover { transform: scale(1.02); }
  </style>

  @stack('styles')
</head>
<body>
  <nav class="navbar navbar-light bg-white border-bottom mb-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-semibold" href="{{ route('notes.index') }}">🗒️ Sticky Notes</a>
      <button id="logoutBtn" class="btn btn-outline-secondary btn-sm">Sair</button>
    </div>
  </nav>

  <main class="container py-3">
    @yield('content')
  </main>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- Script global de auth --}}
  <script>
    const API_BASE = '/api';
    const getToken = () => localStorage.getItem('token');
    const clearToken = () => localStorage.removeItem('token');

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      clearToken();
      localStorage.removeItem('email');
      window.location.href = '{{ route('login') }}';
    });
  </script>

  @stack('scripts')
</body>
</html>
