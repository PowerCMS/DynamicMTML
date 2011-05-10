<?php
    $entry_author_id = array();
    $archive_remove_author = array();
    if ( $updated ) {
        if ( $entry ) {
            $changed_entries = array( $entry );
        } else {
            $changed_entries = $this->stash( 'changed_entries' );
        }
        $check_author = array();
        if ( $changed_entries ) {
            foreach ( $changed_entries as $entry ) {
                if (! in_array( $entry->author_id, $entry_author_id ) ) {
                    array_push( $entry_author_id, $entry->author_id );
                    $author = $entry->author();
                    if ( $author && ( $author->status == 1 ) ) {
                        array_push( $check_author, $author );
                    }
                }
            }
            if ( $at == 'Author' ) {
                if ( $check_author ) {
                    foreach ( $check_author as $author ) {
                        $terms = array( 'status' => 2, 'blog_id' => $blog_id, 'author_id' => $author->id );
                        $count = $this->count( 'Entry', $terms );
                        if ( $count ) {
                            array_push( $entry_author, $author );
                        } else {
                            array_push( $archive_remove_author, $author );
                        }
                    }
                }
            } else {
                $entry_author = $check_author;
            }
        }
    } else {
        $terms = array( 'status' => 1 );
        $join  = array( 'mt_permission' => array( 'condition' =>
                        "permission_author_id=author_id AND permission_blog_id={$blog_id}" ) );
        $extra[ 'join' ] = $join;
        if ( $limit ) {
            $extra[ 'limit' ] = $limit;
            $extra[ 'offset' ] = $offset;
        }
        $authors = $this->load( 'Author', $terms, $extra );
        if ( $authors ) {
            foreach ( $authors as $author ) {
                $terms = array( 'status' => 2, 'blog_id' => $blog_id, 'author_id' => $author->id );
                $count = $this->count( 'Entry', $terms );
                if ( $count ) {
                    array_push( $entry_author, $author );
                    array_push( $entry_author_id, $author->id );
                } else {
                    array_push( $archive_remove_author, $author );
                }
            }
        }
    }
?>