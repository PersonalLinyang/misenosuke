<?php

// 全域変数有効化
global $page_title, $page_description, $page_keywords, $style_key;

// 翻訳有効化
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

$page_title = ucwords($lang->translate('system error'));
$page_topic = strtoupper($page_title);
$style_key = 'error';

get_header();

get_template_part( 'template-parts/error/500' );

get_footer();
