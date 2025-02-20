<?php

class LanguageSupporter {
  private static $code;
  private static $domain;
  private static $language_name_list;
  private static $language_name_list_fixity;
  
  function __construct() {
    $default = get_option('qtranslate_default_language');
    if($default == false) {
      $default = 'en';
    }
    
    self::$code = function_exists('qtranxf_getLanguage') ? qtranxf_getLanguage() : $default;
    self::$domain = 'misenosuke-' . self::$code;
    
    self::$language_name_list = array(
      'ja' => ucwords(self::translate('japanese language')),
      'en' => ucwords(self::translate('english language')),
      'zh' => ucwords(self::translate('chinese language')),
    );
    
    self::$language_name_list_fixity = array(
      'ja' => '日本語',
      'en' => 'English',
      'zh' => '简体中文',
    );
  }

  public function code() {
    return self::$code;
  }

  public function name() {
    $name_list = get_option('qtranslate_language_names');
    return array_key_exists(self::$code, $name_list) ? $name_list[self::$code] : '';
  }

  public function domain() {
    return self::$domain;
  }

  public function translate($message, $language='') {
    if($language) {
      // 一時的に地域変更
      switch_to_locale($language);
      $text = __($message, 'misenosuke-' . $language);
      
      return $text;
    } else {
      return __($message, self::$domain);
    }
  }

  public function get_text($message, $language='') {
    if(!$language) {
      $language = self::$code;
    }
    
    if(function_exists('qtranxf_use')) {
      return qtranxf_use($language, $message, false, true);
    } else {
      preg_match('/\[:' . $language . '\](.*?)\[:/', $message, $matches);
      if(count($matches) > 1) {
        return $matches[1];
      } else {
        return '';
      }
    }
  }

  public function get_lang_list() {
    $languages = function_exists('qtranxf_getSortedLanguages') ? qtranxf_getSortedLanguages() : ['ja'];
    
    $result = array();
    foreach($languages as $language) {
      $result[$language] = self::$language_name_list[$language];
    }
    return $result;
  }
  
  public function get_lang_name($language) {
    return self::$language_name_list[$language];
  }
  
  public function get_lang_name_fixity($language) {
    return self::$language_name_list_fixity[$language];
  }

}