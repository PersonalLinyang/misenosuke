<?php

global $page_title, $page_description, $page_keywords, $style_key;

?><!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <title><?php echo $page_title; ?></title>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11" />
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/site_icon.ico">
    <script>
      (function(d) {
        var config = {
          kitId: 'jfr8jyk',
          scriptTimeout: 3000,
          async: true
        },
        h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
      })(document);
    </script>
    <?php wp_head(); ?>
  </head>

  <body class="<?php echo $style_key; ?>-body">

    <?php 
    if(file_exists(get_theme_file_path('template-parts/header/' . $style_key . '.php'))) {
      get_template_part('template-parts/header/' . $style_key); 
    } else {
      get_template_part('template-parts/header/common'); 
    }
    ?>

    <main class="<?php echo $style_key; ?>-main">