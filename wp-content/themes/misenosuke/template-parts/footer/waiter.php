<?php
/**
 * 注文画面用フッター
 */

// 翻訳有効化
$lang = new LanguageSupporter();

?>
<footer class="footer waiter-footer">
  <div class="waiter-footer-inner">
    <div class="waiter-footer-button waiter-footer-button-home">
      <div class="waiter-footer-button-icon">
        <p class="waiter-footer-button-image"></p>
      </div>
      <p class="waiter-footer-button-text">
        <?php echo strtoupper($lang->translate('top page')); ?>
      </p>
    </div>
    <div class="waiter-footer-button waiter-footer-button-scan">
      <div class="waiter-footer-button-icon">
        <p class="waiter-footer-button-image"></p>
      </div>
      <p class="waiter-footer-button-text">
        <?php echo strtoupper($lang->translate('qr scan')); ?>
      </p>
    </div>
    <div class="waiter-footer-button waiter-footer-button-cart">
      <div class="waiter-footer-button-icon">
        <p class="waiter-footer-button-image"></p>
      </div>
      <p class="waiter-footer-button-text">
        <?php echo strtoupper($lang->translate('order cart')); ?>
      </p>
    </div>
    <div class="waiter-footer-button waiter-footer-button-call">
      <div class="waiter-footer-button-icon">
        <p class="waiter-footer-button-image"></p>
      </div>
      <p class="waiter-footer-button-text">
        <?php echo strtoupper($lang->translate('call staff')); ?>
      </p>
    </div>
  </div>
</footer>