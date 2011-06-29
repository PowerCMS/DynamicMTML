<?php
function smarty_function_mtpowercmsfilesdir ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $powercms_files_dir = NULL;
    if (! $powercms_files_dir = $app->config( 'PowerCMSFilesDir' ) ) {
        $powercms_files_dir = dirname( $app->cfg_file ) . DIRECTORY_SEPARATOR . 'powercms_files';
    }
    $powercms_files_dir = preg_replace( "/DIRECTORY_SEPARATOR$/", '', $powercms_files_dir );
    return $powercms_files_dir;
}
?>