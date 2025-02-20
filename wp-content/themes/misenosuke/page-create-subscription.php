<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $page_title = ucwords($lang->translate('plan selection'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    // 全プラン情報を取得
    $plans = get_posts(array(
      'post_type' => 'plan',
      'post_status' => 'publish',
      'orderby' => 'meta_value',
      'order' => 'ASC',
      'meta_key' => 'priority',
      'posts_per_page' => -1,
    ));
    
    // 利用中プランIDを取得
    $user_id = get_current_user_id();
    $current_plan_id = get_field('plan', 'user_' . $user_id);
    
    get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="member-section">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('select the plan you want please')); ?></h3>
  </div>
  
  <form class="form" id="create-subscription-form" method="post" action="<?php echo LANG_DOMAIN; ?>/confirm-subscription/">
  
    <ul class="create-subscription-list">
      <?php 
      foreach($plans as $plan): 
        $plan_id = $plan->ID;
        $plan_image = get_field('image', $plan_id);
        $plan_setup_fee = intval(get_field('setup_fee', $plan_id));
        $plan_price = intval(get_field('price', $plan_id));
        $plan_period = intval(get_field('period', $plan_id));
        $plan_unit = get_field('unit', $plan_id);
        $plan_free_period = intval(get_field('free_period', $plan_id));
      ?>
        <li class="create-subscription-item">
          <div class="create-subscription-image">
            <?php 
              if($plan_image):
            ?>
              <img src="<?php echo $plan_image; ?>" />
            <?php else: ?>
              <p class="create-subscription-image-text"><?php echo strtoupper($lang->translate('image will come soon')); ?></p>
            <?php endif; ?>
          </div>
          <div class="create-subscription-info">
            <div class="create-subscription-header">
              <p class="create-subscription-name"><?php echo get_the_title($plan_id); ?></p>
            </div>
            <div class="create-subscription-body">
              <div class="create-subscription-description">
                <?php echo nl2br($plan->post_content); ?>
              </div>
              <div class="create-subscription-price">
                <?php if($plan_setup_fee): ?>
                <div class="create-subscription-price-item">
                  <p class="create-subscription-price-title"><?php echo ucwords($lang->translate('initial price')); ?></p>
                  <p class="create-subscription-price-price">
                    <?php echo sprintf($lang->translate('%syen'), '<span class="price">' . number_format($plan_setup_fee) . '</span>'); ?>
                  </p>
                </div>
                <?php endif; ?>
                <div class="create-subscription-price-item">
                  <p class="create-subscription-price-title"><?php echo ucwords($lang->translate('amount price')); ?></p>
                  <p class="create-subscription-price-price">
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
                <p class="create-subscription-trial">※ <?php echo sprintf(ucfirst($lang->translate('you can try the plan free for %s')), $trial_period); ?></p>
              <?php endif; ?>
            </div>
            <div class="create-subscription-footer">
              <?php if($plan_id == $current_plan_id): ?>
                <p class="create-subscription-current"><?php echo ucwords($lang->translate('using this plan')); ?></p>
              <?php else: ?>
                <p>
                  <label class="radio radio-center">
                    <input class="create-subscription-plan-radio" type="radio" name="plan_code" value="<?php echo $plan->post_name; ?>" />
                    <?php echo ucfirst($lang->translate('want this plan')); ?>
                  </label>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="warning center" id="create-subscription-warning-select"><?php echo ucfirst($lang->translate('select the plan you want please')); ?></p>
    
    <div class="form-btnarea">
      <p class="button shine-active"><a class="full-link" href="<?php echo LANG_DOMAIN; ?>/member/"><?php echo ucwords($lang->translate('back')); ?></a></p>
      <p class="button shine-active" id="create-subscription-submit"><span><?php echo ucwords($lang->translate('continue')); ?></span></p>
    </div>
    <p class="warning center" id="create-subscription-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    
  </form>

</section>

<?php
    get_footer();
  else:
    // プロファイルページがない場合、権限エラーテンプレートを切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
