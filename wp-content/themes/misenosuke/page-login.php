<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

if(is_user_logged_in()) :
  // ログイン中に再ログインできない
  // ログイン後自動リダイレクト先URLを取得
  $redirect_url = get_login_redirect_url(wp_get_current_user());
  
  if($redirect_url) :
    // リダイレクト先URLがある場合そのページにリダイレクト
    header('Location: ' . $redirect_url);
    exit;
  else:
    // リダイレクト先URLがない場合会員個人ページにリダイレクト
    header('Location: ' . LANG_DOMAIN . '/member/');
    exit;
  endif;
else :
  $page_title = ucwords($lang->translate('login'));
  $page_topic = strtoupper($page_title);
  $style_key = 'account';
  
  // 正常表示
  get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="login-section">
  <form class="form" id="login-form">
    <div class="form-block">
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('loginid')); ?></p>
        <div class="form-input">
          <input class="login-input" id="login-loginid" type="text" name="loginid" placeholder="<?php echo ucwords($lang->translate('username or mail address')); ?>" />
          <p class="warning warning-loginid"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('password')); ?></p>
        <div class="form-input">
          <div class="password">
            <input class="login-input" id="login-password" type="password" name="password" placeholder="<?php echo ucwords($lang->translate('password')); ?>" />
            <div class="button password-show float-description">
              <p class="description"><?php echo ucwords($lang->translate('change password display')); ?></p>
            </div>
          </div>
          <p class="warning warning-password"></p>
        </div>
      </div>
    </div>
    <div class="form-block">
      <div class="form-line">
        <div class="form-input">
          <label class="checkbox checkbox-center login-remember">
            <input type="checkbox" name="remember" id="chk-remember" />
            <?php echo ucfirst($lang->translate('remember me')); ?>
          </label>
          <p class="warning warning-remember"></p>
        </div>
      </div>
    </div>
    <div class="form-btnarea">
      <p class="button shine-active login-button" id="login-submit"><span><?php echo ucwords($lang->translate('login')); ?></span></p>
    </div>
    <p class="warning center" id="login-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    <p class="login-bottomlink"><a href="<?php echo LANG_DOMAIN; ?>/signup/" class="underline"><?php echo ucfirst($lang->translate('create new account')); ?></a></p>
    <p class="login-bottomlink"><a href="<?php echo LANG_DOMAIN; ?>/reset-password/" class="underline"><?php echo ucfirst($lang->translate('forgot password')); ?></a></p>
  </form>
</section>

<?php 
  get_footer();

endif;
