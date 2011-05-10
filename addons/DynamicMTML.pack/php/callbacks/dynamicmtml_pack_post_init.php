<?php
function dynamicmtml_pack_post_init ( $mt, &$ctx, &$args ) {
    $app = $ctx->stash( 'bootstrapper' );

/*
    //Example 1: Check username and password, Set $app->user and login
    if ( $app->mode == 'login' ) {
       $app->login();
    } elsif ( $app->mode == 'logout' ) {
       $app->logout();
    }
*/

/*
    // Example 2: Get user
    if (! $timeout = $app->config( 'UserSessionTimeout' ) ) {
         $timeout = 14400;
    }
    $author = $app->get_author( $ctx, $timeout ); # or $app->user();
*/

/*
    // Example 3: http://example.com/entry_1/JapaneseTitle/ => http://example.com/entry_1/index.html
    // See also => dynamicmtml_pack_pre_build_page.php
    // Setting  : Individual Archive Mapping => entry_<$mt:EntryID$>/%i
    //            Category Archive Mapping   => category_<$mt:CategoryID$>/%i
    //            <$mt:EntryPermalink$>      => <$mt:EntryPermalink$><$mt:EntryTitle make_seo_basename="50"$>/
    //            <$mt:CategoryArchiveLink$> => <$mt:CategoryArchiveLink$><$mt:CategoryLabel make_seo_basename="50"$>/
    $file = $app->stash( 'file' );
    $url  = $app->stash( 'url' );
    $request = $app->stash( 'request' );
    if (! file_exists ( $file ) ) {
        $file = $app->path2index( $file, 'index.html' );
        if ( file_exists ( $file ) ) {
            $request = $app->path2index( $request );
            $url     = $app->path2index( $url );
            $app->stash( 'file', $file );
            $app->stash( 'request', $request );
            $app->stash( 'url', $url );
            $app->stash( 'contenttype', 'text/html' );
            $app->stash( 'extension', 'html' );
            $cache = $app->cache_filename( $ctx->stash( 'blog_id' ), $file, $app->query_string );
            $app->stash( 'cache', $cache );
        }
    }
*/

/*
    $request = $app->request;
    list ( $nill, $root, $entry_id ) = explode( '/', $app->request );
    if ( ctype_digit ( $entry_id ) ) {
        $blog_id = $app->blog_id;
        $where = "fileinfo_blog_id = {$blog_id} "
               . " AND fileinfo_entry_id = {$entry_id} "
               . " AND fileinfo_archive_type = 'Individual' ";
        $extra = array(
            'limit' => 1,
        );
        require_once( 'class.mt_fileinfo.php' );
        $_fileinfo = new FileInfo;
        $fileinfo = $_fileinfo->Find( $where, false, false, $extra );
        if ( isset ( $fileinfo ) ) {
            $fileinfo = $fileinfo[0];
            $file = $fileinfo->fileinfo_file_path;
            $root = $app->root;
            $root = preg_quote( $root, '/' );
            $reqest = preg_replace( "/^$root/", '', $file_path );
            $app->stash( 'file', $file );
            $app->stash( 'request', $request );
            $app->stash( 'url', $app->base . $request );
            $app->stash( 'contenttype', 'text/html' );
            $app->stash( 'extension', 'html' );
            $cache = $app->cache_filename( $ctx->stash( 'blog_id' ), $file, $app->query_string );
            $app->stash( 'cache', $cache );
        }
    }
*/

/*
    // Example 4: Required login. Basic Auth using MT.
    if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) &&
       ( $app->is_valid_author( $ctx, $_SERVER[ 'PHP_AUTH_USER' ],
                             $_SERVER[ 'PHP_AUTH_PW' ] ) ) ) {
    } else {
        header( 'WWW-Authenticate: Basic realm=""' );
        header( 'HTTP/1.0 401 Unauthorized' );
        exit();
    }
*/

/*
    //  Example 5: Required login. using MT Commenter Auth.
    if (! $timeout = $mt->config( 'UserSessionTimeout' ) ) {
        $timeout = 14400;
    }
    $client_author = $app->get_author( $ctx, $timeout, 'comment' );
    if (! $client_author ) {
        $url = $args[ 'url' ];
        $return_url  = $mt->config( 'CGIPath' );
        $return_url .= $mt->config( 'CommentScript' );
        $return_url .= '?__mode=login&blog_id=' . $ctx->stash( 'blog_id' );
        $return_url .= '&return_url=' . rawurlencode( $url );
        $app->redirect( $return_url );
        exit();
    }
*/

}
?>