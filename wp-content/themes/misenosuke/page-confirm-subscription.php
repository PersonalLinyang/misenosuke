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

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    if(array_key_exists('plan_code', $_POST)):
      // プラン情報取得
      $plan_code = $_POST['plan_code'];
      $plan = get_page_by_path($plan_code, OBJECT, "plan");
      
      if($plan):
        // 正常利用
        // TDK設定
        $page_title = ucwords($lang->translate('subscription confirmation'));
        $page_topic = strtoupper($page_title);
        $style_key = 'member';
        
        // ログイン中ユーザーを取得
        $user_id = get_current_user_id();
        
        // サブスクリプション作成Nonceを作成
        $create_subscription_nonce = wp_create_nonce('create_subscription');
        
        get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<?php 
/**
 * 現在利用中のプランとサブスクリプション情報を表示
 */
$current_plan_id = get_field('plan', 'user_' . $user_id);
if($current_plan_id){
  $current_plan = get_post($current_plan_id);
} else {
  $current_plan = null;
}

if($current_plan):
  $current_plan_code = $current_plan->post_name;
  $current_plan_image = get_field('image', $current_plan_id);
  $current_plan_price = intval(get_field('price', $current_plan_id));
  $current_plan_period = intval(get_field('period', $current_plan_id));
  $current_plan_unit = get_field('unit', $current_plan_id);
  
  $current_subscription_id = get_field('stripe_subscription_id', 'user_' . $user_id);
  if($current_subscription_id){
    try {
      $current_subscription = \Stripe\Subscription::retrieve($current_subscription_id);
      $current_subscription_status = $current_subscription->status;
    } catch (\Stripe\Exception\ApiErrorException $e) {
      $current_subscription = null;
      $current_subscription_status = 'undetected';
      error_log('Stripe Error: ' . $e->getMessage());
    }
  } else {
    $current_subscription = null;
    $current_subscription_status = 'unfound';
  }
?>
  <section class="member-section">
    <div class="member-section-header">
      <h3><?php echo strtoupper($lang->translate('current plan')); ?></h3>
    </div>
    <div class="subscription">
      <div class="subscription-image">
        <?php if($current_plan_image):?>
          <img src="<?php echo $current_plan_image; ?>" />
        <?php else: ?>
          <p class="subscription-image-text"><?php echo strtoupper($lang->translate('image will come soon')); ?></p>
        <?php endif; ?>
      </div>
      <div class="subscription-info">
        <div class="subscription-header">
          <p class="subscription-name"><?php echo get_the_title($current_plan_id); ?></p>
          <p class="subscription-status <?php echo $current_subscription_status; ?>"><?php echo strtoupper($lang->translate($current_subscription_status)); ?></p>
        </div>
        <div class="subscription-body">
          <div class="subscription-price">
            <div class="subscription-price-item">
              <p class="subscription-price-title"><?php echo ucwords($lang->translate('amount price')); ?></p>
              <p class="subscription-price-price">
                <?php 
                if(intval($current_plan_period) == 1) {
                  $current_cycle_period = $lang->translate($current_plan_unit);
                } else {
                  $current_cycle_period = sprintf($lang->translate('%s' . $current_plan_unit . 's'), $current_plan_period);
                } 
                echo sprintf($lang->translate('%syen'), '<span class="price">' . number_format($current_plan_price) . '</span>') . ' / ' . $current_cycle_period; 
                ?>
              </p>
            </div>
          </div>
          
          <?php 
          if($current_subscription_status == 'active'): 
            $current_period_start = $current_subscription->current_period_start;
            $current_period_end = $current_subscription->current_period_end;
            
            $remaining_period = $current_period_end - time();
            $total_period = $current_period_end - $current_period_start;
            $refund_amount = floor($current_plan_price * $remaining_period / $total_period);
          ?>
            <p class="subscription-note">
              <?php echo sprintf(ucfirst($lang->translate('next payment will be at %s')), '<span class="point">' . date($lang->translate('F j G:i'), $current_period_end) . '</span>'); ?>
            </p>
            <p class="subscription-note">
              <?php echo sprintf(ucfirst($lang->translate('we will refund you %s')), '<span class="point">' . sprintf($lang->translate('%syen'), number_format($refund_amount)) . '</span>'); ?>
            </p>
          <?php elseif($current_subscription_status == 'trialing'): ?>
            <p class="subscription-note">
              <?php echo sprintf($lang->translate('subscription will stop trialing and claim at %s'), '<span class="point">' . date($lang->translate('F j G:i'), $current_period_end) . '</span>'); ?>
            </p>
          <?php endif; ?>
        </div>
    </div>
  </section>
