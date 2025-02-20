<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key, $order_languages, $wpdb;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

// 席UIDを取得
$seat_uid = '';
if(array_key_exists('seat_uid', $_GET)) {
  // 直接URLで指定する場合
  $seat_uid = $_GET['seat_uid'];
} elseif(array_key_exists('seat_uid', $_SESSION)) {
  // Sessionから取得する場合
  $seat_uid = $_SESSION['seat_uid'];
}

if(!$seat_uid): 
  // 席UIDを取得できない場合、ページエラーテンプレートを切り替え
  include(locate_template('404.php'));
else: 
  // 席情報を取得
  $seat = get_page_by_path($seat_uid, OBJECT, 'seat');
  
  if(!$seat):
    // 席情報を取得できない場合、ページエラーテンプレートを切り替え
    include(locate_template('404.php'));
  else:
    // 全域編集値付与
    $page_title = ucwords($lang->translate('order'));
    $style_key = 'order';
    
    // 店情報取得
    $restaurant_id = intval($seat->post_author);
    $restaurant_uid = get_field('uid', 'user_' . $restaurant_id);
    $restaurant_logo_id = get_field('restaurant_logo', 'user_' . $restaurant_id);
    $restaurant_name = get_field('restaurant_name', 'user_' . $restaurant_id);
    if($lang->code() != 'ja') {
      foreach(get_field('restaurant_names', 'user_' . $restaurant_id) as $lang_info) {
        if($lang->code() == $lang_info['language']) {
          $restaurant_name = $lang_info['name'];
          break;
        }
      }
    }
    
    // 利用可能言語取得
    $order_languages = explode(",", get_field('languages', 'user_' . $restaurant_id));
    array_unshift($order_languages, 'ja');
    
    // Sessionから注文情報を取得
    $order_uid = '';
    $order = NULL;
    if(array_key_exists('order_uid', $_SESSION)) {
      // Sessionの注文情報取得
      $sql_select_order = "SELECT * FROM tb_order WHERE uid='" . $_SESSION['order_uid'] . "' AND finish_time IS NULL LIMIT 1";
      $order = $wpdb->get_row($sql_select_order);
      
      if($order) {
        if(intval($order->restaurant_id) == $restaurant_id) {
          // Sessionの注文の店は現在の店と一致する場合
          $order_uid = $_SESSION['order_uid'];
        } else {
// 別店の未完了注文がある場合
        }
      } else {
        // Sessionの注文情報が取得できない場合、Sessionをクリア
        $_SESSION['order_uid'] = '';
      }
    }
    
    // URLで注文UIDを指定
    if(array_key_exists('order_uid', $_GET)) {
      if($order_uid && $_GET['order_uid'] != $order_uid) {
// 同店の未完了注文がある場合
      } elseif(!$order) {
        // Sessionからの注文情報がない場合、URLで注文情報取得
        $sql_select_order = "SELECT * FROM tb_order WHERE uid='" . $_GET['order_uid'] . "' AND finish_time is null LIMIT 1";
        $order = $wpdb->get_row($sql_select_order);
        
        if($order) {
          $order_uid = $_GET['order_uid'];
        }
      }
    }
    
    // 注文UIDをSESSIONに更新
    $_SESSION['order_uid'] = $order_uid;
    
    // 注文人数取得
    $order_people = $order ? intval($order->people_number) : 0;
    
    // 通信用顧客UIDを初期化
    if(array_key_exists('customer_uid', $_SESSION)) {
      $customer_uid = $_SESSION['customer_uid'];
    } else {
      $customer_uid = md5(uniqid(microtime(), true));
      $_SESSION['customer_uid'] = $customer_uid;
    }
    
    // コースとメニュー情報を取得
    $course_info_list = get_course_info($restaurant_id, true);
    $menu_info_list = get_menu_info_with_category($restaurant_id, true);
    
    // 税込み/税抜き表記
    $tax_type_text = get_field('tax_include', 'user_' . $restaurant_id) ? $lang->translate('tax included') : $lang->translate('tax excluded');
    
    // メニュータグキーワードリストを定義
    $tag_text_list = array(
      'new' => '新',
      'limited' => '限',
      'great' => '得',
      'special' => '特',
      'recommendation' => '薦',
      'no1' => '１',
      'no2' => '２',
      'no3' => '３',
    );
    
    get_header();
