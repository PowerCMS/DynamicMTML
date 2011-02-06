<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

    $entries = array();
    $class = 'entry';
    if ( $at == 'Page' ) {
        $class = 'page';
    }
    if ( $updated ) {
        if ( $entry ) {
            array_push( $entries, $entry );
        } else {
            if ( $class == 'entry' ) {
                $entries = $this->stash( 'changed_entries' );
            } elseif ( $class == 'page' ) {
                $entries = $this->stash( 'changed_pages' );
            }
        }
    } else {
        $terms = array( 'blog_id' => $blog_id, 'status' => 2, 'class' => $class );
        $extra = array();
        if ( $limit ) {
            $extra = array(
                'limit' => $limit,
                'offset' => $offset,
            );
        }
        $entries = $this->load( 'Entry', $terms, $extra );
    }
    if ( $entries ) {
        foreach ( $entries as $entry ) {
            if ( $blog->id == $entry->blog_id ) {
                if ( $this->rebuild_entry( array( 'entry' => $entry,
                                                  'build_type' => $build_type ) ) ) {
                    $do = 1;
                }
            }
        }
    }
?>