<?php endif; ?>

<?php  
/**
 * サブスクリプション対象プラン情報を表示
 */
$plan_id = $plan->ID;
$plan_image = get_field('image', $plan_id);
$plan_setup_fee = intval(get_field('setup_fee', $plan_id));
$plan_price = intval(get_field('price', $plan_id));
$plan_period = intval(get_field('period', $plan_id));
$plan_unit = get_field('unit', $plan_id);
$plan_free_period = intval(get_field('free_period', $plan_id));
?>
<section class="member-section">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('selected plan')); ?></h3>
  </div>
  <div class="subscription">
    <p class="warning center warning-plan"></p>
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
      </div>
      <div class="subscription-body">
        <div class="subscription-price">
          <?php if($plan_setup_fee): ?>
          <div class="subscription-price-item">
            <p class="subscription-price-title"><?php echo ucwords($lang->translate('initial price')); ?></p>
            <p class="subscription-price-price">
              <?php echo sprintf($lang->translate('%syen'), '<span class="price">' . number_format($plan_setup_fee) . '</span>'); ?>
            </p>
          </div>
          <?php endif; ?>
          <div class="subscription-price-item">
            <p class="subscription-price-title"><?php echo ucwords($lang->translate('amount price')); ?></p>
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
        </div>
        <?php 
        if($plan_free_period): 
          $trial_number = $plan_free_period * $plan_period;
          $trial_period = sprintf($lang->translate('%s' . $plan_unit . ($trial_number == 1 ? '' : 's')), strval($trial_number));
        ?>
          <p class="subscription-note"> <?php echo sprintf(ucfirst($lang->translate('you can try the plan free for %s')), '<span class="point">' . $trial_period . '</span>'); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php 
/**
 * クレジットカード情報を表示
 */
$stripe_payment_method_id = get_field('stripe_payment_method_id', 'user_' . $user_id);
try {
  $payment_method = \Stripe\PaymentMethod::retrieve($stripe_payment_method_id);
} catch (Exception $e) {
  $payment_method = null;
}
?>
<section class="member-section">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('payment method')); ?></h3>
    <?php if(!$payment_method): ?>
    <div class="member-section-controller">
      <p class="member-section-button">
        <a target="_blank" href="<?php echo LANG_DOMAIN; ?>/register-card/"><?php echo ucwords($lang->translate('register card')); ?></a>
      </p>
    </div>
    <?php endif; ?>
  </div>
  
  <?php 
  if($payment_method): 
    get_template_part('template-parts/member/credit-card', null, array('card' => $payment_method->card));
  else:
  ?>
    <p class="member-empty"><?php echo ucfirst($lang->translate('you have no payment method, please register a card first and refresh this page')); ?></p>
  <?php endif;?>
  
  <form class="form" id="confirm-subscription-form">
    <div class="form-block">
      <div class="form-line">
        <div class="form-input center">
          <label class="checkbox checkbox-center">
            <input type="checkbox" name="agreement" id="confirm-subscription-agreement" />
            <?php 
            echo sprintf(ucfirst($lang->translate('sure to use this plan and agree with %s')), 
                '<a href="' . LANG_DOMAIN . '/terms/" class="underline blue" target="_blank">' . $lang->translate('terms of service') . '</a>'); 
            ?>
          </label>
          <p class="warning warning-agreement"></p>
        </div>
      </div>
    </div>
    
    <div class="form-btnarea">
      <p class="button shine-active"><a class="full-link" href="<?php echo LANG_DOMAIN; ?>/create-subscription/"><?php echo ucwords($lang->translate('reselect plan')); ?></a></p>
      <p class="button shine-active" id="confirm-subscription-submit"><span><?php echo ucwords($lang->translate('begin to use')); ?></span></p>
    </div>
    <p class="warning center warning-system"></p>
    <p class="warning center" id="confirm-subscription-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    <input type="hidden" name="plan" value="<?php echo $plan_code; ?>" />
    <input type="hidden" name="create_subscription_nonce" value="<?php echo esc_attr($create_subscription_nonce); ?>" />
  
  </form>
  
</section>

<?php
        get_footer();
      else:
        // パラメータのplan_codeでプランを取得でない場合、404エラーテンプレートに切り替え
        include(locate_template('500.php'));
      endif;
    else:
      // プラン未選択の際プラン選択ページに遷移
      header('Location: ' . LANG_DOMAIN . '/create_subscription/');
      exit;
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
