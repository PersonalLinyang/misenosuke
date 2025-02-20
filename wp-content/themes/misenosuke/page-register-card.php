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
    // 正常表示
    $page_title = ucwords($lang->translate('card registration'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    $user_id = get_current_user_id();
    
    $stripe_payment_method_id = get_field('stripe_payment_method_id', 'user_' . $user_id);
    try {
      $payment_method = \Stripe\PaymentMethod::retrieve($stripe_payment_method_id);
    } catch (Exception $e) {
      $payment_method = null;
    }
    
    // カード登録Nonceを作成
    $register_card_nonce = wp_create_nonce('register_card');
    
    get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<?php 
if($payment_method): 
  $card = $payment_method->card;
  
  $expiration_class = '';
  $year = intval(date('Y'));
  $month = intval(date('m'));
  if($card->exp_year < $year || ($card->exp_year == $year && $card->exp_month <= $month)) {
    $expiration_class = 'error';
  }
?>
  <section class="member-section register-card-section" id="register-card-currentpayment">
    <div class="member-section-header">
      <h3><?php echo strtoupper($lang->translate('current card')); ?></h3>
    </div>
    <p><?php echo ucfirst($lang->translate('if register a new card, you will be unable to use this card')); ?></p>
    <?php get_template_part('template-parts/member/credit-card', null, array('card' => $payment_method->card)); ?>
  </section>
<?php endif; ?>

<section class="member-section register-card-section" id="register-card-payment">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('new card information')); ?></h3>
  </div>
  <form class="form" id="register-card-form">
    <div class="form-block">
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('card number')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_number" class="input"></div>
          <p class="warning warning-card_number"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('card expiration')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_expiry" class="input"></div>
          <p class="warning warning-card_expiry"></p>
        </div>
      </div>
      <div class="form-line">
        <p class="form-title"><?php echo ucwords($lang->translate('security code')); ?><span class="required">*</span></p>
        <div class="form-input">
          <div id="creditcard-card_cvc" class="input"></div>
          <p class="warning warning-card_cvc"></p>
        </div>
      </div>
      <input id="creaditcard-input" type="hidden" name="token_id" />
      <p class="warning center warning-card_info"></p>
    </div>
    
    <div class="form-block">
      <div class="form-line">
        <div class="form-input center">
          <label class="checkbox checkbox-center">
            <input type="checkbox" name="agreement" id="register-card-agreement" />
            <?php echo ucfirst($lang->translate('sure to regist the new card')); ?>
          </label>
          <p class="warning warning-agreement"></p>
        </div>
      </div>
    </div>
    
    <div class="form-btnarea">
      <p class="button shine-active"><a class="full-link" href="<?php echo LANG_DOMAIN; ?>/member/"><?php echo ucwords($lang->translate('back')); ?></a></p>
      <p class="button shine-active" id="register-card-submit"><span><?php echo ucwords($lang->translate('register')); ?></span></p>
    </div>
    <p class="warning center" id="register-card-warning-system"><?php echo ucfirst($lang->translate('system error')); ?></p>
    <input type="hidden" name="refer" value="<?php echo array_key_exists('refer', $_GET) ? $_GET['refer'] : ''; ?>" />
    <input type="hidden" name="register_card_nonce" value="<?php echo esc_attr($register_card_nonce); ?>" />
  </form>
  
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
