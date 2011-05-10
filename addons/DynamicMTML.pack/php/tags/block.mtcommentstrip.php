<?php
function smarty_block_mtcommentstrip( $args, $content, $ctx, $repeat ) {
    $begin = preg_quote( '<!--', '/' );
    $end   = preg_quote( '-->', '/' );
    $content = preg_replace( "/$begin/", '', $content );
    $content = preg_replace( "/$end/", '', $content );
    return $content;
}
?>