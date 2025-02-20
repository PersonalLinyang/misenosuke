const resizePreview = function() {
  var area_width = $('.reciept-preview-area')[0].offsetWidth;
  var section_width = $('.reciept-preview-section').width();

  if (area_width > section_width) {
    var scale = section_width / area_width;
    $('.reciept-preview-area').css('transform', 'scale(' + scale + ')');
  } else {
    $('.reciept-preview-area').css('transform', 'none');
  }
}

$(document).ready(function() {
  // ダウンロートボタンをクリック
  $('.reciept-download').click(async function() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'px', 'a4');

    // HTMLからキャンバスを作成
    const content = document.getElementById('reciept-preview');
    const canvas = await html2canvas(content, { scale: 3 });

    // キャンバスから画像データを取得
    const img_data = canvas.toDataURL('image/png');

    // 画像をPDFに追加
    const pdf_width = pdf.internal.pageSize.getWidth();
    const pdf_height = (canvas.height * pdf_width) / canvas.width;
    pdf.addImage(img_data, 'PNG', 0, 0, pdf_width, pdf_height);

    // 新しいタブでPDFを開く
    const pdf_url = pdf.output('bloburl');
    window.open(pdf_url, '_blank');

    // PDFをダウンロード
    pdf.save('download.pdf');
  });
  
  // 印刷ボタンをクリック
  $('.reciept-print').click(async function() {
    // HTMLからキャンバスを作成
    const content = document.getElementById('reciept-preview');
    const canvas = await html2canvas(content, { scale: 3 });
    
    // キャンバスを新しいウィンドウに表示
    var print_window = window.open('', '', 'width=800,height=600');
    
    print_window.document.write('<html><head><title>' + ucwords(translations.print_reciept_document) + '</title>');

    // 現在のページのスタイルシートをコピー
    $('link[rel="stylesheet"]').each(function() {
      print_window.document.write('<link href="' + $(this).attr('href') + '" rel="stylesheet" type="text/css">');
    });

    // 内部スタイルもコピー
    $('style').each(function() {
      print_window.document.write('<style>' + $(this).html() + '</style>');
    });

    print_window.document.write('</head><body>');
    print_window.document.body.appendChild(canvas);
    print_window.document.write('</body></html>');

    // ドキュメントの書き込みを終了
    print_window.document.close();

    // 印刷を実行
    print_window.print();

    // 印刷後にウィンドウを閉じる
    print_window.onafterprint = function() {
      print_window.close();
    };
  });
  
  resizePreview();
  window.addEventListener('resize', resizePreview);
  window.addEventListener('DOMContentLoaded', resizePreview);
});