<?php

/** 名前を英語順で表示する言語 */
define('EN_NAME_ORDER_LANG', array('en'));

/**
 * SMTP経由の送信設定
 */
function ag_send_mail_smtp($phpmailer)
{
  /* SMTP有効設定 */
  $phpmailer->isSMTP();
  /* SMTPホスト名 */
  $phpmailer->Host       = "smtp.gmail.com";
  /* SMTP認証の有無 */
  $phpmailer->SMTPAuth   = true;
  /* ポート番号 */
  $phpmailer->Port       = "587";
  /* ユーザー名 */
  $phpmailer->Username   = "personal.linyang@gmail.com";
  /* パスワード */
  $phpmailer->Password   = "ylin19920518";
  /* 暗号化方式 */
  $phpmailer->SMTPSecure = "tls";
  /* 送信者メールアドレス */
  $phpmailer->From       = "personal.linyang@gmail.com";
  /* 送信者名 */
  $phpmailer->FromName = "yang";
  /* デバッグ */
  $phpmailer->SMTPDebug = 0;
}
add_action("phpmailer_init", "ag_send_mail_smtp");


/* 
 * 多言語対応有効化
 */
function multilingual_setup(){
  $lang_key_list = function_exists('qtranxf_getSortedLanguages') ? qtranxf_getSortedLanguages() : array('en');
  foreach($lang_key_list as $lang_key) {
    load_theme_textdomain('misenosuke-' . $lang_key, get_template_directory() . '/languages/' . $lang_key);
  }
}
add_action('after_setup_theme', 'multilingual_setup');

/* 
 * 言語コード付きドメインを定数化
 */
function get_lang_domain() {
  $default = get_option('qtranslate_default_language');
  if($default == false) {
    $default = 'en';
  }
  
  $code = function_exists('qtranxf_getLanguage') ? qtranxf_getLanguage() : $default;
  
  if($code == $default) {
    return get_site_url();
  } else {
    return get_site_url() . '/' . $code;
  }
}
define('LANG_DOMAIN', get_lang_domain());

/* 
 * データベースから直接post_metaを取得
 */
function get_post_meta_from_db($post_id, $post_key) {
  global $wpdb;
  
  $meta_value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $post_key));
  
  return $meta_value ? $meta_value : '';
}

/*
 * Wordpressツールバー表示
 * ログイン中のユーザーによりだし分け、管理者のみツールバーを出す
 */
add_filter( 'show_admin_bar', 'customize_admin_bar' );
function customize_admin_bar(){
  if(is_user_logged_in()) {
    $roles = wp_get_current_user()->roles;
    foreach($roles as $role) {
      if($role == 'administrator') {
        return true;
      }
    }
    return false;
  } else {
    return false;
  }
}


/*
 * 選択型カスタムフィールド選択肢リスト取得
 */
function get_acf_select_options($field_group, $field_key) {
  $options = array();
  $acfg = get_page_by_title('ACFG For ' . $field_group, OBJECT, 'acf-field-group');
  if($acfg) {
    global $wpdb;
    $sql = 'SELECT post_content FROM wp_posts WHERE post_type="acf-field" AND post_parent = ' . $acfg->ID . ' AND post_excerpt ="' . $field_key . '"';
    $acf_query = $wpdb->get_results($wpdb->prepare($sql));
    if(count($acf_query)) {
      $acf_content = maybe_unserialize(current($acf_query)->post_content);
      if(array_key_exists('choices', $acf_content)) {
        $options = $acf_content['choices'];
      }
    }
  }
  return $options;
}


/*
 * URLアクセス可能かどうかチェック
 */
function isUrlAccessible($url) {
    $ch = curl_init($url);
    
    // オプションを設定
    curl_setopt($ch, CURLOPT_NOBODY, true); // ボディを取得しない
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // タイムアウトを設定
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // リダイレクトをたどる
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // 最大リダイレクト数を設定
    
    // リクエストを送信
    curl_exec($ch);
    
    // HTTPレスポンスコードを取得
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // cURLセッションを終了
    curl_close($ch);
    
    // 200番台のステータスコードはアクセス可能とみなす
    return ($httpCode >= 200 && $httpCode < 300);
}


/* 
 * ログインしている場合ログイン画面にアクセスする際のリダイレクト先URL取得
 */
function get_login_redirect_url($user) {
  $user_id = $user->ID;
  $roles = $user->roles;
  
  if(in_array('administrator', $roles)) {
    return LANG_DOMAIN . '/wp-admin/';
  } else if(in_array('manager', $roles)) {
    return LANG_DOMAIN . '/member/';
  } else if(in_array('cook', $roles)) {
    return LANG_DOMAIN;
  } else if(in_array('waiter', $roles)) {
    return LANG_DOMAIN . '/waiter/';
  }
  
  return LANG_DOMAIN;
}


/* 
 * 郵便番号から住所を取得
 */
function checkZipcode($zipcode) {
  $url = "https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . urlencode($zipcode);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  $data = json_decode($response, true);

  if ($data['status'] === 200 && isset($data['results'])) {
    return $data['results'];
  } else {
    return false;
  }
}

/* 
 * セッションの有効期間を24時間に設定して開始する
 */
function set_custom_session_lifetime() {
    // セッションを開始する前に設定を行います
    ini_set('session.gc_maxlifetime', 86400);
    session_set_cookie_params(86400);
    
    // セッションを開始
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}
add_action('init', 'set_custom_session_lifetime');