<?php 
/**
 * 現在利用中のプランとサブスクリプション情報を表示
 */

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();

// プラン情報取得
$plan_id = get_field('plan', 'user_' . $user_id);
if($plan_id){
  $plan = get_post($plan_id);
} else {
  $plan = null;
}
?>

<section class="member-section" id="member-subscription">
  
  <?php 
  if($plan): 
    // 利用中プランがある場合、現在のプラン情報を表示
    $plan_code = $plan->post_name;
    $plan_image = get_field('image', $plan_id);
    $plan_price = intval(get_field('price', $plan_id));
    $plan_period = intval(get_field('period', $plan_id));
    $plan_unit = get_field('unit', $plan_id);
    
    $subscription_id = get_field('stripe_subscription_id', 'user_' . $user_id);
    if($subscription_id){
      try {
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        $subscription_status = $subscription->status;
      } catch (\Stripe\Exception\ApiErrorException $e) {
        $subscription = null;
        $subscription_status = 'undetected';
        error_log('Stripe Error: ' . $e->getMessage());
      }
    } else {
      $subscription = null;
      $subscription_status = 'unfound';
    }
  ?>
    <div class="member-section-header">
      <h3><?php echo strtoupper($lang->translate('current plan')); ?></h3>
      <div class="member-section-controller">
        <p class="member-section-button">
          <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/create-subscription/"><?php echo ucwords($lang->translate('change plan')); ?></a>
        </p>
        <?php if($subscription_status == 'active' || $subscription_status == 'trialing'): ?>
          <p class="member-section-button">
            <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/cancel-subscription/"><?php echo ucwords($lang->translate('cancel subscription')); ?></a>
          </p>
        <?php endif; ?>
      </div>
    </div>
    <div class="subscription">
      <div class="subscription-image">
        <?php if($plan_image):?>
          <img src="<?php echo $plan_image; ?>" />
        <?php else: ?>
          <p class="subscription-image-text"><?php echo strtoupper($lang->translate('image will come soon')); ?></p>
        <?php endif; ?>
      </div>
      <div class="subscription-info">
        <div class="subscription-header">
          <p class="subscription-name"><?php echo get_the_title($plan_id); ?></p>
          <p class="subscription-status <?php echo $subscription_status; ?>"><?php echo strtoupper($lang->translate($subscription_status)); ?></p>
        </div>
        <div class="subscription-body">
          <div class="subscription-description">
            <?php echo apply_filters('the_content', $plan->post_content); ?>
          </div>
          <div class="subscription-price">
            <p class="subscription-price-price">
              <?php 
              if(intval($plan_period) == 1) {
                $cycle_period = $lang->translate($plan_unit);
              } else {
                $cycle_period = sprintf($lang->translate('%s' . $plan_unit . 's'), $plan_period);
              } 
              echo sprintf($lang->translate('%syen'), '<span class="price">' . number_format($plan_price) . '</span>') . ' / ' . $cycle_period; 
              ?>
            </p>
          </div>
          <?php 
          if($subscription_status == 'trialing'): ?>
            <p class="subscription-note">
              <?php 
              echo sprintf(ucfirst($lang->translate('subscription will stop trialing and claim at %s')), 
                           '<span class="point">' . date($lang->translate('F j G:i'), $subscription->trial_end) . '</span>'); 
              ?>
            </p>
          <?php endif; ?>
          <?php if($subscription_status == 'active'): ?>
            <p class="subscription-note">
              <?php 
              echo sprintf(ucfirst($lang->translate('next payment will be at %s')), 
                           '<span class="point">' . date($lang->translate('F j G:i'), $subscription->current_period_end) . '</span>'); 
              ?>
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php 
  else: 
    $prev_plan_id = get_field('prev_plan', 'user_' . $user_id);
    if($prev_plan_id){
      $prev_plan = get_post($prev_plan_id);
    } else {
      $prev_plan = null;
    }
    
    // 前回プラン情報がある場合、前回プラン情報を表示
    if($prev_plan): 
      $prev_plan_code = $prev_plan->post_name;
      $prev_plan_image = get_field('image', $plan_id);
      $prev_cancellation_time = intval(get_field('prev_cancellation_time', 'user_' . $user_id));
  ?>
      <div class="member-section-header">
        <?php if($prev_cancellation_time < time()): ?>
          <h3><?php echo strtoupper($lang->translate('last plan')); ?></h3>
          <div class="member-section-controller">
            <p class="member-section-button">
              <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/create-subscription/"><?php echo ucwords($lang->translate('restart subscription')); ?></a>
            </p>
          </div>
        <?php else: ?>
          <h3><?php echo strtoupper($lang->translate('current plan')); ?></h3>
        <?php endif; ?>
      </div>
      <div class="subscription">
        <div class="subscription-image">
          <?php if($prev_plan_image):?>
            <img src="<?php echo $prev_plan_image; ?>" />
          <?php else: ?>
            <p class="subscription-image-text"><?php echo strtoupper($lang->translate('image will come soon')); ?></p>
          <?php endif; ?>
        </div>
        <div class="subscription-info">
          <div class="subscription-header">
            <p class="subscription-name"><?php echo get_the_title($prev_plan_id); ?></p>
            <?php if($prev_cancellation_time < time()): ?>
              <p class="subscription-status canceled"><?php echo strtoupper($lang->translate('subscription canceled')); ?></p>
            <?php else: ?>
              <p class="subscription-status cancelling"><?php echo strtoupper($lang->translate('subscription cancelling')); ?></p>
            <?php endif; ?>
          </div>
          <div class="subscription-body">
            <div class="subscription-description">
              <?php echo apply_filters('the_content', $prev_plan->post_content); ?>
            </div>
            <?php if($prev_cancellation_time < time()): ?>
              <p class="subscription-note">
                <?php echo sprintf(ucfirst($lang->translate('subscription was canceled at %s')), '<span class="point">' . date($lang->translate('F j G:i'), $prev_cancellation_time) . '</span>'); ?>
              </p>
            <?php 
            else: 
              // 返金額を取得
              $prev_subscription_id = get_field('prev_stripe_subscription_id', 'user_' . $user_id);
              $refund_amount = 0;
              try {
                $upcoming_invoice = \Stripe\Invoice::upcoming([
                  'subscription' => $prev_subscription_id,
                ]);
                $invoice_lines = $upcoming_invoice->lines->data;
                foreach ($invoice_lines as $line) {
                  if($line->amount < 0) {
                    $refund_amount -= $line->amount;
                  }
                }
              } catch (\Stripe\Exception\ApiErrorException $e) {
                error_log('Stripe Error: ' . $e->getMessage());
              }
            ?>
              <p class="subscription-note">
                <?php echo sprintf(ucfirst($lang->translate('subscription will be canceled at %s')), '<span class="point">' . date($lang->translate('F j G:i'), $prev_cancellation_time) . '</span>'); ?>
              </p>
              <?php if($refund_amount): ?>
                <p class="subscription-note">
                  <?php 
                  echo sprintf(ucfirst($lang->translate('we will refund you %s after the subscription is cancelled')), 
                               '<span class="point">' . sprintf($lang->translate('%syen'), number_format($refund_amount)) . '</span>'); 
                  ?>
                </p>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php else: // 現在プランも前回プランもない場合、プラン作成に誘導する ?>
      <div class="member-section-header">
        <h3><?php echo strtoupper($lang->translate('current plan')); ?></h3>
      </div>
      <p class="member-empty"><?php echo ucfirst($lang->translate('there is no subscription information')); ?></p>
      <p class="member-empty"><?php echo ucfirst($lang->translate('select a plan to start subscription now')); ?></p>
      <p class="button subcription-createlink">
        <a href="<?php echo LANG_DOMAIN; ?>/create-subscription/"><?php echo ucwords($lang->translate('select plan and start')); ?></a>
      </p>
  <?php
    endif; 
  endif;
  ?>
</section>