<?php
function smarty_block_mtcommentout( $args, $content, $ctx, $repeat ) {
    return '<!--' . $content . '-->';
}
?>