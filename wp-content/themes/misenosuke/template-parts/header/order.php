<?php

// 全域変数有効化
global $order_languages;

// 翻訳有効化
$lang = new LanguageSupporter();
$lang_code = $lang->code();

$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

?>

<header class="header order-header">
  <div class="order-header-inner">
    <div class="order-header-logo">店の助</div>
    <div class="order-header-language">
      <p class="order-header-language-handler"><?php echo $lang->translate('Language Mark'); ?></p>
      <div class="order-header-language-menu">
        <ul class="order-header-language-list">
          <?php foreach($order_languages as $order_language): ?>
            <?php if($order_language != $lang_code): ?>
              <li class="order-header-language-item">
                <a class="full-link" href="<?php echo qtranxf_convertURL($current_url, $order_language, '', true);?>">
                  <?php echo ucfirst($lang->get_lang_name_fixity($order_language)); ?>
                </a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="order-header-handler">
      <div class="order-header-handler-inner">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>
  <div class="order-header-menu">
    <div class="order-header-menu-shadow"></div>
    <div class="order-header-menu-inner">
      <ul class="order-header-menu-list">
        <li class="order-header-menu-item">
          <p class="order-header-menu-title has-sub">テストメニュー1</p>
          <ul class="order-header-menu-sublist">
            <li class="order-header-menu-subitem"><a class="full-link" href="/order/">サブメニュー1-1</a></li>
            <li class="order-header-menu-subitem"><a class="full-link" href="/order/">サブメニュー1-2</a></li>
            <li class="order-header-menu-subitem"><a class="full-link" href="/order/">サブメニュー1-3</a></li>
          </ul>
        </li>
        <li class="order-header-menu-item">
          <p class="order-header-menu-title"><a class="full-link" href="/order/">テストメニュー2</a></p>
        </li>
        <li class="order-header-menu-item">
          <p class="order-header-menu-title"><a class="full-link" href="/order/">テストメニュー3</a></p>
        </li>
      </ul>
    </div>
  </div>
</header>