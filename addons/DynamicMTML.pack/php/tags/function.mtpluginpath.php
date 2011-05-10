<?php
function smarty_function_mtpluginpath ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $component = $args[ 'component' ];
    $folder = $args[ 'folder' ];
    if ( $folder ) {
        $option = explode( ',', $folder );
    }
    if ( $component ) {
        $component = strtolower( $component );
        $plugins_directories = $app->stash( 'plugins_directories' );
        if ( $plugins_directories ) {
            if ( isset( $plugins_directories[ $component ] ) ) {
                $component_path = $plugins_directories[ $component ];
                if ( is_array( $option ) ) {
                    foreach( $option as $opt ) {
                        $component_path .= DIRECTORY_SEPARATOR . $opt;
                    }
                }
                $component_path .= DIRECTORY_SEPARATOR;
                return $component_path;
            }
        }
    }
    return '';
}
?>