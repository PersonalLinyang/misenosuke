<?php

/*
 * 再帰関数で一年以内の全請求を取得
 *   $stripe_customer_id : Stripe顧客ID
 *   $all_invoices : 結果請求リスト
 *   $starting_after : 取得開始Object
 *   
 *   return : 請求リスト
 */
function get_stripe_invoices_lastyear($stripe_customer_id, $all_invoices = null, $starting_after = null) {
  // 基本パラメータ
  $params = [
    'customer' => $stripe_customer_id,
    'created' => array('gte' => strtotime('-1 year -2 month')),
    'limit' => 15,
  ];
  
  // 開始Object指定がある場合パラメータに追加
  if($starting_after) {
    $params['starting_after'] = $starting_after;
  }
  
  // 今回取得した請求
  $invoices = \Stripe\Invoice::all($params);
  
  if($all_invoices) {
    // 今まで取得できた場合今回の結果と合流
    $all_invoices = array_merge($all_invoices, $invoices->data);
  } else {
    // 初回取得の際今回の結果を入れる
    $all_invoices = $invoices->data;
  }
  
  if($invoices->has_more) {
    // まだ条件を満たすものがある場合、開始Objectを今回取得した最後の請求にして、自身を呼び出す
    $starting_after = end($invoices->data)->id;
    $all_invoices = get_stripe_invoices_lastyear($stripe_customer_id, $all_invoices, $starting_after);
  }
  
  // 取得の結果を返す
  return $all_invoices;
}


/*
 * 請求ステータス表示情報を取得
 *   $stripe_invoice : Stripe請求
 *   
 *   return : HTML文
 */
function get_invoice_status_display($stripe_invoice) {
  $lang = new LanguageSupporter();
  $invoice_status = $stripe_invoice->status;
  
  $invoice_status_text_list = array(
    'draft' => $lang->translate('invoice stauts waiting'),
    'deleted' => $lang->translate('invoice stauts deleted'),
    'open' => $lang->translate('invoice stauts waiting'),
    'paid' => $lang->translate('invoice stauts paid'),
    'uncollectible' => $lang->translate('invoice stauts error'),
    'void' => $lang->translate('invoice stauts void'),
  );
  
  if(array_key_exists($invoice_status, $invoice_status_text_list)) {
    return '<span class="' . $invoice_status . '">' . $invoice_status_text_list[$invoice_status] . '</span>';
  } else {
    return '<span class="unknown">' . $lang->translate('invoice stauts unknown') . '</span>';
  }
}


/*
 * 請求理由表示情報を取得
 *   $stripe_invoice : Stripe請求
 *   
 *   return : HTML文
 */
function get_invoice_reason_display($stripe_invoice) {
  $lang = new LanguageSupporter();
  $invoice_reason = $stripe_invoice->billing_reason;
  $invoice_total = $stripe_invoice->total;
  
  if($invoice_total < 0) {
    return $lang->translate('invoice refund');
  } else {
    if($invoice_reason == 'upcoming') {
      return $lang->translate('next subscription');
    } elseif($invoice_reason == 'subscription_create') {
      return $lang->translate('new subscription');
    } elseif($invoice_reason == 'subscription_cycle') {
      return $lang->translate('continue subscription');
    } elseif($invoice_reason == 'subscription_threshold') {
      return $lang->translate('adjust for overlimit');
    } elseif($invoice_reason == 'subscription_update') {
      return $lang->translate('change subsctiption');
    } else {
      return $lang->translate('unknown');
    }
  }
}


/*
 * 請求明細説明表示情報を取得
 *   $stripe_invoice_detail : Stripe請求明細
 *   
 *   return : HTML文
 */
function get_invoice_detail_description_display($stripe_invoice_detail, $language = '') {
  $lang = new LanguageSupporter();
  $detail_type = $stripe_invoice_detail->type;
  $detail_subtotal = $stripe_invoice_detail->amount;
  $detail_price = $stripe_invoice_detail->price->unit_amount;
  
  if($detail_subtotal > 0) {
    if($detail_type == 'subscription') {
      return $lang->translate('invoice to continue subscription', $language);
    } elseif($detail_type == 'invoiceitem') {
      return $lang->translate('invoice to start subscription', $language);
    } else {
      return $lang->translate('unknown invoice detail', $language);
    }
  } else {
    return sprintf($lang->translate('refund for unused period (%s unused)'), strval(floor(abs($detail_subtotal * 100 / $detail_price))) . '%');
  }
}


/*
 * 請求明細数量表示情報を取得
 *   $stripe_invoice_detail : Stripe請求明細
 *   
 *   return : HTML文
 */
function get_invoice_detail_quantity_display($stripe_invoice_detail) {
  $lang = new LanguageSupporter();
  $detail_subtotal = $stripe_invoice_detail->amount;
  $detail_price = $stripe_invoice_detail->price->unit_amount;
  
  if($detail_subtotal < 0) {
    return abs(floor($detail_subtotal * 100 / $detail_price) / 100);
  } else {
    return 1;
  }
}