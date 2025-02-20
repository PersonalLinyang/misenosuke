<?php

// 翻訳有効化
$lang = new LanguageSupporter();
$lang_code = $lang->code();

$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$user_id = get_current_user_id();
$restaurant_id = get_field('restaurant', 'user_' . $user_id);
$order_languages = explode(",", get_field('languages', 'user_' . $restaurant_id));
array_unshift($order_languages, 'ja');

?>

<header class="header waiter-header">
  <div class="waiter-header-inner">
    <div class="waiter-header-logo">店の助</div>
    <div class="waiter-header-language">
      <p class="waiter-header-language-handler"><?php echo $lang->translate('Language Mark'); ?></p>
      <div class="waiter-header-language-menu">
        <ul class="waiter-header-language-list">
          <?php foreach($order_languages as $order_language): ?>
            <?php if($order_language != $lang_code): ?>
              <li class="waiter-header-language-item">
                <a class="full-link" href="<?php echo qtranxf_convertURL($current_url, $order_language, '', true);?>">
                  <?php echo ucfirst($lang->get_lang_name_fixity($order_language)); ?>
                </a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="waiter-header-handler">
      <div class="waiter-header-handler-inner">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>
  <div class="waiter-header-menu">
    <div class="waiter-header-menu-shadow"></div>
    <div class="waiter-header-menu-inner">
      <ul class="waiter-header-menu-list">
        <li class="waiter-header-menu-item">
          <p class="waiter-header-menu-title has-sub">テストメニュー1</p>
          <ul class="waiter-header-menu-sublist">
            <li class="waiter-header-menu-subitem"><a class="full-link" href="/waiter/">サブメニュー1-1</a></li>
            <li class="waiter-header-menu-subitem"><a class="full-link" href="/waiter/">サブメニュー1-2</a></li>
            <li class="waiter-header-menu-subitem"><a class="full-link" href="/waiter/">サブメニュー1-3</a></li>
          </ul>
        </li>
        <li class="waiter-header-menu-item">
          <p class="waiter-header-menu-title"><a class="full-link" href="/waiter/">テストメニュー2</a></p>
        </li>
        <li class="waiter-header-menu-item">
          <p class="waiter-header-menu-title"><a class="full-link" href="/waiter/">テストメニュー3</a></p>
        </li>
      </ul>
    </div>
  </div>
</header>