<?php

/*
 * 全メニューとカテゴリ情報を取得Ajax処理
 */
function func_get_all_menus(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $list = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      $list = get_menu_info_with_category($user_id);
    }
  }
  
  $response = array(
    'result' => $result,
    'list' => $list,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_all_menus', 'func_get_all_menus');
add_action('wp_ajax_nopriv_get_all_menus', 'func_get_all_menus');


/*
 * メニューカテゴリ取得Ajax処理
 */
function func_get_menu_category(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $category_info = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_GET)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of menu category'));
      } else {
        // スラッグでメニューカテゴリ取得
        $slug = $_GET['slug'];
        $category = get_term_by('slug', $slug, 'menu_category');
        
        if(!$category) {
          // メニューカテゴリが存在しない場合エラーを出す
          $result = false;
          $error = ucfirst($lang->translate('failed to get data of menu category'));
        } else {
          // カテゴリID取得
          $category_id = $category->term_id;
          
          if(get_field('created_by', 'term_' . $category_id) != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this menu category'));
          } else {
            // 利用可能言語取得
            $languages = explode(",", get_field('languages', 'user_' . $user_id));
            array_unshift($languages, 'ja');
            
            // カテゴリ名整理
            $name_list = array();
            $field_name = get_term_meta($category_id, 'name', true);
            foreach($languages as $language) {
              $name_list[$language] = $lang->get_text($field_name, $language);
            }
            
            // カテゴリ説明整理
            $description_list = array();
            $field_name = get_term_meta($category_id, 'description', true);
            foreach($languages as $language) {
              $description_list[$language] = $lang->get_text($field_name, $language);
            }
            
            // カテゴリ情報整理
            $category_info = array(
              'names' => $name_list,
              'descriptions' => $description_list,
              'options' => array(),
            );
            
            // オプション情報整理
            $option_fields = get_field('common_option', 'term_' . $category_id, true);
            if($option_fields) {
              foreach($option_fields as $option_key => $option_field) {
                $option = array(
                  'names' => array(), 
                  'choices' => array(),
                );
                
                $option_name = get_term_meta($category_id, 'common_option_' . $option_key . '_name', true);
                foreach($languages as $language) {
                  $option['names'][$language] = $lang->get_text($option_name, $language);
                }
                
                foreach($option_field['choices'] as $choice_key => $choice_field) {
                  // オプションの選択肢情報整理
                  $choice = array(
                    'names' => array(), 
                    'price' => $choice_field['price'],
                  );
                  
                  $choice_name = get_term_meta($category_id, 'common_option_' . $option_key . '_choices_' . $choice_key . '_name', true);
                  foreach($languages as $language) {
                    $choice['names'][$language] = $lang->get_text($choice_name, $language);
                  }
                  
                  array_push($option['choices'], $choice);
                }
                
                array_push($category_info['options'], $option);
              }
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'category' => $category_info,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_menu_category', 'func_get_menu_category');
add_action('wp_ajax_nopriv_get_menu_category', 'func_get_menu_category');


/*
 * メニューカテゴリ保存Ajax処理
 */
function func_save_menu_category(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $lang_code = $lang->code();
  $result = true;
  $slug = '';
  $category_name = '';
  $error_list = array();
  
  if(!is_user_logged_in()){
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // 利用可能言語取得
      $languages = explode(",", get_field('languages', 'user_' . $user_id));
      array_unshift($languages, 'ja');
      
      // 言語別項目の空白配列を用意
      $language_base_list = array();
      foreach($languages as $language) {
        $language_base_list[$language] = '';
      }
      
      // 整理後データ格納変数を定義
      $category = null;
      $name_list = $language_base_list;
      $description_list = $language_base_list;
      $option_priority_list = array();
      $option_list = array();
      $init_option_item = array(
        'names' => $language_base_list,
        'choices' => array(),
        'choice_priority_list' => array(),
      );
      $init_choice_item = array(
        'names' => $language_base_list,
        'price' => array(),
      );
      
      // POST内容整理
      foreach($_POST as $key => $value) {
        if($key == 'slug') {
          // スタッグを付与
          $slug = $value;
        } else if(preg_match('/^name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別カテゴリ名を付与
          if(in_array($matches[1], $languages)) {
            if($value) {
              $name_list[$matches[1]] = $value;
            } else {
              $result = false;
              $error_list[$key] = ucfirst(sprintf($lang->translate('name of %s is required'), ucwords($lang->get_lang_name($matches[1]))));
            }
          }
        } else if(preg_match('/^description_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別説明文を付与
          if(in_array($matches[1], $languages)) {
            $description_list[$matches[1]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_priority$/', $key, $matches)) {
          // オプション順番配列を付与
          $option_priority_list[intval($value)] = $matches[1];
        } else if(preg_match('/^(option_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別オプション項目名を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(in_array($matches[2], $languages)) {
            $option_list[$matches[1]]['names'][$matches[2]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_priority$/', $key, $matches)) {
          // 選択肢順番配列を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          $option_list[$matches[1]]['choice_priority_list'][intval($value)] = $matches[2];
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別選択肢名前を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          if(in_array($matches[3], $languages)) {
            $option_list[$matches[1]]['choices'][$matches[2]]['names'][$matches[3]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_price$/', $key, $matches)) {
          // 選択肢価格を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          $option_list[$matches[1]]['choices'][$matches[2]]['price'] = $value;
        }
      }
      
      // スラッグバリデーション
      if($slug) {
        $category = get_term_by('slug', $slug, 'menu_category');
        if(!$category) {
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('this menu category is not existed'));
        } else if(get_field('created_by', 'term_' . $category->term_id) != $user_id) {
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('you have no permission to update this menu category'));
        }
      }
      
      // オプション項目数バリデーション
      if(count($option_list) != count($option_priority_list)) {
        $result = false;
        $error_list['options'] = ucfirst($lang->translate('option data is not matched'));
      } else {
        foreach($option_list as $option_key => $option_info) {
          // オプション名バリデーション
          foreach($option_info['names'] as $language => $name) {
            if(!$name) {
              $result = false;
              $error_list[$option_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('item name of %s is required'), ucwords($lang->get_lang_name($language))));
            }
          }
          
          // 選択肢数バリデーション
          if(count($option_info['choices']) == 0) {
            $result = false;
            $error_list[$option_key . '_choices'] = ucfirst($lang->translate('at least one choice is required'));
          } else if(count($option_info['choices']) != count($option_info['choice_priority_list'])) {
            $result = false;
            $error_list[$option_key . '_choices'] = ucfirst($lang->translate('choice data is not matched'));
          }
          
          foreach($option_info['choices'] as $choice_key => $choice_info) {
            // 選択肢名前バリデーション
            foreach($choice_info['names'] as $language => $name) {
              if(!$name) {
                $result = false;
                $error_list[$option_key . '_' . $choice_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('choice name of %s is required'), ucwords($lang->get_lang_name($language))));
              }
            }
            
            // 選択肢値段バリデーション
            if(!is_numeric($choice_info['price'])) {
              $error_list[$option_key . '_' . $choice_key . '_price'] = ucfirst($lang->translate('price should be a number'));
            }
          }
        }
      }
      
      // バリデーションに問題ない場合、処理を続行
      if($result) {
        try {
          $wpdb->query('START TRANSACTION');
          
          // ターム用の名前と説明を取得（デフォルト言語のもの、実際画面上で表示しない）
          $category_name = $name_list[$lang_code];
          
          $category_id = null;
          $new_flag = false;
          if($category) {
            // 既存のメニューカテゴリを更新
            $category_id = $category->term_id;
          } else {
            // メニューカテゴリを新規作成
            $slug = md5(uniqid(microtime(), true));
            $insert_result = wp_insert_term(
              $category_name,
              'menu_category',
              array(
                'slug' => $slug,
              ),
            );
            
            // Wordpress データ処理エラーが発生するときログを残す
            if(is_wp_error($insert_result)) {
              $result = false;
              $error_list['system'] = ucfirst($lang->translate('failed to create menu category'));
              error_log('Create Menu Category :' . $insert_result->get_error_message());
            } else {
              $category_id = $insert_result['term_id'];
            }
            
            // 新規フラグを立つ
            $new_flag = true;
          }
          
          if($result && $category_id) {
            if($new_flag) {
              // 新規の場合作者情報と表示順を更新
              update_term_meta($category_id, 'created_by', $user_id);
              update_term_meta($category_id, 'priority', '99999999');
            }
            
            // 名前と説明を更新
            update_field('name', $name_list, 'term_' . $category_id);
            update_field('description', $description_list, 'term_' . $category_id);
            
            // オプション情報整理
            $common_option = array();
            foreach($option_priority_list as $option_key) {
              $option = array(
                'name' => $option_list[$option_key]['names'],
                'choices' => array(),
              );
              
              // 選択肢情報整理
              foreach($option_list[$option_key]['choice_priority_list'] as $choice_key) {
                $choice = array(
                  'name' => $option_list[$option_key]['choices'][$choice_key]['names'],
                  'price' => $option_list[$option_key]['choices'][$choice_key]['price'],
                );
                
                // 選択肢情報を配列に格納
                array_push($option['choices'], $choice);
              }
              
              // オプション情報を配列に格納
              array_push($common_option, $option);
            }
            
            // 共通オプションを更新
            update_field('common_option', $common_option, 'term_' . $category_id);
          }
          
          $wpdb->query('COMMIT');
        } catch (Exception $e) {
          $wpdb->query('ROLLBACK');
          
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to save menu category'));
          error_log('Save Menu Category : ' . $e->get_error_message());
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'category_name' => $category_name,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_menu_category', 'func_save_menu_category');
add_action('wp_ajax_nopriv_save_menu_category', 'func_save_menu_category');


/*
 * メニューカテゴリ削除Ajax処理
 */
function func_delete_menu_category(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $slug = '';
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_POST)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of menu category'));
      } else {
        // メニューカテゴリ取得
        $slug = $_POST['slug'];
        $category = get_term_by('slug', $slug, 'menu_category');
        
        if(!$category) {
          // メニューカテゴリが存在しない場合エラーを出す
          $result = false;
          $error = ucfirst($lang->translate('failed to get data of menu category'));
        } else {
            
          $category_id = $category->term_id;
          
          if(get_field('created_by', 'term_' . $category_id) != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to delete this menu category'));
          } else {
            try {
              $wpdb->query('START TRANSACTION');
              // 関連のカスタムフィールドを削除
              delete_field('name', 'term_' . $category_id);
              delete_field('description', 'term_' . $category_id);
              delete_field('common_option', 'term_' . $category_id);
              delete_field('priority', 'term_' . $category_id);
              
              // 関連のメニューと関連性を解除
              $menus = get_objects_in_term($category_id, 'menu_category');
              foreach($menus as $menu_id) {
                wp_remove_object_terms($menu_id, $category_id, 'menu_category');
              }
              
              // メニューカテゴリ自体を削除
              wp_delete_term($category_id, 'menu_category');
              
              $wpdb->query('COMMIT');
            } catch(Exception $e) {
              $wpdb->query('ROLLBACK');
              
              $result = false;
              $error = ucfirst($lang->translate('failed to delete menu category'));
              error_log('Delete Menu Category : ' . $e->get_error_message());
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_delete_menu_category', 'func_delete_menu_category');
add_action('wp_ajax_nopriv_delete_menu_category', 'func_delete_menu_category');


/*
 * メニュー取得Ajax処理
 */
function func_get_menu(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $menu_info = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_GET)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of menu'));
      } else {
        // メニュー取得
        $slug = $_GET['slug'];
        $menu = get_page_by_path($slug, OBJECT, 'menu');
        
        if(!$menu) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to get data of menu'));
        } else {
          // メニューID取得
          $menu_id = $menu->ID;
          
          if($menu->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this menu'));
          } else {
            // 利用可能言語取得
            $languages = explode(",", get_field('languages', 'user_' . $user_id));
            array_unshift($languages, 'ja');
            
            // メニュー名整理
            $name_list = array();
            foreach($languages as $language) {
              $name_list[$language] = $lang->get_text($menu->post_title, $language);
            }
            
            // カテゴリ説明整理
            $description_list = array();
            foreach($languages as $language) {
              $description_list[$language] = $lang->get_text($menu->post_content, $language);
            }
            
            // 画像URL整理
            $image_id = get_field('menu_image', $menu_id);
            $image_url = '';
            if($image_id) {
              $image_url = wp_get_attachment_url($image_id);
            }
            
            // タグ整理
            $tags = array();
            $tag_list = get_field('tag', $menu_id);
            foreach($tag_list as $tag) {
              array_push($tags, $tag['value']);
            }
            
            // カテゴリ整理
            $terms = wp_get_object_terms($menu_id, 'menu_category');
            if (!is_wp_error($terms) && !empty($terms)) {
              $category = $terms[0]->slug;
            } else {
              $category = 'uncategorized';
            }
            
            // メニュー情報整理
            $menu_info = array(
              'names' => $name_list,
              'descriptions' => $description_list,
              'image_id' => $image_id,
              'image' => $image_url,
              'price' => get_field('price', $menu_id),
              'tags' => $tags,
              'category' => $category,
              'options' => array(),
            );
            
            $option_fields = get_field('option', $menu_id);
            if($option_fields) {
              foreach($option_fields as $option_key => $option_field) {
                // オプション整理
                $option = array(
                  'names' => array(), 
                  'choices' => array(),
                );
                
                $option_name = get_post_meta_from_db($menu_id, 'option_' . $option_key . '_name');
                foreach($languages as $language) {
                  $option['names'][$language] = $lang->get_text($option_name, $language);
                }
                
                foreach($option_field['choices'] as $choice_key => $choice_field) {
                  // 選択肢整理
                  $choice = array(
                    'names' => array(), 
                    'price' => $choice_field['price'],
                  );
                  
                  $choice_name = get_post_meta_from_db($menu_id, 'option_' . $option_key . '_choices_' . $choice_key . '_name');
                  foreach($languages as $language) {
                    $choice['names'][$language] = $lang->get_text($choice_name, $language);
                  }
                  
                  array_push($option['choices'], $choice);
                }
                
                array_push($menu_info['options'], $option);
              }
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'menu' => $menu_info,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_menu', 'func_get_menu');
add_action('wp_ajax_nopriv_get_menu', 'func_get_menu');


/*
 * メニュー保存Ajax処理
 */
function func_save_menu(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $lang_code = $lang->code();
  $result = true;
  $slug = '';
  $menu_name = '';
  $error_list = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // 利用可能言語取得
      $languages = explode(",", get_field('languages', 'user_' . $user_id));
      array_unshift($languages, 'ja');
      
      // 言語別項目の空白配列を用意
      $language_base_list = array();
      foreach($languages as $language) {
        $language_base_list[$language] = '';
      }
      
      // 整理後データ格納変数を定義
      $menu = null;
      $menu_title = '';
      $menu_content = '';
      $option_priority_list = array();
      $option_list = array();
      $init_option_item = array(
        'names' => $language_base_list,
        'choices' => array(),
        'choice_priority_list' => array(),
      );
      $init_choice_item = array(
        'names' => $language_base_list,
        'price' => array(),
      );
      
      // POST内容整理
      foreach($_POST as $key => $value) {
        if($key == 'slug') {
          // スタッグを付与
          $slug = $value;
        } else if(preg_match('/^name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別カテゴリ名を付与
          if(in_array($matches[1], $languages)) {
            if($value) {
              $menu_title .= '[:' . $matches[1] . ']' . $value;
            } else {
              $result = false;
              $error_list[$key] = ucfirst(sprintf($lang->translate('name of %s is required'), ucwords($lang->get_lang_name($matches[1]))));
            }
          }
          
          if($matches[1] == $lang_code) {
            $menu_name = $value;
          }
        } else if(preg_match('/^description_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別説明文を付与
          if(in_array($matches[1], $languages)) {
            $menu_content .= '[:' . $matches[1] . ']' . $value;
          }
        } else if(preg_match('/^(option_\d+)_priority$/', $key, $matches)) {
          // オプション順番配列を付与
          $option_priority_list[intval($value)] = $matches[1];
        } else if(preg_match('/^(option_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別オプション項目名を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(in_array($matches[2], $languages)) {
            $option_list[$matches[1]]['names'][$matches[2]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_priority$/', $key, $matches)) {
          // 選択肢順番配列を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          $option_list[$matches[1]]['choice_priority_list'][intval($value)] = $matches[2];
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別選択肢名前を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          if(in_array($matches[3], $languages)) {
            $option_list[$matches[1]]['choices'][$matches[2]]['names'][$matches[3]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_price$/', $key, $matches)) {
          // 選択肢価格を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          $option_list[$matches[1]]['choices'][$matches[2]]['price'] = $value;
        }
      }
      $menu_title .= '[:]';
      $menu_content .= '[:]';
      
      // スラッグバリデーション
      if($slug) {
        $menu = get_page_by_path($slug, OBJECT, 'menu');
        
        if(!$menu) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('this menu is not existed'));
        } elseif($menu->post_author != $user_id) {
          // ログイン中のユーザーが作者ではない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('you have no permission to update this menu'));
        }
      }
      
      // カテゴリバリデーション
      $category = null;
      if(array_key_exists('category', $_POST) && $_POST['category'] && $_POST['category'] != 'uncategorized') {
        $category = get_term_by('slug', $_POST['category'], 'menu_category');
        if(!$category) {
          // カテゴリが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('can not add menu to a category that is not existed'));
        } else if(get_field('created_by', 'term_' . $category->term_id) != $user_id) {
          // ログイン中のユーザーが作者ではない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('you have no permission to add menu to this menu category'));
        }
      }
      
      // 値段バリデーション
      if(array_key_exists('price', $_POST)) {
        if(!is_numeric($_POST['price'])) {
          $result = false;
          $error_list['price'] = ucfirst($lang->translate('price should be a number'));
        }
      } else {
        $result = false;
        $error_list['price'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('price')));
      }
      
      // タグバリデーション
      if(array_key_exists('tag', $_POST)) {
        if(!is_array($_POST['tag'])) {
          $result = false;
          $error_list['tag'] = ucfirst(sprintf($lang->translate('%s should be a list'), $lang->translate('price')));
        } else {
          $tag_allow_list = array_keys(get_acf_select_options('Menu', 'tag'));
          foreach($_POST['tag'] as $tag_key) {
            if(!in_array($tag_key, $tag_allow_list)) {
              $result = false;
              $error_list['tag'] = ucfirst($lang->translate('used tag is not existed'));
            }
          }
        }
      }
      
      // オプション項目数バリデーション
      if(count($option_list) != count($option_priority_list)) {
        $result = false;
        $error_list['options'] = ucfirst($lang->translate('option data is not matched'));
      }
      
      // 画像バリデーション
      $file = null;
      $copy_from = null;
      if(!empty($_FILES['image'])) {
        $file = $_FILES['image'];
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon');
        if($file['tmp_name']) {
          $file_mime_type = mime_content_type($file['tmp_name']);
          
          if (!in_array($file_mime_type, $allowed_mime_types)) {
            $result = false;
            $error_list['image'] = ucfirst($lang->translate('upload an image please'));
          }
        } else {
          $file = null;
        }
      } else {
        // コピー元バリデーション
        if(array_key_exists('copy_from', $_POST) && $_POST['copy_from']) {
          $copy_from = get_page_by_path($_POST['copy_from'], OBJECT, 'menu');
          
          if(!$copy_from) {
            // コピー元が存在しない場合エラーを出す
            $result = false;
            $error_list['image'] = ucfirst($lang->translate('menu to copy is not existed'));
          } elseif($copy_from->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error_list['image'] = ucfirst($lang->translate('you have no permission to copy this menu'));
          }
        }
      }
      
      foreach($option_list as $option_key => $option_info) {
        // オプション名バリデーション
        foreach($option_info['names'] as $language => $name) {
          if(!$name) {
            $result = false;
            $error_list[$option_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('item name of %s is required'), ucwords($lang->get_lang_name($language))));
          }
        }
        
        // 選択肢数バリデーション
        if(count($option_info['choices']) == 0) {
          $result = false;
          $error_list[$option_key . '_choices'] = ucfirst($lang->translate('at least one choice is required'));
        } else if(count($option_info['choices']) != count($option_info['choice_priority_list'])) {
          $result = false;
          $error_list[$option_key . '_choices'] = ucfirst($lang->translate('choice data is not matched'));
        }
        
        foreach($option_info['choices'] as $choice_key => $choice_info) {
          // 選択肢名前バリデーション
          foreach($choice_info['names'] as $language => $name) {
            if(!$name) {
              $result = false;
              $error_list[$option_key . '_' . $choice_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('choice name of %s is required'), ucwords($lang->get_lang_name($language))));
            }
          }
          
          // 選択肢値段バリデーション
          if(!is_numeric($choice_info['price'])) {
            $result = false;
            $error_list[$option_key . '_' . $choice_key . '_price'] = ucfirst($lang->translate('price should be a number'));
          }
        }
      }
      
      // バリデーションに問題ない場合、処理を続行
      if($result) {
        try {
          $wpdb->query('START TRANSACTION');
          
          $menu_id = null;
          $new_flag = false;
          if($menu) {
            // 既存のメニューを更新
            $menu_id = $menu->ID;
            $menu = get_post($menu_id);
            $menu->post_title = $menu_title;
            $menu->post_content = $menu_content;
            wp_update_post($menu);
          } else {
            // メニューを新規作成
            $slug = md5(uniqid(microtime(), true));
            $menu_id = wp_insert_post(array(
              'post_title' => $menu_title,
              'post_name' => $slug,
              'post_content' => $menu_content,
              'post_status' => 'publish',
              'post_author' => $user_id,
              'post_type' => 'menu',
            ));
            
            // Wordpress データ処理エラーが発生するときログを残す
            if(!$menu_id) {
              $result = false;
              $error_list['system'] = ucfirst($lang->translate('failed to create menu'));
            }
            
            // 新規フラグを立つ
            $new_flag = true;
          }
          
          // 画像処理
          $attachment_id = null;
          if($file) {
            // WordPress のメディアライブラリにファイルをアップロード
            $upload = wp_handle_upload($file, array('test_form' => false));

            if (!$upload || isset($upload['error'])) {
              $result = false;
              $error_list['image'] = ucfirst($lang->translate('failed to upload image'));
            } else {
              // アップロード成功、添付ファイルとして登録
              $attachment = array(
                  'post_mime_type' => $upload['type'],
                  'post_title'     => $slug,
                  'post_content'   => '',
                  'post_status'    => 'inherit'
              );
              $attachment_id = wp_insert_attachment($attachment, $upload['file']);
              
              $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
              wp_update_attachment_metadata($attachment_id, $attach_data);
            }
          } elseif($copy_from) {
            // 既存画像からコピー
            $old_attachment_id = get_field('menu_image', $copy_from->ID);
            if($old_attachment_id) {
              $old_file_path = get_attached_file($old_attachment_id);
              $old_file_name = basename($old_file_path);
              $upload_dir = wp_upload_dir();
              $file_name = wp_unique_filename($upload_dir['path'], $old_file_name);
              $file_path = $upload_dir['path'] . '/' . $file_name;
              
              if (copy($old_file_path, $file_path)) {
                // 新しいファイルとして登録
                $attachment = array(
                  'post_mime_type' => get_post_mime_type($old_attachment_id),
                  'post_title' => $slug,
                  'post_content' => '',
                  'post_status' => 'inherit',
                );
                
                // 新しい添付ファイルをデータベースに挿入
                $attachment_id = wp_insert_attachment($attachment, $file_path);
                
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
              } else {
                $result = false;
                $error_list['image'] = ucfirst($lang->translate('failed to copy image'));
              }
            }
          }
          
          if($result && $menu_id) {
            if($new_flag) {
              // 表示順を更新
              update_field('priority', '99999999', $menu_id);
            }
            
            // メニューカテゴリと関連し直す
            $current_categories = wp_get_object_terms($menu_id, 'menu_category');
            if (!is_wp_error($current_categories) && !empty($current_categories)) {
              $current_category_ids = wp_list_pluck($current_categories, 'term_id');
              wp_remove_object_terms($menu_id, $current_category_ids, 'menu_category');
            }
            if($category) {
              wp_set_object_terms($menu_id, array($category->term_id), 'menu_category');
            }
            
            // 画像を更新
            if($attachment_id) {
              $old_attachment_id = get_field('menu_image', $menu_id);
              if($old_attachment_id) {
                wp_delete_attachment($old_attachment_id, true);
              }
              update_field('menu_image', $attachment_id, $menu_id);
            } elseif(array_key_exists('image_delete', $_POST) && $_POST['image_delete'] == 'on') {
              $old_attachment_id = get_field('menu_image', $menu_id);
              if($old_attachment_id) {
                wp_delete_attachment($old_attachment_id, true);
              }
            }
            
            // 値段を更新
            update_field('price', $_POST['price'], $menu_id);
            
            // タグを更新
            update_field('tag', $_POST['tag'], $menu_id);
            
            // オプション情報整理
            $options = array();
            foreach($option_priority_list as $option_key) {
              $option = array(
                'name' => $option_list[$option_key]['names'],
                'choices' => array(),
              );
              
              // 選択肢情報整理
              foreach($option_list[$option_key]['choice_priority_list'] as $choice_key) {
                $choice = array(
                  'name' => $option_list[$option_key]['choices'][$choice_key]['names'],
                  'price' => $option_list[$option_key]['choices'][$choice_key]['price'],
                );
                
                // 選択肢情報を配列に格納
                array_push($option['choices'], $choice);
              }
              
              // オプション情報を配列に格納
              array_push($options, $option);
            }
            
            // オプションを更新
            update_field('option', $options, $menu_id);
          }
          
          $wpdb->query('COMMIT');
        } catch (Exception $e) {
          $wpdb->query('ROLLBACK');
          
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to save menu'));
          error_log('Save Menu : ' . $e->get_error_message());
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'category' => $category ? $category->slug : 'uncategorized',
    'menu_name' => $menu_name,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_menu', 'func_save_menu');
add_action('wp_ajax_nopriv_save_menu', 'func_save_menu');


/*
 * メニュー削除Ajax処理
 */
function func_delete_menu(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $slug = '';
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_POST)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of menu'));
      } else {
        // メニュー取得
        $slug = $_POST['slug'];
        $menu = get_page_by_path($slug, OBJECT, 'menu');
        
        if(!$menu) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error = ucfirst($lang->translate('failed to get data of menu'));
        } else {
          // メニューID取得
          $menu_id = $menu->ID;
          
          if($menu->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this menu'));
          } else {
            try {
              $wpdb->query('START TRANSACTION');
              
              // 画像削除
              $image_id = get_field('menu_image', $menu_id);
              
              // 関連のカスタムフィールドと画像を削除
              delete_field('menu_image', $menu_id);
              wp_delete_attachment($image_id, true);
              delete_field('price', $menu_id);
              delete_field('tag', $menu_id);
              delete_field('priority', $menu_id);
              delete_field('option', $menu_id);
              
              // カテゴリと関連を外す
              $categories = wp_get_object_terms($menu_id, 'menu_category');
              if (!is_wp_error($categories) && !empty($categories)) {
                $category_ids = wp_list_pluck($categories, 'term_id');
                $result = wp_remove_object_terms($menu_id, $category_ids, 'menu_category');
              }
              
              // メニュー自体を削除
              wp_delete_post($menu_id, true);
              
              $wpdb->query('COMMIT');
            } catch(Exception $e) {
              $wpdb->query('ROLLBACK');
              
              $result = false;
              $error = ucfirst($lang->translate('failed to delete menu'));
              error_log('Delete Menu Category : ' . $e->get_error_message());
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_delete_menu', 'func_delete_menu');
add_action('wp_ajax_nopriv_delete_menu', 'func_delete_menu');


/*
 * メニュー表示順保存Ajax処理
 */
function func_save_menu_priority(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage menu cateogry'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // 表示順情報バリデーショと整理
      $category_priority_list = array();
      $menu_priority_list = array();
      foreach($_POST as $key => $value) {
        if(preg_match('/^category_\d+_slug$/', $key)) {
          if($value == 'uncategorized') {
            continue;
          }
          
          // メニューカテゴリ取得
          $category = get_term_by('slug', $value, 'menu_category');
          
          if($category) {
            $category_id = $category->term_id;
            
            if(get_field('created_by', 'term_' . $category_id) != $user_id) {
              // ログイン中のユーザーが作者ではない場合エラーを出す
              $result = false;
              $error = ucfirst($lang->translate('there is a menu category that you cannot access'));
            } else {
              $priority_key = str_replace('slug', 'priority', $key);
              if(!array_key_exists($priority_key, $_POST)) {
                // 相応の順番情報がない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a menu category has no priority information'));
              } else if(!is_numeric($_POST[$priority_key])) {
                // 順番が数値でない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a priority is not number'));
              } else {
                $priority_list['term_' . $category_id] = $_POST[$priority_key];
              }
            }
          } else {
            // メニューカテゴリ情報が取得できない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('failed to get menu and category information'));
          }
        } elseif(preg_match('/^menu_\d+_\d+_slug$/', $key)) {
          // メニュー取得
          $menu = get_page_by_path($value, OBJECT, 'menu');
          
          if($menu) {
            $menu_id = $menu->ID;
            
            if($menu->post_author != $user_id) {
              // ログイン中のユーザーが作者ではない場合エラーを出す
              $result = false;
              $error = ucfirst($lang->translate('there is a menu that you cannot access'));
            } else {
              $priority_key = str_replace('slug', 'priority', $key);
              if(!array_key_exists($priority_key, $_POST)) {
                // 相応の順番情報がない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a menu has no priority information'));
              } else if(!is_numeric($_POST[$priority_key])) {
                // 順番が数値でない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a priority is not number'));
              } else {
                $priority_list[$menu_id] = $_POST[$priority_key];
              }
            }
          } else {
            $result = false;
            $error = ucfirst($lang->translate('failed to get menu and category information'));
          }
        }
      }
      
      if($result) {
        foreach($priority_list as $key => $value) {
          update_field('priority', str_pad($value, 8, '0', STR_PAD_LEFT), $key);
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_menu_priority', 'func_save_menu_priority');
add_action('wp_ajax_nopriv_save_menu_priority', 'func_save_menu_priority');


/*
 * 全コース情報を取得Ajax処理
 */
function func_get_all_courses(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $list = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage course'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      $list = get_course_info($user_id);
    }
  }
  
  $response = array(
    'result' => $result,
    'list' => $list,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_all_courses', 'func_get_all_courses');
add_action('wp_ajax_nopriv_get_all_courses', 'func_get_all_courses');


/*
 * コース取得Ajax処理
 */
function func_get_course(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $course_info = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage course'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_GET)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of course'));
      } else {
        // メニュー取得
        $slug = $_GET['slug'];
        $course = get_page_by_path($slug, OBJECT, 'course');
        
        if(!$course) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to get data of course'));
        } else {
          // メニューID取得
          $course_id = $course->ID;
          
          if($course->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this course'));
          } else {
            // 利用可能言語取得
            $languages = explode(",", get_field('languages', 'user_' . $user_id));
            array_unshift($languages, 'ja');
            
            // メニュー名整理
            $name_list = array();
            foreach($languages as $language) {
              $name_list[$language] = $lang->get_text($course->post_title, $language);
            }
            
            // カテゴリ説明整理
            $description_list = array();
            foreach($languages as $language) {
              $description_list[$language] = $lang->get_text($course->post_content, $language);
            }
            
            // 画像URL整理
            $image_id = get_field('course_image', $course_id);
            $image_url = '';
            if($image_id) {
              $image_url = wp_get_attachment_url($image_id);
            }
            
            // タグ整理
            $tags = array();
            $tag_list = get_field('tag', $course_id);
            foreach($tag_list as $tag) {
              array_push($tags, $tag['value']);
            }
            
            // 食べ飲み放題メニュー整理
            $free_menus = array();
            $free_menu_list = get_field('free_menus', $course_id);
            foreach($free_menu_list as $free_menu_id) {
              array_push($free_menus, get_post_field('post_name', $free_menu_id));
            }
            
            // メニュー情報整理
            $course_info = array(
              'names' => $name_list,
              'descriptions' => $description_list,
              'image_id' => $image_id,
              'image' => $image_url,
              'price' => get_field('price', $course_id),
              'tags' => $tags,
              'min_people' => get_field('min_people', $course_id),
              'max_people' => get_field('max_people', $course_id),
              'start_time' => get_field('start_time', $course_id),
              'end_time' => get_field('end_time', $course_id),
              'start_date' => get_field('start_date', $course_id),
              'end_date' => get_field('end_date', $course_id),
              'time_limit' => get_field('time_limit', $course_id),
              'last_order' => get_field('last_order', $course_id),
              'options' => array(),
              'free_menus' => $free_menus,
            );
            
            $option_fields = get_field('option', $course_id);
            if($option_fields) {
              foreach($option_fields as $option_key => $option_field) {
                // オプション整理
                $option = array(
                  'names' => array(), 
                  'choices' => array(),
                );
                
                $option_name = get_post_meta_from_db($course_id, 'option_' . $option_key . '_name');
                foreach($languages as $language) {
                  $option['names'][$language] = $lang->get_text($option_name, $language);
                }
                
                foreach($option_field['choices'] as $choice_key => $choice_field) {
                  // 選択肢整理
                  $choice = array(
                    'names' => array(), 
                    'price' => $choice_field['price'],
                  );
                  
                  $choice_name = get_post_meta_from_db($course_id, 'option_' . $option_key . '_choices_' . $choice_key . '_name');
                  foreach($languages as $language) {
                    $choice['names'][$language] = $lang->get_text($choice_name, $language);
                  }
                  
                  array_push($option['choices'], $choice);
                }
                
                array_push($course_info['options'], $option);
              }
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'course' => $course_info,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_course', 'func_get_course');
add_action('wp_ajax_nopriv_get_course', 'func_get_course');


/*
 * コース保存Ajax処理
 */
function func_save_course(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $lang_code = $lang->code();
  $result = true;
  $slug = '';
  $course_name = '';
  $error_list = array();
  
  if(!is_user_logged_in()){
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('you have no permission to manage course'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // 利用可能言語取得
      $languages = explode(",", get_field('languages', 'user_' . $user_id));
      array_unshift($languages, 'ja');
      
      // 言語別項目の空白配列を用意
      $language_base_list = array();
      foreach($languages as $language) {
        $language_base_list[$language] = '';
      }
      
      // 整理後データ格納変数を定義
      $course = null;
      $course_title = '';
      $course_content = '';
      $option_priority_list = array();
      $option_list = array();
      $init_option_item = array(
        'names' => $language_base_list,
        'choices' => array(),
        'choice_priority_list' => array(),
      );
      $init_choice_item = array(
        'names' => $language_base_list,
        'price' => array(),
      );
      
      // POST内容整理
      foreach($_POST as $key => $value) {
        if($key == 'slug') {
          // スタッグを付与
          $slug = $value;
        } else if(preg_match('/^name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別カテゴリ名を付与
          if(in_array($matches[1], $languages)) {
            if($value) {
              $course_title .= '[:' . $matches[1] . ']' . $value;
            } else {
              $result = false;
              $error_list[$key] = ucfirst(sprintf($lang->translate('name of %s is required'), ucwords($lang->get_lang_name($matches[1]))));
            }
          }
          
          if($matches[1] == $lang_code) {
            $course_name = $value;
          }
        } else if(preg_match('/^description_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別説明文を付与
          if(in_array($matches[1], $languages)) {
            $course_content .= '[:' . $matches[1] . ']' . $value;
          }
        } else if(preg_match('/^(option_\d+)_priority$/', $key, $matches)) {
          // オプション順番配列を付与
          $option_priority_list[intval($value)] = $matches[1];
        } else if(preg_match('/^(option_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別オプション項目名を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(in_array($matches[2], $languages)) {
            $option_list[$matches[1]]['names'][$matches[2]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_priority$/', $key, $matches)) {
          // 選択肢順番配列を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          $option_list[$matches[1]]['choice_priority_list'][intval($value)] = $matches[2];
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_name_([a-z]+)$/', $key, $matches)) {
          // 利用できる言語の前提で言語別選択肢名前を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          if(in_array($matches[3], $languages)) {
            $option_list[$matches[1]]['choices'][$matches[2]]['names'][$matches[3]] = $value;
          }
        } else if(preg_match('/^(option_\d+)_(choice_\d+)_price$/', $key, $matches)) {
          // 選択肢価格を初期化そして付与
          if(!array_key_exists($matches[1], $option_list)) {
            $option_list[$matches[1]] = $init_option_item;
          }
          if(!array_key_exists($matches[2], $option_list[$matches[1]]['choices'])) {
            $option_list[$matches[1]]['choices'][$matches[2]] = $init_choice_item;
          }
          $option_list[$matches[1]]['choices'][$matches[2]]['price'] = $value;
        }
      }
      $course_title .= '[:]';
      $course_content .= '[:]';
      
      // スラッグバリデーション
      if($slug) {
        $course = get_page_by_path($slug, OBJECT, 'course');
        
        if(!$course) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('this course is not existed'));
        } elseif($course->post_author != $user_id) {
          // ログイン中のユーザーが作者ではない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('you have no permission to update this course'));
        }
      }
      
      // 値段バリデーション
      if(array_key_exists('price', $_POST)) {
        if(!is_numeric($_POST['price'])) {
          $result = false;
          $error_list['price'] = ucfirst($lang->translate('price should be a number'));
        }
      } else {
        $result = false;
        $error_list['price'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('price')));
      }
      
      $min_people = null;
      $max_people = null;
      // 最大人数バリデーション
      if(array_key_exists('max_people', $_POST) && $_POST['max_people']) {
        if(!is_numeric($_POST['max_people']) || intval($_POST['max_people']) <= 0) {
          $result = false;
          $error_list['people'] = ucfirst(sprintf($lang->translate('%s need to be a positive integer'), $lang->translate('most people')));
        } else {
          $max_people = intval($_POST['max_people']);
        }
      }
      
      // 最少人数バリデーション
      if(array_key_exists('min_people', $_POST) && $_POST['min_people']) {
        if(!is_numeric($_POST['min_people']) || intval($_POST['min_people']) < 0) {
          $result = false;
          $error_list['people'] = ucfirst(sprintf($lang->translate('%s need to be a positive integer'), $lang->translate('least people')));
        } else {
          $min_people = intval($_POST['min_people']);
        }
      }
      
      // 人数バリデーション
      if($min_people && $max_people && $min_people > $max_people) {
        $result = false;
        $error_list['people'] = ucfirst($lang->translate('most people need to be more than least people'));
      }
      
      // 時間制限バリデーション
      if(array_key_exists('time_limit', $_POST) && $_POST['time_limit']) {
        if(!is_numeric($_POST['time_limit']) || intval($_POST['time_limit']) <= 0) {
          $result = false;
          $error_list['time_limit'] = ucfirst(sprintf($lang->translate('%s need to be a positive integer'), $lang->translate('time limit')));
        }
      }
      
      // ラストオーダーバリデーション
      if(array_key_exists('last_order', $_POST) && $_POST['last_order']) {
        if(!is_numeric($_POST['last_order']) || intval($_POST['last_order']) <= 0) {
          $result = false;
          $error_list['last_order'] = ucfirst(sprintf($lang->translate('%s need to be a positive integer'), $lang->translate('last_order')));
        }
      }
      
      // タグバリデーション
      if(array_key_exists('tag', $_POST)) {
        if(!is_array($_POST['tag'])) {
          $result = false;
          $error_list['tag'] = ucfirst(sprintf($lang->translate('%s should be a list'), $lang->translate('price')));
        } else {
          $tag_allow_list = array_keys(get_acf_select_options('Course', 'tag'));
          foreach($_POST['tag'] as $tag_key) {
            if(!in_array($tag_key, $tag_allow_list)) {
              $result = false;
              $error_list['tag'] = ucfirst($lang->translate('used tag is not existed'));
            }
          }
        }
      }
      
      // オプション項目数バリデーション
      if(count($option_list) != count($option_priority_list)) {
        $result = false;
        $error_list['options'] = ucfirst($lang->translate('option data is not matched'));
      }
      
      // 画像バリデーション
      $file = null;
      $copy_from = null;
      if(!empty($_FILES['image'])) {
        $file = $_FILES['image'];
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon');
        if($file['tmp_name']) {
          $file_mime_type = mime_content_type($file['tmp_name']);
          
          if (!in_array($file_mime_type, $allowed_mime_types)) {
            $result = false;
            $error_list['image'] = ucfirst($lang->translate('upload an image please'));
          }
        } else {
          $file = null;
        }
      } else {
        // コピー元バリデーション
        if(array_key_exists('copy_from', $_POST) && $_POST['copy_from']) {
          $copy_from = get_page_by_path($_POST['copy_from'], OBJECT, 'course');
          
          if(!$copy_from) {
            // コピー元が存在しない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('course to copy is not existed'));
          } elseif($copy_from->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('you have no permission to copy this course'));
          }
        }
      }
      
      foreach($option_list as $option_key => $option_info) {
        // オプション名バリデーション
        foreach($option_info['names'] as $language => $name) {
          if(!$name) {
            $result = false;
            $error_list[$option_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('item name of %s is required'), ucwords($lang->get_lang_name($language))));
          }
        }
        
        // 選択肢数バリデーション
        if(count($option_info['choices']) == 0) {
          $result = false;
          $error_list[$option_key . '_choices'] = ucfirst($lang->translate('at least one choice is required'));
        } else if(count($option_info['choices']) != count($option_info['choice_priority_list'])) {
          $result = false;
          $error_list[$option_key . '_choices'] = ucfirst($lang->translate('choice data is not matched'));
        }
        
        foreach($option_info['choices'] as $choice_key => $choice_info) {
          // 選択肢名前バリデーション
          foreach($choice_info['names'] as $language => $name) {
            if(!$name) {
              $result = false;
              $error_list[$option_key . '_' . $choice_key . '_name_' . $language] = ucfirst(sprintf($lang->translate('choice name of %s is required'), ucwords($lang->get_lang_name($language))));
            }
          }
          
          // 選択肢値段バリデーション
          if(!is_numeric($choice_info['price'])) {
            $result = false;
            $error_list[$option_key . '_' . $choice_key . '_price'] = ucfirst($lang->translate('price should be a number'));
          }
        }
      }
      
      // 自由注文バリデーション
      $free_menus = array();
      if(array_key_exists('free_menus', $_POST) && $_POST['free_menus']) {
        $free_slug_list = explode(',', $_POST['free_menus']);
        foreach($free_slug_list as $free_index => $free_slug) {
          $free_menu = get_page_by_path($free_slug, OBJECT, 'menu');
          
          if(!$free_menu) {
            // メニューが存在しない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('this course is not existed'));
          } elseif($free_menu->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('this course is not existed'));
          } else {
            array_push($free_menus, $free_menu->ID);
          }
        }
      }
      
      // バリデーションに問題ない場合、処理を続行
      if($result) {
        try {
          $wpdb->query('START TRANSACTION');
          
          $course_id = null;
          $new_flag = false;
          if($course) {
            // 既存のメニューを更新
            $course_id = $course->ID;
            $course = get_post($course_id);
            $course->post_title = $course_title;
            $course->post_content = $course_content;
            wp_update_post($course);
          } else {
            // メニューを新規作成
            $slug = md5(uniqid(microtime(), true));
            $course_id = wp_insert_post(array(
              'post_title' => $course_title,
              'post_name' => $slug,
              'post_content' => $course_content,
              'post_status' => 'publish',
              'post_author' => $user_id,
              'post_type' => 'course',
            ));
            
            // Wordpress データ処理エラーが発生するときログを残す
            if(!$course_id) {
              $result = false;
              $error_list['system'] = ucfirst($lang->translate('failed to create course'));
            }
            
            // 新規フラグを立つ
            $new_flag = true;
          }
          
          // 画像処理
          $attachment_id = null;
          if($file) {
            // WordPress のメディアライブラリにファイルをアップロード
            $upload = wp_handle_upload($file, array('test_form' => false));

            if (!$upload || isset($upload['error'])) {
              $result = false;
              $error_list['image'] = ucfirst($lang->translate('failed to upload image'));
            } else {
              // アップロード成功、添付ファイルとして登録
              $attachment = array(
                  'post_mime_type' => $upload['type'],
                  'post_title'     => $slug,
                  'post_content'   => '',
                  'post_status'    => 'inherit'
              );
              $attachment_id = wp_insert_attachment($attachment, $upload['file']);
              
              $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
              wp_update_attachment_metadata($attachment_id, $attach_data);
            }
          } elseif($copy_from) {
            // 既存画像からコピー
            $old_attachment_id = get_field('course_image', $copy_from->ID);
            if($old_attachment_id) {
              $old_file_path = get_attached_file($old_attachment_id);
              $old_file_name = basename($old_file_path);
              $upload_dir = wp_upload_dir();
              $file_name = wp_unique_filename($upload_dir['path'], $old_file_name);
              $file_path = $upload_dir['path'] . '/' . $file_name;
              
              if (copy($old_file_path, $file_path)) {
                // 新しいファイルとして登録
                $attachment = array(
                  'post_mime_type' => get_post_mime_type($old_attachment_id),
                  'post_title' => $slug,
                  'post_content' => '',
                  'post_status' => 'inherit',
                );
                
                // 新しい添付ファイルをデータベースに挿入
                $attachment_id = wp_insert_attachment($attachment, $file_path);
                
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
              } else {
                $result = false;
                $error_list['image'] = ucfirst($lang->translate('failed to copy image'));
              }
            }
          }
          
          if($result && $course_id) {
            if($new_flag) {
              // 表示順を更新
              update_field('priority', '99999999', $course_id);
            }
            
            // 画像を更新
            if($attachment_id) {
              $old_attachment_id = get_field('course_image', $course_id);
              if($old_attachment_id) {
                wp_delete_attachment($old_attachment_id, true);
              }
              update_field('course_image', $attachment_id, $course_id);
            } elseif(array_key_exists('image_delete', $_POST) && $_POST['image_delete'] == 'on') {
              $old_attachment_id = get_field('course_image', $course_id);
              if($old_attachment_id) {
                wp_delete_attachment($old_attachment_id, true);
              }
            }
            
            // 値段を更新
            update_field('price', $_POST['price'], $course_id);
            
            // タグを更新
            update_field('tag', $_POST['tag'], $course_id);
            
            // 利用人数を更新
            if($min_people) {
              update_field('min_people', $_POST['min_people'], $course_id);
            }
            if($max_people) {
              update_field('max_people', $_POST['max_people'], $course_id);
            }
            
            // 利用時間帯を更新
            update_field('start_time', $_POST['start_time'], $course_id);
            update_field('end_time', $_POST['end_time'], $course_id);
            
            // 販売期間を更新
            update_field('start_date', $_POST['start_date'], $course_id);
            update_field('end_date', $_POST['end_date'], $course_id);
            
            // 時間制限を更新
            update_field('time_limit', $_POST['time_limit'], $course_id);
            
            // ラストオーダーを更新
            update_field('last_order', $_POST['last_order'], $course_id);
            
            // オプション情報整理
            $options = array();
            foreach($option_priority_list as $option_key) {
              $option = array(
                'name' => $option_list[$option_key]['names'],
                'choices' => array(),
              );
              
              // 選択肢情報整理
              foreach($option_list[$option_key]['choice_priority_list'] as $choice_key) {
                $choice = array(
                  'name' => $option_list[$option_key]['choices'][$choice_key]['names'],
                  'price' => $option_list[$option_key]['choices'][$choice_key]['price'],
                );
                
                // 選択肢情報を配列に格納
                array_push($option['choices'], $choice);
              }
              
              // オプション情報を配列に格納
              array_push($options, $option);
            }
            
            // オプションを更新
            update_field('option', $options, $course_id);
            
            // 自由注文を更新
            update_field('free_menus', $free_menus, $course_id);
          }
          
          $wpdb->query('COMMIT');
        } catch (Exception $e) {
          $wpdb->query('ROLLBACK');
          
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to save course'));
          error_log('Save Course : ' . $e->get_error_message());
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'course_name' => $course_name,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_course', 'func_save_course');
add_action('wp_ajax_nopriv_save_course', 'func_save_course');


/*
 * コース削除Ajax処理
 */
function func_delete_course(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $slug = '';
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage course'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_POST)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of course'));
      } else {
        // メニュー取得
        $slug = $_POST['slug'];
        $course = get_page_by_path($slug, OBJECT, 'course');
        
        if(!$course) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error = ucfirst($lang->translate('failed to get data of course'));
        } else {
          // メニューID取得
          $course_id = $course->ID;
          
          if($course->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this course'));
          } else {
            try {
              $wpdb->query('START TRANSACTION');
              
              // 画像削除
              $image_id = get_field('course_image', $course_id);
              
              // 関連のカスタムフィールドと画像を削除
              delete_field('course_image', $course_id);
              wp_delete_attachment($image_id, true);
              delete_field('price', $course_id);
              delete_field('tag', $course_id);
              delete_field('min_people', $course_id);
              delete_field('max_people', $course_id);
              delete_field('start_date', $course_id);
              delete_field('end_date', $course_id);
              delete_field('start_time', $course_id);
              delete_field('end_time', $course_id);
              delete_field('time_limit', $course_id);
              delete_field('last_order', $course_id);
              delete_field('priority', $course_id);
              delete_field('option', $course_id);
              delete_field('free_menus', $course_id);
              
              // メニュー自体を削除
              wp_delete_post($course_id, true);
              
              $wpdb->query('COMMIT');
            } catch(Exception $e) {
              $wpdb->query('ROLLBACK');
              
              $result = false;
              $error = ucfirst($lang->translate('failed to delete course'));
              error_log('Delete Course : ' . $e->get_error_message());
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_delete_course', 'func_delete_course');
add_action('wp_ajax_nopriv_delete_course', 'func_delete_course');


/*
 * メニュー表示順保存Ajax処理
 */
function func_save_course_priority(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage course'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // 表示順情報バリデーショと整理
      $category_priority_list = array();
      $course_priority_list = array();
      foreach($_POST as $key => $value) {
        if(preg_match('/^course_\d+_slug$/', $key)) {
          // コース取得
          $course = get_page_by_path($value, OBJECT, 'course');
          
          if($course) {
            $course_id = $course->ID;
            
            if($course->post_author != $user_id) {
              // ログイン中のユーザーが作者ではない場合エラーを出す
              $result = false;
              $error = ucfirst($lang->translate('there is a course that you cannot access'));
            } else {
              $priority_key = str_replace('slug', 'priority', $key);
              if(!array_key_exists($priority_key, $_POST)) {
                // 相応の順番情報がない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a course has no priority information'));
              } else if(!is_numeric($_POST[$priority_key])) {
                // 順番が数値でない場合エラーを出す
                $result = false;
                $error = ucfirst($lang->translate('there is a priority is not number'));
              } else {
                $priority_list[$course_id] = $_POST[$priority_key];
              }
            }
          } else {
            $result = false;
            $error = ucfirst($lang->translate('failed to get course information'));
          }
        }
      }
      
      if($result) {
        foreach($priority_list as $key => $value) {
          update_field('priority', str_pad($value, 8, '0', STR_PAD_LEFT), $key);
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_course_priority', 'func_save_course_priority');
add_action('wp_ajax_nopriv_save_course_priority', 'func_save_course_priority');




























/*
 * 全席情報を取得Ajax処理
 */
function func_get_all_seats(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $list = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage seat'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      $list = get_seat_info($user_id);
    }
  }
  
  $response = array(
    'result' => $result,
    'list' => $list,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_all_seats', 'func_get_all_seats');
add_action('wp_ajax_nopriv_get_all_seats', 'func_get_all_seats');


/*
 * 席取得Ajax処理
 */
function func_get_seat(){
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $result = true;
  $seat_info = array();
  $error = '';
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage seat'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_GET)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of seat'));
      } else {
        // メニュー取得
        $slug = $_GET['slug'];
        $seat = get_page_by_path($slug, OBJECT, 'seat');
        
        if(!$seat) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to get data of seat'));
        } else {
          // メニューID取得
          $seat_id = $seat->ID;
          
          if($seat->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this seat'));
          } else {
            // メニュー情報整理
            $seat_info = array(
              'name' => get_the_title($seat_id),
              'people' => get_field('people', $seat_id),
            );
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'seat' => $seat_info,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_get_seat', 'func_get_seat');
add_action('wp_ajax_nopriv_get_seat', 'func_get_seat');


/*
 * 席保存Ajax処理
 */
function func_save_seat(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

  $lang = new LanguageSupporter();
  $lang_code = $lang->code();
  $result = true;
  $slug = '';
  $seat_name = '';
  $error_list = array();
  
  if(!is_user_logged_in()){
    $result = false;
    $error_list['system'] = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      $result = false;
      $error_list['system'] = ucfirst($lang->translate('you have no permission to manage seat'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      // スラッグバリデーション
      if(array_key_exists('slug', $_POST)) {
        $slug = $_POST['slug'];
        if($slug) {
          $seat = get_page_by_path($slug, OBJECT, 'seat');
          
          if(!$seat) {
            // メニューが存在しない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('this seat is not existed'));
          } elseif($seat->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error_list['system'] = ucfirst($lang->translate('you have no permission to update this seat'));
          }
        }
      }
      
      // 席名バリデーション
      if(array_key_exists('name', $_POST)) {
        $seat_name = $_POST['name'];
      } else {
        $result = false;
        $error_list['price'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('name')));
      }
      
      // 人数バリデーション
      if(array_key_exists('people', $_POST)) {
        if(!is_numeric($_POST['people'])) {
          $result = false;
          $error_list['people'] = ucfirst($lang->translate('people should be a number'));
        }
      } else {
        $result = false;
        $error_list['people'] = ucfirst(sprintf($lang->translate('%s is required'), $lang->translate('people')));
      }
      
      // バリデーションに問題ない場合、処理を続行
      if($result) {
        try {
          $wpdb->query('START TRANSACTION');
          
          $seat_id = null;
          $new_flag = false;
          if($seat) {
            // 既存のメニューを更新
            $seat_id = $seat->ID;
            $seat = get_post($seat_id);
            $seat->post_title = $seat_name;
            wp_update_post($seat);
          } else {
            // メニューを新規作成
            $slug = md5(uniqid(microtime(), true));
            $seat_id = wp_insert_post(array(
              'post_title' => $seat_name,
              'post_name' => $slug,
              'post_content' => '',
              'post_status' => 'publish',
              'post_author' => $user_id,
              'post_type' => 'seat',
            ));
            
            // Wordpress データ処理エラーが発生するときログを残す
            if(!$seat_id) {
              $result = false;
              $error_list['system'] = ucfirst($lang->translate('failed to create seat'));
            }
            
            // 新規フラグを立つ
            $new_flag = true;
          }
          
          if($result && $seat_id) {
            // 人数を更新
            update_field('people', $_POST['people'], $seat_id);
          }
          
          $wpdb->query('COMMIT');
        } catch (Exception $e) {
          $wpdb->query('ROLLBACK');
          
          $result = false;
          $error_list['system'] = ucfirst($lang->translate('failed to save seat'));
          error_log('Save Seat : ' . $e->get_error_message());
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'seat_name' => $seat_name,
    'errors' => $error_list,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_save_seat', 'func_save_seat');
add_action('wp_ajax_nopriv_save_seat', 'func_save_seat');


/*
 * 席削除Ajax処理
 */
function func_delete_seat(){
  global $wpdb;
  
  require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
  
  $lang = new LanguageSupporter();
  $result = true;
  $slug = '';
  $error = array();
  
  if(!is_user_logged_in()){
    // 未ログイン時エラーを出す
    $result = false;
    $error = ucfirst($lang->translate('login is required'));
  } else {
    if(!current_user_can('manager')) {
      // ログインユーザーが管理係ではない場合エラーを出す
      $result = false;
      $error = ucfirst($lang->translate('you have no permission to manage seat'));
    } else {
      // ログインユーザーID取得
      $user_id = get_current_user_id();
      
      if(!array_key_exists('slug', $_POST)) {
        // スラッグ未指定の場合エラーを出す
        $result = false;
        $error = ucfirst($lang->translate('failed to get data of seat'));
      } else {
        // メニュー取得
        $slug = $_POST['slug'];
        $seat = get_page_by_path($slug, OBJECT, 'seat');
        
        if(!$seat) {
          // メニューが存在しない場合エラーを出す
          $result = false;
          $error = ucfirst($lang->translate('failed to get data of seat'));
        } else {
          // メニューID取得
          $seat_id = $seat->ID;
          
          if($seat->post_author != $user_id) {
            // ログイン中のユーザーが作者ではない場合エラーを出す
            $result = false;
            $error = ucfirst($lang->translate('you have no permission to access this seat'));
          } else {
            try {
              $wpdb->query('START TRANSACTION');
              
              // 関連のカスタムフィールドを削除
              delete_field('people', $seat_id);
              
              // メニュー自体を削除
              wp_delete_post($seat_id, true);
              
              $wpdb->query('COMMIT');
            } catch(Exception $e) {
              $wpdb->query('ROLLBACK');
              
              $result = false;
              $error = ucfirst($lang->translate('failed to delete seat'));
              error_log('Delete Seat : ' . $e->get_error_message());
            }
          }
        }
      }
    }
  }
  
  $response = array(
    'result' => $result,
    'slug' => $slug,
    'error' => $error,
  );
  echo json_encode($response);

  die();
}
add_action('wp_ajax_delete_seat', 'func_delete_seat');
add_action('wp_ajax_nopriv_delete_seat', 'func_delete_seat');
