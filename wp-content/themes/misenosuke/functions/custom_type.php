<?php

/**
 * カスタム投稿タイプとカスタムタクソノミの登録
 */
function create_custom_post_type() {
    require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
    
    $lang = new LanguageSupporter();
    
    // カスタム投稿タイプ「メニュー」を登録
    register_post_type('menu', array(
        'labels' => array(
            'name' => ucwords($lang->translate('menus')),
            'singular_name' => ucwords($lang->translate('menu')),
            'add_new' => ucwords($lang->translate('add new menu')),
            'add_new_item' => ucwords($lang->translate('add new menu')),
            'edit_item' => ucwords($lang->translate('edit menu')),
        ),
        'public' => true,  // 公開設定
        'show_in_menu' => true,  // メニューに表示
        'show_in_admin_bar' => true, // 管理バーに表示
        'show_ui' => true, // UIを表示する設定
        'supports' => array('title', 'editor', 'custom-fields'),  // サポートする機能
        'menu_icon' => 'dashicons-clipboard', // メニューアイコンの設定
        'menu_position' => 101, 
    ));
    
    // タクソノミー「メニューカテゴリ」追加
    register_taxonomy( 
        'menu_category',
        array('menu'),
        array(
            'labels' => array(
                'name' => ucwords($lang->translate('menu category')),
                'edit_item' => ucwords($lang->translate('edit')),
                'update_item' => ucwords($lang->translate('update')),
                'add_new_item' => ucwords($lang->translate('add new menu category'))
            ),
            'meta_box_cb' => 'post_categories_meta_box',
        ) 
    );
    
    // カスタム投稿タイプ「コース」を登録
    register_post_type('course', array(
        'labels' => array(
            'name' => ucwords($lang->translate('courses')),
            'singular_name' => ucwords($lang->translate('course')),
            'add_new' => ucwords($lang->translate('add new course')),
            'add_new_item' => ucwords($lang->translate('add new course')),
            'edit_item' => ucwords($lang->translate('edit course')),
        ),
        'public' => true,  // 公開設定
        'show_in_menu' => true,  // メニューに表示
        'show_in_admin_bar' => true, // 管理バーに表示
        'show_ui' => true, // UIを表示する設定
        'supports' => array('title', 'editor', 'custom-fields'),  // サポートする機能
        'menu_icon' => 'dashicons-list-view', // メニューアイコンの設定
        'menu_position' => 102, 
    ));
    
    // カスタム投稿タイプ「席」を登録
    register_post_type('seat', array(
        'labels' => array(
            'name' => ucwords($lang->translate('seat')),
            'singular_name' => ucwords($lang->translate('seat')),
            'add_new' => ucwords($lang->translate('add new seat')),
            'add_new_item' => ucwords($lang->translate('add new seat')),
            'edit_item' => ucwords($lang->translate('edit seat')),
        ),
        'public' => true,  // 公開設定
        'show_in_menu' => true,  // メニューに表示
        'show_in_admin_bar' => true, // 管理バーに表示
        'show_ui' => true, // UIを表示する設定
        'supports' => array('title', 'custom-fields'),  // サポートする機能
        'menu_icon' => 'dashicons-layout', // メニューアイコンの設定
        'menu_position' => 103, 
    ));
    
    // カスタム投稿タイプ「プラン」を登録
    register_post_type('plan', array(
        'labels' => array(
            'name' => ucwords($lang->translate('plans')),
            'singular_name' => ucwords($lang->translate('plan')),
            'add_new' => ucwords($lang->translate('add new plan')),
            'add_new_item' => ucwords($lang->translate('add new plan')),
            'edit_item' => ucwords($lang->translate('edit plan')),
        ),
        'public' => true,  // 公開設定
        'show_in_menu' => true,  // メニューに表示
        'show_in_admin_bar' => true, // 管理バーに表示
        'show_ui' => true, // UIを表示する設定
        'supports' => array('title', 'editor', 'custom-fields'),  // サポートする機能
        'menu_icon' => 'dashicons-awards', // メニューアイコンの設定
        'menu_position' => 103, 
    ));
}

add_action('init', 'create_custom_post_type');


/**
 * 条件付きメニューカテゴリでグループしたメニュー情報を取得
 */
