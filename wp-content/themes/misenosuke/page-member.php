<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

// Stripe処理を有効化
require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);


if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $page_title = ucwords($lang->translate('member personal page'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<?php 
/**
 * テンプレートパーツで詳細内容を表示
 */

// 個人情報を取得
get_template_part('template-parts/member/profile'); 

// 支払い方法情報を取得
get_template_part('template-parts/member/payment-method'); 

// サブスクリプション情報を取得
get_template_part('template-parts/member/subscription'); 

// 請求情報を取得
get_template_part('template-parts/member/invoice'); 

// スタッフ情報を取得
get_template_part('template-parts/member/employee'); 

// メニュー情報を取得
get_template_part('template-parts/member/menu'); 

// 席情報を取得
get_template_part('template-parts/member/seat'); 

?>
  
<section class="popup-shadow"></section>

<section class="popup-message">
  <p class="popup-message-text"></p>
</section>

<?php 
    get_footer();
  else:
    // プロファイルページがない場合、権限エラーテンプレートを切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
