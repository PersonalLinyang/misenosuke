<?php

/*
 * 変数入れのため翻訳エディターに更新できない翻訳を補足
 */
require_once get_template_directory() . '/inc/custom-classes/language_supporter.inc.php';

$lang = new LanguageSupporter();

$translations = [
  // 期間に使う
  $lang->translate('day'),
  $lang->translate('week'),
  $lang->translate('month'),
  $lang->translate('year'),
  $lang->translate('%sday'),
  $lang->translate('%sweek'),
  $lang->translate('%smonth'),
  $lang->translate('%syear'),
  $lang->translate('%sdays'),
  $lang->translate('%sweeks'),
  $lang->translate('%smonths'),
  $lang->translate('%syears'),
  
  // サブスクリプションステータス
  $lang->translate('active'),
  $lang->translate('past_due'),
  $lang->translate('cancelled'),
  $lang->translate('expired'),
  $lang->translate('pending'),
  $lang->translate('undetected'),
  $lang->translate('unfound'),
  $lang->translate('trialing'),
  
  // メニュータグ選択肢
  $lang->translate('new product'),
  $lang->translate('limited period'),
  $lang->translate('great value product'),
  $lang->translate('special price product'),
  $lang->translate('recommendation'),
  $lang->translate('popular no.1'),
  $lang->translate('popular no.2'),
  $lang->translate('popular no.3'),
];