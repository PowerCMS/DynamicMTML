<?php
    $condition = array( 'blog' => $blog, 'build_type' => $build_type );
    if ( $limit ) {
        if (! $offset ) $offset = 0;
        $condition[ 'limit' ] = $limit;
        $condition[ 'offset' ] = $offset;
    }
    $do = $this->rebuild_indexes( $condition );
?>