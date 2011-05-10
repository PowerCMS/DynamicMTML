<?php
function smarty_modifier_intval( $text, $arg ) {
    $val = intval( trim( $text ) );
    if (! $val ) $val = 0;
    return $val;
}
?>