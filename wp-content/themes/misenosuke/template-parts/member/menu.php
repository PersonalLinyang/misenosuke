<?php 
/**
 * メニュー、コース情報を表示
 */

global $wpdb;

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();

// メニューとカテゴリ情報取得
$menu_list = get_menu_info_with_category($user_id);

// コース情報取得
$course_list = get_course_info($user_id);

?>

<section class="member-section" id="member-menu">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('menu')); ?></h3>
  </div>
  
  <div class="member-section-subheader">
    <h4><?php echo strtoupper($lang->translate('dish menu')); ?></h4>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/manage-menu/"><?php echo ucwords($lang->translate('menu management')); ?></a>
      </p>
    </div>
  </div>
  
  <div class="member-menu-category">
    <?php foreach($menu_list as $menu_category_index => $menu_category): ?>
      <div class="member-menu-category-item">
        <div class="member-menu-category-header">
          <p class="member-menu-category-controller"></p>
          <p class="member-menu-category-title"><?php echo $menu_category['name']; ?></p>
        </div>
        <div class="member-menu-category-body">
          <?php 
          if(count($menu_category['menus'])): 
            foreach($menu_category['menus'] as $menu_index => $menu):
          ?>
            <p class="member-menu-category-menu"><?php echo $menu['name']; ?></p>
          <?php 
            endforeach;
          else: 
          ?>
            <p class="member-menu-category-empty"><?php echo ucfirst($lang->translate('no menu in this category')); ?></p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  
  <div class="member-section-subheader">
    <h4><?php echo strtoupper($lang->translate('course menu')); ?></h4>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/manage-course/"><?php echo ucwords($lang->translate('course management')); ?></a>
      </p>
    </div>
  </div>
  
  <div class="member-menu-course">
    <?php foreach($course_list as $course_index => $course): ?>
      <p class="member-menu-course-item"><?php echo $course['name']; ?></p>
    <?php endforeach; ?>
  </div>
</section>