<?php 
/**
 * 支払方法情報を表示
 */

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();

// 支払い方法情報を取得
$stripe_payment_method_id = get_field('stripe_payment_method_id', 'user_' . $user_id);
try {
  $payment_method = \Stripe\PaymentMethod::retrieve($stripe_payment_method_id);
} catch (Exception $e) {
  $payment_method = null;
}
?>

<section class="member-section" id="member-card">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('payment method')); ?></h3>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a href="<?php echo LANG_DOMAIN; ?>/register-card/"><?php echo ucwords($lang->translate($payment_method ? 'reregister card' : 'register card')); ?></a>
      </p>
    </div>
  </div>
  
  <?php 
  if($payment_method): 
    // クレジットカード情報を表示
    get_template_part('template-parts/member/credit-card', null, array('card' => $payment_method->card));
  else:
  ?>
    <p class="member-empty"><?php echo ucfirst($lang->translate('you have no payment method')); ?></p>
  <?php endif;?>
</section>