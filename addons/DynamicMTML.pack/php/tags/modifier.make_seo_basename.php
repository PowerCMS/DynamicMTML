<?php
function smarty_modifier_make_seo_basename( $text, $arg ) {
    require_once ( 'dynamicmtml.util.php' );
    return make_seo_basename( $text, $arg );
}
?>