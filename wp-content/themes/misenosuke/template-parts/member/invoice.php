<?php 
/**
 * 現在利用中のプランとサブスクリプション情報を表示
 */

global $wpdb;

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();

// 再帰関数で一年以内の全請求を取得
$stripe_customer_id = get_field('stripe_customer_id', 'user_' . $user_id);
$invoices = get_stripe_invoices_lastyear($stripe_customer_id);

// 未来の請求を取得し全請求に追加
try {
  $upcoming_invoice = \Stripe\Invoice::upcoming([
    'customer' => $stripe_customer_id,
  ]);
  array_unshift($invoices, $upcoming_invoice);
} catch (\Stripe\Exception\ApiErrorException $e) {
  error_log('Stripe Error: ' . $e->getMessage());
}

// Stripe商品IDとプランIDの連想配列を作成
$plan_product_results = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", 'stripe_product_id'));
$product_plan_list = array();
foreach($plan_product_results as $plan_product_row) {
    $product_plan_list[$plan_product_row->meta_value] = $plan_product_row->post_id;
}

?>

<section class="member-section" id="member-invoice">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('invoice and refund')); ?></h3>
  </div>
  
  <?php if(count($invoices)): ?>
    <p><?php echo ucfirst($lang->translate('only invoices and refunds created from 1 year and 2 months ago will be on hold')); ?></p>
    <div class="member-invoice-area">
      <table class="member-invoice-table">
        <tr class="member-invoice-header">
          <th class="member-invoice-status"><?php echo ucwords($lang->translate('status')); ?></th>
          <th class="wrap member-invoice-plan"><?php echo ucwords($lang->translate('plan')); ?></th>
          <th class="member-invoice-reason"><?php echo ucwords($lang->translate('reason')); ?></th>
          <th class="wrap member-invoice-peroid"><?php echo ucwords($lang->translate('invoice peroid')); ?></th>
          <th class="member-invoice-total"><?php echo ucwords($lang->translate('total price ')); ?></th>
          <th class="member-invoice-action"><?php echo ucwords($lang->translate('invoice detail')); ?></th>
          <th class="member-invoice-action"><?php echo ucwords($lang->translate('invoice document')); ?></th>
          <th class="member-invoice-action"><?php echo ucwords($lang->translate('reciept document')); ?></th>
        </tr>
        <?php 
        $row_counter = 0;
        foreach($invoices as $invoice): 
          if($invoice->subtotal != 0):
            $row_counter++;
            $invoice_id = str_replace('in_', '', $invoice->id);
            
            // 請求の商品IDを取得
            $stripe_product_id = null; 
            $period_start = null; 
            $period_end = null; 
            if(count($invoice->lines->data)) {
              $stripe_product_id = $invoice->lines->data[0]->price->product;
              $period_start = $invoice->lines->data[0]->period->start;
              $period_end = $invoice->lines->data[0]->period->end;
            }
        ?>
          <tr>
            <td class="member-invoice-status"><p class="status <?php echo $invoice->status; ?>"><?php echo strtoupper(get_invoice_status_display($invoice)); ?></p></td>
            <td class="wrap member-invoice-plan">
              <?php echo array_key_exists($stripe_product_id, $product_plan_list) ? get_the_title($product_plan_list[$stripe_product_id]) : ucwords($lang->translate('unknown')); ?>
            </td>
            <td class="member-invoice-reason"><?php echo ucwords(get_invoice_reason_display($invoice)); ?></td>
            <td class="wrap member-invoice-peroid">
              <?php echo date('m/d', $period_start); ?> ~ <?php echo date('m/d', $period_end); ?>
            </td>
            <td class="member-invoice-total">&yen;&nbsp;<?php echo number_format(abs($invoice->total)); ?></td>
            <td class="member-invoice-action">
              <p class="member-invoice-button member-invoice-button-detail" data-row="<?php echo $row_counter; ?>"><span><?php echo ucwords($lang->translate('invoice detail')); ?></span></p>
            </td>
            <td class="member-invoice-action">
              <?php if($invoice->id && $invoice->total > 0): ?>
                <p class="member-invoice-button">
                  <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/invoice/?invoice_id=<?php echo $invoice_id; ?>">
                    <?php echo ucwords($lang->translate('invoice document')); ?>
                  </a>
                </p>
              <?php elseif($invoice->billing_reason == 'upcoming' && $invoice->total > 0): ?>
                <p class="member-invoice-button">
                  <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/invoice/?invoice_id=upcoming">
                    <?php echo ucwords($lang->translate('invoice document')); ?>
                  </a>
                </p>
              <?php endif; ?>
            </td>
            <td class="member-invoice-action">
              <?php if($invoice->id && $invoice->status == 'paid' && $invoice->total > 0): ?>
                <p class="member-invoice-button">
                  <a class="full-link" href="<?php echo LANG_DOMAIN; ?>/reciept/?reciept_id=<?php echo $invoice_id; ?>">
                    <?php echo ucwords($lang->translate('reciept document')); ?>
                  </a>
                </p>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td colspan="8" class="detail">
              <div class="member-invoice-detail" id="invoice-detail-<?php echo $row_counter; ?>">
                <table class="member-invoice-detail-table">
                  <tr class="member-invoice-detail-header">
                    <th class="member-invoice-detail-description"><?php echo ucwords($lang->translate('description')); ?></th>
                    <th class="member-invoice-detail-quantity"><?php echo ucwords($lang->translate('quantity')); ?></th>
                    <th class="member-invoice-detail-unit"><?php echo ucwords($lang->translate('unit')); ?></th>
                    <th class="member-invoice-detail-amount"><?php echo ucwords($lang->translate('unit price')); ?></th>
                    <th class="member-invoice-detail-subtotal"><?php echo ucwords($lang->translate('subtotal price')); ?></th>
                  </tr>
                  <?php foreach($invoice->lines->data as $line): ?>
                    <tr>
                      <td class="wrap member-invoice-detail-description"><?php echo ucfirst(get_invoice_detail_description_display($line)); ?></td>
                      <td class="member-invoice-detail-quantity"><?php echo get_invoice_detail_quantity_display($line); ?></td>
                      <td class="member-invoice-detail-unit"><?php echo ucwords($lang->translate('unit set')); ?></td>
                      <td class="member-invoice-detail-amount">&yen;&nbsp;<?php echo number_format($line->price->unit_amount); ?></td>
                      <td class="member-invoice-detail-subtotal">&yen;&nbsp;<?php echo number_format(abs($line->amount)); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </table>
              </div>
            </td>
          </tr>
        <?php 
          endif;
        endforeach; 
        ?>
      </table>
    </div>
  <?php else: ?>
    <p class="member-empty"><?php echo ucfirst($lang->translate('there is no invoice information')); ?></p>
  <?php endif; ?>

</section>