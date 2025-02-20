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
  $page_title = ucwords($lang->translate('sign up'));
  $page_topic = strtoupper($page_title);
  $style_key = 'account';
  
  // 正常表示
  get_header();
  
  $languages = $lang->get_lang_list();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="profile-section">

  <form class="form" id="signup-form">
  
    <div class="form-block">
      <div class="member-section-header">
        <h3><?php echo strtoupper($lang->translate('personal information')); ?></h3>
      </div>
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
        <p class="form-title"><?php echo ucwords($lang->translate('management language')); ?><span class="required">*</span></p>
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
        <p class="form-title"><?php echo ucwords($lang->translate('menu language except japanese')); ?></p>
        <div class="form-input">
          <ul class="profile-language-list">
            <?php 
            foreach($languages as $key => $value): 
              if($key != 'ja'): 
            ?>
              <li class="profile-language-item">
                <label class="checkbox form-language-label">
                  <input class="profile-menulanguage" type="checkbox" name="languages[]" value="<?php echo $key; ?>" />
                  <?php echo $value; ?>
                </label>
              </li>
            <?php 
              endif;
            endforeach; 
            ?>
          </ul>
          <p class="warning warning-languages"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant name')); ?>(<?php echo $languages['ja']; ?>)<span class="required">*</span></p>
        <div class="form-input">
          <input type="text" name="restaurant_name" placeholder="<?php echo ucwords($lang->translate('restaurant name')); ?>(<?php echo $languages['ja']; ?>)" />
          <p class="warning warning-restaurant_name"></p>
        </div>
      </div>
      <div class="profile-restaurantname">
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant address')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div class="form-input-group profile-code-area">
            <input class="profile-code form-input-item profile-zipcode1" type="text" name="zipcode1" max-length="3" placeholder="000" />-
            <input class="profile-code form-input-item profile-zipcode2" type="text" name="zipcode2" max-length="4" placeholder="0000" />
            <p class="profile-getaddress"><?php echo ucwords($lang->translate('search address')); ?></p>
          </div>
          <p class="warning warning-zipcode"></p>
          <div class="form-input-group profile-address">
            <input class="form-input-item readonly profile-prefecture" type="text" name="prefecture" placeholder="<?php echo ucwords($lang->translate('prefecture')); ?>" readonly />
            <input class="form-input-item readonly profile-city" type="text" name="city" placeholder="<?php echo ucwords($lang->translate('city')); ?>" readonly />
            <input class="form-input-item readonly profile-street" type="text" name="street" placeholder="<?php echo ucwords($lang->translate('street')); ?>" readonly />
          </div>
          <p class="warning warning-address"></p>
          <div class="form-input-group profile-address">
            <input class="form-input-item" type="text" name="address_other" placeholder="<?php echo ucwords($lang->translate('address other')); ?>" />
          </div>
          <p class="warning warning-address_other"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant telephone')); ?><span class="required">*</span></p>
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
        <p class="form-title"><?php echo ucwords($lang->translate('owner name')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div class="form-input-group">
            <input class="form-input-item" type="text" name="family_name" placeholder="<?php echo ucwords($lang->translate('family name')); ?>" />
            <input class="form-input-item" type="text" name="first_name" placeholder="<?php echo ucwords($lang->translate('first name')); ?>" />
          </div>
          <p class="warning warning-family_name"></p>
          <p class="warning warning-first_name"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant url')); ?></p>
        <div class="form-input">
          <input type="text" name="restaurant_url" placeholder="<?php echo ucwords($lang->translate('restaurant url')); ?>" />
          <p class="warning warning-restaurant_url"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant logo')); ?></p>
        <div class="form-input">
          <div class="profile-logo">
            <div class="profile-logo-inner"></div>
            <label class="profile-logo-button">
              <input class="profile-restaurantlogo" id="signup-restaurantlogo" type="file" name="restaurant_logo" /><?php echo ucwords($lang->translate('upload')); ?>
            </label>
          </div>
          <p class="warning warning-restaurant_logo"></p>
        </div>
      </div>
    </div>
    
    <div class="form-block">
      <div class="member-section-header">
        <h3><?php echo strtoupper($lang->translate('payment method information')); ?></h3>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('card number')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_number" class="input"></div>
          <p class="warning warning-card_number"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('card expiration')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_expiry" class="input"></div>
          <p class="warning warning-card_expiry"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('security code')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_cvc" class="input"></div>
          <p class="warning warning-card_cvc"></p>
        </div>
      </div>
      <input id="creaditcard-input" type="hidden" name="token_id" />
      <p class="warning center warning-card_info"></p>
      <p class="warning center" id="signup-card_info"></p>
    </div>
    
    <div class="form-block">
      <div class="form-line">
        <div class="form-input center">
          <label class="checkbox checkbox-center">
            <input type="checkbox" name="agreement" id="signup-agreement" />
            <?php 
            echo sprintf(ucfirst($lang->translate('I agree with %s')), 
                '<a href="' . LANG_DOMAIN . '/terms/" class="underline blue" target="_blank">' . $lang->translate('terms of service') . '</a>'); 
            ?>
          </div>
          <p class="warning warning-agreement"></p>
        </div>
      </div>
    </div>
    
    <div class="form-btnarea">
      <p class="button shine-active signup-submit" id="signup-submit"><span><?php echo ucwords($lang->translate('sign up')); ?></span></p>
    </div>
    <p class="warning center" id="signup-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    <p class="signup-bottomlink"><a href="<?php echo LANG_DOMAIN; ?>/login/" class="underline"><?php echo ucfirst($lang->translate('login by my account')); ?></a></p>
  </form>
  
</section>

<?php 
  get_footer();

endif;
