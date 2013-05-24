package DynamicMTML::L10N::en_us;
use strict;
use base qw/DynamicMTML::L10N/;

our %Lexicon = (
    'Cache expiration'                               => 'Cache expiration',
    'Cache file was not found.'                      => 'Cache was not found.',
    'Directory Index'                                => 'Directory Index',
    'Dynamic Extensions'                             => 'Extensions for DynamicMTML',
    'Dynamic Search Options'                         => 'Build Options',
    'Enable Conditional GET on DynamicMTML'          => 'Enable conditional GET on DynamicMTML',
    'Enable DynamicMTML Cache'                       => 'Enable DynamicMTML cache',
    'Exclude Extensions'                             => 'Exclude Extensions',
    'Flush Dynamic Cache was successful.'            => 'Claer cache is completed.',
    'Flush Dynamic Cache'                            => 'Clear Cache',
    'Install DynamicMTML failed.'                    => 'Error is ocuurred on instaling DynamicMTML template',
    'Install DynamicMTML was successful.'            => 'DynamicMTML template is installd.',
    'Install DynamicMTML'                            => 'Install DynamicMTML',
    'DynamicMTML is PHP extension for Movable Type.' => 'This package extends dynamic publishing.',

    'Enable DynamicMTML (Create the file <code>.htaccess</code> underneath your blog directory)' =>
        'Enable DynamicMTML (create <code>.htaccess</code> file on your site path.)',
    'Error: Movable Type cannot overwrite the file <code>[_1]</code>. Please check the file <code>[_1]</code> underneath your blog directory.' =>
        'Error: The system can\'t overwrite the file <code>[_1]</code>. Please check or delete file <code>[_1]</code>',
    'Error: Movable Type cannot write to the file [_1]. Please check the permissions for the file <code>[_1]</code> underneath your blog directory.' =>
        'Error: The system can\'t pubish the file <code>[_1]</code>. Please check the permission or delete file <code>[_1]</code>.',
    'Error: Movable Type cannot write to the search cache directory.<br />Please check the permissions for the directory called <code>[_1]</code>.' =>
        'Error: The system can\'t make directory for cache. Please check the permission or make directory <code>[_1]</code>.',
);
1;
