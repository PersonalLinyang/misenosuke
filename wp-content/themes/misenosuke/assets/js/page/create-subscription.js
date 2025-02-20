$(document).ready(function(){
  $('#create-subscription-submit').click(function(){
    var plan = $('input[name="plan_code"]:checked');
    if(plan.length == 1) {
      $('#create-subscription-form').submit();
    } else {
      $('#create-subscription-warning-select').slideDown();
    }
  });
  
  $('input[name="plan_code"]').click(function(){
    $('#create-subscription-submit').addClass('active');
  });
});