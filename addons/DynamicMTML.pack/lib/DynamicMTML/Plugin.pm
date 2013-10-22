package DynamicMTML::Plugin;

use strict;
# use lib qw( addons/DynamicMTML.pack/lib );
use PowerCMS::Util qw( get_children_files powercms_files_dir plugin_template_path
                       powercms_files_dir_path site_path make_dir remove_item
                       build_tmpl write2file read_from_file path2url );

our $plugin_dynamicmtml = MT->component( 'DynamicMTML' );

sub _build_page {
    my ( $cb, %args ) = @_;
    my $file  = $args{ File };
    if ( my $key = MT->request( 'dynamicmtml_output_file_key' ) ) {
        $$file = $$file . '.' . $key;
    }
    return 1;
}

sub _build_file_filter {
    my ( $cb, %args ) = @_;
    my $template  = $args{ Template };
    my $file  = $args{ File };
    my $blog  = $args{ Blog };
    if ( my $key = MT->request( 'dynamicmtml_output_file_key' ) ) {
        if (-f $file ) {
            my $src = read_from_file( $file );
            $$template->text( $src );
        }
    }
    my $ctx  = $args{ Context };
    $ctx->stash( 'current_archive_file', $file );
    my $url = path2url( $file, $blog );
    $ctx->stash( 'current_archive_url', $url );
    $ctx->stash( 'is_file', 1 );
    return 1;
}

sub _build_file {
    my ( $cb, %args ) = @_;
    my $app = MT->instance();
    my $file  = $args{ File };
    my $blog  = $args{ Blog };
    my $fi    = $args{ FileInfo };
    my $html  = $args{ Content };
    if ( defined $blog ) {
        my $fmgr = $blog->file_mgr;
        if ( $$html && $$html =~ /<\${0,1}mt/i ) {
            require Digest::MD5;
            require File::Spec;
            my $path;
            if ( $blog->dynamic_cache ) {
                $path = Digest::MD5::md5_hex( $file );
                my $cache = 'blog_' . $blog->id . '_' . $path;
                my $powercms_files_dir = powercms_files_dir();
                if ( $powercms_files_dir ) {
                    my $cache_dir = File::Spec->catdir( $powercms_files_dir, 'cache' );
                    if ( -d $cache_dir ) {
                        my @caches = get_children_files( $cache_dir, "/$cache/" );
                        for my $cache ( @caches ) {
                            $fmgr->delete( $cache );
                        }
                    }
                }
            }
            if (! $app->config( 'DynamicForceCompile' ) ) {
                my $templates_c = File::Spec->catdir( site_path( $blog ), 'templates_c' );
                if ( -d $templates_c ) {
                    if (! $path ) {
                        $path = Digest::MD5::md5_hex( $file );
                    }
                    my $search = '_' . $path . '_';
                    my @template = get_children_files( $templates_c, "/$search/" );
                    for my $tmpl ( @template ) {
                        $fmgr->delete( $tmpl );
                    }
                }
            }
        }
    }
    return 1;
}

sub _build_dynamic {
    # for MT5.02 or later.
    my ( $cb, %args ) = @_;
    my $app = MT->instance();
    my $file  = $args{ File };
    my $blog  = $args{ Blog };
    my $fi    = $args{ FileInfo };
    if ( defined $blog ) {
        return 1 unless $blog->dynamic_cache;
        my $fmgr = $blog->file_mgr;
        require Digest::MD5;
        require File::Spec;
        my $path = Digest::MD5::md5_hex( $file );
        my $cache = 'blog_' . $blog->id . '_' . $path;
        my $powercms_files_dir = powercms_files_dir();
        return 1 unless $powercms_files_dir;
        my $cache_dir = File::Spec->catdir( $powercms_files_dir, 'cache' );
        if ( -d $cache_dir ) {
            my @caches = get_children_files( $cache_dir, "/$cache/" );
            for my $cache ( @caches ) {
                $fmgr->delete( $cache );
            }
        }
    }
    return 1;
}

