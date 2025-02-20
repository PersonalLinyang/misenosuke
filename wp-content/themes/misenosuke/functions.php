<?php

/*
 * Wordpressコア機能関連functions
 */
get_template_part('functions/core');


/*
 * リダイレクト関連functions
 */
get_template_part('functions/rewrite_rules');


/*
 * カスタム権限グループ関連functions
 */
get_template_part('functions/custom_role');


/*
 * カスタム投稿タイプ関連functions
 */
get_template_part('functions/custom_type');


/*
 * CSS関連functions
 */
get_template_part('functions/style');


/*
 * Javascript関連functions
 */
get_template_part('functions/javascript');

/*
 * Stripe拡張機能
 */
get_template_part('functions/stripe');


/*
 * Ajax関連functions
 */
// アカウント関連functions
get_template_part('functions/ajax/account');
// 管理係用契約関連functions
get_template_part('functions/ajax/contract');
// 管理係用管理関連functions
get_template_part('functions/ajax/management');
// 接客係関連functions
get_template_part('functions/ajax/waiter');
// 顧客注文関連functions
get_template_part('functions/ajax/order');













function enqueue_socketio_script() {
    wp_enqueue_script('socketio', 'https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.1/socket.io.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_socketio_script');






















/* 
 * 管理画面JS読み込み
 */
function customize_admin_scripts()
{
  $current_page_slug = isset($_GET['page']) ? $_GET['page'] : '';
  //プラン管理でJS読み込み
  if($current_page_slug == 'self_plan') {
    wp_enqueue_script('js_admin', get_theme_file_uri().'/assets/js/admin/self_plan.js', array('jquery'));
  }
}
add_action('admin_enqueue_scripts', 'customize_admin_scripts');

/* 
 * 注文関連
 */
function add_custom_capabilities1() {
    // 権限追加
    $manager_role = get_role('manager');
    if ($manager_role) {
//        $manager_role->add_cap('self_plan');
    }
}

add_action('admin_init', 'add_custom_capabilities1');

// ページのコールバック関数
function render_admin_self_plan_page() {
    get_template_part( 'template-parts/admin/self_plan', null, array());
}

function add_admin_page() {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  
  // メニューアイテムを追加
  add_menu_page(
      $lang->translate('plan management'), // ページタイトル
      $lang->translate('plan management'), // メニュータイトル
      'self_plan', // 権限
      'self_plan', // スラッグ
      'render_admin_self_plan_page', // コールバック関数
      'dashicons-star-filled', // アイコン
      150 // メニューの位置
  );
}

// メニューアイテムを追加
add_action('admin_menu', 'add_admin_page');
