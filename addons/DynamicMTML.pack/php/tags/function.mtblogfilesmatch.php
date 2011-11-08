<?php
function smarty_function_mtblogfilesmatch ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $blog = $ctx->stash( 'blog' );
    $dynamic_extension = $blog->dynamic_extension;
    if (! $dynamic_extension ) {
        $dynamic_extension = 'html,mtml';
    }
    $dynamic_extension = preg_replace( '/\s/', '', $dynamic_extension );
    $lc = strtolower ( $dynamic_extension );
    $uc = strtoupper ( $dynamic_extension );
    $extensions = explode( ',', $lc );
    $extensions_uc = explode( ',', $uc );
    $extensions = array_merge( $extensions, $extensions_uc );
    $dynamic_extension = implode( '|', $extensions );
    $FilesMatch = $dynamic_extension;
    return $FilesMatch;
}
?>
