<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

if(is_user_logged_in()) :
  $page_title = ucwords($lang->translate('change password'));
  $page_topic = strtoupper($page_title);
  $style_key = 'account';
  
  get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="change-password-section">
  <form class="form" id="change-password-form">
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('old password')); ?></p>
      <div class="form-input">
        <div class="password">
          <input class="change-password-input" type="password" name="old_password" placeholder="<?php echo ucwords($lang->translate('old password')); ?>" />
          <div class="button password-show float-description">
            <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
          </div>
        </div>
        <p class="warning warning-old_password"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('new password')); ?></p>
      <div class="form-input">
        <div class="password">
          <input class="change-password-input" type="password" name="new_password" placeholder="<?php echo ucwords($lang->translate('new password')); ?>" />
          <div class="button password-show float-description">
            <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
          </div>
        </div>
        <p class="warning warning-new_password"></p>
      </div>
    </div>
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('password confirmation')); ?></p>
      <div class="form-input">
        <div class="password">
          <input class="change-password-input" type="password" name="password_confirm" placeholder="<?php echo ucwords($lang->translate('password confirmation')); ?>" />
          <div class="button password-show float-description">
            <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
          </div>
        </div>
        <p class="warning warning-password_confirm"></p>
      </div>
    </div>
    <div class="form-btnarea">
      <p class="button shine-active change-password-button" id="change-password-submit"><?php echo ucwords($lang->translate('change')); ?></p>
    </div>
    <p class="warning center warning-system"></p>
    <p class="warning center" id="change-password-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
  </form>
</section>

<section class="popup-message">
  <p class="popup-message-text"></p>
</section>

<?php 
  get_footer();
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;