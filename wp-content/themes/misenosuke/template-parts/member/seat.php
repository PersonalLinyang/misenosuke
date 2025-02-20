<?php 
/**
 * 席情報を表示
 */

global $wpdb;

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();

$seats = get_seat_info($user_id);

?>

<section class="member-section" id="member-seat">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('seat')); ?></h3>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/manage-seat/"><?php echo ucwords($lang->translate('seat management')); ?></a>
      </p>
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/export-seat/"><?php echo ucwords($lang->translate('download QR')); ?></a>
      </p>
    </div>
  </div>
  
  <div class="member-seat-area">
    <ul class="member-seat-list">
      <?php foreach($seats as $seat): ?>
        <li class="member-seat-item"><?php echo $seat['name']; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>