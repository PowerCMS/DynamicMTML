<?php
function smarty_modifier_strip_php( $text, $arg ) {
    global $app;
    return $app->strip_php( $text );
}
?>