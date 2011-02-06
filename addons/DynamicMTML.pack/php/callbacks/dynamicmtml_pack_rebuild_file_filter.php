<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function dynamicmtml_pack_rebuild_file_filter ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );
    return 1;
}
?>