<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();
$lang_code = $lang->code();

if(is_user_logged_in()) :
  if(current_user_can('manager')):
    $page_title = ucwords($lang->translate('course management'));
    $page_topic = strtoupper($page_title);
    $style_key = 'member';
    
    // ログイン時ユーザーID取得
    $user_id = get_current_user_id();
    
    // 利用可能言語取得
    $languages = explode(",", get_field('languages', 'user_' . $user_id));
    array_unshift($languages, 'ja');
    
    // コースタグの選択肢を取得
    $course_tags = get_acf_select_options('Course', 'tag');
    
    get_header();
?>

  <section class="manage-section">
    <h2 class="manage-h2"><?php echo $page_topic; ?></h2>
    
    <div class="manage-body">
      
      <div class="manage-viewer">
        <div class="manage-viewer-header">
          <p class="button manage-viewer-header-button manage-addcourse"><?php echo ucwords($lang->translate('add course')); ?></p>
          <p class="button manage-viewer-header-button manage-savepriority"><?php echo ucwords($lang->translate('save priority')); ?></p>
        </div>
        <div class="manage-viewer-body">
          <form id="manage-priority-form">
            <ul class="manage-viewer-list" data-index="0">
            </ul>
          </form>
        </div>
        <div class="manage-viewer-loading">
          <p class="manage-viewer-spinner"></p>
          <p><?php echo ucfirst($lang->translate('loading course information')); ?></p>
        </div>
      </div>
      
      <div class="manage-editor-shadow">
        <p class="manage-editor-shadow-text"><?php echo ucfirst($lang->translate('click here to close the editor area')); ?></p>
      </div>
      <div class="manage-editor">
        <div class="manage-editor-inner">
          <div class="manage-editor-header">
            <?php foreach(array_reverse($languages) as $language): ?>
              <p class="manage-language-tabhandler <?php echo $language == $lang_code ? 'active' : ''; ?>" data-language="<?php echo $language; ?>">
                <?php echo ucwords($lang->get_lang_name($language)); ?>
              </p>
            <?php endforeach; ?>
            <p class="manage-language-tabhandler-topic"><?php echo ucwords($lang->translate('input language')); ?></p>
          </div>
          
          <div class="manage-editor-body">
            <form class="form manage-editor-form" id="manage-course-form">
              <input type="hidden" class="manage-editor-course-slug" name="slug" value="" />
              <p class="warning center warning-system"></p>
              
              <div class="manage-editor-content">
                <h3><?php echo strtoupper('add course'); ?></h3>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('name')); ?></div>
                  <div class="form-input">
                    <?php foreach($languages as $language): ?>
                      <div class="manage-language-tab tab-<?php echo $language; ?> <?php echo $language == $lang_code ? '' : 'hidden'; ?>">
                        <input type="text" name="name_<?php echo $language; ?>" />
                      </div>
                    <?php endforeach; ?>
                    <?php foreach($languages as $language): ?>
                      <p class="warning warning-name_<?php echo $language; ?>"></p>
                    <?php endforeach; ?>
                    <p class="warning warning-name"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title">
                    <p class="manage-editor-topic"><?php echo ucwords($lang->translate('description')); ?></p>
                  </div>
                  <div class="form-input">
                    <?php foreach($languages as $language): ?>
                      <div class="manage-language-tab tab-<?php echo $language; ?> <?php echo $language == $lang_code ? '' : 'hidden'; ?>">
                        <textarea type="text" name="description_<?php echo $language; ?>"></textarea>
                      </div>
                    <?php endforeach; ?>
                    <?php foreach($languages as $language): ?>
                      <p class="warning warning-description_<?php echo $language; ?>"></p>
                    <?php endforeach; ?>
                    <p class="warning warning-description"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('photo')); ?></div>
                  <div class="form-input">
                    <div class="manage-editor-image">
                      <div class="manage-editor-image-inner"></div>
                      <label class="manage-editor-image-button">
                        <input id="manage-editor-image-file" type="file" name="course_image" /><?php echo ucwords($lang->translate('upload')); ?>
                      </label>
                      <label class="manage-editor-image-delete">
                        <input type="checkbox" name="image_delete" />×
                      </label>
                      <input type="hidden" name="copy_from" value="" />
                    </div>
                    <p class="warning warning-image"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('price')); ?></div>
                  <div class="form-input">
                    <div class="manage-editor-price">
                      <input type="number" name="price" value="0" />
                    </div>
                    <p class="warning warning-price"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('tag')); ?></div>
                  <div class="form-input">
                    <div class="manage-editor-taglist">
                      <?php foreach($course_tags as $tag_key => $tag_text): ?>
                        <label class="manage-editor-tag checkbox">
                          <input type="checkbox" name="tag[]" value="<?php echo $tag_key; ?>" /><?php echo ucwords($lang->translate($tag_text)); ?>
                        </label>
                      <?php endforeach; ?>
                    </div>
                    <p class="warning warning-tag"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('people available')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <input class="form-input-item manage-editor-number" type="number" name="min_people" value="" /><?php echo ucwords($lang->translate('people')); ?>～
                      <input class="form-input-item manage-editor-number" type="number" name="max_people" value="" /><?php echo ucwords($lang->translate('people')); ?>
                    </div>
                    <p class="instruction"><?php echo ucfirst($lang->translate('keep it empty if no limit')); ?></p>
                    <p class="warning warning-people"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('time available')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <select class="form-input-item manage-editor-time" name="start_time">
                        <option value="">--<?php echo ucwords($lang->translate('start time')); ?>--</option>
                        <?php for($hour = 0; $hour < 24; $hour++): ?>
                          <?php for($minute = 0; $minute < 60; $minute+=15): ?>
                            <option value="<?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT) . ':00'; ?>">
                              <?php echo str_pad($hour, 2, '0', STR_PAD_LEFT); ?>:<?php echo str_pad($minute, 2, '0', STR_PAD_LEFT); ?>
                            </option>
                          <?php endfor; ?>
                        <?php endfor; ?>
                      </select>～
                      <select class="form-input-item manage-editor-time" name="end_time">
                        <option value="">--<?php echo ucwords($lang->translate('end time')); ?>--</option>
                        <?php for($hour = 0; $hour < 24; $hour++): ?>
                          <?php for($minute = 0; $minute < 60; $minute+=15): ?>
                            <option value="<?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT) . ':00'; ?>">
                              <?php echo str_pad($hour, 2, '0', STR_PAD_LEFT); ?>:<?php echo str_pad($minute, 2, '0', STR_PAD_LEFT); ?>
                            </option>
                          <?php endfor; ?>
                        <?php endfor; ?>
                      </select>
                    </div>
                    <p class="instruction"><?php echo ucfirst($lang->translate('keep it empty if you sell it always')); ?></p>
                    <p class="warning warning-time"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('limit period')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <input class="form-input-item manage-datepicker" type="text" name="start_date" value="" />～
                      <input class="form-input-item manage-datepicker" type="text" name="end_date" value="" />
                    </div>
                    <p class="instruction"><?php echo ucfirst($lang->translate('keep it empty if no limit')); ?></p>
                    <p class="warning warning-period"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('time limit')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <input class="form-input-item manage-editor-number" type="number" name="time_limit" value="" /><?php echo ucwords($lang->translate('minutes')); ?>
                    </div>
                    <p class="instruction"><?php echo ucfirst($lang->translate('keep it empty if no limit')); ?></p>
                    <p class="warning warning-time_limit"></p>
                  </div>
                </div>
              </div>
              
              <div class="manage-editor-content">
                <div class="form-line">
                  <div class="form-title manage-editor-topic"><?php echo ucwords($lang->translate('last order')); ?></div>
                  <div class="form-input">
                    <div class="form-input-group">
                      <input class="form-input-item manage-editor-number" type="number" name="last_order" value="" /><?php echo ucwords($lang->translate('minutes')); ?>
                    </div>
                    <p class="instruction"><?php echo ucfirst($lang->translate('keep it empty if no limit')); ?></p>
                    <p class="warning warning-time_limit"></p>
                  </div>
                </div>
              </div>
              
              <p class="manage-editor-topic"><?php echo ucwords($lang->translate('options')); ?></p>
              <div class="manage-editor-content">
                <ul class="manage-editor-option-list">
                  <p class="form-input manage-editor-option-empty"><?php echo ucfirst($lang->translate('no option for this course')); ?></p>
                </ul>
                <p class="manage-editor-button manage-addoption" data-index="0"><?php echo ucwords($lang->translate('add option')); ?></p>
                <p class="warning center warning-options"></p>
              </div>
              
              <p class="manage-editor-topic"><?php echo ucwords($lang->translate('free menu')); ?></p>
              <div class="manage-editor-content">
                <div class="manage-editor-freemenus">
                  <select name="free_menus" class="manage-editor-freemenus-select" multiple="multiple">
                  </select>
                </div>
                <p class="warning center warning-free_menus"></p>
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
      <p class="popup-header"><?php echo strtoupper($lang->translate('course deletion confirmation')); ?></p>
      <div class="popup-body">
        <p><?php echo sprintf(ucfirst($lang->translate('are you sure to delete the course [%s]?')), '<span class="manage-popup-delete-name"></span>'); ?></p>
        <p><?php echo ucfirst($lang->translate('the deleted course can not be restored')); ?></p>
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
