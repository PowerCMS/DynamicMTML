<?php
function smarty_block_mtloginauthorctx( $args, $content, &$ctx, &$repeat ) {
    require_once ( 'block.mtclientauthorblock.php' );
    return smarty_block_mtclientauthorblock( $args, $content, $ctx, $repeat );
}
?>