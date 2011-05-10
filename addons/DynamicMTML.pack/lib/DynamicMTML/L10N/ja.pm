package DynamicMTML::L10N::ja;

use strict;
use base 'DynamicMTML::L10N';
use vars qw( %Lexicon );

our %Lexicon = (
    'DynamicMTML is PHP extension for Movable Type.' => 'Movable Typeのダイナミックパブリッシングを拡張します。',
    'Enable DynamicMTML (Create the file <code>.htaccess</code> underneath your blog directory)' => 'DynamicMTMLを有効にする (サイト・パス直下に<code>.htaccess</code>を生成します)',
    'Enable DynamicMTML Cache' => 'ビルド結果をキャッシュする',
    'Cache expiration' => 'ビルド結果のキャッシュ有効期限(秒)',
    'Enable Conditional GET on DynamicMTML' => 'DynamicMTMLで条件付きGETを有効にする',
    'Dynamic Search Options' => 'ダイナミックビルド・オプション',
    'Dynamic Extensions' => 'ビルドする拡張子',
    'Exclude Extensions' => '処理対象外の拡張子',
    'Directory Index' => 'ディレクトリインデックス',
    'Install DynamicMTML' => 'DynamicMTMLのインストール',
    'Flush Dynamic Cache' => 'ダイナミックキャッシュのクリア',
    'Install DynamicMTML was successful.' => 'DynamicMTMLテンプレートをインストールしました。',
    'Install DynamicMTML failed.' => 'DynamicMTMLをインストールできませんでした。',
    'Flush Dynamic Cache was successful.' => 'キャッシュをクリアしました。',
    'Cache file was not found.' => 'キャッシュが見つかりませんでした。',
    'Error: Movable Type cannot overwrite the file <code>[_1]</code>. Please check the file <code>[_1]</code> underneath your blog directory.'
        => '<code>[_1]</code>を更新できませんでした。サイトパスの下にある<code>[_1]</code>ファイルを削除するか[_1]の内容を確認してください。',
    'Error: Movable Type cannot write to the file [_1]. Please check the permissions for the file <code>[_1]</code> underneath your blog directory.'
        => '<code>[_1]</code>を作成できませんでした。サイトパスの下にある<code>[_1]</code>ファイルを削除するか[_1]のパーミッションを確認してください。',
    'Error: Movable Type cannot write to the search cache directory.<br />Please check the permissions for the directory called <code>[_1]</code>.'
        => 'クエリー付きリクエストのビルド結果をキャッシュするディレクトリを作成できません。<br /><code>[_1]</code>ディレクトリを作成してください。',
    );

1;