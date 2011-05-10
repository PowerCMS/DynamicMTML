<?php
    if ( $changed_entries ) {
        foreach ( $changed_entries as $entry ) {
            if ( $entry->category()->id == $category->id ) {
                $entry_ts = __date2ts( $entry->authored_on );
                if ( preg_match( '/Yearly/i', $at ) ) {
                    $entry_ts = start_end_year( $entry_ts );
                } elseif ( preg_match( '/Monthly/i', $at ) ) {
                    $entry_ts = start_end_month( $entry_ts );
                } elseif ( preg_match( '/Weekly/i', $at ) ) {
                    $entry_ts = start_end_week( $entry_ts );
                } elseif ( preg_match( '/Daily/i', $at ) ) {
                    $entry_ts = start_end_day( $entry_ts );
                }
                $entry_ts = $entry_ts[0];
                if (! in_array( $entry_ts, $rebuild_start_ts ) ) {
                    array_push( $rebuild_start_ts, $entry_ts );
                }
            }
        }
        if ( $changed_entries_ts = $this->stash( 'changed_entries_ts' ) ) {
            foreach ( $changed_entries_ts as $entry_ts ) {
                $entry_ts = __date2ts( $entry_ts );
                if ( preg_match( '/Yearly/i', $at ) ) {
                    $entry_ts = start_end_year( $entry_ts );
                } elseif ( preg_match( '/Monthly/i', $at ) ) {
                    $entry_ts = start_end_month( $entry_ts );
                } elseif ( preg_match( '/Weekly/i', $at ) ) {
                    $entry_ts = start_end_week( $entry_ts );
                } elseif ( preg_match( '/Daily/i', $at ) ) {
                    $entry_ts = start_end_day( $entry_ts );
                }
                $entry_ts = $entry_ts[0];
                if (! in_array( $entry_ts, $rebuild_start_ts ) ) {
                    array_push( $rebuild_start_ts, $entry_ts );
                }
            }
        }
    }
?>