<?php
// Reusable modal component - markup + styles + minimal JS API (showAppModal / closeAppModal)
?>
<!-- Modal component -->
<div id="app-modal" class="modal-overlay" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="app-modal-title">
    <div class="modal-header">
      <h3 id="app-modal-title" class="modal-title">Aviso</h3>
    </div>
    <div class="modal-body" id="app-modal-body">Contenido</div>
    <div class="modal-actions">
      <button class="btn-modal ghost" id="app-modal-cancel" type="button">Cancelar</button>
      <button class="btn-modal primary" id="app-modal-confirm" type="button">Continuar</button>
    </div>
  </div>
</div>

<style>
/* ====== Minimal styles (paleta compatible con tu UI) ====== */
:root{
  --coffee:#574737; --terracotta:#C7764A; --beige:#fff;
  --overlay: rgba(0,0,0,.44); --ring: rgba(199,118,74,.35);
}
.modal-overlay{
  position:fixed; inset:0; display:none;
  align-items:center; justify-content:center;
  background:var(--overlay); z-index:2000;
  padding:16px;
}
.modal-overlay.active{ display:flex; }

.modal-card{
  background:var(--beige);
  width:min(92vw, 420px);
  border-radius:16px;
  box-shadow:0 20px 40px rgba(0,0,0,.22);
  border:1px solid rgba(87,71,55,.08);
  overflow:hidden;
  transform: translateY(8px) scale(.98);
  opacity:0;
  transition:transform .18s ease, opacity .18s ease;
}
.modal-overlay.active .modal-card{
  transform: translateY(0) scale(1);
  opacity:1;
}

.modal-header{ padding:18px 22px 6px 22px; }
.modal-title{
  margin:0; color:var(--coffee); font-size:1.15rem; letter-spacing:.02em;
}

.modal-body{
  padding:0 22px 4px 22px; color:#5f5346; line-height:1.45;
}

.modal-actions{
  display:flex; gap:10px; justify-content:flex-end;
  padding:16px 22px 18px 22px;
}

.btn-modal{
  border:none; cursor:pointer; font-weight:700;
  padding:10px 16px; border-radius:10px;
  transition:filter .15s ease, box-shadow .15s ease;
}
.btn-modal.primary{ background:var(--terracotta); color:#fff; }
.btn-modal.primary:focus{ outline:0; box-shadow:0 0 0 3px var(--ring); }
.btn-modal.primary:hover{ filter:brightness(.95); }

.btn-modal.ghost{
  background:#efeae5; color:#5e4e3f;
}
.btn-modal.ghost:hover{ filter:brightness(.97); }
.btn-modal.ghost:focus{ outline:0; box-shadow:0 0 0 3px rgba(0,0,0,.08); }

/* Soporte reducido en móviles muy pequeños */
@media (max-width:380px){
  .modal-actions{ flex-direction:column-reverse; }
  .btn-modal{ width:100%; }
}
</style>

<script>
// Minimal modal API: showAppModal({title, body, confirmText, onConfirm, onCancel})
(function(){
  var overlay   = document.getElementById('app-modal');
  var card      = overlay.querySelector('.modal-card');
  var titleEl   = document.getElementById('app-modal-title');
  var bodyEl    = document.getElementById('app-modal-body');
  var btnConfirm= document.getElementById('app-modal-confirm');
  var btnCancel = document.getElementById('app-modal-cancel');

  var current   = { onConfirm:null, onCancel:null };
  var lastFocus = null;

  function open(){
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden','false');
    // Guardar foco y bloquear scroll
    lastFocus = document.activeElement;
    document.body.style.overflow = 'hidden';
    // Enfocar botón principal
    btnConfirm.focus();
    // Esc para cerrar
    document.addEventListener('keydown', onKey);
    // Clic fuera cierra
    overlay.addEventListener('mousedown', onBackdrop);
    // Focus trap
    document.addEventListener('focus', trapFocus, true);
  }

  function close(){
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    // Restaurar foco
    if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    // Limpieza
    current.onConfirm = null;
    current.onCancel  = null;
    document.removeEventListener('keydown', onKey);
    overlay.removeEventListener('mousedown', onBackdrop);
    document.removeEventListener('focus', trapFocus, true);
  }

  function onBackdrop(e){
    // Si se hizo clic fuera de la tarjeta, cerrar como "cancel"
    if (!card.contains(e.target)) {
      if (typeof current.onCancel === 'function') current.onCancel();
      close();
    }
  }

  function onKey(e){
    if (e.key === 'Escape'){
      if (typeof current.onCancel === 'function') current.onCancel();
      close();
    }
    if (e.key === 'Enter' && document.activeElement === btnConfirm){
      if (typeof current.onConfirm === 'function') current.onConfirm();
      close();
    }
  }

  function trapFocus(e){
    if (!overlay.classList.contains('active')) return;
    if (!card.contains(e.target)) {
      // Redirige el foco a la tarjeta si se intenta salir
      e.stopPropagation();
      card.focus();
      btnConfirm.focus();
    }
  }

  btnCancel.addEventListener('click', function(){
    if (typeof current.onCancel === 'function') current.onCancel();
    close();
  });

  btnConfirm.addEventListener('click', function(){
    if (typeof current.onConfirm === 'function') current.onConfirm();
    close();
  });

  window.showAppModal = function(options){
    options = options || {};
    titleEl.textContent       = options.title || 'Aviso';
    // Permite pasar body como string o como HTML
    if (options.bodyIsHTML) {
      bodyEl.innerHTML        = options.body || '';
    } else {
      bodyEl.textContent      = options.body || '';
    }
    btnConfirm.textContent    = options.confirmText || 'Aceptar';
    current.onConfirm         = options.onConfirm || null;
    current.onCancel          = options.onCancel || null;
    open();
  };

  window.closeAppModal = close;
})();
</script>
