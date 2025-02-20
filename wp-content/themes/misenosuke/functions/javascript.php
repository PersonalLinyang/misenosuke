<?php


/*
 * Jquery翻訳リスト取得
 */
function get_js_translations($key) {
  $lang = new LanguageSupporter();
  $translations = array();
  
  switch($key) {
    case 'page_invoice':
      $translations = array(
        'print_invoice_document' => $lang->translate('print invoice document'),
      );
      break;
    case 'page_reciept':
      $translations = array(
        'print_reciept_document' => $lang->translate('print reciept document'),
      );
      break;
    case 'page_export-seat':
      $translations = array(
        'print_exportseat_document' => $lang->translate('print seat QR code'),
      );
      break;
    case 'page_change-password':
      $translations = array(
        'pwdchange_success' => $lang->translate('changed password successfully'),
      );
      break;
    case 'page_member':
      $translations = array(
        'ajax_delete_employee_success' => $lang->translate('deleted employee successfully'),
        'ajax_delete_employee_failed' => $lang->translate('failed to delete employee'),
      );
      break;
    case 'page_waiter':
      $translations = array(
        'ajax_get_seat_failed' => $lang->translate('failed to get seat information'),
      );
      break;
    case 'page_order':
      $translations = array(
        'join_name_is_required' => $lang->translate('input your name please'),
        'join_order_is_required' => $lang->translate('select the order to join please'),
        'people_number_is_updated' => $lang->translate('people number is updated, start ordering please'),
        'ajax_get_seat_failed' => $lang->translate('failed to get seat information'),
        'ajax_create_order_failed' => $lang->translate('failed to create order'),
        'ajax_create_order_success' => $lang->translate('created order successfully'),
        'ajax_join_order_failed' => $lang->translate('failed to join order'),
        'ajax_join_order_success' => $lang->translate('joined order successfully'),
        'ajax_join_order_refused' => $lang->translate('application to join order is refused, check the order number please'),
        'ajax_save_people_failed' => $lang->translate('failed to save people number'),
        'ajax_save_people_success' => $lang->translate('saved people number successfully'),
      );
      break;
    case 'page_manage-menu-category':
      $translations = array(
        'add' => $lang->translate('add'),
        'add_choice' => $lang->translate('add choice'),
        'ajax_delete_failed' => $lang->translate('failed to delete menu category'),
        'ajax_delete_success' => $lang->translate('deleted successfully'),
        'ajax_get_failed' => $lang->translate('failed to get data of menu category'),
        'ajax_save_failed' => $lang->translate('failed to save menu category'),
        'ajax_save_priority_failed' => $lang->translate('failed to save the proirity of menu category'),
        'ajax_save_success' => $lang->translate('saved successfully'),
        'choices' => $lang->translate('choices'),
        'copy' => $lang->translate('copy and create new'),
        'edit' => $lang->translate('edit'),
        'item' => $lang->translate('item'),
        'menu' => $lang->translate('menu'),
        'menu_count' => $lang->translate('menu count'),
        'no_choice' => $lang->translate('there is no choice for this option'),
        'save' => $lang->translate('save'),
      );
      break;
    case 'page_manage-menu':
      $translations = array(
        'ajax_get_list_success' => $lang->translate('get your menu data successfully'),
        'ajax_get_list_failed' => $lang->translate('failed to get your menu data'),
        'ajax_save_category_failed' => $lang->translate('failed to save menu category'),
        'ajax_save_menu_failed' => $lang->translate('failed to save menu'),
        'add_menu_category' => $lang->translate('add menu category'),
        'edit_menu_category' => $lang->translate('edit menu category'),
        'copy_menu_category' => $lang->translate('copy menu category'),
        'delete_menu_category' => $lang->translate('delete menu categroy'),
        'add_menu' => $lang->translate('add menu'),
        'edit_menu' => $lang->translate('edit menu'),
        'copy_menu' => $lang->translate('copy menu'),
        'category_without_menu' => $lang->translate('no menu in this category'),
        'edit_menu' => $lang->translate('edit menu'),
        'copy_menu' => $lang->translate('copy menu'),
        'delete_menu' => $lang->translate('delete menu'),
        'menu' => $lang->translate('menu'),
      );
      break;
    case 'page_manage-course':
      $translations = array(
        'ajax_get_list_success' => $lang->translate('get your course data successfully'),
        'ajax_get_list_failed' => $lang->translate('failed to get your course data'),
        'add_course' => $lang->translate('add course'),
        'edit_course' => $lang->translate('edit course'),
        'copy_course' => $lang->translate('copy course'),
        'delete_course' => $lang->translate('delete course'),
      );
      break;
    case 'page_manage-seat':
      $translations = array(
        'ajax_get_list_success' => $lang->translate('get your seat data successfully'),
        'ajax_get_list_failed' => $lang->translate('failed to get your seat data'),
        'add_seat' => $lang->translate('add seat'),
        'edit_seat' => $lang->translate('edit seat'),
        'copy_seat' => $lang->translate('copy seat'),
        'delete_seat' => $lang->translate('delete seat'),
      );
      break;
    case 'manage':
      $translations = array(
        'add' => $lang->translate('add'),
        'save' => $lang->translate('save'),
        'saving_message' => $lang->translate('data is saving now, wait a moment please'),
        'save_unactive' => $lang->translate('cannot save because some error occured, try to load data again please'),
        'ajax_delete_failed' => $lang->translate('failed to delete'),
        'ajax_delete_success' => $lang->translate('deleted successfully'),
        'ajax_delete_nothing' => $lang->translate('deleted nothing because failed to get target data'),
        'ajax_get_failed' => $lang->translate('failed to get current data'),
        'ajax_save_nothing' => $lang->translate('saved nothing because failed to get target data'),
        'ajax_save_priority_failed' => $lang->translate('failed to save the proirity'),
        'ajax_save_success' => $lang->translate('saved successfully'),
        'choices' => $lang->translate('choices'),
        'item' => $lang->translate('item'),
        'add_choice' => $lang->translate('add choice'),
        'no_choice' => $lang->translate('no choice for this option'),
      );
      break;
    case 'profile':
      $translations = array(
        'restaurant_name' => $lang->translate('restaurant name'),
        'zipcode_wrong' => $lang->translate('zipcode is wrong'),
        'get_address_failed' => $lang->translate('failed to get address'),
        'zipcode_required' => sprintf($lang->translate('%s is required'), $lang->translate('zipcode')),
        'save_success' => $lang->translate('saved successfully'),
      );
      break;
    case 'stripe':
      $translations = array(
        'incomplete_number' => $lang->translate('this is a incomplete card number'),
        'incomplete_expiry' => $lang->translate('this is a incomplete card expiration'),
        'invalid_expiry_year_past' => $lang->translate('the card expiration should be in the future'),
        'incomplete_cvc' => $lang->translate('this is a incomplete card security code'),
        'card_validate_error' => $lang->translate('there is an error to validate credit card'),
      );
      break;
    case 'datepicker':
      $translations = array(
        'prev_month' => $lang->translate('prev month'),
        'next_month' => $lang->translate('next month'),
      );
      break;
  }
  
  return $translations;
}

