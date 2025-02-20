<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

// Stripe処理を有効化
require_once(get_template_directory() . '/inc/stripe-php-master/init.php');
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if(is_user_logged_in()):
  if(current_user_can('manager')):
    // ログイン中ユーザーのプランとサブスクリプション情報を取得
    $user_id = get_current_user_id();
    
    $plan_id = get_field('plan', 'user_' . $user_id);
    if($plan_id){
      $plan = get_post($plan_id);
    } else {
      $plan = null;
    }
    
    if($plan):
      $page_title = ucwords($lang->translate('cancel subscription'));
      $page_topic = strtoupper($page_title);
      $style_key = 'member';
      
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
          $current_period_end = $subscription->current_period_end;
        } catch (\Stripe\Exception\ApiErrorException $e) {
          $subscription = null;
          $subscription_status = 'undetected';
          error_log('Stripe Error: ' . $e->getMessage());
        }
      } else {
        $subscription = null;
        $subscription_status = 'unfound';
      }
      
      // サブスクリプション作成Nonceを作成
      $cancel_subscription_nonce = wp_create_nonce('cancel_subscription');
      
      get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="member-section">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('current plan')); ?></h3>
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
        <div class="subscription-price">
          <div class="subscription-price-item">
            <p class="subscription-price-title"><?php echo ucwords($lang->translate('amount price')); ?></p>
            <p class="subscription-price-price">
              <?php 
              if(intval($plan_period) == 1) {
                $cycle_period = $lang->translate($plan_unit);
              } else {
                $cycle_period = sprintf($lang->translate('%s' . $plan_unit . 's'), $plan_period);
              } 
              ?>
              <?php echo sprintf($lang->translate('%syen'), '<span class="price">' . number_format($plan_price) . '</span>'); ?>/<?php echo $cycle_period; ?>
            </p>
          </div>
        </div>
        
        <?php if($subscription_status == 'active'): ?>
          <p class="subscription-note">
            <?php echo sprintf(ucfirst($lang->translate('next payment will be at %s')), '<span class="point">' . date($lang->translate('F j G:i'), $current_period_end) . '</span>'); ?>
          </p>
        <?php elseif($subscription_status == 'trialing'): ?>
          <p class="subscription-note">
            <?php echo sprintf(ucfirst($lang->translate('subscription will stop trialing and claim at %s')), '<span class="point">' . date($lang->translate('F j G:i'), $current_period_end) . '</span>'); ?>
          </p>
        <?php endif; ?>
      </div>
  </div>
</section>

<?php if(in_array($subscription_status, ['active', 'trialing'])): ?>
  
  <input type="hidden" id="hidden-plan-price" value="<?php echo $plan_price; ?>" />
  <input type="hidden" id="hidden-current-period-start" value="<?php echo date('Y-m-d H:i:s', $subscription->current_period_start); ?>" />
  <input type="hidden" id="hidden-current-period-end" value="<?php echo date('Y-m-d  H:i:s', $current_period_end); ?>" />

  <section class="member-section">
    <div class="member-section-header">
      <h3><?php echo strtoupper($lang->translate('cancel subscription confirmation')); ?></h3>
    </div>
    <form class="form" id="cancel-subscription-form">
    
      <div class="cancel-subscription-message">
        <p><?php echo ucfirst($lang->translate('you will be unable to use this service if you cancel the subscription')); ?></p>
        <p><?php echo ucfirst($lang->translate('are you sure to cancel this subscription')); ?></p>
      </div>
      
      <div class="form-line">
        <div class="form-input">
          <label class="radio">
            <input class="cancel-subscription-type-radio" type="radio" name="cancel_type" value="none" />
            <?php echo ucfirst($lang->translate('i want to continue the subscription')); ?>
          </label>
        </div>
      </div>
      
      <div class="form-line">
        <div class="form-input">
          <label class="radio">
            <input class="cancel-subscription-type-radio" type="radio" name="cancel_type" value="end" />
            <?php echo ucfirst($lang->translate('i want to cancel the subscription before next payment')); ?>
          </label>
        </div>
      </div>
      
      <?php if(date('Y-m-d', time()) != date('Y-m-d', $current_period_end)): ?>
        <div class="form-line">
          <div class="form-input">
            <label class="radio">
              <input class="cancel-subscription-type-radio" type="radio" name="cancel_type" value="date" />
              <?php echo ucfirst($lang->translate('i want to cancel the subscription at the end of a specified date')); ?>
            </label>
          </div>
        </div>
        <div class="cancel-subscription-datearea">
          <div class="form-line">
            <div class="form-title"><?php echo ucwords($lang->translate('cancellation date')); ?></div>
            <div class="form-input">
              <input type="text" class="cancel-subscription-datepicker" name="cancel_date"
                     placeholder="<?php echo ucwords($lang->translate('year')) . ' / ' . ucwords($lang->translate('month')) . ' / ' . ucwords($lang->translate('day'));?>" />
              <p class="warning warning-cancel_date"></p>
            </div>
          </div>
        </div>
      <?php endif;?>
      
      <div class="form-line">
        <div class="form-input">
          <label class="radio">
            <input class="cancel-subscription-type-radio" type="radio" name="cancel_type" value="now" />
            <?php echo ucfirst($lang->translate('i want to cancel the subscription now')); ?>
          </label>
        </div>
      </div>
      
      <p class="warning warning-cancel_type"></p>
      
      <?php if($subscription_status == 'active'): ?>
        <div class="cancel-subscription-refund">
          <div class="cancel-subscription-refund-title"><?php echo ucwords($lang->translate('refund amount')); ?></div>
          <div class="cancel-subscription-refund-value">&yen;&nbsp;<span class="cancel-subscription-refund-price">0</span></div>
        </div>
      <?php endif; ?>
      
      <div class="form-btnarea">
        <p class="button shine-active">
          <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/member/">
            <?php echo ucwords($lang->translate('cancel')); ?>
          </a>
        </p>
        <p class="button shine-active" id="cancel-subscription-submit"><span><?php echo ucwords($lang->translate('confirm')); ?></span></p>
      </div>
      <p class="warning center warning-system"></p>
      <p class="warning center" id="cancel-subscription-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
      <input type="hidden" name="plan" value="<?php echo $plan_code; ?>" />
      <input type="hidden" name="cancel_subscription_nonce" value="<?php echo esc_attr($cancel_subscription_nonce); ?>" />
    </form>
  </section>
  
<?php else: ?>

  <section class="member-section">
    <div class="member-section-header">
      <h3><?php echo $lang->translate('cancel subscription method'); ?></h3>
    </div>
    <div class="cancel-subscription-apologize">
      <p><?php echo $lang->translate('there is an error with your subscription information'); ?></p>
      <p><?php echo $lang->translate('inform us with the current situation on the contact page please'); ?></p>
      <p><?php echo $lang->translate('we will invstigate and resolve the problem as soon as possible'); ?></p>
      <p><?php echo $lang->translate('we apologize for any inconvenience caused'); ?></p>
      <p><a href="<?php echo LANG_DOMAIN; ?>/contact/"><?php echo $lang->translate('redirect to contact'); ?></a></p>
    </div>
  </section>
  
<?php endif;?>

<?php 
      get_footer();
    else:
      // プロファイルページがない場合、権限エラーテンプレートに切り替え
      include(locate_template('404.php'));
    endif;
  else:
    // プロファイルページがない場合、権限エラーテンプレートに切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
