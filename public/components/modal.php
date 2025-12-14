<?php
// Reusable modal component - outputs markup and a minimal JS API
?>
<!-- Modal component -->
<div id="app-modal" class="modal-overlay" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="app-modal-title">
    <div class="modal-header">
      <h3 id="app-modal-title" class="modal-title">Aviso</h3>
    </div>
    <div class="modal-body" id="app-modal-body">Contenido</div>
    <div class="modal-actions">
      <button class="btn-modal ghost" id="app-modal-cancel">Cancelar</button>
      <button class="btn-modal primary" id="app-modal-confirm">Continuar</button>
    </div>
  </div>
</div>

<script>
// Minimal modal API: showModal({title, body, confirmText, onConfirm, onCancel})
(function(){
  var overlay = document.getElementById('app-modal');
  var titleEl = document.getElementById('app-modal-title');
  var bodyEl = document.getElementById('app-modal-body');
  var btnConfirm = document.getElementById('app-modal-confirm');
  var btnCancel = document.getElementById('app-modal-cancel');

  var current = {onConfirm: null, onCancel: null};

  function close(){
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    // cleanup handlers
    current.onConfirm = null; current.onCancel = null;
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
    titleEl.textContent = options.title || 'Aviso';
    bodyEl.textContent = options.body || '';
    btnConfirm.textContent = options.confirmText || 'Aceptar';
    current.onConfirm = options.onConfirm || null;
    current.onCancel = options.onCancel || null;
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
  };

  window.closeAppModal = close;
})();
</script>
