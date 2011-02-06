<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtdynamicsitebootstrapper ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $bootstrapper = $app->config( 'DynamicSiteBootstrapper' );
    if (! $bootstrapper ) {
        $bootstrapper = '.mtview.php';
    }
    return $bootstrapper;
}
?>