function get_menu_info_with_category($restaurant_id, $for_ordering=false) {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  // カテゴリリストを整理
  function push_category($menu_info, $category_id, $category_slug, $menus) {
    $lang = new LanguageSupporter();
    
    if($category_id) {
      // カテゴリあり
      array_push($menu_info, array(
        'id' => intval($category_id),
        'name' => get_field('name', 'term_' . $category_id),
        'slug' => $category_slug,
        'menus' => $menus,
      ));
    } else {
      // カテゴリなし（未分類）
      array_push($menu_info, array(
        'id' => 0,
        'name' => ucwords($lang->translate('uncategorized')),
        'slug' => 'uncategorized',
        'menus' => $menus,
      ));
    }
    
    return $menu_info;
  }
  
  global $wpdb;
  
  // 返り値初期化
  $menu_info = array();
  
  // メニューを絞り込みする際の条件文
  $sqlpart_where_menu = "WHERE post_type='menu' AND post_author = " . $restaurant_id;
  
  // 条件満たすメニューIDを取得するSQL文
  $sqlpart_select_menuid = "SELECT ID FROM wp_posts " . $sqlpart_where_menu;
  
  // メニューカテゴリを絞り込みする際の条件文
  $sqlpart_where_category = "WHERE taxonomy='menu_category' AND term_id IN (SELECT term_id FROM wp_termmeta WHERE meta_key='created_by' AND meta_value='" . $restaurant_id . "')";
  
  // 条件満たすメニューカテゴリIDを取得するSQL文
  $sqlpart_select_categoryid = "SELECT term_id FROM wp_term_taxonomy " . $sqlpart_where_category;

  // 対象メニューを絞り込みするSQL文
  $sqlpart_select_menu = <<<EOT
    SELECT wp.ID menu_id, wp.post_name menu_slug, wtr.term_taxonomy_id relationship_id, wpm_priority.meta_value menu_priority FROM 
    (SELECT ID, post_name, post_title FROM wp_posts {$sqlpart_where_menu}) wp 
    LEFT JOIN (SELECT object_id, term_taxonomy_id FROM wp_term_relationships WHERE object_id IN ({$sqlpart_select_menuid})) wtr ON wtr.object_id = wp.ID 
    LEFT JOIN (SELECT post_id, meta_value FROM wp_postmeta WHERE post_id IN ({$sqlpart_select_menuid}) AND meta_key='priority') wpm_priority ON wpm_priority.post_id = wp.ID 
EOT;

  // 対象メニューカテゴリを絞り込みするSQL文
  $sqlpart_select_category = <<<EOT
    SELECT wtt.term_taxonomy_id relationship_id, wtt.term_id category_id, wt.slug category_slug, wtm_priority.meta_value category_priority FROM
    (SELECT term_taxonomy_id, term_id, taxonomy FROM wp_term_taxonomy {$sqlpart_where_category}) wtt 
    LEFT JOIN (SELECT term_id, slug, name FROM wp_terms WHERE term_id IN ({$sqlpart_select_categoryid})) wt ON wt.term_id = wtt.term_id 
    LEFT JOIN (SELECT term_id, meta_value FROM wp_termmeta WHERE term_id IN ({$sqlpart_select_categoryid}) AND meta_key='priority') wtm_priority ON wtm_priority.term_id = wtt.term_id 
EOT;
  
  // SQL文構築
  if($for_ordering) {
    // 注文用データを取得するためのSQL文（カテゴリはNULL可能、注文は必ずある）
    $sql_select = <<<EOT
      SELECT tm.menu_id, tm.menu_slug, tm.menu_priority, tmc.category_id, tmc.category_slug FROM 
      ({$sqlpart_select_menu}) tm 
      LEFT JOIN ({$sqlpart_select_category}) tmc ON tm.relationship_id = tmc.relationship_id 
      ORDER BY category_priority IS NULL, category_priority ASC, menu_priority ASC 
EOT;
  } else {
    // 管理用データを取得するためのSQL文（カテゴリも注文もNULL可能）
    // MysqlはFULL OUTER JOINをサポートしないためUNIONで結果を結合
    $sql_select = <<<EOT
      SELECT * FROM (
        SELECT tm.menu_id, tm.menu_slug, tm.menu_priority, tmc.category_id, tmc.category_slug, tmc.category_priority FROM 
        ({$sqlpart_select_menu}) tm 
        LEFT JOIN ({$sqlpart_select_category}) tmc ON tm.relationship_id = tmc.relationship_id 
        UNION 
        SELECT tm.menu_id, tm.menu_slug, tm.menu_priority, tmc.category_id, tmc.category_slug, tmc.category_priority FROM 
        ({$sqlpart_select_menu}) tm 
        RIGHT JOIN ({$sqlpart_select_category}) tmc ON tm.relationship_id = tmc.relationship_id 
      ) result
      ORDER BY category_priority IS NULL, category_priority ASC, category_id ASC, menu_priority ASC, menu_id ASC 
EOT;
  }
  
  // SQL実行してデータ取得
  $results = $wpdb->get_results($sql_select);
  
  // SQLデータ整理
  if(count($results)) {
    $category_id = -1;
    $category_slug = '';
    $menus = array();
    foreach($results as $result) {
      if($result->category_id != $category_id) {
        // ループ中のカテゴリが変わる場合いったん結果リストに現情報を入れる
        if($category_id != -1) {
          // 最初以外の場合
          $menu_info = push_category($menu_info, $category_id, $category_slug, $menus);
        }
        
        // 新しい情報をループ記録変数に更新
        $category_id = $result->category_id;
        $category_slug = $result->category_slug;
        $menus = array();
      }
      
      // メニュー情報をループ記録変数に追加
      if($result->menu_id) {
        $menu_id = intval($result->menu_id);
        array_push($menus, array(
          'id' => $menu_id,
          'name' => get_the_title($menu_id),
          'slug' => $result->menu_slug,
        ));
      }
    }
    $menu_info = push_category($menu_info, $category_id, $category_slug, $menus);
  }
  
  return $menu_info;
}


