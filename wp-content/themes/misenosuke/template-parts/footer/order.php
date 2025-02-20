<?php
/**
 * 注文画面用フッター
 */

// 翻訳有効化
$lang = new LanguageSupporter();

?>
<footer class="footer order-footer">
  <div class="order-footer-inner">
    <div class="order-footer-button order-footer-button-menu">
      <div class="order-footer-button-icon">
        <p class="order-footer-button-image"></p>
      </div>
      <p class="order-footer-button-text">
        <?php echo strtoupper($lang->translate('menu')); ?>
      </p>
    </div>
    <div class="order-footer-button order-footer-button-cart">
      <div class="order-footer-button-icon">
        <p class="order-footer-button-image"></p>
      </div>
      <p class="order-footer-button-text">
        <?php echo strtoupper($lang->translate('order cart')); ?>
      </p>
    </div>
    <div class="order-footer-button order-footer-button-slip">
      <div class="order-footer-button-icon">
        <p class="order-footer-button-image"></p>
      </div>
      <p class="order-footer-button-text">
        <?php echo strtoupper($lang->translate('slip')); ?>
      </p>
    </div>
    <div class="order-footer-button order-footer-button-call">
      <div class="order-footer-button-icon">
        <p class="order-footer-button-image"></p>
      </div>
      <p class="order-footer-button-text">
        <?php echo strtoupper($lang->translate('call staff')); ?>
      </p>
    </div>
  </div>
</footer>