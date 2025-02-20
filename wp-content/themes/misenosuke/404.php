<?php

// ‘Sˆæ•Ï”—LŒø‰»
global $page_title, $page_description, $page_keywords, $style_key;

// –|–ó—LŒø‰»
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';
$lang = new LanguageSupporter();

$page_title = ucwords($lang->translate('access error'));
$page_topic = strtoupper($page_title);
$style_key = 'error';

get_header();

get_template_part( 'template-parts/error/404' );

get_footer();