?>
  <input type="hidden" class="order-info-restaurant" value="<?php echo $restaurant_uid; ?>" />
  <input type="hidden" class="order-info-seat" value="<?php echo $seat_uid; ?>" />
  <input type="hidden" class="order-info-order" value="<?php echo $order_uid; ?>" />
  <input type="hidden" class="order-info-customer" value="<?php echo $customer_uid; ?>" />
  <input type="hidden" class="order-info-peoplenumber" value="<?php echo $order_people; ?>" />
  
  <?php if(!$order_uid): ?>
    <section class="order-tab order-top active">
      <div class="order-tab-inner">
        <div class="order-tab-content order-top-content">
          <?php 
          if($restaurant_logo_id): 
            $restaurant_logo_url = wp_get_attachment_url($restaurant_logo_id);
            $restaurant_logo_meta = wp_get_attachment_metadata($restaurant_logo_id);
            $restaurant_logo_padding = '0';
            if($restaurant_logo_meta) {
              $restaurant_logo_width = intval($restaurant_logo_meta['width']);
              $restaurant_logo_height = intval($restaurant_logo_meta['height']);
              $restaurant_logo_height_style = strval(70 / $restaurant_logo_width * $restaurant_logo_height) . 'vw';
            }
          ?>
            <div class="order-top-logo" style="background-image: url(<?php echo $restaurant_logo_url; ?>); height: <?php echo $restaurant_logo_height_style; ?>"></div>
          <?php endif; ?>
          <div class="order-top-welcome">
            <?php echo ucfirst(sprintf($lang->translate('welcome to %s'), '<p class="order-top-welcome-name">' . $restaurant_name . '</p>')); ?>
          </div>
          <p class="order-top-seat"><?php echo ucwords($lang->translate('seat code:')); ?><?php echo get_the_title($seat->ID); ?></p>
          <p class="button order-top-startorder"><?php echo ucwords($lang->translate('start ordering')); ?></p>
        </div>
      </div>
    </section>
  <?php endif; ?>
  
  <?php if(!$order_people): ?>
    <section class="order-tab order-people withheader <?php echo $order_uid ? 'active' : ''; ?>">
      <div class="order-tab-inner">
        <div class="order-tab-header">
          <?php echo ucwords($lang->translate('order number')); ?>
          <p class="order-ordernumber"><?php echo $order_uid; ?></p>
        </div>
        <div class="order-tab-content">
          <div class="form order-people-number">
            <div class="order-people-line">
              <p class="order-people-number-title"><?php echo ucwords($lang->translate('client')); ?><p>
              <div class="spinner order-people-spinner">
                <p class="spinner-minus">－</p>
                <input type="number" class="order-people-input" value="0" />
                <p class="spinner-plus">＋</p>
              </div>
              <p><?php echo ucwords($lang->translate('people')); ?></p>
            </div>
            <p class="button shine-active order-people-submit"><?php echo ucwords($lang->translate('continue')); ?></p>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
  
  <section class="order-tab order-menu withheader withfooter <?php echo ($order_uid && $order_people) ? 'active' : ''; ?>">
    <div class="order-tab-inner">
      <div class="order-tab-header">
        <?php echo ucwords($lang->translate('order number')); ?>
        <p class="order-ordernumber"><?php echo $order_uid; ?></p>
      </div>
      <div class="order-tab-content">
        <form class="form" id="order-menu-form">
          <div class="order-menu-course">
          </div>
          <div class="order-menu-dish">
            <div class="order-menu-category" id="order-menu-dish-category-course">
              <div class="order-menu-category-header">
                <div class="order-menu-category-header-inner">
                  <p class="order-menu-category-name"><?php echo ucwords($lang->translate('course')); ?></p>
                  <p class="order-menu-category-slidehandler"></p>
                </div>
              </div>
              <div class="order-menu-category-body">
                <ul class="order-menu-category-list">
                  <?php 
                  foreach($course_info_list as $course_info): 
                    $course_id = $course_info['id'];
                    $course = get_post($course_id);
                    $course_image_id = get_field('course_image', $course_id);
                    $course_tags = get_field('tag', $course_id);
                    $course_options = get_field('option', $course_id);
                  ?>
                    <li class="order-menu-menu" data-slug="<?php echo $course_info['slug']; ?>" id="order-menu-dish-menu-<?php echo $course_info['slug']; ?>">
                      <ul class="order-menu-tag-list">
                        <?php if(is_array($course_tags)): ?>
                          <?php foreach($course_tags as $course_tag): ?>
                            <li class="order-menu-tag-item <?php echo $course_tag['value']; ?>"><?php echo $tag_text_list[$course_tag['value']]; ?></li>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </ul>
                      <?php if($course_image_id): ?>
                        <p class="order-menu-image">
                          <img src="<?php echo wp_get_attachment_url($course_image_id); ?>" />
                        </p>
                      <?php endif; ?>
                      <div class="order-menu-info">
                        <div class="order-menu-topic">
                          <p class="order-menu-name"><?php echo $course_info['name']; ?></p>
                          <p class="order-menu-price">
                            &yen;<span class="order-menu-price-number"><?php echo number_format(intval(get_field('price', $course_id))); ?></span>
                            <span class="order-menu-price-type">(<?php echo $tax_type_text; ?>)</span>
                          </p>
                        </div>
                        <p class="order-menu-description"><?php echo nl2br($lang->get_text($course->post_content, $lang->code())); ?></p>
                        <?php if(get_field('use_for_every', $course_id)): ?>
                          <label class="checkbox order-menu-courseuse">
                            <input type="checkbox" class="order-menu-peoplenumber" name="number_<?php echo $course_info['slug']; ?>" value="0" />
                            <?php echo $lang->translate('order this course'); ?>
                          </label>
                        <?php else: ?>
                          <div class="spinner order-menu-spinner">
                            <p class="spinner-minus">－</p>
                            <input type="number" class="order-menu-spinner-number" name="number_<?php echo $course_info['slug']; ?>" value="0" min="0" />
                            <p class="spinner-plus">＋</p>
                          </div>
                        <?php endif; ?>
                        <ul class="order-menu-option-list">
                          <?php if(is_array($course_options)): ?>
                            <?php foreach($course_options as $option_index => $course_option): ?>
                              <li class="order-menu-option-item">
                                <p class="order-menu-option-title"><?php echo $course_option['name']; ?></p>
                                <?php if(is_array($course_option['choices'])): ?>
                                  <?php foreach($course_option['choices'] as $choice_index => $course_choice): ?>
                                    <label class="radio order-menu-choice">
                                      <input type="radio" name="option_<?php echo $course_info['slug']; ?>_<?php echo $option_index; ?>" 
                                             value="<?php echo $choice_index; ?>" <?php echo $choice_index ? '' : 'checked'; ?> />
                                      <?php echo $course_choice['name']; ?>
                                        <?php if($course_choice['price'] > 0): ?>
                                          (+&yen;<?php echo number_format(intval($course_choice['price'])); ?>)
                                        <?php elseif($course_choice['price'] < 0): ?>
                                          (-&yen;<?php echo number_format(0 - intval($course_choice['price'])); ?>)
                                        <?php else: ?>
                                          (<?php echo ucwords($lang->translate('free price')); ?>)
                                        <?php endif;?>
                                    </label>
                                  <?php endforeach; ?>
                                <?php endif; ?>
                              </li>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </ul>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <?php 
            foreach($menu_info_list as $menu_category_info): 
              $common_options = get_field('common_option', 'term_' . $menu_category_info['id']);
            ?>
              <div class="order-menu-category" id="order-menu-dish-category-<?php echo $menu_category_info['slug']; ?>">
                <div class="order-menu-category-header">
                  <div class="order-menu-category-header-inner">
                    <p class="order-menu-category-name"><?php echo $menu_category_info['name']; ?></p>
                    <p class="order-menu-category-slidehandler"></p>
                  </div>
                </div>
                <div class="order-menu-category-body">
                  <ul class="order-menu-category-list">
                    <?php 
                    foreach($menu_category_info['menus'] as $menu_info): 
                      $menu_id = $menu_info['id'];
                      $menu = get_post($menu_id);
                      $menu_image_id = get_field('menu_image', $menu_id);
                      $menu_tags = get_field('tag', $menu_id);
                      $menu_options = get_field('option', $menu_id);
                    ?>
                      <li class="order-menu-menu" data-slug="<?php echo $menu_info['slug']; ?>" id="order-menu-dish-menu-<?php echo $menu_info['slug']; ?>">
                        <ul class="order-menu-tag-list">
                          <?php if(is_array($menu_tags)): ?>
                            <?php foreach($menu_tags as $menu_tag): ?>
                              <li class="order-menu-tag-item <?php echo $menu_tag['value']; ?>"><?php echo $tag_text_list[$menu_tag['value']]; ?></li>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </ul>
                        <?php if($menu_image_id): ?>
                          <p class="order-menu-image">
                            <img src="<?php echo wp_get_attachment_url($menu_image_id); ?>" />
                          </p>
                        <?php endif; ?>
                        <div class="order-menu-info">
                          <div class="order-menu-topic">
                            <p class="order-menu-name"><?php echo $menu_info['name']; ?></p>
                            <p class="order-menu-price">
                              &yen;<span class="order-menu-price-number"><?php echo number_format(intval(get_field('price', $menu_id))); ?></span>
                              <span class="order-menu-price-type">(<?php echo $tax_type_text; ?>)</span>
                            </p>
                          </div>
                          <p class="order-menu-description"><?php echo nl2br($lang->get_text($menu->post_content, $lang->code())); ?></p>
                          <div class="spinner order-menu-spinner">
                            <p class="spinner-minus">－</p>
                            <input type="number" class="order-menu-spinner-number" name="number_<?php echo $menu_info['slug']; ?>" value="0" min="0" />
                            <p class="spinner-plus">＋</p>
                          </div>
                          <ul class="order-menu-option-list">
                            <?php if(is_array($common_options)): ?>
                              <?php foreach($common_options as $option_index => $common_option): ?>
                                <li class="order-menu-option-item">
                                  <p class="order-menu-option-title"><?php echo $common_option['name']; ?></p>
                                  <?php if(is_array($common_option['choices'])): ?>
                                    <?php foreach($common_option['choices'] as $choice_index => $menu_choice): ?>
                                      <label class="radio order-menu-choice">
                                        <input type="radio" name="commonoption_<?php echo $menu_info['slug']; ?>_<?php echo $option_index; ?>" 
                                               value="<?php echo $choice_index; ?>" <?php echo $choice_index ? '' : 'checked'; ?> />
                                        <?php echo $menu_choice['name']; ?>
                                        <?php if($menu_choice['price'] > 0): ?>
                                          (+&yen;<?php echo number_format(intval($menu_choice['price'])); ?>)
                                        <?php elseif($menu_choice['price'] < 0): ?>
                                          (-&yen;<?php echo number_format(0 - intval($menu_choice['price'])); ?>)
                                        <?php else: ?>
                                          (<?php echo ucwords($lang->translate('free price')); ?>)
                                        <?php endif;?>
                                      </label>
                                    <?php endforeach; ?>
                                  <?php endif;?>
                                </li>
                              <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if(is_array($menu_options)): ?>
                              <?php foreach($menu_options as $option_index => $menu_option): ?>
                                <li class="order-menu-option-item">
                                  <p class="order-menu-option-title"><?php echo $menu_option['name']; ?></p>
                                  <?php if(is_array($menu_option['choices'])): ?>
                                    <?php foreach($menu_option['choices'] as $choice_index => $menu_choice): ?>
                                      <label class="radio order-menu-choice">
                                        <input type="radio" name="option_<?php echo $menu_info['slug']; ?>_<?php echo $option_index; ?>" 
                                               value="<?php echo $choice_index; ?>" <?php echo $choice_index ? '' : 'checked'; ?> />
                                        <?php echo $menu_choice['name']; ?>
                                        <?php if($menu_choice['price'] > 0): ?>
                                          (+&yen;<?php echo number_format(intval($menu_choice['price'])); ?>)
                                        <?php elseif($menu_choice['price'] < 0): ?>
                                          (-&yen;<?php echo number_format(0 - intval($menu_choice['price'])); ?>)
                                        <?php else: ?>
                                          (<?php echo ucwords($lang->translate('free price')); ?>)
                                        <?php endif;?>
                                      </label>
                                    <?php endforeach; ?>
                                  <?php endif;?>
                                </li>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </ul>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </form>
      </div>
    </div>
    <div class="order-tab-footer">
      <p class="button order-menu-submit"><?php echo ucwords($lang->translate('order')); ?></p>
    </div>
  </section>
  
  <section class="popup-message">
    <p class="popup-message-text"></p>
  </section>
  
  <section class="popup-shadow"></section>
  
  <section class="popup-section order-popup order-join-popup">
    <div class="popup-inner">
      <div class="popup-header">
        <?php echo strtoupper($lang->translate('start ordering')); ?>
        <p class="popup-close popup-header-close"></p>
      </div>
      <div class="popup-body order-join-body">
        <p class="order-join-text"><?php echo ucfirst($lang->translate('there has been customer is ordering')); ?></p>
        <div class="order-join-choice">
          <div class="order-join-choice-item">
            <div class="order-join-choice-message">
              <p class="order-join-choice-text"><?php echo ucfirst($lang->translate('create a new order to pay separately')); ?></p>
            </div>
            <p class="button order-join-button order-join-create"><?php echo ucwords($lang->translate('create')); ?></p>
          </div>
          <div class="order-join-choice-item">
            <div class="order-join-choice-message">
              <p class="order-join-choice-text"><?php echo ucfirst($lang->translate('apply to order and pay together')); ?></p>
            </div>
            <p class="button order-join-button order-join-join"><?php echo ucwords($lang->translate('apply')); ?></p>
          </div>
        </div>
        <div class="form order-join-info">
          <div class="form-line">
            <p class="form-title"><?php echo ucwords($lang->translate('customer name')); ?></p>
            <div class="form-input">
              <input type="text" class="order-join-name" name="join_name" />
              <p class="warning warning-join_name"></p>
            </div>
          </div>
          <div class="form-line">
            <p class="form-title"><?php echo ucwords($lang->translate('order number')); ?></p>
            <div class="form-input">
              <select class="order-join-number">
                <option value="" name="join_order" disabled class="hidden" selected><?php echo ucfirst($lang->translate('select the order to join please')); ?></option>
              </select>
              <p class="warning warning-join_order"></p>
            </div>
          </div>
          <p class="button order-join-button order-join-apply"><?php echo ucwords($lang->translate('send')); ?></p>
        </div>
      </div>
    </div>
  </section>
  
  <section class="popup-section order-popup order-joinapprove-popup">
    <div class="popup-inner">
      <div class="popup-header">
        <?php echo strtoupper($lang->translate('join approval')); ?>
        <p class="popup-close popup-header-close"></p>
      </div>
      <div class="form popup-body">
        <p class="order-joinapprove-text"><?php echo ucfirst(sprintf($lang->translate('%s want to order and pay together with you'), '<span class="order-joinapprove-name"></span>')); ?></p>
        <input type="hidden" class="order-joinapprove-applicant" value="" />
        <div class="form-input">
          <label class="checkbox checkbox-center">
            <input type="checkbox" class="order-joinapprove-remember" />
            <?php echo ucfirst($lang->translate('stop to send a same application when I refuse')); ?>
          </label>
        </div>
      </div>
      <div class="popup-footer order-joinapprove-footer">
        <p class="button popup-close order-joinapprove-refuse"><?php echo ucwords($lang->translate('refuse')); ?></p>
        <p class="button order-joinapprove-approve"><?php echo ucwords($lang->translate('approve')); ?></p>
      </div>
    </div>
  </section>

<?php 
    get_footer();
    
  endif;
endif;
