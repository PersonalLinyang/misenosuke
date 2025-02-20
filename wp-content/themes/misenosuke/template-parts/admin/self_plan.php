<?php 
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

$lang = new LanguageSupporter();

  // 現在のユーザーのIDを取得します。
$current_user_id = get_current_user_id();

?>

<div class="wrap">
  <h1 class="wp-heading-inline"><?php echo ucwords($lang->translate('plan management')); ?></h1>
  <div>
  <?php 
  if(pmpro_hasMembershipLevel(null, $current_user_id)):

// 現在のユーザーに関連する注文情報を取得します。
    // 関連する注文情報を取得するためにPMPの関数を使用します。
    // pmpro_memberships_usersテーブルからユーザーのメンバーシップを取得
    global $wpdb;
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}pmpro_membership_orders WHERE user_id = %d",
        $current_user_id
    ));
    
    if(count($orders)):
      // 取得した注文情報を操作します。
      foreach ($orders as $order) {
          // 各注文情報を操作します。
          // たとえば、注文IDやステータス、金額などを取得できます。
          echo "Order ID: " . $order->id . "<br>";
          echo "Order Status: " . $order->status . "<br>";
          echo "Order Total: $" . $order->total . "<br>";
          // 他の情報を操作します。
      }
    else:
  ?>
  <p>まだ注文が完了していません、現在は有効な契約になっていません</p>
  <a class="button" href="<?php echo LANG_DOMAIN; ?>/member/pay/" target="_blank">支払いに行く</a>
  <?php
    endif;
  else:
  ?>
  <p>現在利用中のプランがありません</p>
  <a class="button" href="<?php echo LANG_DOMAIN; ?>/member/plan/" target="_blank">プラン選択</a>
  <?php endif; ?>
  </div>
</div>