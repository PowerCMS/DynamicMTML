<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

    $condition = array( 'blog' => $blog, 'build_type' => $build_type );
    if ( $limit ) {
        if (! $offset ) $offset = 0;
        $condition[ 'limit' ] = $limit;
        $condition[ 'offset' ] = $offset;
    }
    $do = $this->rebuild_indexes( $condition );
?>