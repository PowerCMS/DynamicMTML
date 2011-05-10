<?php
function dynamicmtml_pack_mt_init_exception ( &$mt, &$ctx, &$args, $error ) {
    global $app;

/*
    // Example 1: Show error message on DebugMode.
    if ( $app->config( 'DebugMode' ) ) {
        echo htmlspecialchars( $error );
        exit();
    }
*/

/*
    //Example 2: Retry get_instance using mt-alt-config.cgi.
    global $mt_dir;
    $config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-alt-config.cgi';
    if ( file_exists ( $config ) ) {
        global $mt_config;
        global $blog_id;
        $mt_config = $config;
        try {
            $mt = MT::get_instance( $blog_id, $mt_config );
        } catch ( MTInitException $e ) {
            $mt = NULL;
            // Continue Non-DynamicMTML
        }
        if ( isset ( $mt ) ) {
            $app->configure( $mt_config );
            $app->init_plugin_dir();
        }
    }
*/
}
?>