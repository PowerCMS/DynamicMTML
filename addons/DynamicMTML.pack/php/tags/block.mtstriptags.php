<?php
function smarty_block_mtstriptags( $args, $content, $ctx, $repeat ) {
    if ( isset( $args[ 'allowable_tags' ] ) ) {
        $allowable_tags = $args[ 'allowable_tags' ];
    } elseif ( !$allowable_tags = $ctx->mt->config( 'AllowableTags' ) ) {
        $allowable_tags = '<a><br><b><i><p><strong><em><img><ul><ol><li><blockquote><pre>';
    }
    $content = strip_tags( $content, $allowable_tags );
    return $content;
}
?>
