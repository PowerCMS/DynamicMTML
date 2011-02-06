<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

    if ( $updated ) {
        $changed_categories = $this->stash( 'changed_categories' );
        if ( isset( $changed_categories ) ) {
            foreach ( $changed_categories as $category ) {
                if ( $blog->id == $category->blog_id ) {
                    if ( $this->rebuild_category( array( 'category' => $category,
                                                         'build_type' => $build_type ) ) ) {
                        $do = 1;
                    }
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
        $categories = $this->load( 'Category', array( 'blog_id' => $blog->id, 'class' => 'category' ), $extra );
        foreach ( $categories as $category ) {
            if ( $this->rebuild_category( array( 'category' => $category,
                                                 'build_type' => $build_type ) ) ) {
                $do = 1;
            }
        }
    }
?>