sub _disable_dynamicmtml {
    my ( $cb, $app, $obj, $original ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    $obj->dynamic_cache( $app->param( 'dynamic_cache' ) );
    $obj->dynamic_conditional( $app->param( 'dynamic_conditional' ) );
    $obj->save or die $obj->errstr;
    my $mtview = MT->config->DynamicSiteBootstrapper || '.mtview.php';
    if (! $obj->dynamic_mtml ) {
        if ( defined( $original ) && (! $original->dynamic_mtml ) ) {
            return 1;
        }
        my $tmpl_path = plugin_template_path( $plugin_dynamicmtml );
        my $blog = $app->blog;
        my %args = ( blog => $blog );
        my $htaccess_out = File::Spec->catdir( site_path( $blog ), '.htaccess' );
        my $dynamicmtml_out = File::Spec->catdir( site_path( $blog ), $mtview );
        require MT::FileMgr;
        require MT::Template;
        my $rebuild;
        my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
        if ( $fmgr->exists( $htaccess_out ) ) {
            my $htaccess_tmpl = MT::Template->load( { blog_id => $blog->id,
                                                      outfile => '.htaccess',
                                                      identifier => 'htaccess',
                                                      type => 'index',
                                                      } );
            if ( $htaccess_tmpl ) {
                $app->rebuild_indexes( Blog => $blog, Template => $htaccess_tmpl );
                $rebuild = 1;
            } else {
                my $htaccess = File::Spec->catdir( $tmpl_path, '_htaccess.tmpl' );
                my $tmpl = read_from_file( $htaccess );
                $htaccess = build_tmpl( $app, $tmpl, \%args );
                my $contents = read_from_file( $htaccess_out );
                if (! $fmgr->content_is_updated( $contents, \$htaccess ) ) {
                    if (! $fmgr->delete( $htaccess_out ) ) {
                        # $app->add_return_arg( no_remove_htaccess => 1 );
                        # But MT create a .htaccess.
                    }
                } else {
                    if ( $contents =~ s![\r\n]##\sDynamicMTML.*?/DynamicMTML\s##[\r\n]!!si ) {
                        if (! write2file( $htaccess_out, $contents ) ) {
                            $app->add_return_arg( no_overwrite_htaccess => 1 );
                        }
                    } else {
                        $app->add_return_arg( no_overwrite_htaccess => 1 );
                    }
                }
            }
        }
        if ( $fmgr->exists( $dynamicmtml_out ) ) {
            my $mtview_tmpl = MT::Template->load( { blog_id => $blog->id,
                                                    outfile => $mtview,
                                                    identifier => 'dynamic_mtml_bootstrapper',
                                                    type => 'index',
                                                    } );
            if ( $mtview_tmpl ) {
                $app->rebuild_indexes( Blog => $blog, Template => $mtview_tmpl );
                $rebuild = 1;
            } else {
                my $dynamicmtml = File::Spec->catdir( $tmpl_path, 'mtview_php.tmpl' );
                my $contents = read_from_file( $dynamicmtml_out );
                my $tmpl = read_from_file( $dynamicmtml );
                $dynamicmtml = build_tmpl( $app, $tmpl, \%args );
                if (! $fmgr->content_is_updated( $contents, \$dynamicmtml ) ) {
                    $fmgr->delete( $dynamicmtml_out );
                } else {
                    if ( $contents =~ s![\r\n]##\sDynamicMTML.*?/DynamicMTML\s##[\r\n]!!si ) {
                        if ( $contents eq '<?php?>' ) {
                            $fmgr->delete( $dynamicmtml_out );
                        } else {
                            if (! write2file( $dynamicmtml_out, $contents ) ) {
                                $app->add_return_arg( no_overwrite_mtview => 1 );
                            }
                        }
                    } else {
                        $app->add_return_arg( no_overwrite_mtview => 1 );
                    }
                }
            }
        }
        my $dynamic;
        if ( (! $fmgr->exists( $htaccess_out ) ) || (! $fmgr->exists( $dynamicmtml_out ) ) ) {
            require MT::Template;
            $dynamic = MT::Template->count( { blog_id => $blog->id, build_type => 3 } );
            if (! $dynamic ) {
                require MT::TemplateMap;
                $dynamic = MT::TemplateMap->count( { blog_id => $blog->id, build_type => 3 } );
            }
            if (! $dynamic ) {
                $dynamic = $blog->is_dynamic;
            }
        }
        if ( $dynamic ) {
            if (! $rebuild ) {
                require MT::CMS::Blog;
                MT::CMS::Blog::prepare_dynamic_publishing( $cb, $blog,
                        $obj->dynamic_cache, $obj->dynamic_conditional, $blog->site_path, $blog->site_url );
            }
        } else {
            if ( (! $fmgr->exists( $htaccess_out ) ) && (! $fmgr->exists( $dynamicmtml_out ) ) ) {
                my $templates_c = File::Spec->catdir( site_path( $blog ), 'templates_c' );
                if (-d $templates_c ) {
                    remove_item( $templates_c );
                }
            }
            # TODO::Remove chache directory?
        }
    }
    return 1;
}

sub _post_save_blog {
    my ( $cb, $app, $obj, $original ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    my $blog = $app->blog;
    if ( $blog->id ne $obj->id ) { # is new
        $blog = $obj;
    }
    my $component = MT->component( 'DynamicMTML' );
    my $version = $component->version;
    if (! $obj->dynamic_mtml ) {
        return 1;
    } else {
        if ( $obj->search_cache ) {
            unless ( my $powercms_files_dir = powercms_files_dir() ) {
                $app->add_return_arg( no_search_cache_path => 1 );
            }
        }
        require File::Spec;
        require MT::Template;
        my $mtview = MT->config->DynamicSiteBootstrapper || '.mtview.php';
        my $templates_c = File::Spec->catdir( site_path( $obj ), 'templates_c' );
        my $cache = File::Spec->catdir( site_path( $obj ), 'cache' );
        make_dir( $templates_c ) if ! -e $templates_c;
        if (! -d $templates_c ) {
            $app->add_return_arg( no_cachedir => 1 );
        }
        if (! -w $templates_c ) {
            $app->add_return_arg( no_writecache => 1 );
        }
        make_dir( $cache ) if ! -e $cache;
        if (! -d $cache ) {
            $app->add_return_arg( no_cache_path => 1 );
        }
        if (! -w $cache ) {
            $app->add_return_arg( no_write_cache_path => 1 );
        }
        my $plugin_dynamicmtml = MT->component( 'DynamicMTML' );
        my $tmpl_path = plugin_template_path( $plugin_dynamicmtml );
        my $htaccess_tmpl = MT::Template->load( { blog_id => $blog->id,
                                                  outfile => '.htaccess',
                                                  identifier => 'htaccess',
                                                  type => 'index',
                                                  } );
        my $tmpl;
        my $old_version; my $old_file; my $update;
        my %args = ( blog => $blog );
        if ( $htaccess_tmpl ) {
            $app->rebuild_indexes( Blog => $blog, Template => $htaccess_tmpl );
        } else {
            my $htaccess = File::Spec->catdir( $tmpl_path, '_htaccess.tmpl' );
            my $tmpl = read_from_file( $htaccess );
            my $htaccess_out = File::Spec->catdir( site_path( $blog ), '.htaccess' );
            $htaccess = build_tmpl( $app, $tmpl, \%args );
            if ( -f $htaccess_out ) {
                my $contents = read_from_file( $htaccess_out );
                if ( $contents !~ /^\s*Rewrite(Cond|Engine|Rule)\b/m ) {
                    # Add
                    $contents .= "\n\n" . $htaccess;
                    if (! write2file( $htaccess_out, $contents ) ) {
                        $app->add_return_arg( no_overwrite_htaccess => 1 );
                    }
                } elsif ( $contents =~ /^##\sDynamicMTML/m ) {
                    # Update
                    if ( $contents =~ m!<version>(.*?)</version>!i ) {
                        $old_version = $1;
                    }
                    if ( $contents =~ m!<bootstrapper>(.*?)</bootstrapper>!i ) {
                        $old_file = $1;
                    }
                    if (! $old_file || ( $mtview ne $old_file ) ) {
                        $update = 1;
                    }
                    if (! $old_version || ( $old_version < $version ) ) {
                        $update = 1;
                    }
                    if ( $update ) {
                        if ( $contents =~ s!##[\r\n]##\sDynamicMTML.*?/DynamicMTML\s##[\r\n]!$htaccess!si ) {
                            if (! write2file( $htaccess_out, $contents ) ) {
                                $app->add_return_arg( no_overwrite_htaccess => 1 );
                            }
                        } else {
                            # Error
                            $app->add_return_arg( no_overwrite_htaccess => 1 );
                        }
                    }
                } else {
                    # Error
                    $app->add_return_arg( no_overwrite_htaccess => 1 );
                }
            } else {
                # Generate
                if (! write2file( $htaccess_out, $htaccess ) ) {
                    $app->add_return_arg( no_generate_htaccess => 1 );
                }
            }
        }
        my $mtview_tmpl = MT::Template->load( { blog_id => $blog->id,
                                                outfile => $mtview,
                                                identifier => 'dynamic_mtml_bootstrapper',
                                                type => 'index',
                                                } );
        if ( $mtview_tmpl ) {
            $app->rebuild_indexes( Blog => $blog, Template => $mtview_tmpl );
        } else {
            my $dynamicmtml = File::Spec->catdir( $tmpl_path, 'mtview_php.tmpl' );
            $tmpl = read_from_file( $dynamicmtml );
            $dynamicmtml = build_tmpl( $app, $tmpl, \%args );
            if (! $old_file || ( $mtview ne $old_file ) ) {
                my $old_mtview = File::Spec->catdir( site_path( $blog ), $old_file );
                if (-f $old_mtview ) {
                    $fmgr->delete( $old_mtview );
                }
            }
            my $dynamicmtml_out = File::Spec->catdir( site_path( $blog ), $mtview );
            if ( $fmgr->exists( $dynamicmtml_out ) ) {
                if (! $fmgr->content_is_updated( $dynamicmtml_out, \$dynamicmtml ) ) {
                    return 1;
                } else {
                    my $contents = read_from_file( $dynamicmtml_out );
                    if (! $contents =~ /^##\sDynamicMTML/m ) {
                        $app->add_return_arg( no_overwrite_mtview => 1 );
                        return 1;
                    }
                }
            }
            if (! write2file( $dynamicmtml_out, $dynamicmtml ) ) {
                $app->add_return_arg( no_generate_mtview => 1 );
            }
        }
    }
    return 1;
}

sub _post_save_template {
    my ( $cb, $app, $obj, $original ) = @_;
    if ( my $blog = $app->blog ) {
        my $fmgr = $blog->file_mgr;
        my $type = $obj->type;
        if ( ( $type eq 'index' ) || ( $type eq 'archive' )
            || ( $type eq 'individual' ) || ( $type eq 'page' ) ) {
            require Digest::MD5;
            require File::Spec;
            my $templates_c = File::Spec->catdir( site_path( $blog ), 'templates_c' );
            if ( -d $templates_c ) {
                my $search = '_mtml_tpl_id_' . $obj->id . '\.php';
                my @template = get_children_files( $templates_c, "/$search/" );
                for my $tmpl ( @template ) {
                    $fmgr->delete( $tmpl );
                }
            }
        }
    }
    return 1;
}

sub _cfg_prefs_param {
    my ( $cb, $app, $param, $tmpl ) = @_;
    my $pointer_node = $tmpl->getElementById( 'dynamic_publishing_options' );
    return unless $pointer_node;
    my $component = MT->component( 'DynamicMTML' );
    my $mtview = MT->config->DynamicSiteBootstrapper || '.mtview.php';
    if ( $app->param( 'no_overwrite_htaccess' ) ) {
        if ( $param->{ error } ) { $param->{ error } .= "<br />"; }
        $param->{ error } = 
                    $component->translate( $component->translate( 'Error: Movable Type cannot overwrite the file <code>[_1]</code>. Please check the file <code>[_1]</code> underneath your blog directory.', '.htaccess' ) );
    }
    if ( $app->param( 'no_overwrite_mtview' ) ) {
        if ( $param->{ error } ) { $param->{ error } .= "<br />"; }
        $param->{ error } .= 
            $component->translate( $component->translate( 'Error: Movable Type cannot overwrite the file <code>[_1]</code>. Please check the file <code>[_1]</code> underneath your blog directory.', $mtview ) );
    }
    if ( $app->param( 'no_generate_htaccess' ) ) {
        if ( $param->{ error } ) { $param->{ error } .= "<br />"; }
        $param->{ error } .= 
            $component->translate( $component->translate( 'Error: Movable Type cannot write to the file [_1]. Please check the permissions for the file <code>[_1]</code> underneath your blog directory.', '.htaccess' ) );
    }
    if ( $app->param( 'no_generate_mtview' ) ) {
        if ( $param->{ error } ) { $param->{ error } .= "<br />"; }
        $param->{ error } .= 
            $component->translate( $component->translate( 'Error: Movable Type cannot write to the file [_1]. Please check the permissions for the file <code>[_1]</code> underneath your blog directory.', $mtview ) );
    }
    if ( $app->param( 'no_search_cache_path' ) ) {
        if ( $param->{ error } ) { $param->{ error } .= "<br />"; }
        require File::Spec;
        my $cache_dir = File::Spec->catdir( powercms_files_dir_path(), 'cache' );
        $param->{ error } .= 
            $component->translate( $component->translate( 'Error: Movable Type cannot write to the search cache directory.<br />Please check the permissions for the directory called <code>[_1]</code>.', $cache_dir ) );
    }
    $param->{ dynamic_enabled } = 1;
    $param->{ hide_build_option } = 0;
    $param->{ dynamic_caching } = $param->{ dynamic_cache };
    my $inner =<<'MTML';
    <__trans_section component="DynamicMTML">
    <ul>
        <li>
            <label><input type="checkbox" id="dynamic_mtml" name="dynamic_mtml" <mt:if name="dynamic_mtml">checked="checked"</mt:if> value="1" />
            <__trans phrase="Enable DynamicMTML (Create the file <code>.htaccess</code> underneath your blog directory)"></label>
        </li>
        <li>
            <input type="checkbox" id="search_cache" name="search_cache" <mt:if name="search_cache">checked="checked"</mt:if> value="1" />
            <label for="search_cache"><__trans phrase="Enable DynamicMTML Cache"></label>
            ( <label for="search_cache_expiration"><__trans phrase="Cache expiration"></label>
            <input
                type="text" name="search_cache_expiration" id="search_cache_expiration" style="width:100px"
                value="<$mt:var name="search_cache_expiration" escape="html"$>" mt:watch-change="1" /> )
        </li>
        <li>
            <input type="checkbox" id="search_conditional" name="search_conditional" <mt:if name="search_conditional">checked="checked"</mt:if> value="1" />
            <label for="search_conditional"><__trans phrase="Enable Conditional GET on DynamicMTML"></label>
        </li>
    </ul>
    <input type="hidden" name="dynamic_mtml" value="0" />
    <input type="hidden" name="dynamic_cache" value="0" />
    <input type="hidden" name="dynamic_conditional" value="0" />
    <input type="hidden" name="search_cache" value="0" />
    <input type="hidden" name="search_conditional" value="0" />
    </__trans_section>
MTML
    my $dynamic_search_options_node = $tmpl->createElement( 'app:setting', {
        id => 'dynamic_search_options',
        label => $plugin_dynamicmtml->translate( 'Dynamic Search Options' ),
        show_label => 0,
    } );
    $dynamic_search_options_node->innerHTML( $inner );
    $tmpl->insertAfter( $dynamic_search_options_node, $pointer_node );
    $inner =<<'MTML';
        <input type="text" name="dynamic_extension" id="dynamic_extension" class="full-width" value="<mt:var name="dynamic_extension" escape="html">" size="30" />
MTML
    my $dynamic_extension_node = $tmpl->createElement( 'app:setting', {
        id => 'dynamic_extension',
        label => $plugin_dynamicmtml->translate( 'Dynamic Extensions' ),
        show_label => 1,
    } );
    $dynamic_extension_node->innerHTML( $inner );
    $tmpl->insertAfter( $dynamic_extension_node, $dynamic_search_options_node );
    $inner =<<'MTML';
        <input type="text" name="exclude_extension" id="exclude_extension" class="full-width" value="<mt:var name="exclude_extension" escape="html">" size="30" />
MTML
    my $exclude_extension_node = $tmpl->createElement( 'app:setting', {
        id => 'exclude_extension',
        label => $plugin_dynamicmtml->translate( 'Exclude Extensions' ),
        show_label => 1,
    } );
    $exclude_extension_node->innerHTML( $inner );
    $tmpl->insertAfter( $exclude_extension_node, $dynamic_extension_node );
    $inner =<<'MTML';
        <input type="text" name="index_files" id="index_files" class="full-width" value="<mt:var name="index_files" escape="html">" size="30" />
MTML
    my $index_files_node = $tmpl->createElement( 'app:setting', {
        id => 'index_files',
        label => $plugin_dynamicmtml->translate( 'Directory Index' ),
        show_label => 1,
    } );
    $index_files_node->innerHTML( $inner );
    $tmpl->insertAfter( $index_files_node, $exclude_extension_node );
}

sub _cfg_prefs_source {
    my ( $cb, $app, $tmpl ) = @_;
    $$tmpl =~ s/(id="dynamic_cache")/$1 value="1"/;
    $$tmpl =~ s/(id="dynamic_conditional")/$1 value="1"/;
}

sub _list_template_source {
    my ( $cb, $app, $tmpl ) = @_;
    my $old = quotemeta( '<li><a href="<mt:var name="script_url">?__mode=list_template&amp;filter_key=backup_templates&amp;blog_id=<mt:var name="blog_id">" class="icon-left icon-related"><__trans phrase="Template Backups"></a></li>' );
    my $blog_id;
    my $return_args = '__mode=list_template&amp;blog_id=';
    if ( $app->blog ) {
        $blog_id = $app->blog->id;
        $return_args .= $blog_id;
    }
    my $link = $app->base . $app->uri(
                    mode => 'install_dynamic_mtml',
                    args => { blog_id => $blog_id,
                              return_args => $return_args,
                              magic_token => $app->current_magic(), } );
    my $label = $plugin_dynamicmtml->translate( 'Install DynamicMTML' );
    my $new = "<li><a class=\"icon-left icon-related\" href=\"$link\">$label</a><li>" if $app->blog;
    my $link2 = $app->base . $app->uri(
                    mode => 'flush_dynamic_cache',
                    args => { blog_id => $blog_id,
                              return_args => $return_args,
                              magic_token => $app->current_magic(), } );
    my $label2 = $plugin_dynamicmtml->translate( 'Flush Dynamic Cache' );
    my $new2 = "<li><a class=\"icon-left icon-related\" href=\"$link2\">$label2</a><li>";
    $$tmpl =~ s/($old)/$new$new2$1/;
}

sub _list_template_param {
    my ( $cb, $app, $param, $tmpl ) = @_;
    my $pointer_node = $tmpl->getElementById( 'saved-settings' );
    if ( $app->param( 'flush_dynamic_cache' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Flush Dynamic Cache was successful.' ) );
    } elsif ( $app->param( 'not_flush_dynamic_cache' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Cache file was not found.' ) );
    } elsif ( $app->param( 'no_cache_directory' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Files for PowerCMS directory unexists. Please make directory [_1], and give enough permission to write from web server.',
                                                      powercms_files_dir_path() ) );
    }
    if ( $app->param( 'installed_dynamicmtml' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Install DynamicMTML was successful.' ) );
    } elsif ( $app->param( 'not_installed_dynamicmtml' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Install DynamicMTML failed.' ) );
    } elsif ( $app->param( 'flush_dynamic_cache' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Flush Dynamic Cache was successful.' ) );
    } elsif ( $app->param( 'not_flush_dynamic_cache' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Cache file was not found.' ) );
    } elsif ( $app->param( 'no_cache_directory' ) ) {
        $param->{ saved } = 1;
        $pointer_node->innerHTML( $plugin_dynamicmtml->translate( 'Files for PowerCMS directory unexists. Please make directory [_1], and give enough permission to write from web server.',
                                                      powercms_files_dir_path() ) );
    }
}

sub _edit_template_param {
    my ( $cb, $app, $param, $tmpl ) = @_;
    if ( $app->config( 'DisableCompilerWarnings' ) ) {
        $param->{ error } = 0;
    }
}

sub _view_log {
    my ( $cb, $app, $param, $tmpl ) = @_;
    my $class_loop = $param->{ class_loop };
    push @$class_loop, { class_name => 'dynamic', class_label => MT->translate( 'Dynamic Error' ) };
    $param->{ class_loop } = $class_loop;
}

sub _footer_source {
    my ( $cb, $app, $tmpl ) = @_;
    my $id = MT->component(__PACKAGE__ =~ /^([^:]+)/)->id;
    $$tmpl =~ s{(<__trans phrase="http://www\.sixapart\.com/movabletype/">)}
               {<mt:if name="id" eq="$id"><__trans phrase="http://alfasado.net/"><mt:else>$1</mt:if>};
}

sub _cb_tp {
    my ( $cb, $app, $param, $tmpl ) = @_;
    my $top_nav_loop = $param->{ top_nav_loop } or return 1;
    for my $top_nav ( @$top_nav_loop ) {
        if ( $top_nav->{ id } eq 'tools' ) {
            my $sub_nav_loop = $top_nav->{ sub_nav_loop };
            for my $sub_nav ( @$sub_nav_loop ) {
                if ( $sub_nav->{ 'link' } =~ /flush_dynamic_cache/ ) {
                    $sub_nav->{ 'link' } .= '&magic_token=' . $app->current_magic();
                }
            }
        }
    }
}

1;
