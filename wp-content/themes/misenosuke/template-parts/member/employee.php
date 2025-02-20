<?php 
/**
 * 現在利用中のプランとサブスクリプション情報を表示
 */

global $wpdb;

// 翻訳有効化
$lang = new LanguageSupporter();

// ユーザーIDを取得
$user_id = get_current_user_id();
$uid = get_field('uid', 'user_' . $user_id);

$employee_query = new WP_User_Query(array(
  'meta_key' => 'restaurant',
  'meta_value' => $user_id,
  'orderby' => 'registered',
  'order'   => 'ASC',
  'number'  => -1,
));
$employee_list = $employee_query->get_results();

?>

<section class="member-section" id="member-employee">
  <div class="member-section-header">
    <h3><?php echo strtoupper($lang->translate('employee')); ?></h3>
    <div class="member-section-controller">
      <p class="member-section-button member-employee-button-add" data-type="cook" data-uid="<?php echo $uid; ?>">
        <span><?php echo ucwords($lang->translate('add cook')); ?></span>
      </p>
      <p class="member-section-button member-employee-button-add" data-type="waiter" data-uid="<?php echo $uid; ?>">
        <span><?php echo ucwords($lang->translate('add waiter')); ?></span>
      </p>
    </div>
  </div>
  
  <?php if(count($employee_list)): ?>
    <div class="member-employee-area">
      <table class="member-employee-table">
        <tr class="member-employee-header">
          <th class="wrap member-employee-type"><?php echo ucwords($lang->translate('type')); ?></th>
          <th class="wrap member-employee-name"><?php echo ucwords($lang->translate('name')); ?></th>
          <th class="wrap member-employee-mail"><?php echo ucwords($lang->translate('mail address')); ?></th>
          <th class="wrap member-employee-telephone"><?php echo ucwords($lang->translate('employee telephone')); ?></th>
          <th class="wrap member-employee-action"><?php echo ucwords($lang->translate('delete')); ?></th>
        </tr>
        <?php 
        foreach($employee_list as $employee_item): 
          $employee_data = $employee_item->data;
          $employee = get_user_by('id', $employee_data->ID);
        ?>
          <tr>
            <td class="member-employee-type"><?php echo $lang->translate($employee->roles[0]); ?></td>
            <td class="wrap member-employee-name"><?php echo $employee->display_name; ?></td>
            <td class="member-employee-mail"><?php echo $employee->user_email; ?></td>
            <td class="member-employee-telephone"><?php echo get_field('telephone', 'user_' . $employee->ID); ?></td>
            <td class="member-employee-action">
              <p class="member-employee-button member-employee-delete-button" data-uid="<?php echo get_field('uid', 'user_' . $employee->ID); ?>">
                <span><?php echo ucwords($lang->translate('delete')); ?></span>
              </p>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php else: ?> 
    <p class="member-empty"><?php echo ucfirst($lang->translate('there is no employee information')); ?></p>
  <?php endif; ?>
</section>

<section class="popup-section member-employee-addqr">
  <div class="popup-inner">
    <p class="popup-header"><?php echo strtoupper($lang->translate('qr code for employee creation')); ?></p>
    <div class="popup-body">
      <p><?php echo ucfirst($lang->translate('let your employee use this qr code to signup please')); ?></p>
      <div class="member-employee-addqr-qr" id="member-employee-addqr-qr"></div>
    </div>
    <div class="popup-footer">
      <p class="popup-close member-section-button"><span><?php echo ucwords($lang->translate('close')); ?></span></p>
    </div>
  </div>
</section>

<section class="popup-section member-employee-delete">
  <div class="popup-inner">
    <p class="popup-header"><?php echo strtoupper($lang->translate('employee delete confirmation')); ?></p>
    <div class="popup-body">
      <p><?php echo sprintf(ucfirst($lang->translate('are you sure to delete the employee [%s]?')), '<span class="member-employee-delete-name"></span>'); ?></p>
      <p><?php echo ucfirst($lang->translate('the deleted employee can not be restored')); ?></p>
      <form id="member-employee-delete-form">
        <input type="hidden" class="member-employee-delete-uid" name="uid" value="" />
      </form>
    </div>
    <div class="popup-footer">
      <p class="button shine-active member-employee-delete-close"><span><?php echo ucwords($lang->translate('close')); ?></span></p>
      <p class="button member-employee-delete-submit"><span><?php echo ucwords($lang->translate('delete')); ?></span></p>
    </div>
  </div>
</section>