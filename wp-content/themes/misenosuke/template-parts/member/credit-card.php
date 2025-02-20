<?php 
/**
 * クレジットカード情報表示
 */

// 翻訳有効化
$lang = new LanguageSupporter();

// カード情報をパラメータで受け取り
$card = $args['card'];

// カード有効期限情報を取得
$expiration_class = '';
$year = intval(date('Y'));
$month = intval(date('m'));
if($card->exp_year < $year || ($card->exp_year == $year && $card->exp_month <= $month)) {
  $expiration_class = 'error';
}

?>

<div class="creditcard">
  <p class="creditcard-icon <?php echo $card->display_brand; ?>"></p>
  <div class="creditcard-info">
    <p class="creditcard-number" style="display: flex; align-items: center;">
      <span class="creditcard-number-dot" style="font-size: 0.5em;">&#9679;&#9679;&#9679;&#9679;&nbsp;</span>
      <span class="creditcard-number-dot" style="font-size: 0.5em;">&#9679;&#9679;&#9679;&#9679;&nbsp;</span>
      <span class="creditcard-number-dot" style="font-size: 0.5em;">&#9679;&#9679;&#9679;&#9679;&nbsp;</span>
      <?php echo $card->last4; ?>
    </p>
    <p class="creditcard-expiration <?php echo $expiration_class; ?>">
      <?php echo ucwords($lang->translate('card expiration')); ?> : <?php echo str_pad($card->exp_month, 2, "0", STR_PAD_LEFT); ?> / <?php echo substr($card->exp_year, -2); ?>
    </p>
  </div>
</div>