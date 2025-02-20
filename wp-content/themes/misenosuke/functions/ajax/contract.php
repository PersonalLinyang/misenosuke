<?php

/*
 * サブスクリプション登録Ajax処理
 */
function func_create_subscription() {
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  // Stripe処理を有効化
  require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
  \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
  
  // レスポンス用データを初期化
  $result = true;
  $url = '';
  $error_list = array();
  
  // 各項目バリデーション
  $user_id = 0;
  $stripe_customer_id = '';
  if(is_user_logged_in()) {
    $user_id = get_current_user_id();
    $stripe_customer_id = get_field('stripe_customer_id', 'user_' . $user_id);
    $stripe_payment_method_id = get_field('stripe_payment_method_id', 'user_' . $user_id);
    
    if($stripe_customer_id) {
      try {
        $customer = \Stripe\Customer::retrieve($stripe_customer_id);
      } catch (\Stripe\Exception\ApiErrorException $e) {
        // Stripeにて未登録支払い方法
        $result = false;
        $error_list['system'] = ucfirst($lang->translate('failed to get stripe customer information'));
        error_log('Stripe Error: ' . $e->getMessage());
      }
    } else {
      // Stripeにて未登録顧客
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to get stripe customer information'));
    }
    
    if($stripe_payment_method_id) {
      try {
        $payment_method = \Stripe\PaymentMethod::retrieve($stripe_payment_method_id);
      } catch (\Stripe\Exception\ApiErrorException $e) {
        // Stripeにて未登録支払い方法
        $result = false;
        $error_list['system'] = ucfirst($lang->translate('failed to get stripe payment method information'));
        error_log('Stripe Error: ' . $e->getMessage());
      }
    } else {
      // 支払い方法未登録
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to get stripe payment method information'));
    }
  } else {
    // 未ログイン
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  }
  
  if(!in_array('plan', array_keys($_POST)) || $_POST['plan'] == '') {
    // プラン指定なし
    $result = false;
    $error_list['plan'] = ucfirst($lang->translate('please select a plan on plan page'));
  } else {
    $plan = get_page_by_path($_POST['plan'], OBJECT, "plan");
    
    if(!$plan) {
      // 存在しないプランコード
      $result = false;
      $error_list['plan'] = ucfirst($lang->translate('this is not a active plan'));
    }
  }
  
  if($plan && $user_id) {
    $current_plan_id = get_field('plan', 'user_' . $user_id);
    
    if($current_plan_id == $plan->ID) {
      // 同じプランの乗り換えできない
      $result = false;
      $error_list['plan'] = ucfirst($lang->translate('cannot change to current plan'));
    }
  }
  
  if(!in_array('agreement', array_keys($_POST)) || $_POST['agreement'] != 'on') {
    // 同意未チェック
    $result = false;
    $error_list['agreement'] = ucfirst(sprintf($lang->translate('check %s first'), $lang->translate('the confirmation of create subscription')));
  }
  
  if(!in_array('create_subscription_nonce', array_keys($_POST)) || !wp_verify_nonce($_POST['create_subscription_nonce'], 'create_subscription')) {
    // 作成Nonce不一致
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('nonce verification has error'));
  }
  
  if($result) {
    // 現契約がある場合、まず現契約を即時解約して、未使用分を返金
    $current_subscription_id = get_field('stripe_subscription_id', 'user_' . $user_id);
    
    if($current_subscription_id) {
      try {
        $current_subscription = \Stripe\Subscription::retrieve($current_subscription_id);
        $current_subscription->cancel(['invoice_now' => true, 'prorate' => true]);
        
        // ユーザーの契約情報を更新
        update_field('plan', '' , 'user_' . $user_id);
        update_field('stripe_subscription_id', '' , 'user_' . $user_id);
        update_field('prev_plan', $current_plan_id , 'user_' . $user_id);
        update_field('prev_stripe_subscription_id', $current_subscription_id , 'user_' . $user_id);
        update_field('prev_cancellation_time', time() , 'user_' . $user_id);
      } catch (\Stripe\Exception\ApiErrorException $e) {
        $result = false;
        $error_list['system'] = ucfirst($lang->translate('failed to cancel current subscription'));
        error_log('Stripe Error: ' . $e->getMessage());
      }
    }
  }
  
  if($result) {
    // プランからStipeの製品IDと料金ID取得
    $plan_id = $plan->ID;
    $stripe_product_id = get_field('stripe_product_id', $plan_id);
    $stripe_price_id = get_field('stripe_price_id', $plan_id);
    
    // 新サブスクリプション情報を整理
    $subscription_info = [
      'customer' => $stripe_customer_id,
      'items' => [['price' => $stripe_price_id, 'tax_rates' => ['txr_1PHiSrCPUg4pFufolJ23tVbu']]],
      'expand' => ['latest_invoice.payment_intent'],
    ];
    
    // 初期費用は単独で請求
    $plan_setup_fee = intval(get_field('setup_fee', $plan_id));
    if($plan_setup_fee) {
      $subscription_info['add_invoice_items'] = [[
        'price_data' => [
          'currency' => 'jpy',
          'product' => $stripe_product_id,
          'unit_amount' => $plan_setup_fee,
        ],
        'tax_rates' => ['txr_1PHiSrCPUg4pFufolJ23tVbu'],
      ]];
    }
    
    // 無料試用期間対応
    $plan_free_period = intval(get_field('free_period', $plan_id));
    if($plan_free_period) {
      $plan_period = intval(get_field('period', $plan_id));
      $plan_unit = get_field('unit', $plan_id);
      $trial_number = $plan_free_period * $plan_period;
      switch($plan_unit) {
        case 'week':
          $trial_period = $trial_number * 7;
          break;
        case 'month':
          $trial_period = $trial_number * intval(date('t'));
          break;
        case 'year':
          $trial_period = $trial_number * (date('L') ? 366 : 365);
          break;
        default:
          $trial_period = $trial_number;
          break;
      }
      $trial_period = $trial_period * 24 * 60 * 60;
      $subscription_info['trial_end'] = time() + $trial_period;
    }
    
    try {
      // サブスクリプションを新規作成
      $subscription = \Stripe\Subscription::create($subscription_info);
      
      // ユーザーの契約情報を更新
      update_field('plan', $plan_id , 'user_' . $user_id);
      update_field('stripe_subscription_id', $subscription->id , 'user_' . $user_id);
      
      $url = LANG_DOMAIN . '/complete-subscription/';
    } catch (\Stripe\Exception\ApiErrorException $e) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('failed to create stripe subscription'));
      error_log('Stripe Error: ' . $e->getMessage());
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
add_action('wp_ajax_create_subscription', 'func_create_subscription');
add_action('wp_ajax_nopriv_create_subscription', 'func_create_subscription');

