<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

if(is_user_logged_in()) :
  // ログイン中に再ログインできない
  // ログイン後自動リダイレクト先URLを取得
  $redirect_url = get_login_redirect_url(wp_get_current_user());
  
  if($redirect_url) {
    // リダイレクト先URLがある場合そのページにリダイレクト
    header('Location: ' . $redirect_url);
    exit;
  } else {
    // リダイレクト先URLがない場合会員個人ページにリダイレクト
    header('Location: ' . LANG_DOMAIN . '/employee/');
    exit;
  }
else :
  if(!array_key_exists('restaurant', $_GET) || !array_key_exists('type', $_GET)) :
    // タイプとレストランを指定していない場合、ページエラーテンプレートを切り替え
    include(locate_template('404.php'));
  else:
    $restaurant = $_GET['restaurant'];
    $type = $_GET['type'];
    
    $user_query = new WP_User_Query(array(
      'meta_key' => 'uid',
      'meta_value' => $restaurant,
    ));
    $user_list = $user_query->get_results();
    
    if(count($user_list) != 1):
      // 対象レストランは一つではない場合、ページエラーテンプレートを切り替え
      include(locate_template('404.php'));
    elseif(!in_array($type, array('cook', 'waiter'))):
      // 指定のタイプではない場合、ページエラーテンプレートを切り替え
      include(locate_template('404.php'));
    else:
      $page_title = ucwords($lang->translate('employee sign up'));
      $page_topic = strtoupper($page_title);
      $style_key = 'account';
      
      // 正常表示
      get_header();
      
      $languages = $lang->get_lang_list();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="profile-section">

  <form class="form" id="add-employee-form">
  
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('username')); ?><span class="required">*</span></p>
      <div class="form-input">
        <input type="text" name="user_login" placeholder="<?php echo ucwords($lang->translate('username')); ?>" />
        <p class="warning warning-user_login"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('mail address')); ?><span class="required">*</span></p>
      <div class="form-input">
        <input type="text" name="email" placeholder="<?php echo ucwords($lang->translate('mail address')); ?>" />
        <p class="warning warning-email"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('password')); ?><span class="required">*</span></p>
      <div class="form-input">
        <div class="password">
          <input type="password" name="password" placeholder="<?php echo ucwords($lang->translate('password')); ?>" />
          <div class="button password-show float-description">
            <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
          </div>
        </div>
        <p class="warning warning-password"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('password confirmation')); ?><span class="required">*</span></p>
      <div class="form-input">
        <div class="password">
          <input type="password" name="password_confirm" placeholder="<?php echo ucwords($lang->translate('password confirmation')); ?>" />
          <div class="button password-show float-description">
            <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
          </div>
        </div>
        <p class="warning warning-password_confirm"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('language')); ?><span class="required">*</span></p>
      <div class="form-input">
        <select name="language">
          <?php foreach($languages as $key => $value): ?>
            <option value="<?php echo $key; ?>" <?php echo $key == $lang_code ? 'selected' : ''; ?>><?php echo $value; ?></option>
          <?php endforeach; ?>
        </select>
        <p class="warning warning-language"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('employee telephone')); ?><span class="required">*</span></p>
      <div class="form-input">
        <div class="form-input-group profile-code-area">
          <input class="profile-code form-input-item" type="text" name="telephone1" max-length="4" placeholder="0000" />-
          <input class="profile-code form-input-item" type="text" name="telephone2" max-length="4" placeholder="0000" />-
          <input class="profile-code form-input-item" type="text" name="telephone3" max-length="4" placeholder="0000" />
        </div>
        <p class="warning warning-telephone"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('name')); ?><span class="required">*</span></p>
      <div class="form-input">
        <div class="form-input-group">
          <input class="form-input-item" type="text" name="family_name" placeholder="<?php echo ucwords($lang->translate('family name')); ?>" />
          <input class="form-input-item" type="text" name="first_name" placeholder="<?php echo ucwords($lang->translate('first name')); ?>" />
        </div>
        <p class="warning warning-family_name"></p>
        <p class="warning warning-first_name"></p>
      </div>
    </div>
    
    <input type="hidden" name="restaurant" value="<?php echo $restaurant; ?>" />
    <input type="hidden" name="type" value="<?php echo $type; ?>" />
    <div class="form-btnarea">
      <p class="button shine-active add-employee-submit active" id="add-employee-submit"><span><?php echo ucwords($lang->translate('sign up')); ?></span></p>
    </div>
    <p class="warning center" id="add-employee-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
  </form>
  
</section>

<?php 
      get_footer();
    endif;
  endif;
endif;
