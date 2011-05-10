<?php
function smarty_function_mtdynamicsitebootstrapper ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $bootstrapper = $app->config( 'DynamicSiteBootstrapper' );
    if (! $bootstrapper ) {
        $bootstrapper = '.mtview.php';
    }
    return $bootstrapper;
}
?>