<?php
function smarty_function_mtpluginversion ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $component = $args[ 'plugin' ];
    if (! $component ) {
        $component = $args[ 'component' ];
    }
    $scope = $args[ 'scope' ];
    if (! $scope ) $scope = 'version';
    if ( preg_match( '/version$/', $scope ) ) {
        $version = $app->get_plugin_config( $component, $scope );
        if ( $version ) {
            return $version;
        }
        return $version;
    }
    return 0;
}
?>