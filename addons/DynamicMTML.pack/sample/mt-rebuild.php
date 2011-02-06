<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

//    Rebuild blog by blog_id and archive types.
//
//    /usr/bin/php ./mt-rebuild.php --blog_id 1,2 --at Individual,Page,Index
//
//    --at <comma,separated,archive_types>
//        Optional: Individual,Page,Category,Monthly,...
//    --blog_id <comma,separated,blog_ids>
//        Optional: 1,2,3...

    $mt_dir = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR;
    $blog_ids = NULL;
    $archivetype = NULL;
    if ( isset( $argv ) ) {
        if ( is_array( $argv ) ) {
            $i = 0;
            foreach ( $argv as $arg ) {
                if ( $arg == '--blog_id' ) {
                    $blog_ids = $argv[ $i + 1 ];
                } elseif ( $arg == '--at' ) {
                    $archivetype = $argv[ $i + 1 ];
                }
                $i++;
            }
        }
    }
    if (! file_exists ( $mt_dir . 'mt-config.cgi' ) ) {
        echo "mt-config.cgi was not found.\n";
        return;
    }
    require_once ( $mt_dir . 'php' . DIRECTORY_SEPARATOR . 'mt.php' );
    require_once ( $mt_dir . 'addons' . DIRECTORY_SEPARATOR . 'DynamicMTML.pack' .
                   DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'dynamicmtml.php' );
    $mt_config = $mt_dir . 'mt-config.cgi';
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    $mt = MT::get_instance( NULL, $mt_config );
    $ctx =& $mt->context();
    $app->set_context( $mt, $ctx );
    if (! $blog_ids ) {
        $blogs = $app->load( 'Blog',
                              array( 'class' => array( 'blog', 'website' ) ),
                              array( 'sort' => 'id', 'sort_order' => 'ascend' ) );
    } else {
        $blogs = explode( ',', $blog_ids );
    }
    if (! $blogs ) return;
    $start = mktime();
    foreach ( $blogs as $blog ) {
        if ( is_object( $blog ) ) {
            $blog_id = $blog->id;
        } else {
            $blog_id = trim( $blog );
        }
        $blog_id = intval( $blog_id );
        if ( $blog_id ) {
            $blog_start = mktime();
            $app->init_mt( $mt, $ctx, $blog_id );
            $terms[ 'BlogID' ] = $blog_id;
            if ( $archivetype ) {
                $terms[ 'ArchiveType' ] = $archivetype;
            }
            $app->rebuild( $terms );
            $blog_end = mktime();
            $time = $blog_end - $blog_start;
            echo $app->translate( 'Publish time: [_1] seconds(BlogID: [_2]).' , array( $time, $blog_id ) ) . "\n";
        }
    }
    $end = mktime();
    $time = $end - $start;
    echo $app->translate( 'Publish time: [_1] seconds.' , $time ) . "\n";
?>