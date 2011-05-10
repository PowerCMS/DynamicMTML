<?php
    $terms = array( 'blog_id' => $blog_id,
                    'archive_type' => $at );
    $maps = $this->load( 'TemplateMap', $terms );
    if ( $maps ) {
        $entry_author = array();
        include ( 'get_entry_authors.php' );
        if ( $entry_author ) {
            foreach ( $entry_author as $author ) {
                foreach ( $maps as $map ) {
                    $terms = array( 'blog_id' => $blog_id,
                                    'archive_type' => $at,
                                    'templatemap_id' => $map->id,
                                    'author_id' => $author->id );
                    $fileinfos = $this->load( 'FileInfo', $terms );
                    if (! $fileinfos ) {
                        $fileinfo = $this->create_fileinfo_from_map( $blog, $map, array( 'author' => $author ) );
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
                                                $this->run_callbacks( 'build_file', $mt, $ctx, $args, $output );
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
                        unset( $i );
                    }
                }
            }
        }
        if ( $archive_remove_author ) {
            foreach ( $archive_remove_author as $author ) {
                $terms = array( 'blog_id' => $blog_id,
                                'archive_type' => $at,
                                'author_id' => $author->id );
                $fileinfos = $this->load( 'FileInfo', $terms );
                if ( $fileinfos ) {
                    foreach ( $fileinfos as $fileinfo ) {
                        if ( $file = $fileinfo->file_path ) {
                            if ( file_exists( $file ) ) {
                                unlink( $file );
                            }
                        }
                        $fileinfo->Delete();
                    }
                }
            }
        }
    }
?>