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
    if(array_key_exists('reciept_id', $_GET)):
      // ログイン時ユーザーID取得
      $user = wp_get_current_user();
      $user_id = $user->ID;
      
      // 顧客ID取得
      $stripe_customer_id = get_field('stripe_customer_id', 'user_' . $user_id);
      
      // 請求ID取得
      $stripe_invoice_id = 'in_' . $_GET['reciept_id'];
      
      // 指定請求を取得
      try {
        $invoice = \Stripe\Invoice::retrieve($stripe_invoice_id, [
          'customer' => $stripe_customer_id,
        ]);
      } catch (\Stripe\Exception\ApiErrorException $e) {
        $invoice = null;
        error_log('Stripe Error: ' . $e->getMessage());
      }
      
      if($invoice && $invoice->total > 0):
        $page_title = ucwords($lang->translate('reciept document'));
        $page_topic = strtoupper($page_title);
        $style_key = 'member';
        
        get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="reciept-controller-section">
  <p class="button reciept-download"><span><?php echo ucfirst($lang->translate('export PDF')); ?></span></p>
</section>

<section class="reciept-preview-section">
  <div class="reciept-preview-inner">
    <div class="reciept-preview-area" id="reciept-preview">
      <div class="reciept-header">
        <p class="reciept-number">No. <?php echo $invoice->number;?></p>
        <p class="reciept-date"><?php echo date('Y年n月j日', $invoice->created); ?></p>
      </div>
      <h3>領収書</h3>
      <p class="reciept-to"><?php echo get_field('restaurant_name', 'user_' . $user_id); ?> 御中</p>
      <p class="reciept-total">&yen;&nbsp;<?php echo number_format($invoice->total); ?></p>
      <p class="reciept-text">但し<br/>上記正に領収いたしました。</p>
      <div class="reciept-footer">
        <div class="reciept-detail">
          <table class="reciept-detail-table">
            <tr>
              <th colspan="2">内訳</th>
            </tr>
            <tr>
              <th>税別金額</th>
              <td>&yen;&nbsp;<?php echo number_format($invoice->total_excluding_tax); ?></td>
            </tr>
            <tr>
              <th>消費税額</th>
              <td>&yen;&nbsp;<?php echo number_format($invoice->tax); ?></td>
            </tr>
          </table>
        </div>
        <div class="reciept-company">
          <p class="reciept-company-name">個人事業主 DREAL</p>
          <p class="reciept-company-text">&#12306;221-0801</p>
          <p class="reciept-company-text">神奈川県横浜市神奈川区<br/>神大寺2丁目5番3-507号</p>
          <p class="reciept-company-tel">TEL：080-3345-0158</p>
          <p class="reciept-company-stamp"></p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
        get_footer();
      else:
        // 請求が存在しない或いは返金の場合、ページエラーテンプレートを切り替え
        include(locate_template('404.php'));
      endif;
    else:
      // 請求番号指定がない場合、ページエラーテンプレートを切り替え
      include(locate_template('404.php'));
    endif;
  else:
    // 管理係ではない場合、権限エラーテンプレートを切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
