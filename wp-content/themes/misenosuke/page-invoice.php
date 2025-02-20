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
    if(array_key_exists('invoice_id', $_GET)):
      // ログイン時ユーザーID取得
      $user = wp_get_current_user();
      $user_id = $user->ID;
      
      // 顧客ID取得
      $stripe_customer_id = get_field('stripe_customer_id', 'user_' . $user_id);
      
      if($_GET['invoice_id'] == 'upcoming') {
        // 次の請求を取得
        try {
          $invoice = \Stripe\Invoice::upcoming([
            'customer' => $stripe_customer_id,
          ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
          $invoice = null;
          error_log('Stripe Error: ' . $e->getMessage());
        }
      } else {
        // 請求ID取得
        $stripe_invoice_id = 'in_' . $_GET['invoice_id'];
        
        // 指定請求を取得
        try {
          $invoice = \Stripe\Invoice::retrieve($stripe_invoice_id, [
            'customer' => $stripe_customer_id,
          ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
          $invoice = null;
          error_log('Stripe Error: ' . $e->getMessage());
        }
      }
      
      if($invoice && $invoice->total > 0):
        $page_title = ucwords($lang->translate('invoice document'));
        $page_topic = strtoupper($page_title);
        $style_key = 'member';
        
        get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="invoice-controller-section">
  <p class="button invoice-download"><span><?php echo ucfirst($lang->translate('export PDF')); ?></span></p>
</section>

<section class="invoice-preview-section">
  <div class="invoice-preview-inner">
    <div class="invoice-preview-area" id="invoice-preview">
      <h3>請求書</h3>
      <div class="invoice-top">
        <table class="invoice-top-table">
          <tr>
            <th>請求日</th>
            <td><?php echo date('Y年n月j日', $invoice->created); ?></td>
          </tr>
          <?php if($invoice->number): ?>
          <tr>
            <th>請求番号</th>
            <td><?php echo $invoice->number; ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
      <div class="invoice-info">
        <div class="invoice-info-left">
          <div class="invoice-target">
            <h4 class="invoice-target-title"><?php echo get_field('restaurant_name', 'user_' . $user_id); ?> 御中</h4>
            <p class="invoice-target-text">&#12306;<?php echo get_field('zipcode', 'user_' . $user_id); ?></p>
            <p class="invoice-target-text"><?php echo get_field('address', 'user_' . $user_id); ?></p>
          </div>
          <div class="invoice-total">
            <p class="invoice-total-text">下記の通り、ご請求申し上げます。</p>
            <p class="invoice-total-topic">ご請求金額（税込）</p>
            <p class="invoice-total-price">&yen;&nbsp;<?php echo number_format($invoice->total); ?></p>
          </div>
        </div>
        <div class="invoice-info-right">
          <div class="invoice-company">
            <h4 class="invoice-company-title">個人事業主 DREAL</h4>
            <p class="invoice-company-text">&#12306;221-0801</p>
            <p class="invoice-company-text">住所：　神奈川県横浜市神奈川区<br/>　　　　神大寺2丁目5番3-507号</p>
            <p class="invoice-company-text">電話：　080-3345-0158</p>
            <p class="invoice-company-text">メール：dreal.linyang@gmail.com</p>
            <p class="invoice-company-stamp"></p>
          </div>
          <div class="invoice-bank">
            <table class="invoice-bank-table">
              <tr>
                <th>振込先</th>
                <td>みずほ銀行 相模大野支店 <br/>普通口座 4107699 楊林</td>
              </tr>
              <tr>
                <th>振込期日</th>
                <td><?php echo date('Y年n月t日', $invoice->created); ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <table class="invoice-table">
        <tr>
          <th>対象期間</th>
          <th>内容</th>
          <th>数量</th>
          <th>単位</th>
          <th>単価（税抜）</th>
          <th>税率</th>
          <th>金額（税抜）</th>
        </tr>
        <?php 
        $line_counter = 0;
        foreach($invoice->lines->data as $line): 
          $line_counter++;
          if(count($line->tax_rates)) {
            $tax_rate_text = $line->tax_rates[0]->effective_percentage . '%';
          } else {
            $tax_rate_text = '-';
          }
        ?>
        <tr>
          <td class="invoice-table-date"><?php echo date($lang->translate('Y/m/d'), $line->period->start) . '~' . ($line->type == 'subscription' ? date($lang->translate('Y/m/d'), $line->period->end) : ''); ?></td>
          <td class="invoice-table-content"><?php echo get_invoice_detail_description_display($line, 'ja'); ?></td>
          <td class="invoice-table-quantity">1</td>
          <td class="invoice-table-unit">式</td>
          <td class="invoice-table-price">&yen;&nbsp;<?php echo number_format(intval($line->unit_amount_excluding_tax)); ?></td>
          <td class="invoice-table-tax"><?php echo $tax_rate_text; ?></td>
          <td class="invoice-table-subtotal">&yen;&nbsp;<?php echo number_format(intval($line->amount_excluding_tax)); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php for(; $line_counter < 15; $line_counter++): ?>
        <tr>
          <td>&nbsp;</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <?php endfor; ?>
      </table>
      <div class="invoice-bottom">
        <div class="invoice-price">
          <table class="invoice-price-table">
            <tr>
              <th>小計</th>
              <td>&yen;&nbsp;<?php echo number_format($invoice->total_excluding_tax); ?></td>
            </tr>
            <tr>
              <th>消費税</th>
              <td>&yen;&nbsp;<?php echo number_format($invoice->tax); ?></td>
            </tr>
            <tr>
              <th>合計</th>
              <td>&yen;&nbsp;<?php echo number_format($invoice->total); ?></td>
            </tr>
          </table>
        </div>
        <div class="invoice-note">
          <p class="invoice-note-title">備考</p>
          <p class="invoice-note-text"></p>
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
