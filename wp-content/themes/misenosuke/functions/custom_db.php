<?php


/* 
 * テーマが有効化されたときに必要なテーブルをデータベースに追加
 */
function theme_custom_plugin_activate() {
    function create_table($table_name, $sql_create, $sql_key, $sql_ai) {
      global $wpdb;
      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      
      if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) {
        // テーブルが存在しない場合、作成する
        dbDelta($sql_create);
        
        $wpdb->query($sql_key);
        
        $wpdb->query($sql_ai);
      }
    }
    
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // メニューカテゴリ追加
    $table_name = 'tb_menu_category';
    $sql_create = "
      CREATE TABLE `tb_menu_category` (
        `id` bigint(20) NOT NULL,
        `uid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `priority` int(11) NOT NULL DEFAULT 0,
        `author_id` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp()
      ) " . $charset_collate . ";
    ";
    $sql_key = "
      ALTER TABLE `tb_menu_category`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `uid` (`uid`);
    ";
    $sql_ai = "
      ALTER TABLE `tb_menu_category`
        MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
    ";
    create_table($table_name, $sql_create, $sql_key, $sql_ai);
    
    // メニュー追加
    $table_name = 'tb_menu';
    $sql_create = "
      CREATE TABLE `tb_menu` (
        `id` bigint(20) NOT NULL,
        `uid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `description` int(11) NOT NULL,
        `image_id` int(11) DEFAULT NULL,
        `price` int(11) NOT NULL DEFAULT 0,
        `priority` int(11) NOT NULL DEFAULT 0,
        `author_id` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp()
      ) " . $charset_collate . ";
    ";
    $sql_key = "
      ALTER TABLE `tb_menu`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `uid` (`uid`);
    ";
    $sql_ai = "
      ALTER TABLE `tb_menu`
        MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
    ";
    create_table($table_name, $sql_create, $sql_key, $sql_ai);
    
    // オプション追加
    $table_name = 'tb_option';
    $sql_create = "
      CREATE TABLE `tb_option` (
        `id` bigint(20) NOT NULL,
        `target_uid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `priority` int(11) NOT NULL DEFAULT 0
      ) " . $charset_collate . ";
    ";
    $sql_key = "
      ALTER TABLE `tb_option`
        ADD PRIMARY KEY (`id`);
    ";
    $sql_ai = "
      ALTER TABLE `tb_option`
        MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
    ";
    create_table($table_name, $sql_create, $sql_key, $sql_ai);
    
    // 選択肢追加
    $table_name = 'tb_choice';
    $sql_create = "
      CREATE TABLE `tb_choice` (
        `id` bigint(20) NOT NULL,
        `option_id` bigint(20) NOT NULL DEFAULT 0,
        `name` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `price` int(11) NOT NULL DEFAULT 0,
        `priority` int(11) NOT NULL DEFAULT 0
      ) " . $charset_collate . ";
    ";
    $sql_key = "
      ALTER TABLE `tb_choice`
        ADD PRIMARY KEY (`id`);
    ";
    $sql_ai = "
      ALTER TABLE `tb_choice`
        MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
    ";
    create_table($table_name, $sql_create, $sql_key, $sql_ai);
}
add_action('after_switch_theme', 'theme_custom_plugin_activate');