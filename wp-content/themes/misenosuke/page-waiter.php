<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key, $wpdb;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();


if(is_user_logged_in()) :
  if(current_user_can('waiter')):
    $page_title = ucwords($lang->translate('waiter'));
    $style_key = 'waiter';
    
    $user_id = get_current_user_id();
    $uid = get_field('uid', 'user_' . $user_id);
    $restaurant_id = get_field('restaurant', 'user_' . $user_id);
    $restaurant_uid = get_field('uid', 'user_' . $restaurant_id);
    
    $seats = get_seat_info($restaurant_id);
    $order_list = array();
    
    foreach($seats as $seat) {
      $order_list['s' . $seat['id']] = array();
    }
    
    $order_query = "SELECT * FROM tb_order WHERE restaurant_id = " . $restaurant_id . " AND finish_time IS NULL ORDER BY id ASC";
    $order_results = $wpdb->get_results($order_query);
    if(!empty($order_results)) {
      foreach($order_results as $order_row) {
        if(array_key_exists('s' . $order_row->seat_id, $order_list)) {
          array_push($order_list['s' . $order_row->seat_id], array(
            'uid' => $order_row->uid,
            'people_number' => $order_row->people_number,
          ));
        }
      }
    }
    
    get_header();
?>
  
  <section class="waiter-tab waiter-top withheader active">
    <div class="waiter-tab-inner">
      <div class="waiter-tab-header">
        <p class="waiter-tab-header-text"><?php echo strtoupper($lang->translate('top page')); ?></p>
      </div>
      <div class="waiter-tab-content">
        <ul class="waiter-tab-list">
          <?php 
          foreach($seats as $seat): 
            if(count($order_list['s' . $seat['id']])) {
              $seat_status_key = 'using';
              $seat_status = strtoupper($lang->translate('seat status using'));
            } else {
              $seat_status_key = 'empty';
              $seat_status = strtoupper($lang->translate('seat status empty'));
            }
          ?>
            <li class="waiter-tab-item"  data-seat="<?php echo $seat['slug']; ?>">
              <div class="waiter-tab-item-header">
                <p class="waiter-tab-item-name"><?php echo $seat['name']; ?></p>
                <p class="waiter-tab-item-status status-<?php echo $seat_status_key; ?>"><?php echo $seat_status; ?></p>
              </div>
              <div class="waiter-tab-item-footer">
                <p class="waiter-tab-item-button waiter-top-seatdetail"><?php echo ucwords($lang->translate('detail')); ?></p>
              </div>
            </li>
          <?php endforeach;?>
        </ul>
      </div>
    </div>
  </section>
  
  <section class="waiter-tab waiter-seat withheader">
    <div class="waiter-tab-inner">
      <div class="waiter-tab-header">
        <p class="waiter-tab-header-text"><?php echo strtoupper($lang->translate('top page')); ?></p>
      </div>
      <div class="waiter-tab-content">
        <ul class="waiter-seat-list">
          <?php 
          foreach($seats as $seat): 
            if(count($order_list['s' . $seat['id']])) {
              $seat_status_key = 'using';
              $seat_status = strtoupper($lang->translate('seat status using'));
            } else {
              $seat_status_key = 'empty';
              $seat_status = strtoupper($lang->translate('seat status empty'));
            }
          ?>
            <li class="waiter-seat-item"  data-seat="<?php echo $seat['slug']; ?>">
              <div class="waiter-seat-header">
                <p class="waiter-seat-name"><?php echo $seat['name']; ?></p>
                <p class="waiter-seat-status status-<?php echo $seat_status_key; ?>"><?php echo $seat_status; ?></p>
              </div>
              <div class="waiter-seat-content <?php echo count($order_list['s' . $seat['id']]) ? 'active' : ''; ?>">
                <p class="waiter-seat-order-topic"><?php echo ucfirst($lang->translate('order list')); ?></p>
                <ul class="waiter-seat-order-list">
                  <?php foreach($order_list['s' . $seat['id']] as $order_info): ?>
                    <li class="waiter-seat-order-item" data-order="<?php echo $order_info['uid']; ?>">
                      <p class="waiter-seat-order-uid"><?php echo $order_info['uid']; ?></p>
                      <div class="waiter-seat-order-controller">
                        <p class="waiter-seat-order-button waiter-seat-order-qr"><?php echo ucwords($lang->translate('QR code')); ?></p>
                        <p class="waiter-seat-order-button"><?php echo ucwords($lang->translate('action')); ?></p>
                        <p class="waiter-seat-order-button"><?php echo ucwords($lang->translate('action')); ?></p>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="waiter-seat-footer">
                <p class="waiter-seat-button waiter-seat-addorder"><?php echo ucwords($lang->translate('add order')); ?></p>
              </div>
            </li>
          <?php endforeach;?>
        </ul>
      </div>
    </div>
  </section>
  
  <section class="popup-shadow"></section>
  
  <section class="popup-section waiter-popup-scan">
    <div class="popup-inner waiter-popup-scan-inner">
      <div class="popup-header">
        <?php echo strtoupper($lang->translate('qr scan')); ?>
        <p class="popup-close popup-header-close"></p>
      </div>
      <div class="popup-body waiter-popup-scan-body">
        <video class="waiter-popup-scan-video" id="waiter-popup-scan-video" autoplay></video>
        <p class="waiter-popup-scan-result"></p>
        <input type="hidden" class="waiter-popup-scan-seat" value="" />
      </div>
      <div class="popup-footer">
        <p class="button shine-active waiter-popup-scan-confirm"><?php echo ucwords($lang->translate('confirm')); ?></p>
      </div>
    </div>
  </section>

  <section class="popup-section waiter-popup-order">
    <div class="popup-inner">
      <div class="popup-header">
        <?php echo strtoupper($lang->translate('order qr')); ?>
        <p class="popup-close popup-header-close"></p>
      </div>
      <div class="popup-body">
        <div class="waiter-popup-order-qr" id="waiter-popup-order-qr"></div>
      </div>
      <div class="popup-footer">
        <p class="button popup-close waiter-popup-order-close"><span><?php echo ucwords($lang->translate('close')); ?></span></p>
      </div>
    </div>
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
