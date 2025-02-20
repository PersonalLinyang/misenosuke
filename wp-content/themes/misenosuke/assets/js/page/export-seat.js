const resizePreview = function() {
  var area_width = $('.export-seat-preview-area')[0].offsetWidth;
  var section_width = $('.export-seat-preview-section').width();

  if (area_width > section_width) {
    var scale = section_width / area_width;
    $('.export-seat-preview-area').css('transform', 'scale(' + scale + ')');
  } else {
    $('.export-seat-preview-area').css('transform', 'none');
  }
}

$(document).ready(function() {
  // ダウンロートボタンをクリック
  $('.export-seat-download').click(async function() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'px', 'a4');

    // PDFサイズ取得
    const pdf_width = pdf.internal.pageSize.getWidth();
    const pdf_height = pdf.internal.pageSize.getHeight();
    
    for(let page=0; page < $('#export-seat-preview').find('.export-seat-list').length; page++) {
      // HTMLからキャンバスを作成
      let content = document.getElementById('export-seat-list-' + page);
      let canvas = await html2canvas(content, { scale: 3 });
      
      // キャンバスから画像データを取得
      let img_data = canvas.toDataURL('image/png');
      
      // 出力する高さを計算
      let img_height = (canvas.height * pdf_width) / canvas.width;
      
      // ページを追加
      if(page > 0) {
        pdf.addPage();
      }
      
      // PDFに内容入れ
      pdf.addImage(img_data, 0, 50, pdf_width, img_height);
    }
        
    // 新しいタブでPDFを開く
    const pdf_url = pdf.output('bloburl');
    window.open(pdf_url, '_blank');

    // PDFをダウンロード
    pdf.save('download.pdf');
  });
  
  var area_height = $('.export-seat-preview-area').outerHeight();
  var area_width = $('.export-seat-preview-area').outerWidth();
  $('.export-seat-preview-inner').css('padding-top', (area_height * 100 / area_width) + '%');
  
  resizePreview();
  window.addEventListener('resize', resizePreview);
  window.addEventListener('DOMContentLoaded', resizePreview);
  
  $('.export-seat-qr').each(async function(){
    var slug = $(this).data('slug');
    var url = simple_domain + '/order/?seat_uid=' + slug; // 指定のURL
    $(this).empty(); // QRコードを生成する前にクリア
    await new QRCode($(this)[0], {
        text: url,
        width: 128,
        height: 128
    });
  });
});