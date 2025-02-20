$(document).ready(function(){
  // メニュー言語変更時に店名入力欄を調整
  $('.profile-menulanguage').change(function(){
    var code = $(this).val();
    if($(this).is(':checked')) {
      var html = `
        <div class="form-line profile-restaurantname-` + code + `">
          <p class="form-title">` + ucwords(translations.restaurant_name) + `(` + languages[code] + `)<span class="required">*</span></p>
          <div class="form-input">
            <input type="text" name="restaurant_name_` + code + `" placeholder="` + ucwords(translations.restaurant_name) + `(` + languages[code] + `)" />
            <p class="warning warning-restaurant_name_` + code + `"></p>
          </div>
        </div>
      `;
      $('.profile-restaurantname').append(html);
      $('.profile-restaurantname-' + code).hide().slideDown();
    } else {
      $('.profile-restaurantname-' + code).slideUp(function(){
        $(this).remove();
      });
    }
  });
  
  // 住所自動入力
  $('.profile-getaddress').click(function(){
    var zipcode = $('.profile-zipcode1').val() + $('.profile-zipcode2').val();
    
    if(zipcode) {
      $.ajax({
        url: 'https://zipcloud.ibsnet.co.jp/api/search',
        type: 'GET',
        dataType: 'jsonp',
        data: {
          zipcode: zipcode
        },
        success: function(response) {
          if (response.status === 200 && response.results) {
            var result = response.results[0];
            $('.profile-prefecture').val(result.address1);
            $('.profile-city').val(result.address2);
            $('.profile-street').val(result.address3);
          } else {
            showPopupMessage(ucfirst(translations.zipcode_wrong), 'error');
          }
        },
        error: function() {
          showPopupMessage(ucfirst(translations.get_address_failed), 'error');
        }
      });
    } else {
      showPopupMessage(ucfirst(translations.zipcode_required), 'error');
    }
  });
  
  // ファイルをアップロードし直し
  $('.profile-restaurantlogo').change(function(e){
    var reader = new FileReader();
    reader.onload = function(e) {
      $('.profile-logo-inner').css('background-image', 'url(' + e.target.result + ')');
    }
    reader.readAsDataURL(e.target.files[0]);
  });
});