<?php
function smarty_block_mtcommentout( $args, $content, $ctx, $repeat ) {
    if ( $args[ 'invisible' ] ) {
        return '';
    } else {
        return '<!--' . $content . '-->';
    }
}
?>