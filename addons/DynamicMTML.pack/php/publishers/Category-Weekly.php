<?php
    require_once( 'MTUtil.php' );
    require_once( 'dynamicmtml.util.php' );
    $terms = array( 'blog_id' => $blog_id,
                    'archive_type' => $at );
    $maps = $this->load( 'TemplateMap', $terms );
    if ( $maps ) {
        $categories = array();
        include ( 'get_entry_categories.php' );
        if ( $categories ) {
            foreach( $categories as $category ) {
                $category_id = $category->id;
                $first_ts = NULL;
                $last_ts  = NULL;
                if (! $updated ) {
                    list ( $first_entry, $last_entry ) = $this->start_end_entry( $blog, array( 'category' => $category ) );
                    if ( $first_entry ) {
                        $first_ts = __date2ts( $first_entry->authored_on );
                        $last_ts = __date2ts( $last_entry->authored_on );
                    }
                }
                $rebuild_start_ts = array();
                $delete_start_ts = array();
                if (! $updated ) {
                    if ( $first_ts ) {
                        $first_ts = start_end_week( $first_ts );
                        $first_ts = $first_ts[0];
                        $last_ts = start_end_week( $last_ts );
                        $last_ts = $last_ts[0];
                        $current_ts = $first_ts;
                        do {
                            $ts_epoch = datetime_to_timestamp( $current_ts );
                            $week_number = date( o, $ts_epoch ) . date( W, $ts_epoch );
                            $terms = array( 'blog_id' => $blog_id,
                                            'class'   => 'entry',
                                            'week_number' => $week_number,
                                            'status'  => 2 );
                            $extra = array( 'limit' => 1 );
                            $join = array( 'mt_placement' => array( 'condition' =>
                                           "entry_id=placement_entry_id AND placement_category_id={$category_id}" ) );
                            $extra[ 'join' ] = $join;
                            $count = $this->count( 'Entry', $terms, $extra );
                            if ( $count ) {
                                array_push( $rebuild_start_ts, $current_ts );
                            } else {
                                array_push( $delete_start_ts, $current_ts );
                            }
                            $current_ts = __get_next_week( $current_ts );
                        }
                        while( $current_ts != __get_next_week( $last_ts ) );
                    }
                } else {
                    if ( $entry ) {
                        $changed_entries = array( $entry );
                    } else {
                        $changed_entries = $this->stash( 'changed_entries' );
                    }
                    include( 'Date' . DIRECTORY_SEPARATOR . 'set-rebuild-start-ts.php' );
                }
                $extra_terms = array( 'category_id' => $category->id );
                $fileinfo_object = array( 'category' => $category );
                include( 'Date' . DIRECTORY_SEPARATOR . 'date-based-archive-publisher.php' );
            }
        }
    }
?>