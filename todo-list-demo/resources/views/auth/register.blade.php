@extends('layouts.app', ['title' => 'Registrar'])

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
    <h4 class="mb-4 text-center">Criar Conta</h4>

    <form id="registerForm">
      <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" id="name" name="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-dark w-100">Criar Conta</button>
    </form>

    <div id="msg" class="alert mt-3 d-none" role="alert"></div>

    <hr class="my-3">
    <p class="text-center small mb-0">
      Já tem conta? <a href="{{ route('login') }}">Entrar</a>
    </p>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  const msg = document.getElementById('msg');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.classList.add('d-none');
    msg.classList.remove('alert-success', 'alert-danger');
    msg.innerText = '';

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    console.log("🔄 Enviando dados para API:", { name, email });

    try {
      baseUrl = window.location.origin + '/api/singup';
      const res = await fetch( baseUrl , {
        method: 'POST',
        headers: { 
          'Accept': 'application/json',
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({ name, email, password }),
      });

      console.log("📥 Resposta recebida:", res.status, res.redirected);

      if (!res.ok) {
        throw new Error('Erro HTTP: ' + res.status);
      }

      const data = await res.json();
      console.log("JSON:", data);

      if (data.success) {
        msg.classList.remove('d-none');
        msg.classList.add('alert-success');
        msg.innerText = data.message || 'Conta criada com sucesso!';

        // redirecionar após 2 segundos
        setTimeout(() => window.location.href = "{{ route('login') }}", 2000);
      } else {
        msg.classList.remove('d-none');
        msg.classList.add('alert-danger');
        msg.innerText = data.message || 'Falha ao criar conta.';
      }

    } catch (err) {
      console.error("Erro no envio:", err);
      msg.classList.remove('d-none');
      msg.classList.add('alert-danger');
      msg.innerText = 'Erro ao conectar ao servidor.';
    }
  });
});
</script>
@endpush