/*
 * 全コース情報を取得する
 */
function get_course_info($user_id) {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $list = array();
  
  $courses = get_posts(array(
    'post_type' => 'course',
    'post_author' => $user_id,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_key' => 'priority',
  ));
  
  // メニュー情報整理
  foreach($courses as $course) {
    array_push($list, array(
      'id' => $course->ID,
      'name' => get_the_title($course->ID),
      'slug' => $course->post_name,
    ));
  }
  
  return $list;
}


/*
 * 全席情報を取得する
 */
function get_seat_info($user_id) {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $list = array();
  
  $seats = get_posts(array(
    'post_type' => 'seat',
    'post_author' => $user_id,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'id',
    'order' => 'ASC',
  ));
  
  // メニュー情報整理
  foreach($seats as $seat) {
    array_push($list, array(
      'id' => $seat->ID,
      'name' => get_the_title($seat->ID),
      'slug' => $seat->post_name,
    ));
  }
  
  return $list;
}


















/**
 * プランを登録する際Stripe商品と料金を同時に登録（未完成）
 */
function create_plan_customize($post_id) {
    // 投稿タイプがプランであることを確認
    if (get_post_type($post_id) == 'plan') {
        $title = get_the_title($post_id);
        $amount = get_post_meta($post_id, 'price', true);
        $period = get_post_meta($post_id, 'period', true);
        $unit = get_post_meta($post_id, 'unit', true);
        
        // 必要なデータが存在することを確認
        if (!$title || !$amount || !$period || !$unit) {
            return;
        }
        
        require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Stripe商品情報更新
        $product_id = get_post_meta($post_id, 'stripe_product_id', true);
        if ($product_id) {
            // Stripeで商品名を更新
            $product = \Stripe\Product::update($product_id, [ 'name' => $title, ]);
        } else {
            // Stripeで商品を作成
            $product = \Stripe\Product::create([ 'name' => $title, ]);
            $product_id = $product->id;
            update_post_meta($post_id, 'stripe_product_id', $product_id);
        }
        
        // Stripe料金情報作成/更新必要性判定
        $price_update_flag = false;
        $price_id = get_post_meta($post_id, 'stripe_price_id', true);
        
        if($price_id) {
            $price = \Stripe\Price::retrieve($price_id);
            
            if(strval($price->unit_amount) != $amount || strval($price->recurring->interval) != $unit || strval($price->recurring->interval_count) != $period) {
                $price_update_flag = true;
            }
        } else {
            $price_update_flag = true;
        }
        
        // Stripe料金情報作成/更新
        if($price_update_flag) {
            // Stripeで価格を作成
            $price = \Stripe\Price::create([
                'unit_amount' => $amount,
                'currency' => 'jpy',
                'recurring' => [
                    'interval' => $unit,
                    'interval_count' => $period,
                ],
                'product' => $product_id,
            ]);
            update_post_meta($post_id, 'stripe_price_id', $price->id);
            
            // Stripe側で既存サブスクリプション金額更新（未完成部分）
        }
    }
}
add_action('save_post', 'create_plan_customize');