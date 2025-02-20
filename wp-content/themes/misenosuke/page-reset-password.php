<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

if(is_user_logged_in()):
  // ログイン中にパスワードをリセットできない
  // ログイン後自動リダイレクト先URLを取得
  $redirect_url = get_login_redirect_url(wp_get_current_user());
  
  if($redirect_url) :
    // リダイレクト先URLがある場合そのページにリダイレクト
    header('Location: ' . $redirect_url);
    exit;
  else:
    // プロファイルページがある場合、会員個人ページにリダイレクト
    header('Location: ' . LANG_DOMAIN . '/member/');
    exit;
  endif;
else :
  $page_title = ucwords($lang->translate('reset password'));
  $page_topic = strtoupper($page_title);
  $style_key = 'account';
  
  // 正常表示
  get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="reset-password-section">
  <form class="form" id="reset-password-form">
    <div class="form-line">
      <p class="form-title"><?php echo ucwords($lang->translate('loginid')); ?></p>
      <div class="form-input">
        <input id="reset-password-loginid" type="text" name="loginid" placeholder="<?php echo ucwords($lang->translate('username or mail address')); ?>" />
        <p class="warning warning-loginid"></p>
      </div>
    </div>
    <div class="form-btnarea">
      <p class="button shine-active reset-password-button" id="reset-password-submit"><?php echo ucwords($lang->translate('reset')); ?></p>
    </div>
    <p class="warning center warning-system"></p>
    <p class="warning center" id="reset-password-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    <div class="reset-password-complete" id="reset-password-complete">
      <p><?php echo ucfirst($lang->translate('the password has been reseted successfully')); ?></p>
      <p><?php echo ucfirst($lang->translate('the new password will be sent to the mail address you inputed')); ?></p>
      <p><?php echo ucfirst($lang->translate('please login and change your password on member page')); ?></p>
    </div>
    <p class="reset-password-login"><a href="<?php echo LANG_DOMAIN; ?>/login/" class="underline"><?php echo ucfirst($lang->translate('redirect to login')); ?></a></p>
  </form>
</section>

<?php
  get_footer();

endif;
