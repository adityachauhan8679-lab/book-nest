<?php
/**
 * BookNest - Reusable Modal Dialog Component
 * Expects $modalId, $modalTitle, $modalContent
 */
?>
<div class="modal-backdrop" id="<?= e($modalId ?? 'defaultModal') ?>" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
  <div class="modal-dialog" style="background:#fff; border-radius:16px; width:90%; max-width:540px; padding:28px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
    <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <h3 style="font-size:1.25rem; font-weight:700;"><?= e($modalTitle ?? 'BookNest Notice') ?></h3>
      <button type="button" class="modal-close" onclick="document.getElementById('<?= e($modalId ?? 'defaultModal') ?>').style.display='none';" style="background:none; border:none; font-size:1.4rem; cursor:pointer;">✕</button>
    </div>
    <div class="modal-body">
      <?= $modalContent ?? '' ?>
    </div>
  </div>
</div>