/*
 * Jquery呼び出しと内容翻訳設定
 */
function my_enqueue_scripts() {
  $post = get_post();
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  $lang = new LanguageSupporter();
  $translations = array();
  
  $user_id = get_current_user_id();
  
  wp_enqueue_script('js_jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js', array(), false);
  wp_enqueue_script('js_jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('js_jquery'), false);
  wp_enqueue_script('js_jquery_touch', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array('js_jquery_ui'), false);
  
  $js_common_path = 'assets/js/common/common.js';
  wp_enqueue_script('js_common', get_theme_file_uri($js_common_path), array('js_jquery_touch'), filemtime(get_theme_file_path($js_common_path)));

  if(is_page()) {
    if(is_page('signup') || is_page('register-card')) {
      wp_enqueue_script('js_stripe_inc', 'https://js.stripe.com/v3/', array(), false);
      
      $js_stripe_path = 'assets/js/common/stripe.js';
      wp_enqueue_script('js_stripe', get_theme_file_uri($js_stripe_path), array('js_stripe_inc'), filemtime(get_theme_file_path($js_stripe_path)));
      wp_localize_script('js_stripe', 'stripe_public_key', STRIPE_PUBLIC_KEY);
      $translations = array_merge($translations, get_js_translations('stripe'));
    }
    
    if(is_page('cancel-subscription') || is_page('manage-course')) {
      $translations = array_merge($translations, get_js_translations('datepicker'));
    }
    
    if(is_page('invoice') || is_page('reciept') || is_page('export-seat')) {
      wp_enqueue_script('js_jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js', array('js_jquery'), false);
      wp_enqueue_script('js_html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array('js_jquery'), false);
      
//      $js_pdf_path = 'assets/js/common/pdf.js';
//      wp_enqueue_script('js_pdf', get_theme_file_uri($js_pdf_path), array('js_jspdf', 'js_html2canvas'), filemtime(get_theme_file_path($js_pdf_path)));
    }
    
    if(is_page('member') || is_page('waiter') || is_page('export-seat')) {
      wp_enqueue_script('js_qrcode', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array('js_jquery'), false);
    }
    
    if(is_page('signup') || is_page('edit-profile')) {
      $js_profile_path = 'assets/js/common/profile.js';
      wp_enqueue_script('js_profile', get_theme_file_uri($js_profile_path), array('js_jquery'), filemtime(get_theme_file_path($js_profile_path)));
      $translations = array_merge($translations, get_js_translations('profile'));
    }
    
    if(is_page('manage-course') || is_page('manage-menu') || is_page('manage-seat')) {
      $js_manage_path = 'assets/js/common/manage.js';
      wp_enqueue_script('js_managemenu', get_theme_file_uri($js_manage_path), array('js_jquery'), filemtime(get_theme_file_path($js_manage_path)));
      $translations = array_merge($translations, get_js_translations('manage'));
    }
    
    if(is_page('manage-course')) {
      wp_enqueue_script('js_select', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('js_jquery'), false);
    }
    
    if(is_page('waiter')) {
      $js_qr_path = 'assets/js/inc/jsQR.js';
      wp_enqueue_script('js_qr', get_theme_file_uri($js_qr_path), array('js_jquery'), filemtime(get_theme_file_path($js_qr_path)));
    }
    
    if(is_page('waiter') || is_page('order')) {
      wp_localize_script('js_jquery', 'socket_server', SOCKET_SERVER);
    }
    
    $js_page_path = 'assets/js/page/' . $post->post_name . '.js';
    if(file_exists(get_theme_file_path($js_page_path))) {
      wp_enqueue_script('js_page', get_theme_file_uri($js_page_path), array('js_jquery_touch'), filemtime(get_theme_file_path($js_page_path)));
      $translations = array_merge($translations, get_js_translations('page_' . $post->post_name));
    }
  }
  
  wp_localize_script('js_jquery', 'simple_domain', get_site_url());
  wp_localize_script('js_jquery', 'lang_domain', LANG_DOMAIN);
  wp_localize_script('js_jquery', 'ajaxurl', admin_url('admin-ajax.php'));
  wp_localize_script('js_jquery', 'languages', $lang->get_lang_list());
  wp_localize_script('js_jquery', 'translations', $translations);
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_scripts' );