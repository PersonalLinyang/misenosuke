<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

// Stripe処理を有効化
require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    if(array_key_exists('HTTP_REFERER', $_SERVER) && strpos($_SERVER['HTTP_REFERER'], LANG_DOMAIN . '/confirm-subscription/') !== false):
      $page_title = ucwords($lang->translate('subscription complete'));
      $page_topic = strtoupper($page_title);
      $style_key = 'member';
      
      get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="member-section">
  <p class="center"><?php echo ucfirst($lang->translate('subscription is created successfully')); ?></p>
  <p class="center">
    <?php echo ucfirst($lang->translate('please confirm your subscription on member page')); ?>
  </p>
  <p class="center">
    <a class="underline" href="<?php echo LANG_DOMAIN; ?>/member/"><?php echo ucfirst($lang->translate('redirect to member page')); ?></a>
  </p>
</section>

<?php
      get_footer();
    else:
      // サブスクリプション確認ページ以外のところから遷移した場合ページエラーテンプレートに切り替え
      include(locate_template('404.php'));
    endif;
  else:
    // プロファイルページがない場合、権限エラーテンプレートに切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
