<?php 
/**
 * 現在利用中のプランとサブスクリプション情報を表示
 */

global $wpdb;

// 翻訳有効化
$lang = new LanguageSupporter();
$lang_code = $lang->code();

// ユーザー情報を取得
$user = wp_get_current_user();
$user_id = $user->ID;

?>

<section class="member-section" id="member-profile">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('profile')); ?></h3>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/edit-profile/"><?php echo ucwords($lang->translate('edit profile')); ?></a>
      </p>
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/change-password/"><?php echo ucwords($lang->translate('change password')); ?></a>
      </p>
    </div>
  </div>
  
  <?php $language_name_list = $lang->get_lang_list(); ?>
  <table class="member-profile-table">
    <tr>
      <th><?php echo ucwords($lang->translate('username')); ?></th>
      <td><?php echo $user->user_login; ?></td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('mail address')); ?></th>
      <td><?php echo $user->user_email; ?></td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('management language')); ?></th>
      <td><?php echo $language_name_list[get_field('language', 'user_' . $user_id)]; ?></td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('menu language')); ?></th>
      <td>
        <?php 
        $menu_language_list = explode(',', get_field('languages', 'user_' . $user_id)); 
        $menu_language_name_list = array();
        foreach($menu_language_list as $menu_language) {
          array_push($menu_language_name_list, $language_name_list[$menu_language]);
        }
        echo implode(', ', $menu_language_name_list);
        ?>
      </td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('restaurant name')); ?></th>
      <td>
        <?php
        $restaurant_name_list = array($language_name_list['ja'] . ' : ' . get_field('restaurant_name', 'user_' . $user_id));
        foreach(get_field('restaurant_names', 'user_' . $user_id) as $restaurant_name_info) {
          array_push($restaurant_name_list, $language_name_list[$restaurant_name_info['language']] . ' : ' . $restaurant_name_info['name']);
        }
        echo implode('<br/>', $restaurant_name_list);
        ?>
      </td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('restaurant address')); ?></th>
      <td>
        &#12306;<?php echo get_field('zipcode', 'user_' . $user_id); ?><br/>
        <?php echo get_field('address', 'user_' . $user_id); ?>
      </td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('restaurant telephone')); ?></th>
      <td><?php echo get_field('telephone', 'user_' . $user_id); ?></td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('owner name')); ?></th>
      <td>
        <?php 
        if(in_array($lang_code, EN_NAME_ORDER_LANG)) {
          echo $user->first_name . ' ' . $user->last_name;
        } else {
          echo $user->last_name . ' ' . $user->first_name;
        }
        ?>
      </td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('restaurant url')); ?></th>
      <td><?php echo $user->user_url; ?></td>
    </tr>
    <tr>
      <th><?php echo ucwords($lang->translate('restaurant logo')); ?></th>
      <td><img class="member-profile-logo" src="<?php echo wp_get_attachment_url(get_field('restaurant_logo', 'user_' . $user_id)); ?>" /></td>
    </tr>
  </table>
</section>