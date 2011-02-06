<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtentrystatusint ( $args, &$ctx ) {
    $entry = $ctx->stash( 'entry' );
    return $entry->entry_status;
}
?>