<?php
function smarty_function_mtentrystatusint ( $args, &$ctx ) {
    $entry = $ctx->stash( 'entry' );
    return $entry->entry_status;
}
?>