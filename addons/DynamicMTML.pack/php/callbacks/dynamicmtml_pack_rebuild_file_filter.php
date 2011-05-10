<?php
function dynamicmtml_pack_rebuild_file_filter ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );
    return 1;
}
?>