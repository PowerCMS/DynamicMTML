<?php
    // $extra_terms = array();
    if ( $rebuild_start_ts ) {
        foreach ( $maps as $map ) {
            foreach ( $rebuild_start_ts as $startdate ) {
                $terms = array( 'blog_id' => $blog_id,
                                'archive_type' => $at,
                                'templatemap_id' => $map->id,
                                'startdate' => $startdate );
                if ( $extra_terms ) {
                    foreach ( $extra_terms as $key => $val ) {
                        $terms[ $key ] = $val;
                    }
                }
                $fileinfos = $this->load( 'FileInfo', $terms );
                if (! $fileinfos ) {
                    $create_terms = array( 'startdate' => $startdate );
                    if ( $fileinfo_object ) {
                        foreach ( $fileinfo_object as $key => $val ) {
                            $create_terms[ $key ] = $val;
                        }
                    }
                    $fileinfo = $this->create_fileinfo_from_map( $blog, $map, $create_terms );
                    $fileinfos = array( $fileinfo );
                }
                if ( $fileinfos ) {
                    $i = 0;
                    foreach ( $fileinfos as $fileinfo ) {
                        if (! $i ) {
                            $this->stash( 'fileinfo_tmap:' . $fileinfo->id, $map );
                            if ( $output = $this->rebuild_from_fileinfo( $fileinfo ) ) {
                                $do = 1;
                                if ( $map->build_type == 1 ) {
                                    $file_out = $fileinfo->file_path;
                                    if ( $output != NULL ) {
                                        if ( $this->content_is_updated( $file_out, $output ) ) {
                                            $this->write2file( $file_out, $output );
                                            $args = $this->get_args();
                                            $this->run_callbacks( 'rebuild_file', $mt, $ctx, $args, $output );
                                        }
                                    }
                                }
                            }
                        } else {
                            $file = $fileinfo->file_path;
                            if ( file_exists( $file ) ) {
                                unlink( $file );
                            }
                            $fileinfo->Delete();
                        }
                        $i++;
                    }
                }
            }
        }
    }
    if ( $delete_start_ts ) {
        foreach ( $maps as $map ) {
            $terms = array( 'blog_id' => $blog_id,
                            'archive_type' => $at,
                            'templatemap_id' => $map->id,
                            'startdate' => $delete_start_ts );
            if ( $extra_terms ) {
                foreach ( $extra_terms as $key => $val ) {
                    $terms[ $key ] = $val;
                }
            }
            $fileinfos = $this->load( 'FileInfo', $terms );
            if ( $fileinfos ) {
                $i = 0;
                foreach ( $fileinfos as $fileinfo ) {
                    if ( $path = $fileinfo->file_path ) {
                        if ( file_exists( $path ) ) {
                            unlink( $path );
                        }
                    }
                    $fileinfo->Delete();
                }
            }
        }
    }
    unset( $rebuild_start_ts );
    unset( $delete_start_ts );
    unset( $ts_counter );
?>