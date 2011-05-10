<?php
    if ( isset( $mt ) ) {
        if (! isset ( $data ) ) {
            $data = $mt->db()->resolve_url( $mt->db()->escape( urldecode( $request ) ), $blog_id, array( 1, 2, 4 ) );
            if ( isset ( $data ) ) {
                $app->stash( 'fileinfo', $data );
            }
        }
        $key = md5( uniqid( "", 1 ) );
        if ( isset( $data ) ) {
            $fileinfo_id = $data->fileinfo_id;
            $script = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'rebuild-from-fi';
            if ( file_exists( $script ) ) {
                $command = '.' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'rebuild-from-fi';
                $command = escapeshellcmd( $command );
                $script_param = escapeshellcmd( $script_param );
                $force_compile = 1;
                $app->stash( 'force_compile', 1 );
                $ctx->force_compile = true;
                $res = exec( "cd $mt_dir;$command $fileinfo_id $key;" );
                if ( file_exists ( "$file.$key" ) ) {
                    $text = file_get_contents( "$file.$key" );
                    unlink ( "$file.$key" );
                }
            }
        } else {
            $client_author = $app->user();
            $client_author_id = NULL;
            if ( isset ( $client_author ) ) {
                $client_author_id = $client_author->id;
            }
            $script_param = " -$file -$blog_id -$client_author_id ";
            $script  = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'build-template-file';
            if ( file_exists( $script ) ) {
                $command = '.' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'build-template-file';
                $command = escapeshellcmd( $command );
                $script_param .= " -$key ";
                $script_param = escapeshellcmd( $script_param );
                $force_compile = 1;
                $app->stash( 'force_compile', 1 );
                $ctx->force_compile = true;
                $res = exec( "cd $mt_dir;$command $script_param;" );
                if ( file_exists ( "$file.$key" ) ) {
                    $text = file_get_contents( "$file.$key" );
                    unlink ( "$file.$key" );
                }
            }
        }
    }
?>