/*
 * 解約Ajax処理
 */
function func_cancel_subscription(){
  // 翻訳有効化
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  // Stripe処理を有効化
  require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
  \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
  
  // レスポンス用データを初期化
  $result = true;
  $url = '';
  $error_list = array();
  
  // 各項目バリデーション
  $user_id = 0;
  $stripe_customer_id = '';
  if(is_user_logged_in()) {
    $user_id = get_current_user_id();
  } else {
    // 未ログイン
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  }
  
  if(!in_array('cancel_subscription_nonce', array_keys($_POST)) || !wp_verify_nonce($_POST['cancel_subscription_nonce'], 'cancel_subscription')) {
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('nonce verification has error'));
  }
  
  if(!in_array('cancel_type', array_keys($_POST)) || !in_array($_POST['cancel_type'], ['none', 'now', 'end', 'date'])) {
    $result = false;
    $error_list['cancel_type'] = ucfirst($lang->translate('you need choose a timing to cancel the subscription'));
  } else {
    if($_POST['cancel_type'] == 'date') {
      if(!in_array('cancel_date', array_keys($_POST)) || !preg_match('/^(19|20)[0-9]{2}-\d{2}-\d{2}$/', $_POST['cancel_date'])) {
        $result = false;
        $error_list['cancel_date'] = ucfirst($lang->translate('you need choose a date to cancel the subscription'));
      }
    }
  }
  
  if($result) {
    // 現契約を解約する
    $plan_id = get_field('plan', 'user_' . $user_id);
    $subscription_id = get_field('stripe_subscription_id', 'user_' . $user_id);
    
    if($subscription_id) {
      try {
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        
        if($_POST['cancel_type'] == 'none') {
          // 解約しない場合
          $url = LANG_DOMAIN . '/member/';
        } else {
          // 解約する場合
          if($_POST['cancel_type'] == 'now') {
            // 即時解約
            $cancellation_time = time();
            $subscription->cancel(['invoice_now' => true, 'prorate' => true]);
          } elseif($_POST['cancel_type'] == 'end') {
            // 現期限満了解約
            $cancellation_time = $subscription->current_period_end;
            \Stripe\Subscription::update($subscription_id, [
              'cancel_at_period_end' => true,
            ]);
          } else {
            // 指定日解約
            $cancellation_time = strtotime($_POST['cancel_date'] . ' 23:59:59');
            \Stripe\Subscription::update($subscription_id, [
              'cancel_at' => $cancellation_time,
            ]);
          }
          
          // ユーザーの契約情報を更新
          update_field('plan', '' , 'user_' . $user_id);
          update_field('stripe_subscription_id', '' , 'user_' . $user_id);
          update_field('prev_plan', $plan_id , 'user_' . $user_id);
          update_field('prev_stripe_subscription_id', $subscription_id , 'user_' . $user_id);
          update_field('prev_cancellation_time', $cancellation_time , 'user_' . $user_id);
        }
        
        $url = LANG_DOMAIN . '/complete-cancel-subscription/';
      } catch (\Stripe\Exception\ApiErrorException $e) {
        $result = false;
        $error_list['system'] = ucfirst($lang->translate('failed to cancel current subscription'));
        error_log('Stripe Error: ' . $e->getMessage());
      }
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
add_action('wp_ajax_cancel_subscription', 'func_cancel_subscription');
add_action('wp_ajax_nopriv_cancel_subscription', 'func_cancel_subscription');
