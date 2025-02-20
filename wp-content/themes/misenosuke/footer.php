<?php
/*
 * フッタ部分
 */

global $style_key;

?>

    </main>
    
    <?php 
    if(file_exists(get_theme_file_path('template-parts/footer/' . $style_key . '.php'))) {
      get_template_part('template-parts/footer/' . $style_key); 
    } else {
      get_template_part('template-parts/footer/common'); 
    }
    ?>

    <?php wp_footer(); ?>
  </body>
</html>
