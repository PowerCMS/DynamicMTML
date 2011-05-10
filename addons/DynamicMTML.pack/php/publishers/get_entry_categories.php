<?php
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