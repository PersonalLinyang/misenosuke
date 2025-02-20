<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $page_title = ucwords($lang->translate('seat management'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    // ログイン時ユーザーID取得
    $user_id = get_current_user_id();
    
    get_header();
?>

  <section class="manage-section">
    <h2 class="manage-h2"><?php echo $page_topic; ?></h2>
    
    <div class="manage-body">
      
      <div class="manage-viewer">
        <div class="manage-viewer-header">
          <p class="button manage-viewer-header-button manage-addseat"><?php echo ucwords($lang->translate('add seat')); ?></p>
          <p class="button manage-viewer-header-button manage-exportseat">
            <a class="full-link" href="<?php echo LANG_DOMAIN . '/export-seat/'; ?>"><?php echo ucwords($lang->translate('download qr')); ?></a>
          </p>
        </div>
        <div class="manage-viewer-body">
          <ul class="manage-viewer-list">
          </ul>
        </div>
        <div class="manage-viewer-loading">
          <p class="manage-viewer-spinner"></p>
          <p><?php echo ucfirst($lang->translate('loading seat information')); ?></p>
        </div>
      </div>
      
      <div class="manage-editor-shadow">
        <p class="manage-editor-shadow-text"><?php echo ucfirst($lang->translate('click here to close the editor area')); ?></p>
      </div>
      <div class="manage-editor">
        <div class="manage-editor-inner">
          <div class="manage-editor-body">
            <form class="form manage-editor-form" id="manage-seat-form">
              <input type="hidden" class="manage-editor-seat-slug" name="slug" value="" />
              <p class="warning center warning-system"></p>
              
              <div class="manage-editor-content">
                <h3><?php echo strtoupper('add seat'); ?></h3>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('name')); ?></div>
                  <div class="form-input">
                    <input type="text" name="name" />
                    <p class="warning warning-name"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('people')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <input type="number" class="form-input-item manage-editor-number" name="people" /><?php echo ucwords($lang->translate('people')); ?>
                    </div>
                    <p class="warning warning-people"></p>
                  </div>
                </div>
              </div>
            </form>
          </div>
          
          <div class="manage-editor-footer">
            <p class="button manage-clear"><?php echo ucwords($lang->translate('clear')); ?></p>
            <p class="button active manage-save"><?php echo ucwords($lang->translate('add')); ?></p>
          </div>
        </div>
      </div>
      
    </div>
  </section>
  
  <section class="popup-message">
    <p class="popup-message-text"></p>
  </section>
  
  <section class="popup-shadow"></section>
  
  <section class="popup-section manage-popup-delete">
    <div class="popup-inner">
      <p class="popup-header"><?php echo strtoupper($lang->translate('seat deletion confirmation')); ?></p>
      <div class="popup-body">
        <p><?php echo sprintf(ucfirst($lang->translate('are you sure to delete the seat [%s]?')), '<span class="manage-popup-delete-name"></span>'); ?></p>
        <p><?php echo ucfirst($lang->translate('the deleted seat can not be restored')); ?></p>
        <form id="manage-delete-form">
          <input type="hidden" class="manage-popup-delete-slug" name="slug" value="" />
        </form>
      </div>
      <div class="popup-footer">
        <p class="button popup-close manage-deletecancel"><?php echo ucwords($lang->translate('cancel')); ?></p>
        <p class="button manage-deleteconfirm"><?php echo ucwords($lang->translate('delete')); ?></p>
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
