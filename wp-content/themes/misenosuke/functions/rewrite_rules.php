<?php


/*
 * リライトルール設定
 */
function custom_rewrite_basic() {
  //add_rewrite_rule('^common-style/([a-f0-9]{32})/([a-f0-9]{32})/?', 'index.php?pagename=common-style&site_uid=$matches[1]&target_uid=$matches[2]', 'top');
  //add_rewrite_rule('^common-style/([a-f0-9]{32})/?', 'index.php?pagename=common-style&target_uid=$matches[1]', 'top');
  flush_rewrite_rules();
}
add_action('init', 'custom_rewrite_basic');


/*
 * リライトルールのindex.phpのパラメータとして使える項目を追加
 */
function add_query_vars_filter( $vars ){
  //array_push($vars, 'target_uid');
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );
