@extends('layouts.app', ['title' => 'Sticky Notes'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-semibold mb-0">Minhas Notas</h4>
  <button id="newNoteBtn" class="btn btn-dark btn-sm">Nova Nota</button>
</div>

<div id="notesContainer" class="row g-3">
  <div class="text-center text-muted py-5" id="emptyState">Nenhuma nota ainda.</div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nova Nota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="noteForm">
          <input type="hidden" id="noteId">
          <div class="mb-3">
            <label for="title" class="form-label">Título</label>
            <input type="text" id="title" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="content" class="form-label">Conteúdo</label>
            <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
          </div>
          <div id="saveStatus" class="text-muted small text-center mb-2 d-none"></div>
          <button class="btn btn-dark w-100" type="submit">Salvar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (!token) {
      alert('Sessão expirada. Faça login novamente.');
      location.href = '/login';
      return;
    }

    const apiBase = '/api/notes';
    const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
    const form = document.getElementById('noteForm');
    const saveStatus = document.getElementById('saveStatus');
    const container = document.getElementById('notesContainer');
    const emptyState = document.getElementById('emptyState');

    // overlay de foco
    const focusOverlay = document.createElement('div');
    focusOverlay.id = 'focusOverlay';
    document.body.appendChild(focusOverlay);

    // estilo do overlay e animação
    const style = document.createElement('style');
    style.textContent = `
    #focusOverlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1055;
    }
    #focusOverlay.active {
      display: flex;
      animation: fadeIn 0.3s ease-in-out;
    }
    #focusOverlay .focused-note {
      transform: scale(1);
      transition: transform 0.3s ease;
      max-width: 600px;
      width: 90%;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      overflow: hidden;
    }
    #focusOverlay .focused-note .card-body {
      padding: 1.5rem;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  `;
    document.head.appendChild(style);

    async function apiFetch(url, options = {}) {
      const res = await fetch(url, {
        ...options,
        headers: {
          'Authorization': 'Bearer ' + token,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(options.headers || {})
        }
      });

      const raw = await res.text();
      let data = null;

      const start = raw.indexOf('{');
      const end = raw.lastIndexOf('}');
      if (start !== -1 && end !== -1) {
        try {
          data = JSON.parse(raw.substring(start, end + 1));
        } catch (_) {}
      }

      if (!res.ok) {
        const msg = data?.message || data?.error || `HTTP ${res.status}`;
        const err = new Error(`Erro ${res.status}: ${msg}`);
        err.status = res.status;
        err.data = data || raw;
        throw err;
      }
      return data;
    }

    async function loadNotes() {
      container.innerHTML = '<div class="text-center text-muted py-5">Carregando notas...</div>';
      try {
        const data = await apiFetch(apiBase);
        if (!data || !data.notes) {
          container.innerHTML = `<div class="text-danger text-center py-5">Nenhuma nota encontrada.</div>`;
          return;
        }

        container.innerHTML = '';
        emptyState.classList.add('d-none');

        data.notes.forEach(note => {
          const col = document.createElement('div');
          col.className = 'col-12 col-sm-6 col-md-4';
          col.innerHTML = `
          <div class="card h-100 shadow-sm border-0 note-card" data-note-id="${note.id}">
            <div class="card-body">
              <h5 class="fw-semibold">${note.title}</h5>
              <p class="text-muted small">${note.content}</p>
              <p class="text-secondary small mb-0">${new Date(note.created_at).toLocaleString('pt-BR')}</p>
            </div>
          </div>
        `;

          // clique: exibir em foco (zoom centralizado)
          col.querySelector('.note-card').addEventListener('click', () => {
            showFocusedNote(note);
          });

          container.appendChild(col);
        });

      } catch (err) {
        console.error('Erro ao carregar notas:', err);
        container.innerHTML = `<div class="text-danger text-center py-5">Falha ao carregar notas.</div>`;
      }
    }

    function showFocusedNote(note) {
      focusOverlay.innerHTML = `
    <div class="focused-note">
      <div class="card-body">
        <h4 class="fw-bold mb-3">${note.title}</h4>
        <p class="text-muted">${note.content}</p>
        <p class="text-secondary small mt-3">
          ${new Date(note.created_at).toLocaleString('pt-BR')}
        </p>
        <div class="mt-3 d-flex justify-content-end gap-2">
          <button class="btn btn-outline-dark btn-sm" id="editNoteBtn">
            Editar
          </button>
          <button class="btn btn-outline-danger btn-sm" id="deleteNoteBtn">
            Excluir
          </button>
        </div>
      </div>
    </div>
  `;
      focusOverlay.classList.add('active');

      // editar nota
      document.getElementById('editNoteBtn').addEventListener('click', () => {
        focusOverlay.classList.remove('active');
        document.getElementById('modalTitle').textContent = 'Editar Nota';
        document.getElementById('title').value = note.title;
        document.getElementById('content').value = note.content;

        // remove listeners antigos para evitar duplo submit
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);
        newForm.querySelector('#title').value = note.title;
        newForm.querySelector('#content').value = note.content;

        const saveStatus = document.getElementById('saveStatus');
        const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));

        // adiciona botão "Cancelar Edição"
        let cancelBtn = newForm.querySelector('.cancelEditBtn');
        if (!cancelBtn) {
          cancelBtn = document.createElement('button');
          cancelBtn.type = 'button';
          cancelBtn.className = 'btn btn-outline-secondary w-100 mt-2 cancelEditBtn';
          cancelBtn.textContent = 'Cancelar Edição';
          cancelBtn.addEventListener('click', () => {
            newForm.reset();
            cancelBtn.remove();
            document.getElementById('modalTitle').textContent = 'Nova Nota';
            noteModal.hide();
            // restaura listener padrão após cancelar
            location.reload();
          });
          newForm.appendChild(cancelBtn);
        }

        // novo submit (PUT)
        newForm.addEventListener('submit', async (e) => {
          e.preventDefault();

          const payload = {
            title: document.getElementById('title').value.trim(),
            content: document.getElementById('content').value.trim(),
          };

          saveStatus.classList.remove('d-none');
          saveStatus.textContent = 'Atualizando...';

          try {
            await apiFetch(`${apiBase}/${note.id}`, {
              method: 'PUT',
              body: JSON.stringify(payload),
            });

            saveStatus.textContent = 'Nota atualizada!';
            setTimeout(() => {
              saveStatus.classList.add('d-none');
              newForm.reset();
              cancelBtn.remove();
              document.getElementById('modalTitle').textContent = 'Nova Nota';
              noteModal.hide();
              loadNotes();
            }, 800);
          } catch (err) {
            console.error('Erro ao atualizar nota:', err);
            saveStatus.textContent = 'Erro ao atualizar.';
            setTimeout(() => saveStatus.classList.add('d-none'), 2500);
          }
        });

        noteModal.show();
      });

      // excluir nota
      document.getElementById('deleteNoteBtn').addEventListener('click', () => {
        deleteNote(note.id);
      });
    }


    async function deleteNote(noteId) {
      if (!confirm('Deseja realmente excluir esta nota?')) return;

      try {
        const res = await apiFetch(`${apiBase}/${noteId}`, {
          method: 'DELETE',
        });

        if (res.success) {
          focusOverlay.classList.remove('active');
          await loadNotes();
          alert('Nota excluída com sucesso!');
        } else {
          alert('Erro ao excluir a nota.');
        }
      } catch (err) {
        console.error('Erro ao excluir nota:', err);
        const msg = err.data?.message || 'Falha ao excluir a nota.';
        alert(msg);
      }
    }

    // fecha foco ao clicar fora ou pressionar ESC
    focusOverlay.addEventListener('click', (e) => {
      if (e.target.id === 'focusOverlay') {
        focusOverlay.classList.remove('active');
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        focusOverlay.classList.remove('active');
      }
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const titleEl = document.getElementById('title');
      const contentEl = document.getElementById('content');
      const payload = {
        title: (titleEl.value || '').trim(),
        content: (contentEl.value || '').trim(),
      };

      if (!payload.title || !payload.content) {
        saveStatus.classList.remove('d-none');
        saveStatus.textContent = 'Preencha título e conteúdo.';
        setTimeout(() => saveStatus.classList.add('d-none'), 1500);
        return;
      }

      saveStatus.classList.remove('d-none');
      saveStatus.textContent = 'Salvando...';

      try {
        await apiFetch(apiBase, {
          method: 'POST',
          body: JSON.stringify(payload),
        });

        saveStatus.textContent = 'Nota salva com sucesso!';
        setTimeout(() => {
          saveStatus.classList.add('d-none');
          form.reset();
          noteModal.hide();
          loadNotes();
        }, 800);
      } catch (err) {
        console.error('Erro ao salvar nota:', err);
        if (err.status === 422 && err.data?.errors) {
          const first = Object.values(err.data.errors)[0]?.[0] || 'Erro de validação.';
          saveStatus.textContent = first;
        } else {
          saveStatus.textContent = (err.data?.message) ?
            `Erro: ${err.data.message}` :
            'Erro ao salvar a nota.';
        }
        setTimeout(() => saveStatus.classList.add('d-none'), 2500);
      }
    });

    document.getElementById('newNoteBtn').addEventListener('click', () => {
      // Garante que o modo de edição seja desativado
      isEditing = false;
      currentNoteId = null;

      form.reset();
      document.getElementById('modalTitle').textContent = 'Nova Nota';
      saveStatus.classList.add('d-none');
      noteModal.show();
    });

    loadNotes();
  });
</script>
@endpush