<?php
function smarty_function_mtblogfilesmatchdirective ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $blog = $ctx->stash( 'blog' );
    $exclude_extension = $blog->exclude_extension;
    if (! $exclude_extension ) {
        $exclude_extension = 'php,cgi,fcgi';
    }
    $exclude_extension = preg_replace( '/\s/', '', $exclude_extension );
    $lc = strtolower ( $exclude_extension );
    $uc = strtoupper ( $exclude_extension );
    $extensions = explode( ',', $lc );
    $extensions_uc = explode( ',', $uc );
    $extensions = array_merge( $extensions, $extensions_uc );
    $exclude_extension = implode( '|', $extensions );
    $FilesMatch = '<FilesMatch .*\.(?!' . $exclude_extension . ')>';
    return $FilesMatch;
}
?>
