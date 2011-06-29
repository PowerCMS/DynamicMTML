<?php
function smarty_block_mtstriptags( $args, $content, $ctx, $repeat ) {
    if ( isset( $args[ 'allowable_tags' ] ) ) {
        $allowable_tags = $args[ 'allowable_tags' ];
    } else {
        if (! $allowable_tags = $ctx->mt->config( 'AllowableTags' ) ) {
            $allowable_tags = '<a>,<br>,<b>,<i>,<p>,<strong>,<em>,<img>,<ul>,<ol>,<li>,<blockquote>,<pre>';
        }
    }
    if ( $allowable_tags ) {
        $content = strip_tags( $content, $allowable_tags );
    } else {
        $content = strip_tags( $content );
    }
    return $content;
}
?>