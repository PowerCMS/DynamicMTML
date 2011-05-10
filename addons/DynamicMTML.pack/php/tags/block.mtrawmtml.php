<?php
function smarty_block_mtrawmtml( $args, $content, $ctx, $repeat ) {
    $localvars = array( 'uncompiled' );
    $app = $ctx->stash( 'bootstrapper' );
    $build_type = $app->build_type;
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        if ( $build_type == 'rebuild_static' ) {
            $id = $args[ 'id' ];
            if ( $id ) {
                $template = $app->template;
                if ( file_exists( $template ) ) {
                    $resource = file_get_contents( $template );
                    $begin = '<?php $this->_tag_stack[] = array(\'mtrawmtml\', array(\'id\' => "mt:dynamicmtml"';
                    $begin = preg_quote( $begin, '/' );
                    $end = '<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_mtrawmtml';
                    $end = preg_quote( $end, '/' );
                    preg_match( "/$begin.*?\?>(.*?)$end/s", $resource, $result );
                    if ( $result ) {
                        $file = md5( $app->stash( 'file' ) );
                        if (! $app->stash( 'echo_header:' . $file ) ) {
                            preg_match( "/<\?php\srequire_once.*?\?>/s", $resource, $header );
                            if ( $header ) {
                                echo $header[0];
                                $app->stash( 'echo_header:' . $file, 1 );
                            }
                        }
                        $uncompiled = $result[1];
                        $uncompiled = "<MTDynamicMTML>$uncompiled</MTDynamicMTML>";
                        $ctx->stash( 'uncompiled', $uncompiled );
                        echo $uncompiled;
                    }
                }
            }
        }
    } else {
        if ( $ctx->stash( 'uncompiled' ) ) {
            $ctx->restore( $localvars );
            $repeat = false;
        } else {
            return $content;
        }
    }
}
?>