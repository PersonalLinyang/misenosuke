<?php

/*
 * 席簡易情報取得Ajax処理
 */
function func_get_seat_simple_waiter(){
  global $wpdb;
  
  $result = true;
  $seat_name = '';
  $error = '';
  
  if(!is_user_logged_in()){
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } elseif(!current_user_can('waiter')) {
    // ログインユーザーが接客係ではない場合エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('you have no permission to get seat infomation'));
  }
  
  if(!array_key_exists('seat', $_GET)) {
    $result = false;
    $error = ucfirst($lang->translate('failed to get seat information'));
  }
  
  if($result) {
    $user_id = get_current_user_id();
    $seat = get_page_by_path($_GET['seat'], OBJECT, 'seat');
    
    if(!$seat) {
      $result = false;
      $error = ucfirst($lang->translate('failed to get seat information'));
    } else {
      $restaurant_id = get_field('restaurant', 'user_' . $user_id);
      
      if($restaurant_id == $seat->post_author) {
        $seat_name = $seat->post_title;
      } else {
        $result = false;
        $error = ucfirst($lang->translate('you can not access this seat'));
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'name' => $seat_name,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_seat_simple_waiter', 'func_get_seat_simple_waiter');
add_action('wp_ajax_nopriv_get_seat_simple_waiter', 'func_get_seat_simple_waiter');



/*
 * 席詳細情報取得Ajax処理
 */
function func_get_seat_detail_waiter(){
  global $wpdb;
  
  $result = true;
  $seat_name = '';
  $order_uid = '';
  $error = '';
  
  if(!is_user_logged_in()){
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } elseif(!current_user_can('waiter')) {
    // ログインユーザーが接客係ではない場合エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('you have no permission to get seat infomation'));
  }
  
  if(!array_key_exists('seat', $_GET)) {
    $result = false;
    $error = ucfirst($lang->translate('failed to get seat information'));
  }
  
  if($result) {
    $seat = get_page_by_path($_GET['seat'], OBJECT, 'seat');
    
    if(!$seat) {
      $result = false;
      $error = ucfirst($lang->translate('failed to get seat information'));
    } else {
      $user_id = get_current_user_id();
      $restaurant_id = get_field('restaurant', 'user_' . $user_id);
      
      if($restaurant_id != $seat->post_author) {
        $result = false;
        $error = ucfirst($lang->translate('you can not access this seat'));
      } else {
        $seat_name = $seat->post_title;
        
        if(array_key_exists('order', $_GET)) {
          $order_uid = $_GET['order'];
        }
        
        $time_now = time();
        $order_query = "SELECT * FROM tb_order WHERE seat_id=" . $seat->ID . " AND start_time<" . $time_now . " AND (finish_time is null OR finish_time>" . $time_now . ")";
        $orders = $wpdb->get_results($order_query);
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'name' => $seat_name,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_seat_detail_waiter', 'func_get_seat_detail_waiter');
add_action('wp_ajax_nopriv_get_seat_detail_waiter', 'func_get_seat_detail_waiter');
