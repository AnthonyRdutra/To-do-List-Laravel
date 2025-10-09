@extends('layouts.app', ['title' => 'Login'])

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
    <h4 class="mb-4 text-center">Entrar na Conta</h4>

    <form id="loginForm">
      <div class="mb-3">
        <label for="loginEmail" class="form-label">E-mail</label>
        <input type="email" id="loginEmail" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="loginPassword" class="form-label">Senha</label>
        <input type="password" id="loginPassword" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-dark w-100">Entrar</button>
    </form>

    <div id="msg" class="alert mt-3 d-none" role="alert"></div>

    <hr class="my-3">

    <p class="text-center small mb-0">
      Ainda não tem conta?
      <a href="{{ route('register') }}">Criar conta</a>
    </p>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const msg = document.getElementById('msg');

  // Redireciona se já estiver autenticado
  if (localStorage.getItem('token')) {
    window.location.href = '{{ route('notes.index') }}';
    return;
  }

  const showMsg = (text, type = 'info') => {
    msg.classList.remove('d-none', 'alert-info', 'alert-danger', 'alert-success');
    msg.classList.add('alert-' + type);
    msg.innerText = text;
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.classList.add('d-none');

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    console.log("Enviando dados de login:", { email });

    try {
      baseUrl = window.location.origin + '/api/singup';
      const res = await fetch(baseUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password }),
      });

      console.log("Resposta recebida:", res.status, res.redirected);

      const text = await res.text();
      console.log("Corpo da resposta:", text);

      if (!res.ok) throw new Error('Erro HTTP: ' + res.status);

      const data = JSON.parse(text);
      console.log("JSON parseado:", data);

      if (data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('email', email);
        showMsg('Login realizado com sucesso!', 'success');
        setTimeout(() => window.location.href = '{{ route('notes.index') }}', 1500);
      } else {
        showMsg(data.message || 'Credenciais inválidas.', 'danger');
      }

    } catch (err) {
      console.error("Erro no envio:", err);
      showMsg('Erro ao conectar ao servidor.', 'danger');
    }
  });
});
</script>
@endpush
