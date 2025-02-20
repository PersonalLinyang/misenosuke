<?php

/*
 * CSS呼び出し
 */
function my_enqueue_styles() {
  // 全域変数有効化
  global $style_key;
  
  // 投稿情報を取得
  $post = get_post();
  
  // 共通CSSを読み込み
  $css_common_path = 'assets/css/common/common.css';
  wp_enqueue_style('css_common', get_theme_file_uri($css_common_path), array(), filemtime(get_theme_file_path($css_common_path)), 'all');
  
  if($style_key == 'member') {
    // 会員ページ共通CSSを読み込み
    $css_member_path = 'assets/css/common/member.css';
    if(file_exists(get_theme_file_path($css_member_path))) {
      wp_enqueue_style('css_member', get_theme_file_uri($css_member_path), array(), filemtime(get_theme_file_path($css_member_path)), 'all');
    }
  } elseif($style_key == 'order') {
    // 注文ページ共通CSSを読み込み
    $css_order_path = 'assets/css/common/order.css';
    if(file_exists(get_theme_file_path($css_order_path))) {
      wp_enqueue_style('css_order', get_theme_file_uri($css_order_path), array(), filemtime(get_theme_file_path($css_order_path)), 'all');
    }
  }
  
  if(is_page()) {
    // プロファイル編集共通CSSを読み込み
    if(is_page('signup') || is_page('edit-profile') || is_page('add-employee')) {
      $css_profile_path = 'assets/css/common/profile.css';
      if(file_exists(get_theme_file_path($css_profile_path))) {
        wp_enqueue_style('css_profile', get_theme_file_uri($css_profile_path), array(), filemtime(get_theme_file_path($css_profile_path)), 'all');
      }
    }
    
    // カレンダー選択共通CSSを読み込み
    if(is_page('cancel-subscription') || is_page('manage-menu') || is_page('manage-course')) {
      $css_datepicker_path = 'assets/css/common/datepicker.css';
      if(file_exists(get_theme_file_path($css_datepicker_path))) {
        wp_enqueue_style('css_datepicker', get_theme_file_uri($css_datepicker_path), array(), filemtime(get_theme_file_path($css_datepicker_path)), 'all');
      }
    }
    
    // メニュー編集共通CSSを読み込み
    if(is_page('manage-course') || is_page('manage-menu') || is_page('manage-seat')) {
      $css_manage_path = 'assets/css/common/manage.css';
      if(file_exists(get_theme_file_path($css_manage_path))) {
        wp_enqueue_style('css_manage', get_theme_file_uri($css_manage_path), array(), filemtime(get_theme_file_path($css_manage_path)), 'all');
      }
    }
    
    // select2のCSSを読み込み
    if(is_page('manage-course')) {
      wp_enqueue_style('css_select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    }
    
    // 固定ページ各自CSSを読み込み
    $css_page_path = 'assets/css/page/' . $post->post_name . '.css';
    if(file_exists(get_theme_file_path($css_page_path))) {
      wp_enqueue_style('css_page', get_theme_file_uri($css_page_path), array(), filemtime(get_theme_file_path($css_page_path)), 'all');
    }
  } elseif(is_singular()) {
    // 詳細ページ記事タイプごとCSSを読み込み
    $css_page_path = 'assets/css/single/' . $post->post_type . '.css';
    if(file_exists(get_theme_file_path($css_page_path))) {
      wp_enqueue_style('css_page', get_theme_file_uri($css_page_path), array(), filemtime(get_theme_file_path($css_page_path)), 'all');
    }
  }
}
add_action('wp_enqueue_scripts', 'my_enqueue_styles');
