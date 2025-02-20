<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $user = wp_get_current_user();
    $user_id = $user->ID;
    
    $page_title = ucwords($lang->translate('edit profile'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    // 正常表示
    get_header();
    
    $languages = $lang->get_lang_list();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="profile-section">

  <form class="form" id="edit-profile-form">
  
    <div class="form-block">
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('mail address')); ?><span class="required">*</span></p>
        <div class="form-input">
          <input type="text" name="email" placeholder="<?php echo ucwords($lang->translate('mail address')); ?>" 
                 value="<?php echo $user->user_email; ?>"/>
          <p class="warning warning-email"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('management language')); ?><span class="required">*</span></p>
        <div class="form-input">
          <select name="language">
            <?php 
            $manage_language = get_field('language', 'user_' . $user_id);
            foreach($languages as $key => $value): 
            ?>
              <option value="<?php echo $key; ?>" <?php echo $key == $manage_language ? 'selected' : ''; ?>><?php echo $value; ?></option>
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
            $menu_languages = explode(',', get_field('languages', 'user_' . $user_id));
            foreach($languages as $key => $value): 
              if($key != 'ja'): 
            ?>
              <li class="profile-language-item">
                <label class="checkbox form-language-label">
                  <input class="profile-menulanguage" type="checkbox" name="languages[]" value="<?php echo $key; ?>" 
                         <?php echo in_array($key, $menu_languages) ? 'checked' : ''; ?> />
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
          <input type="text" name="restaurant_name" value="<?php echo get_field('restaurant_name', 'user_' . $user_id); ?>" 
                 placeholder="<?php echo ucwords($lang->translate('restaurant name')); ?>(<?php echo $languages['ja']; ?>)" />
          <p class="warning warning-restaurant_name"></p>
        </div>
      </div>
      <div class="profile-restaurantname">
        <?php 
        $restaurant_names = get_field('restaurant_names', 'user_' . $user_id);
        $restaurant_name_list = array();
        foreach($restaurant_names as $restaurant_name_info) {
          $restaurant_name_list[$restaurant_name_info['language']] = $restaurant_name_info['name'];
        }
        foreach($menu_languages as $language): 
          if($language != 'ja'): 
        ?>
          <div class="form-line profile-restaurantname-<?php echo $language; ?>">
            <p class="form-title"><?php echo ucwords($lang->translate('restaurant name')); ?>(<?php echo $languages[$language]; ?>)<span class="required">*</span></p>
            <div class="form-input">
              <input type="text" name="restaurant_name_<?php echo $language; ?>"  value="<?php echo $restaurant_name_list[$language]; ?>"
                     placeholder="<?php echo ucwords($lang->translate('restaurant name')); ?>(<?php echo $languages[$language]; ?>)" />
              <p class="warning warning-restaurant_name_<?php echo $language; ?>"></p>
            </div>
          </div>
        <?php 
          endif;
        endforeach; 
        ?>
      </div>
      <?php $zipcode = explode('-', get_field('zipcode', 'user_' . $user_id)); ?>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant address')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div class="form-input-group profile-code-area">
            <input class="profile-code form-input-item profile-zipcode1" type="text" name="zipcode1" max-length="3"
                   placeholder="000" value="<?php echo count($zipcode) == 2 ? $zipcode[0] : ''; ?>" />-
            <input class="profile-code form-input-item profile-zipcode2" type="text" name="zipcode2" max-length="4"
                   placeholder="0000" value="<?php echo count($zipcode) == 2 ? $zipcode[1] : ''; ?>" />
            <p class="profile-getaddress"><?php echo ucwords($lang->translate('search address')); ?></p>
          </div>
          <p class="warning warning-zipcode"></p>
          <div class="form-input-group profile-address">
            <input class="form-input-item readonly profile-prefecture" type="text" name="prefecture" placeholder="<?php echo ucwords($lang->translate('prefecture')); ?>" 
                   value="<?php echo get_field('prefecture', 'user_' . $user_id); ?>" readonly />
            <input class="form-input-item readonly profile-city" type="text" name="city" placeholder="<?php echo ucwords($lang->translate('city')); ?>" 
                   value="<?php echo get_field('city', 'user_' . $user_id); ?>" readonly />
            <input class="form-input-item readonly profile-street" type="text" name="street" placeholder="<?php echo ucwords($lang->translate('street')); ?>" 
                   value="<?php echo get_field('street', 'user_' . $user_id); ?>" readonly />
          </div>
          <p class="warning warning-address"></p>
          <div class="form-input-group profile-address">
            <input class="form-input-item" type="text" name="address_other" placeholder="<?php echo ucwords($lang->translate('address other')); ?>" 
                   value="<?php echo get_field('address_other', 'user_' . $user_id); ?>" />
          </div>
          <p class="warning warning-address_other"></p>
        </div>
      </div>
      <?php $telephone = explode('-', get_field('telephone', 'user_' . $user_id)); ?>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant telephone')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div class="form-input-group profile-code-area">
            <input class="profile-code form-input-item" type="text" name="telephone1" max-length="4"
                   placeholder="0000" value="<?php echo count($telephone) == 3 ? $telephone[0] : ''; ?>" />-
            <input class="profile-code form-input-item" type="text" name="telephone2" max-length="4"
                   placeholder="0000" value="<?php echo count($telephone) == 3 ? $telephone[1] : ''; ?>" />-
            <input class="profile-code form-input-item" type="text" name="telephone3" max-length="4"
                   placeholder="0000" value="<?php echo count($telephone) == 3 ? $telephone[2] : ''; ?>" />
          </div>
          <p class="warning warning-telephone"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('owner name')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div class="form-input-group <?php echo in_array($lang_code, EN_NAME_ORDER_LANG) ? 'reverse' : ''; ?>">
            <input class="form-input-item" type="text" name="family_name" placeholder="<?php echo ucwords($lang->translate('family name')); ?>" 
                   value="<?php echo $user->last_name; ?>" />
            <input class="form-input-item" type="text" name="first_name" placeholder="<?php echo ucwords($lang->translate('first name')); ?>" 
                   value="<?php echo $user->first_name; ?>" />
          </div>
          <p class="warning warning-family_name"></p>
          <p class="warning warning-first_name"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant url')); ?></p>
        <div class="form-input">
          <input type="text" name="restaurant_url" placeholder="<?php echo ucwords($lang->translate('restaurant url')); ?>" value="<?php echo $user->user_url; ?>" />
          <p class="warning warning-restaurant_url"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('restaurant logo')); ?></p>
        <div class="form-input">
          <div class="profile-logo">
            <div class="profile-logo-inner" style="background-image: url(<?php echo wp_get_attachment_url(get_field('restaurant_logo', 'user_' . $user_id)); ?>);"></div>
            <label class="profile-logo-button">
              <input class="profile-restaurantlogo" id="edit-profile-restaurantlogo" type="file" name="restaurant_logo" /><?php echo ucwords($lang->translate('upload')); ?>
            </label>
          </div>
          <p class="warning warning-restaurant_logo"></p>
        </div>
      </div>
    </div>
    
    <div class="form-btnarea">
      <p class="button edit-profile-back"><a class="full-link" href="<?php echo LANG_DOMAIN; ?>/member/"><?php echo ucwords($lang->translate('back')); ?></a></p>
      <p class="button edit-profile-submit" id="edit-profile-submit"><span><?php echo ucwords($lang->translate('save')); ?></span></p>
    </div>
    <p class="warning center" id="edit-profile-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
  </form>
  
</section>

<section class="popup-message">
  <p class="popup-message-text"></p>
</section>

<?php 
    get_footer();
  else:
    // 管理係ではない場合、権限エラーテンプレートを切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
