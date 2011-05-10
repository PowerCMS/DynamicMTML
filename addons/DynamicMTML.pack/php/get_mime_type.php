<?php
/* Backward compatibility */

if (! isset( $app ) ) {

    require_once ( $plugin_path . 'dynamicmtml.php' );
    require_once ( $plugin_path . 'dynamicmtml.util.php' );
    $app = new DynamicMTML();
    $ctx =& $mt->context();
    $blog = $mt->db()->fetch_blog( $blog_id );
    $ctx->stash( 'blog', $blog );
    $ctx->stash( 'blog_id', $blog_id );
    $app->stash( 'blog', $blog );
    $app->stash( 'blog_id', $blog_id );
    $ctx->stash( 'bootstrapper', $app );
    $app->configure( $mt_dir . DIRECTORY_SEPARATOR . 'mt-config.cgi' );
    $app->set_context( $mt, $ctx );
    if ( isset( $_SERVER[ 'REDIRECT_STATUS' ] ) ) {
        $status = $_SERVER[ 'REDIRECT_STATUS' ];
        if ( ( $status == 403 ) || ( $status == 404 ) ) {
            if ( isset ( $_SERVER[ 'REDIRECT_QUERY_STRING' ] ) ) {
                if (! $_GET ) {
                    parse_str( $_SERVER[ 'REDIRECT_QUERY_STRING' ], $_GET );
                }
            }
            if (! $_POST ) {
                if ( $params = file_get_contents( "php://input" ) ) {
                    parse_str( $params, $_POST );
                }
            }
            $app->request_method = $_SERVER[ 'REDIRECT_REQUEST_METHOD' ];
            $app->mod_rewrite = 0;
        } else {
            $app->request_method = $_SERVER[ 'REQUEST_METHOD' ];
            $app->mod_rewrite = 1;
        }
    } else {
        $app->mod_rewrite = 1;
    }
    $app->run_callbacks( 'init_request' );

}
function get_mime_type( $extension ) {
    global $app;
    return $app->get_mime_type( $extension );
}
?>