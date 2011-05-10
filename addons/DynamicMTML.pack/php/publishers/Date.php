<?php
    require_once( 'MTUtil.php' );
    require_once( 'dynamicmtml.util.php' );
    $terms = array( 'blog_id' => $blog_id,
                    'archive_type' => $at );
    $maps = $this->load( 'TemplateMap', $terms );
    if ( $maps ) {
        $first_ts = NULL;
        $last_ts  = NULL;
        if (! $updated ) {
            list ( $first_entry, $last_entry ) = $this->start_end_entry( $blog );
            if ( $first_entry ) {
                $first_ts = __date2ts( $first_entry->authored_on );
                $last_ts = __date2ts( $last_entry->authored_on );
            }
        }
        $rebuild_start_ts = array();
        $delete_start_ts = array();
        $ts_counter = 0;
        $archiver = $this->plugin_path . 'publishers' . DIRECTORY_SEPARATOR . 'Date' . DIRECTORY_SEPARATOR . $at . '.php';
        if ( file_exists( $archiver ) ) {
            require_once( $archiver );
        }
    }
?>