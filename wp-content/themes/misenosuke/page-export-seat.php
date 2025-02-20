<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

$number_per_page = 24;

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $page_title = ucwords($lang->translate('download seat qr code'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    // ログイン時ユーザーID取得
    $user_id = get_current_user_id();
    
    // 席リストを取得
    $seats = get_seat_info($user_id);
    
    get_header();
?>

<h2><?php echo $page_topic; ?></h2>

<section class="export-seat-controller-section">
  <p class="button export-seat-download"><span><?php echo ucwords($lang->translate('export PDF')); ?></span></p>
</section>

<section class="export-seat-preview-section">
  <div class="export-seat-preview-inner">
    <div class="export-seat-preview-area" id="export-seat-preview">
      <?php 
      $total_counter = 0;
      $item_counter = 0;
      $list_counter=0;
      foreach($seats as $seat): 
        if($item_counter == 0):
      ?>
        <ul class="export-seat-list" id="export-seat-list-<?php echo $list_counter; ?>">
      <?php endif;?>
          <li class="export-seat-item">
            <div class="export-seat-qr" data-slug="<?php echo $seat['slug']; ?>"></div>
            <p class="export-seat-text"><?php echo $seat['name']; ?></p>
          </li>
      <?php 
        $item_counter++;
        $total_counter++;
        if($total_counter == count($seats) || $item_counter == $number_per_page) : 
          $item_counter = 0;
          $list_counter ++;
      ?>
        </ul>
      <?php 
        endif;
      endforeach;
      ?>
    </div>
  </div>
</section>

<?php 
    get_footer();
  else:
    // プロファイルページがない場合、権限エラーテンプレートを切り替え
    include(locate_template('403.php'));
  endif;
else :
  // ログインページにリダイレクト
  header('Location: ' . LANG_DOMAIN . '/login/');
  exit;
endif;
