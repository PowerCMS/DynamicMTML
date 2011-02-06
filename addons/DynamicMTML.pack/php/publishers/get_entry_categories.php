<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

    if ( $updated ) {
        $changed_categories = $this->stash( 'changed_categories' );
        if ( isset( $changed_categories ) ) {
            foreach ( $changed_categories as $category ) {
                if ( $blog->id == $category->blog_id ) {
                    array_push( $categories, $category );
                }
            }
        }
    } else {
        $extra = array();
        if ( $limit ) {
            $extra = array(
                'limit' => $limit,
                'offset' => $offset,
            );
        }
        $categories = $this->load( 'Category', array( 'blog_id' => $blog->id ), $extra );
    }
?>