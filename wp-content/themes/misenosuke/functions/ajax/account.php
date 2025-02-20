<?php

/*
 * 新規登録Ajax処理
 */
function func_signup(){
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  // Stripe処理を有効化
  require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
  \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
  
  // ファイルアップロードを有効化
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  
  $result = true;
  $url = '';
  $error_list = array();

  if(!in_array('user_login', array_keys($_POST)) || $_POST['user_login'] == '') {
    $result = false;
    $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('username')));
  } else if(mb_strlen($_POST['user_login']) > 60) {
    $result = false;
    $error_list['user_login'] = ucfirst(sprintf($lang->translate('%1$s cannot be over %2$s characters'), $lang->translate('username'), '60'));
  } else if(!preg_match("/^[a-zA-Z0-9\-_@]+$/", $_POST['user_login'])) {
    $result = false;
    $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('username')));
  } else if(get_user_by('login', $_POST['user_login'])) {
    $result = false;
    $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s had been signuped'), $lang->translate('username')));
  }

  if(!in_array('email', array_keys($_POST)) || $_POST['email'] == '') {
    $result = false;
    $error_list['email'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('mail address')));
  } else if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST['email'])) {
    $result = false;
    $error_list['email'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('mail address')));
  } else if(get_user_by('login', $_POST['user_login'])) {
    $result = false;
    $error_list['email'] = ucfirst(sprintf($lang->translate('%s had been signuped'), $lang->translate('mail address')));
  }

  if(!in_array('password', array_keys($_POST)) || $_POST['password'] == '') {
    $result = false;
    $error_list['password'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password')));
  } else if(!preg_match("/^[a-zA-Z0-9!#\$%&'()\*\+-\.\/:;<=>\?@\[\]\^_`{|}~]+$/", $_POST['password'])) {
    $result = false;
    $error_list['password'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('password')));
  } else if(!in_array('password_confirm', array_keys($_POST)) || $_POST['password_confirm'] == '') {
    $result = false;
    $error_list['password_confirm'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password confirmation')));
  } else if($_POST['password_confirm'] != $_POST['password']) {
    $result = false;
    $error_list['password_confirm'] = ucfirst($lang->translate('password inputed twice is not same'));
  }

  if(!in_array('language', array_keys($_POST)) || $_POST['language'] == '') {
    $result = false;
    $error_list['language'] = ucfirst(sprintf($lang->translate('%s cannot be unselected'), $lang->translate('management language')));
  }

  if(!in_array('restaurant_name', array_keys($_POST)) || $_POST['restaurant_name'] == '') {
    $result = false;
    $error_list['restaurant_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('restaurant name')));
  }

  if(in_array('languages', array_keys($_POST))) {
    if(!is_array($_POST['languages'])) {
      $result = false;
      $error_list['languages'] = ucfirst($lang->translate('error occurred with menu language'));
    } else {
      foreach($_POST['languages'] as $language) {
        if(!in_array('restaurant_name_' . $language, array_keys($_POST)) || $_POST['restaurant_name_' . $language] == '') {
          $result = false;
          $error_list['restaurant_name_' . $language] = ucfirst(sprintf($lang->translate('%s cannot be unselected'), 
                                                                        $lang->translate('restaurant name') . '(' . $languages[$language] . ')'));
        }
      }
    }
  }

  if(!in_array('zipcode1', array_keys($_POST)) || $_POST['zipcode1'] == '' || !in_array('zipcode2', array_keys($_POST)) || $_POST['zipcode2'] == '') {
    $result = false;
    $error_list['zipcode'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('zipcode')));
  } else {
    try {
      $address = checkZipcode($_POST['zipcode1'] . $_POST['zipcode2']);
      if($address) {
        if($address[0]['address1'] != $_POST['prefecture'] || $address[0]['address2'] != $_POST['city'] || $address[0]['address3'] != $_POST['street']) {
          $result = false;
          $error_list['address'] = ucfirst($lang->translate('zipcode and address is not matched'));
        }
      } else {
        $result = false;
        $error_list['zipcode'] = ucfirst(sprintf($lang->translate('zipcode is wrong'), $lang->translate('zipcode')));
      }
    } catch(Exception $e) {
      $result = false;
      $error_list['zipcode'] = ucfirst($lang->translate('failed to get address'));
    }
  }
  
  if(!in_array('address_other', array_keys($_POST)) || $_POST['address_other'] == '') {
    $result = false;
    $error_list['address_other'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('address other')));
  }

  if(!in_array('telephone1', array_keys($_POST)) || $_POST['telephone1'] == '' 
      || !in_array('telephone2', array_keys($_POST)) || $_POST['telephone2'] == ''
      || !in_array('telephone3', array_keys($_POST)) || $_POST['telephone3'] == '') {
    $result = false;
    $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('restaurant telephone')));
  } elseif(!preg_match("/^(\d{2}-?\d{4}-?|\d{3}-?\d{3}-?|\d{4}-?\d{2}-?|\d{5}-?\d{1}-?|\d{6}-?|\d{1,4}-?)\d{4}|0120[-]?\d{3}[-]?\d{3}$/", 
      $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'])) {
    $result = false;
    $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('restaurant telephone')));
  }
  
  if(!in_array('first_name', array_keys($_POST)) || $_POST['first_name'] == '') {
    $result = false;
    $error_list['first_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('first name')));
  }
  
  if(!in_array('family_name', array_keys($_POST)) || $_POST['family_name'] == '') {
    $result = false;
    $error_list['family_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('family name')));
  }
  
  if(in_array('restaurant_url', array_keys($_POST)) && $_POST['restaurant_url'] != '' && !isUrlAccessible($_POST['restaurant_url'])) {
    $result = false;
    $error_list['restaurant_url'] = ucfirst(sprintf($lang->translate('%s is not existed'), $lang->translate('restaurant url')));
  }
  
  if(!empty($_FILES['restaurant_logo'])) {
    $file = $_FILES['restaurant_logo'];
    $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon');
    if($file['tmp_name']) {
      $file_mime_type = mime_content_type($file['tmp_name']);
      if (!in_array($file_mime_type, $allowed_mime_types)) {
        $result = false;
        $error_list['restaurant_logo'] = ucfirst($lang->translate('upload an image please'));
      }
    }
  }
  
  if(!in_array('token_id', array_keys($_POST)) || $_POST['token_id'] == '') {
    $result = false;
    $error_list['card_info'] = ucfirst($lang->translate('please input the correct card information'));
  }
  
  if(!in_array('agreement', array_keys($_POST)) || $_POST['agreement'] != 'on') {
    $result = false;
    $error_list['agreement'] = ucfirst(sprintf($lang->translate('check %s first'), $lang->translate('terms of service')));
  }
  
  if($result) {
    $attachment_id = null;
    
    if($file['tmp_name']) {
      // WordPress のメディアライブラリにファイルをアップロード
      $upload = wp_handle_upload($file, array('test_form' => false));

      if (!$upload || isset($upload['error'])) {
        $result = false;
        $error_list['restaurant_logo'] = ucfirst($lang->translate('failed to upload logo'));
      } else {
        // アップロード成功、添付ファイルとして登録
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name($file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
      }
    }
    
    $user_id = wp_insert_user(array(
      'user_login' => $_POST['user_login'],
      'user_pass' => $_POST['password'],
      'user_email' => $_POST['email'],
      'first_name' => $_POST['first_name'],
      'last_name' => $_POST['family_name'],
      'user_url' => $_POST['restaurant_url'],
      'display_name' => $_POST['family_name'] . ' ' . $_POST['first_name'],
      'show_admin_bar_front' => 'false',
      'role' => 'manager',
    ));
    
    update_field('special_flag', false, 'user_' . $user_id);
    update_field('uid', md5(uniqid(microtime(), true)), 'user_' . $user_id);
    update_field('language', $_POST['language'], 'user_' . $user_id);
    update_field('languages', implode(',', $_POST['languages']), 'user_' . $user_id);
    update_field('restaurant_name', $_POST['restaurant_name'], 'user_' . $user_id);
    $restaurant_names = array();
    foreach($_POST['languages'] as $language) {
      array_push($restaurant_names, array(
        'language' => $language,
        'name' => $_POST['restaurant_name_' . $language],
      ));
    }
    update_field('restaurant_names', $restaurant_names, 'user_' . $user_id);
    update_field('zipcode', $_POST['zipcode1'] . '-' . $_POST['zipcode2'], 'user_' . $user_id);
    update_field('prefecture', $_POST['prefecture'], 'user_' . $user_id);
    update_field('city', $_POST['city'], 'user_' . $user_id);
    update_field('street', $_POST['street'], 'user_' . $user_id);
    update_field('address_other', $_POST['address_other'], 'user_' . $user_id);
    update_field('address', $_POST['prefecture'] . $_POST['city'] . $_POST['street'] . $_POST['address_other'], 'user_' . $user_id);
    update_field('telephone', $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'], 'user_' . $user_id);
    if($attachment_id) {
      update_field('restaurant_logo', $attachment_id, 'user_' . $user_id);
    }
    
    try {
      // Stripe顧客作成
      $customer = \Stripe\Customer::create([
        'name' => $_POST['restaurant_name'],
        'email' => $_POST['email'],
      ]);
      
      // Stripe支払方法作成
      $payment_method = \Stripe\PaymentMethod::create([
        'type' => 'card',
        'card' => ['token' => TEST_MODE ? 'tok_visa' : $_POST['token_id']],
      ]);
      
      // Stripeで顧客と支払方法を連携
      $payment_method->attach(
        ['customer' => $customer->id]
      );
      
      // Stripe連携後支払方法を顧客のデフォルト支払方法に登録
      \Stripe\Customer::update(
        $customer->id,
        [
          'invoice_settings' => [
            'default_payment_method' => $payment_method->id,
          ],
        ]
      );
      
      // Stripe情報をカスタムフィールドに登録
      update_field('stripe_customer_id', $customer->id, 'user_' . $user_id);
      update_field('stripe_payment_method_id', $payment_method->id, 'user_' . $user_id);
    } catch (\Stripe\Exception\ApiErrorException $e) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to create stripe customer'));
      error_log('Stripe Error: ' . $e->getMessage());
    }
    
    if($result) {
      // 正しく作成されたとき新規ユーザーでログインする
      wp_set_current_user($user_id);
      wp_set_auth_cookie($user_id, false);
      $user = get_user_by('id', $user_id);
      $url = get_login_redirect_url($user);
    }
  }
  
  $response = array(
    'result' => $result,
    'url' => $url,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_signup', 'func_signup');
add_action('wp_ajax_nopriv_signup', 'func_signup');

/*
 * 従業員新規登録Ajax処理
 */
function func_add_employee(){
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  $result = true;
  $url = '';
  $error_list = array();

  if(!in_array('restaurant', array_keys($_POST)) || $_POST['restaurant'] == '') {
    $result = false;
    $error_list['system'] = ucfirst(sprintf($lang->translate('%s cannot be empty'), 'restaurant'));
  } else {
    $user_query = new WP_User_Query(array(
      'meta_key' => 'uid',
      'meta_value' => $_POST['restaurant'],
    ));
    $user_list = $user_query->get_results();
    
    if(count($user_list) != 1) {
      $result = false;
      $error_list['system'] = ucfirst(sprintf($lang->translate('%s is not found'), 'restaurant'));
    } else {
      $restaurant_id = intval($user_list[0]->data->ID);
    }
  }
  
  if(!in_array('type', array_keys($_POST)) || $_POST['type'] == '') {
    $result = false;
    $error_list['system'] = ucfirst(sprintf($lang->translate('%s cannot be empty'), 'type'));
  } elseif(!in_array($_POST['type'], array('cook', 'waiter'))) {
    $result = false;
    $error_list['system'] = ucfirst(sprintf($lang->translate('invalid %s'), 'type'));
  } else {
    $type = $_POST['type'];
  }

  if($result) {
    if(!in_array('user_login', array_keys($_POST)) || $_POST['user_login'] == '') {
      $result = false;
      $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('username')));
    } else if(mb_strlen($_POST['user_login']) > 60) {
      $result = false;
      $error_list['user_login'] = ucfirst(sprintf($lang->translate('%1$s cannot be over %2$s characters'), $lang->translate('username'), '60'));
    } else if(!preg_match("/^[a-zA-Z0-9\-_@]+$/", $_POST['user_login'])) {
      $result = false;
      $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('username')));
    } else if(get_user_by('login', $_POST['user_login'])) {
      $result = false;
      $error_list['user_login'] = ucfirst(sprintf($lang->translate('%s had been signuped'), $lang->translate('username')));
    }

    if(!in_array('email', array_keys($_POST)) || $_POST['email'] == '') {
      $result = false;
      $error_list['email'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('mail address')));
    } else if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST['email'])) {
      $result = false;
      $error_list['email'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('mail address')));
    } else if(get_user_by('login', $_POST['user_login'])) {
      $result = false;
      $error_list['email'] = ucfirst(sprintf($lang->translate('%s had been signuped'), $lang->translate('mail address')));
    }

    if(!in_array('password', array_keys($_POST)) || $_POST['password'] == '') {
      $result = false;
      $error_list['password'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password')));
    } else if(!preg_match("/^[a-zA-Z0-9!#\$%&'()\*\+-\.\/:;<=>\?@\[\]\^_`{|}~]+$/", $_POST['password'])) {
      $result = false;
      $error_list['password'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('password')));
    } else if(!in_array('password_confirm', array_keys($_POST)) || $_POST['password_confirm'] == '') {
      $result = false;
      $error_list['password_confirm'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password confirmation')));
    } else if($_POST['password_confirm'] != $_POST['password']) {
      $result = false;
      $error_list['password_confirm'] = ucfirst($lang->translate('password inputed twice is not same'));
    }

    if(!in_array('language', array_keys($_POST)) || $_POST['language'] == '') {
      $result = false;
      $error_list['language'] = ucfirst(sprintf($lang->translate('%s cannot be unselected'), $lang->translate('management language')));
    }

    if(!in_array('telephone1', array_keys($_POST)) || $_POST['telephone1'] == '' 
        || !in_array('telephone2', array_keys($_POST)) || $_POST['telephone2'] == ''
        || !in_array('telephone3', array_keys($_POST)) || $_POST['telephone3'] == '') {
      $result = false;
      $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('employee telephone')));
    } elseif(!preg_match("/^(\d{2}-?\d{4}-?|\d{3}-?\d{3}-?|\d{4}-?\d{2}-?|\d{5}-?\d{1}-?|\d{6}-?|\d{1,4}-?)\d{4}|0120[-]?\d{3}[-]?\d{3}$/", 
        $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'])) {
      $result = false;
      $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('employee telephone')));
    }
    
    if(!in_array('first_name', array_keys($_POST)) || $_POST['first_name'] == '') {
      $result = false;
      $error_list['first_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('first name')));
    }
    
    if(!in_array('family_name', array_keys($_POST)) || $_POST['family_name'] == '') {
      $result = false;
      $error_list['family_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('family name')));
    }
  }
  
  if($result) {
    $user_id = wp_insert_user(array(
      'user_login' => $_POST['user_login'],
      'user_pass' => $_POST['password'],
      'user_email' => $_POST['email'],
      'first_name' => $_POST['first_name'],
      'last_name' => $_POST['family_name'],
      'user_url' => $_POST['restaurant_url'],
      'display_name' => $_POST['family_name'] . ' ' . $_POST['first_name'],
      'show_admin_bar_front' => 'false',
      'role' => $type,
    ));
    
    update_field('uid', md5(uniqid(microtime(), true)), 'user_' . $user_id);
    update_field('restaurant', $restaurant_id, 'user_' . $user_id);
    update_field('language', $_POST['language'], 'user_' . $user_id);
    update_field('restaurant_name', get_field('restaurant_name', 'user_' . $restaurant_id), 'user_' . $user_id);
    update_field('telephone', $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'], 'user_' . $user_id);
    
    // 正しく作成されたとき新規ユーザーでログインする
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, false);
    $user = get_user_by('id', $user_id);
    $url = get_login_redirect_url($user);
  }
  
  $response = array(
    'result' => $result,
    'url' => $url,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_add_employee', 'func_add_employee');
add_action('wp_ajax_nopriv_add_employee', 'func_add_employee');


/*
 * ログインAjax処理
 */
function func_login(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $url = '';
  $error_list = array();
  
  if($_POST['loginid'] == '') {
    $result = false;
    $error_list['loginid'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('loginid')));
  }
  
  if($_POST['password'] == '') {
    $result = false;
    $error_list['password'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password')));
  }
  
  if($result) {
    $user_login = $_POST['loginid'];
    $password = $_POST['password'];
    
    if(is_email($user_login)) {
      $user = get_user_by('email', $user_login);
    } else {
      $user = get_user_by('login', $user_login);
    }
    
    if($user) {
      $remember = true;
      if(!in_array('remember', array_keys($_POST))) {
        $remember = false;
      } else if($_POST['remember'] != 'on') {
        $remember = false;
      }
      
      if (wp_check_password($password, $user->user_pass, $user->ID)) {
        // 認証成功
        wp_set_current_user($user->ID);
        // 'remember' 引数を使用してログインを記憶するかどうかを制御
        wp_set_auth_cookie($user->ID, $remember);
        // ログイン後リダイレクト先URLを取得
        $url = get_login_redirect_url($user);
      } else {
        $result = false;
        $error_list['password'] = ucfirst($lang->translate('password is wrong'));
      }
    } else {
      $result = false;
      $error_list['loginid'] = ucfirst(sprintf($lang->translate('%s is not signuped'), $lang->translate('loginid')));
    }
  }
  
  // リポジトリ出力
  $response = array(
    'result' => $result,
    'url' => $url,
    'errors' => $error_list,
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_login', 'func_login');
add_action('wp_ajax_nopriv_login', 'func_login');


/*
 * パスワードリセットAjax処理
 */
function func_pwdreset(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $error_list = array();

  // バリデーション
  if($_POST['loginid'] == '') {
    $result = false;
    $error_list['loginid'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('username or mail address')));
  } else {
    $user_login = $_POST['loginid'];
    
    if(is_email($user_login)) {
      $user = get_user_by('email', $user_login);
    } else {
      $user = get_user_by('login', $user_login);
    }
    
    if($user) {
      $password = wp_generate_password();
      wp_set_password($password, $user->ID);
      
      try {
        $mail_body = ucfirst(sprintf($lang->translate('mail to %s'), $user->display_name)) . '<br/><br/>';
        $mail_body .= ucfirst(sprintf($lang->translate('thank you for using %s'), $lang->translate('site name'))) . '<br/><br/>';
        $mail_body .= ucfirst($lang->translate('your password has been reseted successfully')) . '<br/>';
        $mail_body .= ucfirst(sprintf($lang->translate('please login with this password: %s')), $password) . '<br/>';
        $mail_body .= ucfirst($lang->translate('and set your password again on the profile page')) . '<br/><br/>';
        $mail_body .= ucfirst(sprintf($lang->translate('please continue to use %s'), $lang->translate('site name'))) . '<br/><br/>';
        $mail_body .= ucfirst($lang->translate('service name')) . '<br/><br/>';
        
        // 顧客へメール送信
        wp_mail($user->user_email, $subject, $mail_body, $headers);
      } catch(Exception $ex) {
        $result = false;
        $error_list['system'] = ucfirst($lang->translate('failed to send email'));
      }
    } else {
      $result = false;
      $error_list['loginid'] = ucfirst(sprintf($lang->translate('%s is not signuped'), $lang->translate('username or mail address')));
    }
  }
  
  // リポジトリ出力
  $response = array(
    'result' => $result,
    'errors' => $error_list,
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_pwdreset', 'func_pwdreset');
add_action('wp_ajax_nopriv_pwdreset', 'func_pwdreset');


/*
 * パスワード変更Ajax処理
 */
function func_pwdchange(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $error_list = array();
  
  if(!is_user_logged_in()) {
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    $user = wp_get_current_user();
    
    if($_POST['old_password'] == '') {
      $result = false;
      $error_list['old_password'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('old password')));
    } elseif (!wp_check_password($_POST['old_password'], $user->user_pass, $user->ID)) {
      $result = false;
      $error_list['old_password'] = ucfirst($lang->translate('password is wrong'));
    }
    
    if(!in_array('new_password', array_keys($_POST)) || $_POST['new_password'] == '') {
      $result = false;
      $error_list['new_password'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('new password')));
    } else if(!preg_match("/^[a-zA-Z0-9!#\$%&'()\*\+-\.\/:;<=>\?@\[\]\^_`{|}~]+$/", $_POST['new_password'])) {
      $result = false;
      $error_list['new_password'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('new password')));
    } else if(!in_array('password_confirm', array_keys($_POST)) || $_POST['password_confirm'] == '') {
      $result = false;
      $error_list['password_confirm'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('password confirmation')));
    } else if($_POST['password_confirm'] != $_POST['new_password']) {
      $result = false;
      $error_list['password_confirm'] = ucfirst($lang->translate('password inputed twice is not same'));
    }
  }
  
  if($result) {
    // パスワード設定
    wp_set_password($_POST['new_password'], $user->ID);
    // もう一度ログイン
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, false);
  }
  
  // リポジトリ出力
  $response = array(
    'result' => $result,
    'errors' => $error_list,
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_pwdchange', 'func_pwdchange');
add_action('wp_ajax_nopriv_pwdchange', 'func_pwdchange');


/*
 * クレジットカード登録Ajax処理
 */
function func_register_card(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
  \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

  $result = true;
  $url = '';
  $error_list = array();
  
  if(is_user_logged_in()) {
    $user_id = get_current_user_id();
    $stripe_customer_id = get_field('stripe_customer_id', 'user_' . $user_id);
    
    if(!$stripe_customer_id) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('you are not a stripe customer'));
    }
  } else {
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  }
  
  if(!in_array('token_id', array_keys($_POST)) || $_POST['token_id'] == '') {
    $result = false;
    $error_list['card_info'] = ucfirst($lang->translate('please input the correct card information'));
  }
  
  if(!in_array('agreement', array_keys($_POST)) || $_POST['agreement'] != 'on') {
    $result = false;
    $error_list['agreement'] = ucfirst(sprintf($lang->translate('check %s first'), $lang->translate('agreement to register card')));
  }
  
  if(!in_array('register_card_nonce', array_keys($_POST)) || !wp_verify_nonce($_POST['register_card_nonce'], 'register_card')) {
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('nonce verification has error'));
  }
  
  if($result) {
    try {
      $payment_method = \Stripe\PaymentMethod::create([
        'type' => 'card',
        'card' => ['token' => TEST_MODE ? 'tok_visa' : $_POST['token_id']],
      ]);
      
      $payment_method->attach(
        ['customer' => $stripe_customer_id]
      );
      
      \Stripe\Customer::update(
        $stripe_customer_id,
        [
          'invoice_settings' => [
            'default_payment_method' => $payment_method->id,
          ],
        ]
      );
      
      update_field('stripe_payment_method_id', $payment_method->id, 'user_' . $user_id);
      
      $url = LANG_DOMAIN;
      if(array_key_exists('refer', $_POST)) {
        if(in_array($_POST['refer'], array('subscription-confirmation'))) {
          $url .= '/' . $_POST['refer'] . '/';
        } else {
          $url .= '/member/#payment-method';
        }
      } else {
        $url .= '/member/#payment-method';
      }
    } catch (\Stripe\Exception\ApiErrorException $e) {
      // エラーハンドリング
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to register stripe card'));
      error_log("Stripe Error: " . $e->getMessage());
    } catch (Exception $e) {
      // その他のエラー
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to register stripe card'));
      error_log("General error: " . $e->getMessage());
    }
  }
  
  $response = array(
    'result' => $result,
    'url' => $url,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_register_card', 'func_register_card');
add_action('wp_ajax_nopriv_register_card', 'func_register_card');


/*
 * プロファイル編集Ajax処理
 */
function func_edit_profile(){
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  $languages = $lang->get_lang_list();
  
  // ファイルアップロードを有効化
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/image.php');
  
  $result = true;
  $url = '';
  $error_list = array();
  
  if(!is_user_logged_in()) {
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    $user = wp_get_current_user();
    
    if(!in_array('email', array_keys($_POST)) || $_POST['email'] == '') {
      $result = false;
      $error_list['email'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('mail address')));
    } else if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST['email'])) {
      $result = false;
      $error_list['email'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('mail address')));
    } else {
      $same_user = get_user_by('email', $_POST['email']);
      
      if($same_user && $same_user->ID != $user->ID) {
        $result = false;
        $error_list['email'] = ucfirst(sprintf($lang->translate('%s had been signuped'), $lang->translate('mail address')));
      }
    }

    if(!in_array('language', array_keys($_POST)) || $_POST['language'] == '') {
      $result = false;
      $error_list['language'] = ucfirst(sprintf($lang->translate('%s cannot be unselected'), $lang->translate('management language')));
    }
    
    if(!in_array('restaurant_name', array_keys($_POST)) || $_POST['restaurant_name'] == '') {
      $result = false;
      $error_list['restaurant_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('restaurant name') . '(' . $languages['ja'] . ')'));
    }

    if(in_array('languages', array_keys($_POST))) {
      if(!is_array($_POST['languages'])) {
        $result = false;
        $error_list['languages'] = ucfirst($lang->translate('error occurred with menu language'));
      } else {
        foreach($_POST['languages'] as $language) {
          if(!in_array('restaurant_name_' . $language, array_keys($_POST)) || $_POST['restaurant_name_' . $language] == '') {
            $result = false;
            $error_list['restaurant_name_' . $language] = ucfirst(sprintf($lang->translate('%s cannot be unselected'), 
                                                                          $lang->translate('restaurant name') . '(' . $languages[$language] . ')'));
          }
        }
      }
    } 

    if(!in_array('zipcode1', array_keys($_POST)) || $_POST['zipcode1'] == '' || !in_array('zipcode2', array_keys($_POST)) || $_POST['zipcode2'] == '') {
      $result = false;
      $error_list['zipcode'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('zipcode')));
    } else {
      try {
        $address = checkZipcode($_POST['zipcode1'] . $_POST['zipcode2']);
        if($address) {
          if($address[0]['address1'] != $_POST['prefecture'] || $address[0]['address2'] != $_POST['city'] || $address[0]['address3'] != $_POST['street']) {
            $result = false;
            $error_list['address'] = ucfirst($lang->translate('zipcode and address is not matched'));
          }
        } else {
          $result = false;
          $error_list['zipcode'] = ucfirst(sprintf($lang->translate('zipcode is wrong'), $lang->translate('zipcode')));
        }
      } catch(Exception $e) {
        $result = false;
        $error_list['zipcode'] = ucfirst($lang->translate('failed to get address'));
      }
    }
    
    if(!in_array('address_other', array_keys($_POST)) || $_POST['address_other'] == '') {
      $result = false;
      $error_list['address_other'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('address other')));
    }

    if(!in_array('telephone1', array_keys($_POST)) || $_POST['telephone1'] == '' 
        || !in_array('telephone2', array_keys($_POST)) || $_POST['telephone2'] == ''
        || !in_array('telephone3', array_keys($_POST)) || $_POST['telephone3'] == '') {
      $result = false;
      $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('restaurant telephone')));
    } elseif(!preg_match("/^(\d{2}-?\d{4}-?|\d{3}-?\d{3}-?|\d{4}-?\d{2}-?|\d{5}-?\d{1}-?|\d{6}-?|\d{1,4}-?)\d{4}|0120[-]?\d{3}[-]?\d{3}$/", 
        $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'])) {
      $result = false;
      $error_list['telephone'] = ucfirst(sprintf($lang->translate('%s cannot be this format'), $lang->translate('restaurant telephone')));
    }
    
    if(!in_array('first_name', array_keys($_POST)) || $_POST['first_name'] == '') {
      $result = false;
      $error_list['first_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('first name')));
    }
    
    if(!in_array('family_name', array_keys($_POST)) || $_POST['family_name'] == '') {
      $result = false;
      $error_list['family_name'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('family name')));
    }
    
    if(in_array('restaurant_url', array_keys($_POST)) && $_POST['restaurant_url'] != '' && !isUrlAccessible($_POST['restaurant_url'])) {
      $result = false;
      $error_list['restaurant_url'] = ucfirst(sprintf($lang->translate('%s is not existed'), $lang->translate('restaurant url')));
    }
    
    if(!empty($_FILES['restaurant_logo'])) {
      $file = $_FILES['restaurant_logo'];
      $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon');
      if($file['tmp_name']) {
        $file_mime_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_mime_type, $allowed_mime_types)) {
          $result = false;
          $error_list['restaurant_logo'] = ucfirst($lang->translate('upload an image please'));
        }
      }
    }
  }
  
  if($result) {
    $user_id = $user->ID;
    $attachment_id = null;
    
    if($file['tmp_name']) {
      // WordPress のメディアライブラリにファイルをアップロード
      $upload = wp_handle_upload($file, array('test_form' => false));

      if (!$upload || isset($upload['error'])) {
        $result = false;
        $error_list['restaurant_logo'] = ucfirst($lang->translate('failed to upload logo'));
      } else {
        // アップロード成功、添付ファイルとして登録
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name($file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        $old_attachment_id = get_field('restaurant_logo', 'user_' . $user_id);
        wp_delete_attachment($old_attachment_id, true);
        update_field('restaurant_logo', $attachment_id, 'user_' . $user_id);
      }
    }
    
    wp_update_user([
      'ID' => $user_id,
      'user_email' => $_POST['email'],
      'first_name' => $_POST['first_name'],
      'last_name' => $_POST['family_name'],
      'user_url' => $_POST['restaurant_url'],
      'display_name' => $_POST['family_name'] . ' ' . $_POST['first_name'],
    ]);
    
    update_field('language', $_POST['language'], 'user_' . $user_id);
    update_field('languages', implode(',', $_POST['languages']), 'user_' . $user_id);
    update_field('restaurant_name', $_POST['restaurant_name'], 'user_' . $user_id);
    $restaurant_names = array();
    foreach($_POST['languages'] as $language) {
      array_push($restaurant_names, array(
        'language' => $language,
        'name' => $_POST['restaurant_name_' . $language],
      ));
    }
    update_field('restaurant_names', $restaurant_names, 'user_' . $user_id);
    update_field('zipcode', $_POST['zipcode1'] . '-' . $_POST['zipcode2'], 'user_' . $user_id);
    update_field('prefecture', $_POST['prefecture'], 'user_' . $user_id);
    update_field('city', $_POST['city'], 'user_' . $user_id);
    update_field('street', $_POST['street'], 'user_' . $user_id);
    update_field('address_other', $_POST['address_other'], 'user_' . $user_id);
    update_field('address', $_POST['prefecture'] . $_POST['city'] . $_POST['street'] . $_POST['address_other'], 'user_' . $user_id);
    update_field('telephone', $_POST['telephone1'] . '-' . $_POST['telephone2'] . '-' . $_POST['telephone3'], 'user_' . $user_id);
  }
  
  $response = array(
    'result' => $result,
    'url' => $url,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_edit_profile', 'func_edit_profile');
add_action('wp_ajax_nopriv_edit_profile', 'func_edit_profile');


/*
 * 従業員新規登録Ajax処理
 */
function func_delete_employee(){
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  $result = true;
  $error = array();
  
  if(!is_user_logged_in()) {
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } elseif(!current_user_can('manager')) {
    $result = false;
    $error = ucfirst($lang->translate('only manager can delete employee'));
  } else {
    $user_id = get_current_user_id();
    
    if(!in_array('uid', array_keys($_POST)) || $_POST['uid'] == '') {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('%s is not found'), 'employee'));
    } else {
      $user_query = new WP_User_Query(array(
        'meta_key' => 'uid',
        'meta_value' => $_POST['uid'],
      ));
      $user_list = $user_query->get_results();
      
      if(count($user_list) != 1) {
        $result = false;
        $error = ucfirst(sprintf($lang->translate('%s is not found'), 'employee'));
      } else {
        $employee_id = intval($user_list[0]->data->ID);
        if(get_field('restaurant', 'user_' . $employee_id) != $user_id) {
          $error = ucfirst($lang->translate('you can not delete this employee'));
        }
      }
    }
  }
  
  if($result) {
    wp_delete_user($employee_id);
  }
  
  $response = array(
    'result' => $result,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_delete_employee', 'func_delete_employee');
add_action('wp_ajax_nopriv_delete_employee', 'func_delete_employee');
