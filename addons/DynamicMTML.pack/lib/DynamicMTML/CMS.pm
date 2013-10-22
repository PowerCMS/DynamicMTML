package DynamicMTML::CMS;

use strict;
use warnings;
# use lib qw( plugins/DynamicMTML.pack/lib );
use PowerCMS::Util qw( get_children_files powercms_files_dir powercms_files_dir_path
                       is_user_can site_path register_templates_to make_dir );

our $plugin_dynamicmtml = MT->component( 'DynamicMTML' );

sub _install_dynamic_mtml {
    my $app = shift;
    $app->validate_magic or
        return $app->trans_error( 'Permission denied.' );
    require File::Spec;
    my $do = 1;
    my $mtview = MT->config->DynamicSiteBootstrapper || '.mtview.php';
    if ( my $blog = $app->blog ) {
        unless ( is_user_can( $blog, $app->user, 'edit_templates' ) ) {
            return $app->trans_error( 'Permission denied.' );
        }
        if (! make_dir( File::Spec->catdir( site_path( $blog ), 'templates_c' ) ) ) {
            $do = 0;
        }
        if (! make_dir( File::Spec->catdir( site_path( $blog ), 'cache' ) ) ) {
            $do = 0;
        }
        if (! powercms_files_dir() ) {
            $do = 0;
        }
        require MT::Template;
        my $bootstrapper = MT::Template->load( { blog_id => $blog->id,
                                                 outfile => $mtview } );
        if (! $bootstrapper ) {
            my %template = (
                dynamic_mtml_bootstrapper => {
                    name => 'DynamicMTML Bootstrapper',
                    path => 'mtview_php.tmpl',
                    type => 'index',
                    outfile => $mtview,
                    rebuild_me => 1,
                    build_type => 1,
                },
            );
            if (! register_templates_to( $blog->id, $plugin_dynamicmtml, \%template ) ) {
                $do = 0;
            }
        }
        my $htacess = MT::Template->load( { blog_id => $blog->id,
                                            outfile => '.htaccess' } );
        if (! $htacess ) {
            my %template = (
                dynamic_mtml_htaccess => {
                    name => 'DynamicMTML .htaccess',
                    path => '_htaccess.tmpl',
                    type => 'index',
                    outfile => '.htaccess',
                    rebuild_me => 1,
                    build_type => 1,
                    identifier => 'htaccess',
                },
            );
            if (! register_templates_to( $blog->id, $plugin_dynamicmtml, \%template ) ) {
                $do = 0;
            }
        }
    }
    if ( $do ) {
        $app->add_return_arg( installed_dynamicmtml => 1 );
    } else {
        $app->add_return_arg( not_installed_dynamicmtml => 1 );
    }
    $app->call_return();
}

sub _flush_dynamic_cache {
    my $app = shift;
    $app->validate_magic or
        return $app->trans_error( 'Permission denied.' );
    my $do;
    require File::Spec;
    if (! _dynamic_permission() ) {
        return $app->trans_error( 'Permission denied.' );
    }
    my %param;
    $param{ page_title } = $plugin_dynamicmtml->translate( 'Flush Dynamic Cache' );
    $app->{ plugin_template_path } = File::Spec->catdir( $plugin_dynamicmtml->path, 'tmpl' );
    my $tmpl = 'system_msg.tmpl';
    my $powercms_files_dir = powercms_files_dir();
    my $statusmsg;
    if (! $powercms_files_dir ) {
        if ( $app->param( 'return_args' ) ) {
            $app->add_return_arg( no_cache_directory => 1 );
            $app->call_return();
        } else {
            $statusmsg = $plugin_dynamicmtml->translate( 'Files for PowerCMS directory unexists. Please make directory [_1], and give enough permission to write from web server.',
                                             powercms_files_dir_path() );
            $param{ 'statusmsg' } = $statusmsg;
            return $app->build_page( $tmpl, \%param );
        }
    }
    my $cache_dir = File::Spec->catdir( $powercms_files_dir, 'cache' );
    my @blogs;
    if ( $app->blog && $app->blog->dynamic_cache ) {
        push ( @blogs, $app->blog );
    } else {
        require MT::Blog;
        @blogs = MT::Blog->load( { class => '*', dynamic_cache => 1 } );
    }
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    for my $blog ( @blogs ) {
        next unless _dynamic_permission( $blog );
        my $search = 'blog_id_' . $blog->id;
        if ( -d $cache_dir ) {
            my @caches = get_children_files( $cache_dir, "/$search/" );
            for my $cache ( @caches ) {
                $fmgr->delete( $cache );
                $do = 1;
            }
        }
        my $templates_c = File::Spec->catdir( site_path( $blog ), 'templates_c' );
        if ( -d $templates_c ) {
            my @template = get_children_files( $templates_c );
            for my $tmpl ( @template ) {
                $fmgr->delete( $tmpl );
                $do = 1;
            }
        }
    }
    if ( $app->param( 'return_args' ) ) {
        if ( $do ) {
            $app->add_return_arg( flush_dynamic_cache => 1 );
        } else {
            $app->add_return_arg( not_flush_dynamic_cache => 1 );
        }
        $app->call_return();
    } else {
        if ( $do ) {
            $statusmsg = $plugin_dynamicmtml->translate( 'Flush Dynamic Cache was successful.' );
        } else {
            $statusmsg = $plugin_dynamicmtml->translate( 'Cache file was not found.' );
        }
        $param{ statusmsg } = $statusmsg;
    }
    return $app->build_page( $tmpl, \%param );
}

sub _dynamic_permission {
    my $blog = shift;
    my $app = MT->instance();
    $blog = $app->blog unless $blog;
    my $user = $app->user; return 1 if $user->is_superuser;
    if ( $blog && $blog->dynamic_cache ) {
        if ( is_user_can( $blog, $user, 'create_post' ) ) { return 1; }
        if ( is_user_can( $blog, $user, 'edit_templates' ) ) { return 1; }
    }
    return 0;
}

1;