<?php

/* 
 * カスタムユーザー権限グループ作成
 */
function create_custom_roles() {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  
  // 「管理係」権限グループの作成
  $role_name = ucwords($lang->translate('manager'));
  add_role(
    'manager',
    'Manager',
    array(
      'read' => true,
    )
  );
  

  // 「料理係」権限グループの作成
  $role_name = ucwords($lang->translate('cook'));
  add_role(
    'cook',
    'Cook',
    array(
      'read' => true,
    )
  );

  // 「接客係」権限グループの作成
  $role_name = ucwords($lang->translate('waiter'));
  add_role(
    'waiter',
    'Waiter',
    array(
      'read' => true,
    )
  );
}

add_action('init', 'create_custom_roles');


/* 
 * ユーザー作成画面で権限グループ選択肢をカスタマイズ
 */
function customize_user_roles_pulldown($roles) {
  global $wp_roles;
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $current_user = wp_get_current_user();
  $role_names = $wp_roles->role_names;
  
  // 選択肢の名前を翻訳後のものに変更
  $roles['manager']['name'] = ucwords($lang->translate($role_names['manager']));
  $roles['waiter']['name'] = ucwords($lang->translate($role_names['waiter']));
  $roles['cook']['name'] = ucwords($lang->translate($role_names['cook']));
  
  return $roles;
}
add_filter('editable_roles', 'customize_user_roles_pulldown');


/* 
 * 管理画面ユーザー一覧上部の各権限グループのユーザー数集計の取得をカスタマイズ
 */
function customize_admin_users_count($views) {
  global $pagenow, $wpdb, $wp_roles;
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $current_user = wp_get_current_user();
  $role_names = $wp_roles->role_names;
  $count_all=0;
  
  if (is_admin() && $pagenow === 'users.php') {
    foreach($views as $view_key => $view_value) {
      if(array_key_exists($view_key, $role_names)) {
        // 権限グループ名を翻訳後のものに変換
        $views[$view_key] = str_replace($role_names[$view_key], ucwords($lang->translate($role_names[$view_key])), $view_value);
      }
    }
  }
  
  return $views;
}
add_filter('views_users', 'customize_admin_users_count');


/* 
 * ユーザー一覧テーブルのカラムをカスタマイズ
 */
function customize_users_columns($columns) {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  
  // 既存の権限グループと投稿数の項目を非表示に
  unset($columns['role']);
  unset($columns['posts']);

  // 新しい「担当」項目を追加
  $columns['role_name'] = ucwords($lang->translate('permission group'));
  
  // 管理者の場合、店名を表示
  if (current_user_can('administrator')) {
    $columns['restaurant_name'] = ucwords($lang->translate('restaurant name'));
  }

  return $columns;
}
add_filter('manage_users_columns', 'customize_users_columns');


/*
 * ユーザー一覧にカスタム列の取り扱い
 */
function display_custom_column($value, $column_name, $user_id) {
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  
  if ($column_name === 'role_name') {
    // カラムが「担当」の場合
    global $wp_roles;
    $role_names = $wp_roles->role_names;
    
    // ユーザーの権限グループを取得
    $user = get_userdata($user_id);
    $roles = $user->roles;
    
    $role_name_list = array();
    
    foreach($roles as $role_key) {
      array_push($role_name_list, ucwords($lang->translate($role_names[$role_key])));
    }
    
    // ユーザーが持つすべての権限グループの名前をカンマ区切りで出力
    return implode(', ', $role_name_list);
  } else if($column_name === 'restaurant_name') {
    // カラムが「店名」の場合
    return get_field('restaurant_name', 'user_' . $user_id);
  }

  return $value;
}
add_filter('manage_users_custom_column', 'display_custom_column', 10, 3);
