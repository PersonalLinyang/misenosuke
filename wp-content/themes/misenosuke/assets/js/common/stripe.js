// Stripe公開鍵を読み込み
const stripe = Stripe(stripe_public_key);

// Stripeカード情報入力を初期化
const elements = stripe.elements();
var card_number = elements.create('cardNumber', {});
var card_expiry = elements.create('cardExpiry', {});
var card_cvc = elements.create('cardCvc', {});


/*
 * クレジットカード入力エリアを有効化
 */
const initCreditcard = function() {
  card_number.mount("#creditcard-card_number");
  card_expiry.mount("#creditcard-card_expiry");
  card_cvc.mount("#creditcard-card_cvc");
}


/*
 * Stripeトークン（クレジットカード情報一時保存）を作成
 * call_back: function トークン作成後に実行する処理
 */
const createToken = function(call_back) {
  // Stripeでカードトークンを作成
  stripe.createToken(card_number, card_expiry, card_cvc).then(function(result){
    if (result.error) {
      switch (result.error.code) {
        case 'incomplete_number':
          addFormWarning('card_number', ucfirst(translations.incomplete_number));
          break;
        case 'incomplete_expiry':
          addFormWarning('card_expiry', ucfirst(translations.incomplete_expiry));
          break;
        case 'invalid_expiry_year_past':
          addFormWarning('card_expiry', ucfirst(translations.invalid_expiry_year_past));
          break;
        case 'incomplete_cvc':
          addFormWarning('card_cvc', ucfirst(translations.incomplete_cvc));
          break;
        default:
          addFormWarning('card_info', ucfirst(translations.card_validate_error));
      }
    } else {
      $('#creaditcard-input').val(result.token.id);
      call_back();
    }
  });
}