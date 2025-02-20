<?php

/*
 * 顧客注文開始チェックAjax処理
 */
function func_start_order_customer(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  $result = true;
  $count = 0;
  $order_uid = '';
  $order_list = array();
  $error = '';
  
  if(!array_key_exists('seat', $_POST)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else if($_POST['seat'] == '') {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else {
    $seat = get_page_by_path($_POST['seat'], OBJECT, 'seat');
    
    if(!$seat) {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
    }
  }
  
  if($result) {
    $time_now = time();
    $select_query = "SELECT * FROM tb_order WHERE seat_id=" . $seat->ID . " AND finish_time is null";
    $orders = $wpdb->get_results($select_query);
    $count = count($orders);
    
    if($count) {
      foreach($orders as $order) {
        array_push($order_list, $order->uid);
      }
    } else {
      // 現在注文者なし、新規注文を追加
      $order_uid = md5(uniqid(microtime(), true));
      $data = array(
        'uid' => $order_uid,
        'restaurant_id' => $seat->post_author,
        'seat_id' => $seat->ID,
      );
      $format = array('%s', '%d', '%d',);
      $wpdb->insert('tb_order', $data, $format);
      
      if ($wpdb->insert_id) {
        $_SESSION['order_uid'] = $order_uid;
      } else {
        $result = false;
        $error = ucfirst(sprintf($lang->translate('failed to %s'), $lang->translate('start ordering')));
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'count' => $count,
    'orders' => $order_list,
    'order' => $order_uid,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_start_order_customer', 'func_start_order_customer');
add_action('wp_ajax_nopriv_start_order_customer', 'func_start_order_customer');


/*
 * 注文新規作成Ajax処理
 */
function func_create_order_customer(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  $result = true;
  $order_uid = '';
  $error = '';
  
  if(!array_key_exists('seat', $_POST)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else if($_POST['seat'] == '') {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else {
    $seat = get_page_by_path($_POST['seat'], OBJECT, 'seat');
    
    if(!$seat) {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
    }
  }
  
  if($result) {
    // 新規注文を追加
    $order_uid = md5(uniqid(microtime(), true));
    $data = array(
      'uid' => $order_uid,
      'restaurant_id' => $seat->post_author,
      'seat_id' => $seat->ID,
    );
    $format = array('%s', '%d', '%d',);
    $wpdb->insert('tb_order', $data, $format);
    
    if ($wpdb->insert_id) {
      $_SESSION['order_uid'] = $order_uid;
    } else {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('failed to %s'), $lang->translate('create order')));
    }
  }
  
  $response = array(
    'result' => $result,
    'order' => $order_uid,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_create_order_customer', 'func_create_order_customer');
add_action('wp_ajax_nopriv_create_order_customer', 'func_create_order_customer');


/*
 * 注文セッション保存Ajax処理
 */
function func_save_ordersession_customer(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  
  $result = true;
  $error = '';
  
  if(!array_key_exists('seat', $_POST)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else if($_POST['seat'] == '') {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
  } else {
    $seat = get_page_by_path($_POST['seat'], OBJECT, 'seat');
    
    if(!$seat) {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('seat'))));
    }
  }
  
  if(!array_key_exists('order', $_POST)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('order')));
  } else if($_POST['order'] == '') {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('order')));
  }
  
  if($result) {
    $time_now = time();
    $select_query = "SELECT * FROM tb_order WHERE uid='" . $_POST['order'] . "' AND seat_id=" . $seat->ID . " AND finish_time is null LIMIT 1";
    $order = $wpdb->get_row($select_query);
    
    if($order) {
      $_SESSION['order_uid'] = $_POST['order'];
    } else {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('%s is not existed'), $lang->translate('order')));
    }
  }
  
  // リポジトリ出力
  $response = array(
    'result' => $result,
    'sql' => $select_query,
    'error' => $error,
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_save_ordersession_customer', 'func_save_ordersession_customer');
add_action('wp_ajax_nopriv_save_ordersession_customer', 'func_save_ordersession_customer');


/*
 * 注文セッション保存Ajax処理
 */
function func_save_peoplenumber_customer(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $error = array();
  
  if(!array_key_exists('order_uid', $_SESSION)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('order'))));
  } else if($_SESSION['order_uid'] == '') {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('failed to %s'), sprintf($lang->translate('get %s information'), $lang->translate('order'))));
  } else {
    $select_query = "SELECT * FROM tb_order WHERE uid='" . $_SESSION['order_uid'] . "' AND finish_time is null LIMIT 1";
    $order = $wpdb->get_row($select_query);
    
    if(!$order) {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('%s is not existed'), $lang->translate('order information')));
    }
  }
  
  if(!array_key_exists('people', $_POST)) {
    $result = false;
    $error = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('people number')));
  } else {
    try {
      $people_number = intval($_POST['people']);
      if($people_number <= 0) {
        $result = false;
        $error = ucfirst(sprintf($lang->translate('%s should be a number over 0'), $lang->translate('people number')));
      }
    } catch(Exception $e) {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('%s should be a number over 0'), $lang->translate('people number')));
    }
  }
  
  if($result) {
    $time_now = time();
    $select_query = "SELECT * FROM tb_order WHERE uid='" . $_SESSION['order_uid'] . "' AND finish_time is null LIMIT 1";
    $order = $wpdb->get_row($select_query);
    
    if($order) {
      if(intval($order->people_number)) {
        $result = false;
        $error = ucfirst($lang->translate('people number has been updated by others'));
      } else {
        $data = array(
          'people_number' => $people_number,
        );
        $format = array('%d',);
        $where = array(
          'id' => $order->id,
        );
        $where_format = array('%d',);
        $updated = $wpdb->update('tb_order', $data, $where, $format, $where_format);
        
        if($updated == false) {
          $result = false;
          $error = ucfirst(sprintf($lang->translate('failed to %s'), $lang->translate('save people number')));
        }
      }
    } else {
      $result = false;
      $error = ucfirst(sprintf($lang->translate('%s is not existed'), $lang->translate('order')));
    }
  }
  
  // リポジトリ出力
  $response = array(
    'result' => $result,
    'error' => $error,
  );
  echo json_encode($response);
  die();
}
add_action('wp_ajax_save_peoplenumber_customer', 'func_save_peoplenumber_customer');
add_action('wp_ajax_nopriv_save_peoplenumber_customer', 'func_save_peoplenumber_customer');

