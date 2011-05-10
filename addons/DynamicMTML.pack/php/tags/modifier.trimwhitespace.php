<?php
function smarty_modifier_trimwhitespace( $text, $arg ) {
    global $mt;
    require_once( 'outputfilter.trimwhitespace.php' );
    $text = smarty_outputfilter_trimwhitespace( $text, $mt );
    return $text;
}
?>