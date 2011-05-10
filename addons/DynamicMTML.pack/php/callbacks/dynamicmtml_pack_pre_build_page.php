<?php
function dynamicmtml_pack_pre_build_page ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );

/*
    // Example 1: http://example.com/entry_1/ => http://example.com/entry_1/EntryTitle_or_CategoryLabel/
    // See also => dynamicmtml_pack_post_init.php
    $request = $app->stash( 'request' );
    if ( preg_match( '!/$!', $request ) ) {
        $file = $app->stash( 'file' );
        $blog_id = $app->blog_id;
        if ( file_exists( $file )  && preg_match( '!/index\.html$!', $file ) ) {
            $fileinfo = $app->stash( 'fileinfo' );
            require_once( 'MTUtil.php' );
            if (! isset( $fileinfo ) ) {
                $fileinfo = $mt->db()->resolve_url( $mt->db()->escape( urldecode( $request ) ),
                                                    $blog_id, array( 1, 2, 4 ) );
            }
            if ( isset( $fileinfo ) ) {
                $app->stash( 'fileinfo', $fileinfo );
                $entry_id = $fileinfo->entry_id;
                $category_id = $fileinfo->category_id;
                if ( $entry_id || $category_id ) {
                    $obj = NULL;
                    if ( $entry_id ) {
                        if ( $fileinfo->archive_type == 'Page' ) {
                            $obj = $mt->db()->fetch_page( $entry_id );
                        } else {
                            $obj = $mt->db()->fetch_entry( $entry_id );
                        }
                    } elseif ( $category_id ) {
                        $obj = $mt->db()->fetch_category( $category_id );
                    }
                    if ( isset( $obj ) ) {
                        $title = NULL;
                        if ( $entry_id ) {
                            $title = $obj->title;
                        } elseif ( $category_id ) {
                            $title = $obj->label;
                        }
                        $title = strip_tags( $title );
                        if ( $title ) {
                            require_once ( 'dynamicmtml.util.php' );
                            $title = make_seo_basename( $title, 50 );
                            $url = $request . $title . '/';
                            $app->moved_permanently( $url );
                            exit();
                        }
                    }
                }
            }
        }
    }
*/

}
?>