# DynamicMTMLドキュメント


## はじめに

DynamicMTML は mod\_rewrite を利用してコンテンツへのリクエストを Dynamic Site Bootstrapper(.mtview.php) に処理させることで、静的ファイルに記述された MTML をダイナミックパブリッシングエンジンを用いて動的ビルドすることの出来る Movable Type の拡張です。また、PHPを利用して Movable Type を拡張するための様々なライブラリやコールバック、プラグインによる拡張のしくみを追加します。


## 動作環境

+ Movable Type5.04以降
+ PHPによるダイナミックパブリッシングが動作する環境
+ mod\_rewriteが有効で.htaccessによる設定が可能な環境


## インストール

mt ディレクトリ直下の addons ディレクトリに DynamicMTML.pack をアップロードします。ウェブサイト/ブログの「設定」→「全般」→「ダイナミックパブリッシング設定」の「DynamicMTMLを有効にする」にチェックを入れて設定を保存してください。

ブログのサイト・パス直下に.htaccess, .mtview.phpを生成します。
mtディレクトリ直下にpowercms\_filesディレクトリを作成し、MTから書き込み可能なパーミッションを設定してください。

Perlによる動的パブリッシングを有効にする場合、addons/DynamicMTML.pack/tools以下の2つのファイルをmtディレクトリのtoolsディレクトリに設置し、実行可能なパーミッションを設定してください。


## 設定
mt-config.cgiに下記のディレクティブを指定することが出来ます。

**テンプレート保存画面のエラーを非表示にする。**

    DisableCompilerWarnings 1

**常にSmartyのテンプレートをコンパイルする**

    DynamicForceCompile 1

**スタティックファイル中のPHPコードを実行する**

    DynamicIncludeStatic 1

**powercms\_filesディレクトリを任意の場所に設定(キャッシュディレクトが含まれます)**

    PowerCMSFilesDir /path/to/powercms\_files

**DynamicMTMLを実行するPHPプラグラムのファイル名称を指定**

    DynamicSiteBootstrapper .mtview.php

**$app->user()を呼び出す時にUserSessionTimeoutの値を無視**

    UserSessionTimeoutNoCheck 1

**magic\_quotes\_gpcをOffにしない(magic\_quotes\_gpcが有効でこの設定が0の時、DynamicMTMLがmagic\_quotes\_gpcをOffにします)**

    AllowMagicQuotesGPC 1 # (デフォルト:0)

**DynamicMTMLの実行時にMTMLよりも先にPHPコードを実行する**

    DynamicPHPFirst 1


## 概要

静的ファイルが存在する時場合、存在するファイルがテキストファイルの場合(mime\_typeがtext/foo もしくはapplication/xhtml+xmlの場合)の場合に、ファイルの中にMTタグを含んでいればファイル内のMTタグをダイナミックパブリッシングエンジンで処理して処理結果を返します。ファイル内のMTタグの有無に関わらず、ファイル内にPHPのコードが記述されていれば、PHPのコードもあわせて実行されます(DynamicIncludeStatic環境設定が有効な場合)。静的ファイルが存在しない場合は、MTのダイナミックパブリッシングに処理が渡されます。
処理の各ポイントでコールされるコールバックに対応したプラグインによる拡張が可能です。

![DynamicMTML Overview](https://github.com/alfasado/DynamicMTML/raw/master/addons/DynamicMTML.pack/dynamicmtml.ja.png)

## データベースへの接続に失敗した時の処理の継続(フェイルセーフ)

コールバックプラグインによって例えば別のデータベースへ接続をリトライして処理を継続することができます。また、キャッシュが有効な場合は(ブログの全般設定で設定)キャッシュが存在すればキャッシュを、キャッシュがない場合でも静的ファイルが存在する場合にはファイルの内容を返すことができます。この時、ファイル内のMTタグは下記のルールで処理されます。

    <MTNonDynamicMTML>
        この中はそのまま出力されます。    
    </MTNonDynamicMTML>
    <MTDynamicMTML>
        この中は出力されません。
    </MTDynamicMTML>

MTDynamicMTML及びMTNonDynamicMTMLタグを解釈した後、結果に残っているMTタグはブラウザに返される前に削除されます。
接続のリトライや別のデータベースへの接続方法(レプリケーション先のDBの利用等)についてはinit\_request, mt\_init_exception コールバックプラグインの例を参照してください。


## テンプレート・タグ

+ [テンプレートタグリファレンス | MovableType.jp](http://www.movabletype.jp/documentation/appendices/tags/).

### DynamicMTMLによって提供されるテンプレート・タグ

---------------------------------------

**MTDynamicMTML(ブロックタグ)**

静的ファイル出力のテンプレートに記載した場合、このタグ(閉じタグを含む)及びタグの中のテキストがビルドされずそのままファイルに出力されます。このタグの内部に記述されたMTMLはダイナミックパブリッシング処理される時にビルドされます。

*このタグ及びタグMTRawMTMLをダイナミックMTML静的出力(PHPによる静的ビルド)の際にこのタグを有効化するためには(最初の)モディファイア'id'にテンプレート内でユニークな値を記載してください(Smartyのテンプレートにidが出力される必要があるため、インクルードしたテンプレートでは利用できません)。*

---------------------------------------

**MTNonDynamicMTML(ブロックタグ)**

静的ファイル出力のテンプレートに記載した場合、このタグの中のテキストをビルドした結果を<MTNonDynamicMTML>〜</MTNonDynamicMTML>で囲んだ形でファイルに出力されます。このタグの中身はダイナミックパブリッシング処理される時には出力されず、DBへのアクセスに失敗した時等に(MT::get\_instanceに失敗した時)に出力されます。

---------------------------------------

**MTRawMTML(ブロックタグ)**

静的ファイル出力のテンプレートにのみ記載できます。タグの中のテキストがビルドされずそのままファイルに出力されます。

---------------------------------------

**MTIfLogin(ブロックタグ)**

ユーザーのログインcookieから、有効なユーザーである場合に出力されます。ユーザーが有効な時、このタグの内部でMTAuthor関連のMTMLが利用可能です。

---------------------------------------
   
**MTClientAuthorBlock(ブロックタグ)**

ユーザーがログインしている時、ユーザー(MT::Authorオブジェクト)のコンテキストをセットします(このタグを使う場合、ブログのダイナミックパブリッシング設定で「キャッシュする」および「ビルド結果をキャッシュする」を Off にする必要があります)。

---------------------------------------

**MTLoginAuthorCTX(ブロックタグ)**

MTClientAuthorBlockのエイリアスです。

---------------------------------------

**MTIfUserHasPermission(ブロックタグ)**

ユーザーがログインしている時、ユーザーがpermissionモディファイアで指定した権限(例:comment)を持っている時に出力されます。

---------------------------------------

**MTIfUserAgent(ブロックタグ)**

wantsモディファイア、likeモディファイアを指定してユーザーエージェント情報による分岐を行います。
詳細については$app->get\_agent()の項を参照してください。

*例:*
 
    <MTIfUserAgent wants="keitai">
        DoCoMo, AU, SoftBankの携帯キャリアからのアクセスの場合出力されます。
    </MTIfUserAgent>
    <MTIfUserAgent wants="SmartPhone">
        Android,BlackBerry,iPhone(iPod touch),iPad,Palm,Opera Miniからのアクセスの場合出力されます。
    </MTIfUserAgent>
    <MTIfUserAgent like="Safari">
        ユーザーエージェント情報に「Safari」を含む場合に出力されます。
    </MTIfUserAgent>

---------------------------------------

**MTEntryCategoryBlock(ブロックタグ)**

エントリーの主カテゴリのコンテキストをセットします。

---------------------------------------

**MTSearchEntries(ブロックタグ)**

targetで指定したカラムにquery文字列を含むエントリーを出力します。
カラム名を省略した場合はタイトル、本文、続き、概要及びキーワードが対象となります。
operator(省略時はLIKE)を指定することで検索条件を指定できます。
例えば operator="=" target="title" query="Movable Type" はタイトルが「Movable Type」と完全一致するエントリーをロードします。

*利用できるモディファイア*

    query         : 検索文字列
    blog_id       : ブログID
    include_blogs : 対象とするブログIDを列記(カンマ区切り)または
                    "all" "children" "siblings"が指定可能です。
    exclude_blogs : 対象外とするブログIDを列記(カンマ区切り)します。
    target        : 検索対象とするカラムを指定します。
    operator      : SQLの条件式を記載します。デフォルトはLIKEです。
    class         : entry(デフォルト)もしくはpage
    category      : カテゴリ名
    category_id   : カテゴリID
    tag           : タグ 
    status        : 数字もしくは'*'(すべて)
    sort_by       : 表示順に指定するカラム名
    sort_order    : 表示順(descendもしくはascend)
    lastn         : 表示数
    offset        : オフセット値(何件目から読み込むか)
    unique        : 同じテンプレート内で使用したMTSearchEntries
                    ブロックで出力したエントリーを除いて出力します。
    not_entry_id  : IDを指定した単一のエントリーを除いて出力します。

---------------------------------------

**MTQueryLoop(ブロックタグ)**

keyモディファイアで指定したクエリー文字列の配列をvar[key]にセットしてループ出力します。

*例:*

    <MTQueryLoop key="foo" glue=",">
        <mt:var name="foo" escape="html">
    </MTQueryLoop>

    このテンプレートは request_url?foo[]=bar&foo[]=buz リクエストの時
    「bar,buz」を出力します。

---------------------------------------

**MTQueryVars(ブロックタグ)**

クエリー文字列の配列をvar[key]、var[value]にセットしてループ出力します。

*例:*

    <MTQueryVars glue=",">
        <mt:var name="key" escape="html">
            =&gt; <mt:var name="value" escape="html">
    </MTQueryVars>

    このテンプレートは request_url?foo=bar&bar=buz リクエストの時
    「foo=>bar,bar=>buz」を出力します。

---------------------------------------

**MTSetQueryVars(ブロックタグ)**

リクエストのクエリー文字列をvarにセットします。

*例:*

    <MTSetQueryVars><mt:var name="foo" escape="html">,
        <mt:var name="bar" escape="html"></MTSetQueryVars>

    このテンプレートは request_url?foo=1&bar=2 リクエストの時
    「1,2」を出力します。

---------------------------------------

**MTSplitVars(ブロックタグ)**

textモディファイアで指定した文字列をdelimiterモディファイアで指定した文字列で分割してvar[value]\(valueはnameモディファイアで指定可能です\)にセットしてループ出力します。

---------------------------------------

**MTReferralKeywords(ブロックタグ)**

Google, bing, MSN, Yahoo!, gooからの流入の際(またはサイト内検索(パラメタ名はquery'または'search'))に検索されたキーワードの配列をvar[keyword]にセットしてループ出力します。

---------------------------------------

**MTCommentOut(ブロックタグ)**

ブロックの前後に'<!\-\-'及び'\-\->'を挿入して出力します。invisible モディファイアを指定した場合、何も出力されません(MTIgnore と同様)。

---------------------------------------

**MTCommentStrip(ブロックタグ)**

ブロックで囲まれたテキスト内の'<!\-\-'及び'\-\->'を削除します。MTCommentOut, MTCommentStripタグを利用することで、静的なHTMLをDreamweaver等で編集する際にMTタグの代わりにダミーの HTMLを配置し、サーバー上では動的にMTタグを処理させる(逆に動的処理の際はダミーの HTML を削除する(隠す))ことが可能になります。

---------------------------------------

**MTML(ファンクションタグ)**

静的ファイル出力のテンプレートにのみ記載できます。tagモディファイアで指定したMTタグにparamsモディファイアで指定したパラメタを付けてMTタグとして出力します。

*例:*

    <MTML tag="MTIfUserHasPermission" params='permission="comment"'>
        <MTBlogName escape="html">にコメントできます。
    <MTML tag="MTElse">
        <MTBlogName escape="html">にコメントできません。
    <MTML tag="/MTIfUserHasPermission">

    このテンプレートは下記のように出力され、ダイナミックパブリッシング時に処理されます。

    <MTIfUserHasPermission permission="comment">
        [ブログ名]にコメントできます。
    <MTElse>
        [ブログ名]にコメントできません。
    </MTIfUserHasPermission>

---------------------------------------

**MTMTML(ファンクションタグ)**

MTMLのエイリアスです。

---------------------------------------

**MTRawMTMLTag(ファンクションタグ)**

MTMLのエイリアスです。

---------------------------------------

**MTQuery(ファンクションタグ)**

keyモディファイアで指定したクエリー文字列を出力します。

---------------------------------------

**MTReferralKeyword(ファンクションタグ)**

Google, bing, MSN, Yahoo!, gooからの流入の際(またはサイト内検索(パラメタ名はquery'または'search'))に検索されたキーワード(フレーズ)を出力します。

---------------------------------------

**MTUserAgent(ファンクションタグ)**

ユーザーエージェントを判別する文字列を返します。rawモディファイアを指定するとHTTP_USER_AGENT情報をそのまま出力します。
詳細については$app->get\_agent()の項を参照してください。

---------------------------------------

**MTCurrentArchiveURL(ファンクションタグ)**

現在のページのURLからクエリ文字列を削除したURLをhttpからのフルパスで出力します。

---------------------------------------

**MTCurrentArchiveFile(ファンクションタグ)**

現在のページのサーバー上のファイルパスをフルパスで出力します。

---------------------------------------

**MTEntryStatusInt(ファンクションタグ)**

エントリーのステータスを数字で返します。

---------------------------------------

**MTAuthorLanguage(ファンクションタグ)**

ユーザーの言語(例:ja)を出力します。

---------------------------------------

**MTTrans(ファンクションタグ)**

phraseモディファイア及びparamsモディファイアに指定された文字列を元に現在のユーザーの言語に応じて翻訳された文字列を出力します。

*PluginName/php/l10n/以下に l10n_ja.php(または他の言語) ファイルを設置し、配列$Lexiconにテーブルを登録することで言語ファイルを拡張することができます。*

---------------------------------------

**highlightingsearchword(モディファイア)**

Google, bing, MSN, Yahoo!, gooからの流入の際(またはサイト内検索(パラメタ名はquery'または'search'))に検索されたキーワードを渡されたパラメタのclass名を付けたstrongタグでマークアップします。

*例:*

    <MTEntryBody highlightingsearchword="match-words">
    
    検索キーワードが <strong class="match-words">〜</strong>でマークアップされます。

---------------------------------------

**make_seo_basename(モディファイア)**

文字列からURLに利用できる文字のみを先頭から指定文字分抽出してURLエンコードした文字列basenameを返します。URLに利用できない文字は'\_'に置換されます。

*例:*

    <$MTEntryTitle make_seo_basename="50"$>
    
   「Movable Typeへようこそ」=>「Movable_Type%e3%81%b8%e3%82%88%e3%81%86%e3%81%93%e3%81%9d」

---------------------------------------

**trimwhitespace(モディファイア)**

余分な空白を削除してソースコードを軽量化します。

---------------------------------------

**intval(モディファイア)**

文字列を数値化します。

---------------------------------------

##テンプレートタグの例

---------------------------------------

**本文欄をキーワードで検索する(/foo.html?q=keyword)**

    <mt:dynamicmtml>
    <mt:query key="q" escape="html" setvar="query">
    <mt:if name="query">
        <mt:query key="limit" intval="1" setvar="limit">
        <mt:unless name="limit">
            <mt:setvar name="limit" value="20">
        </mt:unless>
        <mt:searchentries target="text" query="$query" lastn="$limit" count="1">
        <mt:if name="__first__">
        <div class="search-entry widget">
            <h3 class="widget-header">
                '<$mt:var name="query"$>'の検索結果
                (<mt:var name="__entries_count__">件がマッチ)
            </h3>
            <ul>
        </mt:if>
                <li><a href="<$mt:entrypermalink$>">
                    <$mt:entrytitle escape="html"$></a></li>
        <mt:if name="__last__">
            </ul>
            <mt:if name="__entries_count__" gt="$limit">
                <p>
                <a href="<mt:CurrentArchiveUrl>?q=<$mt:var
                            name="query" escape="url"$>&amp;limit=<mt:var
                            name="__entries_count__">">More
                </a></p>
            </mt:if>
        </mt:if>
        </mt:searchentries>
    </mt:if>
    </mt:dynamicmtml>

---------------------------------------

**検索エンジンからの流入時に本文に検索ワードを含むブログ記事を10件リストアップして表示する(検索ワードをハイライト表示する)**

    <mt:dynamicmtml>
    <mt:archivetype setvar="archive_type">
    <mt:if name="archive_type" eq="Individual">
    <$mt:entryid setvar="me"$>
    </mt:if>
    <mt:if name="archive_type" eq="Page">
    <$mt:pageid setvar="me"$>
    </mt:if>
    <$mt:setvar name="entries_max" value="10"$>
    <$mt:setvar name="entries_counter" value="0"$>
    <mt:referralkeywords trimwhitespace="1">
    <mt:if name="entries_counter" lt="$entries_max">
        <mt:searchentries target="text" query="$keyword" unique="1"
            lastn="$entries_max" not_entry_id="$me" class="*"
            highlightingsearchword="1">
            <mt:unless name="entries_counter">
    <div class="related-entry widget">
        <h3 class="widget-header">
            '<$mt:referralkeyword escape="html"$>'をお探しですか?
        </h3>
        <ul>
            </mt:unless>
            <mt:if name="entries_counter" lt="$entries_max">
            <li><a href="<$mt:entrypermalink$>">
                <$mt:entrytitle escape="html"$>
                </a>
            </li>
            </mt:if>
            <$mt:setvar name="entries_counter" value="1" op="+"$>
        </mt:searchentries>
    </mt:if>
    </mt:referralkeywords>
            <mt:if name="entries_counter"></ul>
    </div>
    </mt:if>
    </mt:dynamicmtml>

---------------------------------------

**PC向けにはスタティックページを返し、携帯・スマートフォンからのアクセスの際には別テンプレートを動的処理する**

    <mtml tag="mt:IfUserAgent" params='wants="keitai"'>
        <mt:dynamicmtml>
            <$mt:include module="携帯用テンプレート"$>
        </mt:dynamicmtml>
    <mtml tag="mt:else">
    <mtml tag="mt:IfUserAgent" params='wants="SmartPhone"'>
        <mt:dynamicmtml>
            <$mt:include module="スマートフォン向けテンプレート"$>
        </mt:dynamicmtml>
    <mtml tag="mt:else">
        <$mt:include module="PC向けテンプレート"$>
    <mtml tag="/mt:else">
    <mtml tag="/mt:IfUserAgent">
    <mtml tag="/mt:else">
    <mtml tag="/mt:IfUserAgent">

---------------------------------------

**権限のあるユーザーにEditリンクを表示する**

    <mt:dynamicmtml>
    <mt:IfUserHasPermission permission="edit_all_posts">
        <$mt:setvar name="can_post" value="1"$>
    <mt:Else>
        <mt:IfUserHasPermission permission="publish_post">
            <$mt:entryAuthorId setvar="entry_author_id"$>
            <$mt:AuthorId setvar="client_author_id"$>
            <mt:if name="entry_author_id" eq="client_author_id">
               <$mt:setvar name="can_post" value="1"$> 
            </mt:if>
        </mt:IfUserHasPermission>
    </mt:Else>
    </mt:IfUserHasPermission>
    <mt:if name="can_post">
        <p>
            <a href="<$mt:CGIPath$>
            <$mt:
                AdminScript$>?__mode=view&amp;_type=entry&amp;id=<$mt:
                    entryid$>&amp;blog_id=<$mt:blogid$>">編集
            </a>
        </p>
    </mt:if>
    </mt:dynamicmtml>

---------------------------------------

## クラス DynamicMTML

    require_once ( $mt_dir . 'php/mt.php' );
    require_once ( $mt_dir . 'addons/DynamicMTML.pack/php/dynamicmtml.php' );

    $blog_id = 1;
    $mt_config = $mt_dir . 'mt-config.cgi';
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    try {
        $mt = MT::get_instance( $blog_id, $mt_config );
    } catch ( MTInitException $e ) {
        $app->run_callbacks( 'mt_init_exception', $mt, $ctx, $args, $e );
    }
    if ( isset( $mt ) ) {
        $ctx =& $mt->context();
        $app->set_context( $mt, $ctx );
    }
    ...


### クラスの取得
DynamicMTML環境下では以下のようにして取得可能です。

    global $app
    // または
    $app = $ctx->stash('bootstrapper')


### MT::App互換のメソッド

---------------------------------------

**$app->login();**

「username」及び「password」パラメーターを探して、ユーザーの認証情報をチェックします。usernameとpasswordが有効なものである場合、mt\_sessionテーブルにセッション情報を保存してログインcookieを設定します。return\_urlパラメーターが指定されている場合、ログイン処理の後リダイレクトします。

---------------------------------------

**$app->logout();**

ログインcookieをクリアします。sessid付きのリクエストの場合、sessidの値を削除したURLへリダイレクトします。

---------------------------------------

**$app->send\_http\_header($content\_type,[$ts,$length]);**

クライアントにHTTPヘッダーを送信します。引数 $content\_type が指定されている場合には、Content-Typeヘッダーを指定された値に設定します。無指定の場合には、デフォルトで"text/html"になります。$ts(Unixタイムスタンプ)を指定するとLast-Modifiedヘッダ、ETagヘッダを、$lengthを指定するとContent-Lengthヘッダを送信します。

---------------------------------------

**$app->user\_cookie();**

ユーザーのログインcookieに用いられているcookie名の文字列を返します。

---------------------------------------

**$app->user();**

ログイン中のユーザーを表すオブジェクトを返します。通常はMT::Authorオブジェクトです。

---------------------------------------

**$app->blog([$blog\_id]);**

アクティブなアプリケーションが実行されているMT::Blogオブジェクトを返します。

---------------------------------------

**$app->current\_magic();**

アクティブなユーザーの「マジック・トークン」を返します。

---------------------------------------

**$app->make\_magic\_token();**

ランダムな文字でできた「マジック・トークン」文字列を新規生成します。

---------------------------------------

**$app->validate\_magic();**

magic\_token パラメーターがあるかどうかを調べ、それが現在のユーザーに対して有効かどうかをチェックします(ユーザーが未定義の場合は0を返します)。有効な場合に1を返します。無効な場合は0が返ります。たとえば次のように呼び出します。

    if (! $app->validate_magic() ) {
        echo $app->translate( 'Error:Invalid request.' );
        exit();
    }

---------------------------------------

**$app->mode();**

\_\_modeパラメタの値を返します。なければ'default'が返ります。

---------------------------------------

**$app->session();**

アクティブなユーザーのセッション・オブジェクトを返します。

---------------------------------------

**$app->static\_path();**

アプリケーションのスタティックURL(例:/path/to/mt-static/)を返します。

---------------------------------------

**$app->cookie\_val($name);**

指定されたcookieの値を返します。

---------------------------------------

**$app->delete\_param($param);**

リクエストから指定されたパラメーターの値をクリアします。

---------------------------------------

**$app->is\_secure();**

アプリケーションへのリクエストがセキュアな(HTTPSによる)接続によって送られたものである場合に1を返します。

---------------------------------------

**$app->param($name);**

リクエストからクエリー・パラメーターを取得します。

---------------------------------------

**$app->query\_string();**

アクティブなリクエストのクエリー文字列を返します。

---------------------------------------

**$app->request\_method();**

アクティブなHTTPリクエストのメソッドを返します。通常はGETかPOSTのいずれかです。

---------------------------------------

**$app->redirect($url);**

クライアントを$urlというURLにリダイレクトします。$urlが絶対URLでない場合には、その前に「$app->base」の値が付加されます。デフォルトでは、このリダイレクトはLocationヘッダーと「302 Redirect」というレスポンスによって行われます。

---------------------------------------

**$app->base();**

アプリケーションのプロトコルとFQDNです。たとえば、アプリケーションの完全なURIがhttp://www.foo.com/mt/mt.phpの場合、このメソッドは"http://www.foo.com"を返します。

---------------------------------------

**$app->path();**

ディレクトリURLのパス部分です。たとえば、完全なURIがhttp://www.foo.com/mt/mt.htmlの場合、このメソッドは"/mt/"を返します。

---------------------------------------

**$app->log($msg[,$args]);**

ログにメッセージ$msgを追記します。ログ・エントリーには「$app->remote\_ip」を用いて、アプリケーションを実行しているクライアント(つまりHTTPリクエストを行ったブラウザー)のIPアドレスが付加されます。第2匹数$argsで$args['level'],$args['class'],$args['metadata']の指定が可能です。

---------------------------------------

**$app->remote\_ip();**

クライアントのIPアドレスを返します。

---------------------------------------

**$app->config($configname);**

$confignameで指定したMTの環境設定の値を返します。

---------------------------------------

**$app->component($plugin\_name);**

config.yamlまたはconfig.phpで定義したクラスを取得します。config.yamlのみでconfig.phpが存在しない場合はMTPluginクラス、config.phpが存在する場合はconfig.phpで定義したクラスを取得します。

---------------------------------------

**$app->translate($str[,$params]);**

$strを現在のユーザーの言語で翻訳します。

---------------------------------------

**$app->build\_page($path[,$params]);**

$pathで指定したテンプレートに配列$paramsで渡した値をvarにセットしてビルドします。

*例:*

    $app->build_page( '/path/to/template.tmpl', array( 'foo' => 'bar' ) );

---------------------------------------

### MT::WeblogPublisher互換のメソッド

ダイナミックパブリッシングエンジンを利用して再構築を行います。この時、$param['build\_type']でテンプレートのbuild_typeを配列で指定することができます。build\_typeが1,2のテンプレートではビルドした結果を静的ファイルとして出力します。build\_typeが3のテンプレートはmt\_fileinfoレコードの更新のみを行います(ファイルが存在する場合は.static拡張子を付けたファイルにリネームされます)。build\_typeが4のテンプレートは再構築キューに登録されます。

---------------------------------------

**$app->rebuild($args);**

ブログ、インデックス、アーカイブを再構築します。再構築の対象を限定する場合には、対象を引数$argsに指定します。$argsには、次の項目を指定できます。

    Blog       : 再構築するブログに対応するMT::Blogオブジェクトです。
                 BlogまたはBlogIDのいずれか一方を必ず指定してください。
    BlogID     : 再構築するブログのブログIDです。
                 BlogIDまたはBlogのいずれか一方を必ず指定してください。
    ArchiveType: 再構築するアーカイブの種類です(複数指定する場合はカンマ区切りテキストで
                 指定してください)。この引数はオプションです。
                 指定しない場合はすべてのアーカイブの種類が再構築の対象となります。
    NoIndexes  : rebuildメソッドは、デフォルトではすべてのアーカイブを再構築したあとで
                 インデックス・テンプレートを再構築します。インデックス・テンプレートを再構築したくない
                 場合は、TRUEをこの引数に指定してください。
                 この引数はオプションです。
    NoStatic   : この値をtrueにすると、再構築ルーチンに対して、スタティックな出力ファイルの再構築の
                 必要がないことを指示します(build_typeが1と2のアーカイブを再構築対象から外します)。
    Limit      : 再構築するエントリーの件数を、当該ブログ中の最新N件に限定します
                (アーカイブタイプが'Indivisual'及び'Page'の時のみ有効です)。
    Offset     : Limitと組み合わせて指定し、エントリー・アーカイブの再構築の開始位置を指定します
                (アーカイブタイプが'Indivisual'及び'Page'の時のみ有効です)。

*例:最新10件のブログ記事を再構築する*

    $app->rebuild( array( 'Blog' => $blog,
                          'ArchiveType' => 'Individual',
                          'NoIndexes' => 1,
                          'Limit' => 10 ) );

---------------------------------------

**$app->rebuild\_indexes([$param]);**

現在のブログまたは$param['blog']にセットしたブログのインデックス・テンプレートを再構築します。

*例:*

    $app->rebuild_indexes(
        array( 'blog' => $blog,
               'build_type' => array( 1, 3, 4 ) );

---------------------------------------

**$app->rebuild\_archives([$param]);**

現在のブログまたは$param['blog']にセットしたブログのテンプレートのうち$param['recipe']で指定したアーカイブを再構築します。$param['updated']を指定することでアクティブなリクエストで更新、保存されたエントリー及びエントリーに関連するアーカイブだけを再構築対象とすることができます。

*例:*

    $archives = array( 'Index', 'Category', 'Monthly',
                       'Yearly', 'Weekly', 'Daily', 'Author' );
    $app->rebuild_archives( array( 'blog' => $blog,
                                   'recipe' => $archives,
                                   'updated' => 1 ) );

$param['limit'],$param['offset']を指定することで再構築対象範囲を指定することができます。アーカイブによってカウント対象となるオブジェクトは変わります。

    インデックス・アーカイブ : テンプレートの数
    ブログ記事 : ブログ記事の数
    ウェブページアーカイブ : ウェブページの数
    カテゴリ : カテゴリの数
    日付アーカイブ : 出力されるアーカイブの数
    ユーザー/日付アーカイブ : ユーザーの数
    カテゴリ/日付アーカイブ : カテゴリの数

*PluginName/php/publishers/ArchiveTypeName.php を設置することでカスタム・アーカイブタイプを再構築させることができます。*

---------------------------------------

**$app->rebuild\_entry($param);**

引数$paramで指定されたエントリーを再構築します。$param['BuildDependencies']を指定することで関連するアーカイブを同時に再構築します。

*例:*

    $app->rebuild_entry( array( 'entry' => $entry,
                                'BuildDependencies' => 1 ) );

---------------------------------------

**$app->rebuild_category($param);**

引数$paramで指定されたカテゴリを再構築します。

*例:*

    $app->rebuild_category( array( 'category' => $category,
                                   'build_type' => array( 1, 3, 4 ) ) );

---------------------------------------

**$app->rebuild\_from\_fileinfo($fileinfo);**

MT::FileInfoオブジェクトから該当するアーカイブを再構築します。MT::FileInfoオブジェクトのbuild\_typeによって静的再構築、再構築キューへの登録のいずれかを行います。

---------------------------------------

### MT::FileMgr互換のメソッド

---------------------------------------

**$app->put($src,$dest[,$type ]);**

$dest で指定したパスのファイルへ $src ファイルの内容を出力します。$src はローカル・ファイルのパス名またはURLです。$dest はローカル・ファイルのパス名です。
$type はオプションで、put がアップロード・ファイル用のものか、HTMLファイル出力かを指定します。これは書き込みのモードや umask設定などを伝えるためのものです。$type には"upload"か"output"が指定可能で、デフォルトは"output"です。戻り値は出力したバイト数(0のこともあります)になります。エラー発生時にはFALSEを返します。

---------------------------------------

**$app->put\_data($data,$dest[,$type ]);**

$dest で指定したパスのファイルへ $data の内容を出力します。$dest はローカル・ファイルのパス名です。
$type はオプションで、put_data がアップロード・ファイル用のものか、HTMLファイル出力かを指定します。これは書き込みのモードや umask設定などを伝えるためのものです。$type には"upload"か"output"が指定可能で、デフォルトは"output"です。戻り値は出力したバイト数(0のこともあります)になります。エラー発生時にはFASEを返します。

---------------------------------------

**$app->get\_data($src);**

$src に指定したパスからデータ・ブロックを取得し、取得したデータを返します。$src はローカル・ファイルのパス名またはURLです。取得に失敗するとFALSEを返します。

---------------------------------------

**$app->mkpath($path[,$perms]);**

$pathに指定したパスを再帰的に作成します。つまり、パス中に一つでも存在しないものがあれば、それを作成します。作成に失敗すると(または作成したディレクトリに書き込み権限がない場合)FALSEを返します。$perms を指定すると作成したディレクトリのパーミッションを $perms に設定します。$permsの指定を省略すると $app->config('DirUmask')で指定されたパーミッションに設定されます。

---------------------------------------

**$app->content\_is\_updated($file,$content);**

$fileが存在しないか $file の内容が $content の内容と異なる場合に1を返します。

---------------------------------------

**$app->delete($file);**

$fileを削除します。削除に成功した場合、および$fileが存在しないか $fileがシンボリックリンクである場合、TRUEを返します。

---------------------------------------

### class DynamicMTMLのその他のメソッド

---------------------------------------

**$app->init\_mt($mt,$ctx,$blog\_id);**

ブログIDを指定してMTを初期化します。$mt->blog\_id()が指定されていない時、もしくは別のブログを指定してMTを再初期化する時に呼び出します。

---------------------------------------

**$app->can\_do($ctx,$permission);**

アクティブなユーザーが$ctxにセットされたブログに対して$permissionで指定された権限を持っている場合に1を返します。Perl APIとの違いは、$permissionに渡す値がpermission(create\_post,edit\_templates等)である点です。

---------------------------------------

**$app->add\_lexicon($lang,$array());**

$Lexicon\_$langに翻訳テーブルを追加します。

---------------------------------------

**$app->plugin\_get\_config\_value($component,$key[,$blog\_id]);**

プラグイン$componentのプラグイン設定$keyを返します。

---------------------------------------

**$app->escape($string,[$urldecode]);**

($urldecodeが指定されている場合はデコードしてから)$mt->db()->escape($string)を呼び出して結果を返します。

---------------------------------------

**$app->get_agent($wants,$like);**

Android,BlackBerry,iPhone(iPod touch),iPad,Palm,Opera Mini($wantsに'Smartphone'を指定した場合はこのいずれかの場合に1を返します),DoCoMo,AU,SoftBank($wantsに'Keitai'を指定した場合はこのいずれかの場合に1を返します)のいずれかの値を返します。これら以外の場合は'PC'が返ります。
第二引数$likeが指定されている場合、HTTP_USER_AGENTに$likeで指定した文字列が含まれている時に1、含まれていない時に0を返します。

---------------------------------------

**$app->delete\_params($array);**

リクエストから配列$arrayで指定された複数のパラメーターの値をクリアします。

$app->include\_exclude\_blogs($ctx,$args);

ブロックタグに渡された$ctxと$args(include\_blogs(blog\_ids), exclude\_blogsモディファイアから)複数のブログを対象とするためのSQLを生成して返します。

**例:**

        $include_exclude_blogs = $app->include_exclude_blogs( $ctx, $args );
        require_once 'class.mt_entry.php';
        $where = " entry_blog_id {$include_exclude_blogs} "
               . " AND entry_class = 'entry' "
               . " AND entry_status = 2 ";
        $extra = array(
            'limit' => 10,
            'offset' => 0,
        ); 
        $_entry = new Entry;
        $entries = $_entry->Find( $where, false, false, $extra );

*loadメソッドを使うと以下のように書くことができます。*

        $include_exclude_blogs = $app->include_exclude_blogs( $ctx, $args );
        $terms = array( 'blog_id' => $include_exclude_blogs,
                        'status' => 2 );
        $extra = array(
            'limit' => 10,
            'offset' => 0,
        ); 
        $entries = $app->load( 'Entry', $terms, $extra );

---------------------------------------

**$app->do\_conditional($ts);**

HTTPヘッダの情報と$tsを比較して、コンテンツが更新されていない場合は304 Not Modifiedヘッダを返してクライアントキャッシュを使わせます。

---------------------------------------

**$app->moved\_permanently($url);**

301 Moved Permanentlyヘッダをつけて$urlへリダイレクトします。

---------------------------------------

**$app->file\_not\_found($msg);**

error.php,error.htmlの順でファイルを探してファイルが存在すればその内容を、存在しなければステータス404 File Not Foundをつけて文字列$msg(省略時"404 File Not Found.")を返します。

---------------------------------------

**$app->access\_forbidden($msg);**

error.php,error.htmlの順でファイルを探してファイルが存在すればその内容を、存在しなければステータス 403 Access Forbiddenをつけて文字列$msg(省略時"403 Access Forbidden.")を返します。

---------------------------------------

**$app->service\_unavailable($msg);**

error.php,error.htmlの順でファイルを探してファイルが存在すればその内容を、存在しなければステータス503 Service Unavailableをつけて文字列$msg(省略時"503 Service Unavailable.」")を返します。

---------------------------------------

**$app->non\_dynamic\_mtml($content);**

$contentの中の<MTDynamicMTML>〜</MTDynamicMTML>ブロックを削除して<MTNonDynamicMTML>〜</MTNonDynamicMTML>の中のコンテンツをアクティブにします。これらの処理の後最終的にMTタグは削除され、その結果を返します。

---------------------------------------

**$app->is\_valid\_author($ctx,$author,$password[,$permission]);**

$ctx,$author(MT::Authorオブジェクトまたは文字列username),$password情報から正しいユーザーかどうかを判別し、正当なユーザーの場合に1を返します。

---------------------------------------

**$app->get\_mime\_type($extension);**

拡張子から判断してmime_type情報を返します。拡張子がhtmlでユーザーエージェントがDoCoMo2.0を含む場合は'application/xhtml+xml'を、該当するものが見つけられなかったときは'text/plain'を返します。

---------------------------------------

**$app->stash($key[,$val]);**

$keyを指定して値$valをキャッシュします。第二引数を省略した場合はキャッシュした値を取得します。

---------------------------------------

**$app->cache($key[,$val]);**

$app->stash()へのエイリアスです。

---------------------------------------

**$app->save\_entry($entry[,$params]);**

エントリーオブジェクトを保存します。いくつかの設定値が指定されていなければブログのデフォルト値、日時関連のカラムに指定がなければ現在の日時、作成者の指定がなければ現在のユーザーをそれぞれセットします。
$params['categories']\(IDまたはオブジェクトの配列\)、$params['tags']\(文字列またはオブジェクトの配列\)が指定されていれば関連オブジェクトとして保存します。エントリーの保存後、関連するmt\_fileinfo及びmt\_trackbackテーブルが作成またはアップデートされます。

---------------------------------------

**$app->delete\_entry($entry);**

エントリーを削除し、子オブジェクト及び関連オブジェクトを削除します。

---------------------------------------

**$app->make\_atom\_id($entry);**

mt_entry_atom_idに格納する文字列を生成します。

---------------------------------------

**$app->can\_edit\_entry($entry);**

現在のユーザーがエントリーを編集できる権限を持っている時に1を返します。

---------------------------------------

**$app->set\_entry\_categories($entry,$categories);**

エントリーをカテゴリに紐付けます。$categoriesには数値、オブジェクト、または数値の配列、オブジェクトの配列を指定可能です。

---------------------------------------

**$app->set\_tags($object,$tags);**

オブジェクトにタグを付与します。$tagsには文字列、オブジェクトまたは文字列の配列、オブジェクトの配列を指定可能です。

---------------------------------------

**$app->fetch\_tags($object[,$args]);**

オブジェクトに紐づいたタグ(MT::Tagオブジェクト)の配列を返します。
$argsにはinclude\_private,sort\_by,sort\_orderを指定可能です。

---------------------------------------

**$app->get\_tag\_obj($str[,$args]);

タグ名が文字列$strと一致するMT::Tagオブジェクトを返します(なければ作成して生成したオブジェクトを返します)。
$args['no\_generate']を指定した場合、生成されたオブジェクトは保存されません。

---------------------------------------

**$app->model($class);**

オブジェクトを新規に生成します。$entry = $app->model('Entry')は下記のコードと同等です。

    require_once 'class.mt_entry.php';
    $entry = new Entry;

---------------------------------------

**$app->load($class,$terms[,$args]);**

オブジェクト$classを$terms、$argsの条件に従ってロードします。
$termsが数値の場合(ID)、及び$args['limit']に1が指定されている場合に返り値は単一のオブジェクトとなり、それ以外の場合はオブジェクトの配列を返します。$termsの各値には配列を指定可能です(OR検索になります)。

*例:ブログIDとエントリーIDを指定してエントリーを1件ロードする*

    $terms = array( 'blog_id' => $blog_id, 'id' => $id );
    $args  = array( 'limit' => 1 );
    $entry = $app->load( 'Entry', $terms, $args );

    次のように書くこともできます。

    $entry = $app->load( 'Entry', $id );

*例:カテゴリーIDを指定してブログ記事を10件ロードする*

    $terms = array( 'blog_id' => $blog_id,
                    'status'  => 2 );
    $extra = array( 'sort' => 'authored_on',
                    'direction' => 'descend',
                    'limit' => 10 );
    $join = array( 'mt_placement', 'entry_id',
                    array( 'category_id' => $category_id ) );
    $extra[ 'join' ] = $join;
    $entries = $app->load( 'Entry', $terms, $extra );

*例:公開日が今日のブログ記事/ウェブページをロードする*

    $current_ts = $app->current_ts( $blog );
    $ymd = substr( $current_ts, 0, 4 ) . '-' .
           substr( $current_ts, 4, 2 ) . '-' .
           substr( $current_ts, 6, 2 ) ;
    // 2011-01-01 or
    // $current_ts = $app->db()->ts2db( $current_ts );
    // $ymd = preg_replace( '/^(([0-9][^0-9]*){8}).*$/', '$1', $current_ts );
    $terms = array( 'blog_id' => $blog->id,
                    'class'   => array( 'entry', 'page' ),
                    'authored_on' => array( 'like' => $ymd . '%' ),
                    'status'  => 2 );
    $entries = $this->load( 'Entry', $terms );

*例:日付を範囲指定してブログ記事をロードする*

    $terms = array( 'blog_id' => $blog_id,
                    'authored_on' => array( '20110101000000',
                                            '20110131235959' ),
                    'status'  => 2 );
    $extra = array( 'sort' => 'authored_on',
                    'direction' => 'descend',
                    'range_incl' => array( 'authored_on' => 1 ),
                    // or 'start_val' => '20110131000000',
                    );
    $entries = $this->load( 'Entry', $terms, $extra );

---------------------------------------

**$app->get\_by\_key($class,$terms);**

$class,$termsからオブジェクトをロードし、条件に一致するものがなければオブジェクトを作成します(戻り値は常に単一のオブジェクトです)。

---------------------------------------

**$app->exists($class,$terms);**

$class,$termsからオブジェクトをロードし、条件に一致するものが存在する場合に1を返します。

---------------------------------------

**$app->column\_values($obj);**

オブジェクトのカラム名とカラム値の配列を返します($obj->GetArray()と同様)。

---------------------------------------

**$app->column\_names($obj);**

オブジェクトのカラム名の配列を返します($obj->GetAttributeNames()と同様)。

---------------------------------------

**$app->touch\_blog([$blog]);**

ブログのchildren_modified_onカラムを現在のタイムスタンプに更新します。

---------------------------------------

**$app->current\_ts([$blog]);**

現在の時刻をYmdHis形式で返します。

---------------------------------------

**$app->build\_tmpl($ctx,$text[,$params]);**

指定した $ctx から $text をビルドした結果を返します。
$params['archive_type'], $params['blog'], $params['basename'], $params['fileinfo']の指定が可能です($paramを省略した場合、現在のブログのインデックス・テンプレートとしてビルドします)。

---------------------------------------

**$app->run\_tasks([$task\_id]);**

config.phpで指定されたtasksを実行します。

*例:プレビューファイルのクリーンアップ*

    <?php
    class CleanTemporaryFiles extends MTPlugin {
        var $app;
        var $registry = array(
            'name' => 'CleanTemporaryFiles',
            'id'   => 'CleanTemporaryFiles',
            'key'  => 'cleantemporaryfiles',
            'tasks' => array(
                'CleanTemporaryFiles' => 
                        array( 'label' => 'Remove Temporary Files',
                               'code'  => 'clean_temporary_files',
                               'frequency' => 3600, ),
            ),
        );
        function clean_temporary_files ( &$app ) {
            $do;
            $ts = time() - 3600;
            $extra = array( 'sort' => 'start',
                            'direction' => 'descend',
                            'start_val' => $ts );
            $files = $this->app->load( 'Session',
                                        array( 'kind' => 'TF' ),
                                        $extra );
            foreach ( $files as $file ) {
                if ( file_exists( $file->name ) ) {
                    unlink( $file->name );
                }
                if (! file_exists( $file->name ) ) {
                    $file->Delete();
                    $do = 1;
                }
            }
            return $do;
        }
    }
    ?>

---------------------------------------

**$app->run\_workers([$workers\_id]);**

config.phpでクラス内に定義されたされたtask\_workersメソッドを実行します。
task_workersに定義したメソッドには引数($app,$jobs)が渡されます(この時$jobsにはMT::TheSchwartz::Jobオブジェクトの配列が格納されています)。

*例:再構築キューを呼び出す*

    <?php
    class RebuildQueue extends MTPlugin {
        var $app;
        var $registry = array(
            'name' => 'RebuildQueue',
            'id'   => 'RebuildQueue',
            'key'  => 'rebuildqueue',
            'task_workers' => array(
              'mt_rebuild' => array( 'label' => 'Publishes content.',
                                     'code'  => 'workers_mt_rebuild',
                                     'class' => 'MT::Worker::Publish', ),
            ),
        );
        function workers_mt_rebuild ( &$app, $jobs ) {
            $do;
            $start = time();
            $files = 0;
            foreach ( $jobs as $job ) {
                $uniqkey = intval( $job->uniqkey );
                if ( $uniqkey ) {
                    $fileinfo = $app->load( 'FileInfo', $uniqkey );
                    if ( ( $fileinfo ) &&
                        ( $file_path = $fileinfo->file_path ) ) {
                        if ( $output =
                               $app->rebuild_from_fileinfo( $fileinfo, 1 ) ) {
                            if ( $output != NULL ) {
                                if ( $app->content_is_updated(
                                                  $file_path, $output ) ) {
                                    $app->put_data( $output, $file_path );
                                    $args = $app->get_args();
                                    $app->run_callbacks( 'rebuild_file',
                                                         $app->mt(),
                                                         $app->ctx(),
                                                         $args, $output );
                                    $do = 1;
                                    $files ++;
                                }
                            }
                        }
                    }
                }
                $job->Delete();
            }
            if ( $do ) {
                $end = time();
                $time = $end - $start;
                $app->log(
                    $app->translate(
                    '-- set complete ([quant,_1,file,files] in [_2] seconds)',
                    array( $files, $time ) ) );
            }
            return $do;
        }
    }
    ?>

---------------------------------------

**$app->run\_callbacks($callbackname,$mt,$ctx,$args,$content);**

$callbacknameを指定してコールバックを呼び出します。/mt/plugins/PluginName/php/callbacks/以下にpluginname\_callbackname.phpのような名前でファイルを設置することで呼び出すことが出来ます(実行順はプラグイン名の昇順)。
またはMTPluginを継承したクラスを定義することでプラグインファイル内にコールバックをまとめて記述することもできます。MTPluginクラスまたはconfig.phpの項を参照してください。
ビルド処理の後で呼ばれたコールバックでは第4引数$contentにビルドされた結果が含まれているため、&$contentとして値を受け取ることでコンテンツの内容を変更することが可能です。
DynamicMTMLの初期化直後には第3引数$argsには標準では下記の値が含まれています。これらの値は$app->stash()で取得することもできます(プラグイン内で再定義することも可能です)。

    blog_id    : ブログID
    conditional: 条件付き取得が有効かどうか
    use_cache  : キャッシュを利用する設定であるかどうか
    cache_dir  : キャッシュディレクトリへのパス
    file       : 現在のリクエストに対するサーバー上のファイルパス
    base       : $app->base()と同様
    path       : $app->path()と同様
    script     : $app->script()と同様
    request    : 現在のURLからクエリー文字列を削除したURL情報
    param      : $app->query_string()と同様
    is_secure  : $app->is_secure()と同様
    url        : 現在のURL
    contenttype: リクエストに対するmime_type
    extension  : リクエストされているファイルの拡張子
    build_type : dynamic_mtml(DynamicMTML)
                 static_text(MTタグを含まないテキストファイル)
                 binary_data(バイナリファイル)
                 mt_dynamic(MTのダイナミックパブリッシング)

---------------------------------------

## クラス MTPlugin

クラスMTPluginはDynamicMTMLを拡張したクラスで、プラグインの設定を定義したりテンプレートタグやコールバックをまとめて管理できます。また、プラグイン設定に容易にアクセス可能なインターフェイスを備えています。

    $plugin = $app->component( 'Foo' );
    $foo = $plugin->get_config_value( 'foo', 'blog:1' );
    $foo++;
    $plugin->set_config_value( 'foo', $foo, 'blog:1' );


*plugins/MyFirstPlugin/php/config.php に以下のような書式で記述したファイルを設置します。より具体的な例はconfig.phpの項を参照してください。*

    <?php
    class MyFirstPlugin extends MTPlugin {
        var $app;
        var $registry = array(
            'name' => 'MyFirstPlugin',
            'key'  => 'myfirstplugin',
            'config_settings' => array(
                'PluginConfigSetting' => array( 'default' => 0 ),
            ),
            'settings' => array(
                'example_setting' => array( 'default' => 1 ),
            ),
            'tags' => array(
                'block'
                    => array( 'example_block'
                        => 'hdlr_block' ), // ,...
                'function'
                    => array( 'example_function1'
                        => 'hdlr_function' ), // ,...
                'modifier'
                    => array( 'example_modifier1'
                        => 'filter_modifier' ), // ,...
            ),
            'callbacks' => array(
                'build_page' => 'filter_build_page',
                'post_init'  => 'post_init_routine',
            ),
        );
        // Tags or Callbacks...
    ?>


### MT::Plugin(MT::PluginData)互換のメソッド

---------------------------------------

**$plugin->get\_config\_obj($scope);**

該当のプラグインのMT::PluginDataオブジェクトを取得します。MT::PluginDataが存在しない場合はプラグインの初期設定値をセットしたオブジェクトを生成して返します。$scopeは"blog:1"のようにブログIDを含む文字列を渡します。書略した場合はシステムスコープのプラグイン設定を返します。

---------------------------------------

**$plugin->get\_config\_hash($scope);**

MT::PluginDataオブジェクトから設定値のみをキーと値の配列で返します。MT::PluginDataが存在しない場合はプラグインの初期設定値をセットした値を返します。

---------------------------------------

**$plugin->reset\_config($scope);**

MT::PluginDataに保存された値をクリアして設定を初期値にリセットします。

---------------------------------------

**$plugin->config\_vars();**

プラグイン設定のキーの配列を返します。

---------------------------------------

**$plugin->get\_config\_value($key,$scope);**

キーとスコープを指定してプラグイン設定を取得します。MT::PluginDataが存在しない場合は初期値を返します。

---------------------------------------

**$plugin->set\_config\_value($key[,$value,$scope]);**

キーと値、スコープを指定してプラグイン設定を更新します。$keyにキーと値の配列を渡した場合、第二引数$valueがスコープとなります。

---------------------------------------

## dynamicmtml.util.php

    require_once('dynamicmtml.util.php');
    $res = some_function($param1,$param2);

---------------------------------------

**get\_agent($wants,$like);**

$app->get\_agent($wants,$like)と同様です。

---------------------------------------

**get\_param($param);**

$app->param($param)と同様です。

---------------------------------------

**convert2thumbnail($text[,$type],$embed[,$link,$dimension]);**

テキスト中に含まれている画像を$typeで指定したフォーマットに(省略の場合はauto(自動判別))変換して$embedで指定したピクセル値の画像に変換し、テキスト中のパスを書き換えます。$dimensionで'width'または'height'(省略の場合'width')を、$linkに数値指定がある場合、画像を$linkピクセルの画像へのリンクにします。

---------------------------------------

**path2url($input\_uri,$site\_url[,$url]);**

HTMLソースの中のリンク先URL文字列と$site\_url(ブログのサイトURL)からhttpから始まるフルURLを生成して返します。起点となるページのURLが分かっている場合、第3引数$urlで起点となるページのURLを指定します。

---------------------------------------

**referral\_site();**

HTTP\_REFERERから流入元サイトのプロトコルとFQDNを返します(例:http://www.google.co.jp/)。

---------------------------------------

**referral\_search\_keyword($ctx[,$array]);**

Google,Yahoo!,bing,msn,goo(またはサイト内検索(パラメタ名はquery'または'search'))からの流入時にHTTP\_REFERERから検索ワードを抽出します。第二引数$arrayを指定した場合、検索キーワードの配列を代入します。

---------------------------------------

**make\_seo\_basename($title,$length);**

$titleからURLに利用できる文字のみを先頭から$length文字抽出してURLエンコードした文字列basenameを返します。URLに利用できない文字は'\_'に置換されます。

---------------------------------------

**\_\_get\_next\_year($timestamp);**

渡されたタイムスタンプから次の年の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get_previous_year($timestamp);**

渡されたタイムスタンプから前の年の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_next\_month($timestamp);**

渡されたタイムスタンプから次の月の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_previous\_month($timestamp);**

渡されたタイムスタンプから前の月の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_next\_week($timestamp);**

渡されたタイムスタンプから次の週の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_previous\_week($timestamp);**

渡されたタイムスタンプから前の週の最初の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_next\_day($timestamp);**

渡されたタイムスタンプから次の日の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_get\_previous\_day($timestamp);**

渡されたタイムスタンプから前の日の日付けを生成してYmdHis形式で返します。

---------------------------------------

**\_\_date2ts($str);**

2011-01-01等の日付けからYmdHis形式の文字列を返します。

---------------------------------------

**\_\_umask2permission($umask);**

与えられたumask値から3桁のパーミッションナンバーを返します。

---------------------------------------

**\_\_is_hash($array);**

配列がハッシュの場合に1を返します。

---------------------------------------

**\_\_cat\_file($dir,$paths);**

$dirと$paths(配列またはスカラー)を繋げてパスを生成します。

---------------------------------------

**\_\_cat\_dir($dir,$paths);**

\_\_cat\_fileのエイリアスです。

---------------------------------------


## PHPプラグインのファイル構成

プラグインディレクトリが /PluginName の時、下記のファイル/ディレクトリを設置することで様々な拡張が可能になります。

- /PluginName/php/config.php (プラグインクラスを定義するファイル)
- /PluginName/php/callbacks/ (コールバックプラグインを設置するディレクトリ)
- /PluginName/php/l10n/ (言語ファイルを設置するディレクトリ)
- /PluginName/php/publishers/ (アーカイブタイプ毎の静的再構築プログラムを設置するディレクトリ)


## config.phpの記述方法

クラスMTPluginを継承したクラス(クラス名はディレクトリ名)を作成し、var $registryに設定値を配列で指定します。配列の各値に対応するメソッドを定義することでテンプレートタグの追加やコールバックの追加を行えます。

*例:*

    <?php
    class DynamicMTML_pack extends MTPlugin {
        var $app;
        var $registry = array(
            'name' => 'DynamicMTML',
            'id'   => 'DynamicMTML',
            'key'  => 'dynamicmtml',
            'author_name' => 'Alfasado Inc.',
            'author_link' => 'http://alfasado.net/',
            'version' => '1.5',
            'description' => 'DynamicMTML is PHP extension for Movable Type.',
            'config_settings' => array( // for mt-config.cgi
                'DynamicForceCompile' => array( 'default' => 0 ),
                'DisableCompilerWarnings' => array( 'default' => 0 ),
                'UserSessionTimeoutNoCheck' => array( 'default' => 0 ),
                'DynamicSiteBootstrapper'
                    => array( 'default' => '.mtview.php' ),
            ),
            'settings' => array( // PluginSettings
                'example_setting' => array( 'default' => 1 ),
            ),
            'tags' => array( // Template Tags
                'block'    => array( 'example_block'     => 'hdlr_block' ),
                'function' => array( 'example_function' => 'hdlr_function' ),
                'modifier' => array( 'example_modifier' => 'filter_modifier' ),
            ),
            'tasks' => array( // Tasks
                'FuturePost' => array( 'label' => 'Publish Scheduled Entries',
                                       'code'  => 'publish_scheduled_entries',
                                       'frequency' => 60, ),
                'CleanTemporaryFiles'
                             => array( 'label' => 'Remove Temporary Files',
                                       'code'  => 'clean_temporary_files',
                                       'frequency' => 3600, ),
            ),
            'task_workers' => array( // Workers
                'mt_rebuild' => array( 'label' => 'Publishes content.',
                                       'code'  => 'workers_mt_rebuild',
                                       'class' => 'MT::Worker::Publish', ),
            ),
            'callbacks' => array( // Callbacks
                'build_page' => 'filter_build_page',
                'post_init'  => 'post_init_routine',
            ),
        );
    
        function post_init_routine ( $mt, &$ctx ) {
            // ... Callback
        }
    
        function filter_build_page ( $mt, &$ctx, &$args, &$content ) {
            // ... Callback($content is build result.)
            return 1;
        }
    
        function hdlr_block ( $args, $content, &$ctx, &$repeat ) {
            // ... Block Tag
            return $content;
        }
    
        function hdlr_if_block ( $args, $content, &$ctx, &$repeat ) {
            // ... Block Tag(Conditional Tag)
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
                                                             // OR FALSE
        }
    
        function hdlr_function ( $args, &$ctx ) {
            // ... Function Tag
            return $this->app->translate( 'Welcome to Movable Type' );
        }
    
        function filter_modifier ( $text, $arg ) {
            // ... Modifier
            return $text;
        }
    
        function clean_temporary_files ( &$app ) {
            // ... Task(Example:Clean up temporary mt-preview files.)
            return 1;
        }
    
        function publish_scheduled_entries ( &$app ) {
            // ... Task(Example:Publish future posts.)
            return 1;
        }
    
        function workers_mt_rebuild ( &$app, $jobs ) {
            // ... Worker(Example:Rebuild Queue.)
            return 1;
        }
    }
    ?>

config\_settingsに指定した値はmt-config.cgiに記述がない時、plugin\_settingsに指定した値はプラグイン設定が保存されていない時のデフォルト値となります。

*上記の例では、*

$app->config( 'DynamicForceCompile' ) を呼び出した時、mt-config.cgiに記述がない場合に0を返します。
$app->plugin\_get\_config\_value( 'DynamicMTML','example\_setting' )を呼び出した時、プラグイン設定が保存されていな場合に1を返します(*プラグインディレクトリ直下にconfig.yamlが存在する場合、config.phpのロードの前にconfig.yamlをロードし、config.phpがない場合はconfig.yamlで定義した値を返します*)。


## コールバック(DynamicMTMLの処理におけるデフォルト・コールバック)

---------------------------------------

**init\_request()**

アプリケーションの実行前に呼び出されます。

---------------------------------------

**pre\_run($mt,$ctx,$args)**

MT::get\_instanceを呼び出す直前に呼び出されます。この段階では$mt,$ctxは未定義です。$argsに初期値が格納されています。

---------------------------------------

**post\_init($mt,$ctx,$args)**

MT::get\_instanceの直後(DBへのアクセスが確立されMTが初期化された後)に$mt,$ctx,$argsパラメタを付けて呼び出されます。

---------------------------------------

**mt\_init\_exception($mt,$ctx,$args,$error)**

MT::get\_instanceに失敗した時に呼び出されます。この段階では$mt,$ctxは未定義です。$errorにはMTInitExceptionによってthrowされたエラーメッセージが格納されています。独自に定義したエラーを返したり他のデータベースへ接続をリトライして処理を続行する等の処理の実装が可能です。

---------------------------------------

**pre\_resolve\_url($mt,$ctx,$args)**

DynamicMTML処理が行われる際に(存在するファイルをビルドするケース)resolve\_urlをコールする直前に呼び出されます。この時 $args['build\_type'] には'dynamic\_mtml'が格納されています。

---------------------------------------

**post\_resolve\_url($mt,$ctx,$args)**

DynamicMTML処理が行われる際に(存在するファイルをビルドするケース)resolve\_urlの直後に呼び出されます。この時 $args['build\_type'] には'dynamic_mtml'が、$args['fileinfo'] にはresolve\_urlの実行結果(MT::FileInfoオブジェクト)が格納されます。

---------------------------------------

**pre\_build\_page($mt,$ctx,$args)**

ページを読み込む直前(build\_typeが'binary\_data'の場合は値が返される直前、MTタグをビルド実行する場合はその直前)に呼び出されます。
build\_typeが'dynamic\_mtml'の場合、$app->stash('text')にビルドされるファイルのデータが含まれています。この内容を書き換えることでビルドされる前にテンプレートに対して処理を行うことができます。

---------------------------------------

**build\_page($mt,$ctx,$args,$content)**

build\_typeが'dynamic\_mtml(DynamicMTML)','static\_text(MTタグを含まないテキストファイル)','mt\_dynamic(MTのダイナミックパブリッシング)'の時にページがビルドされた直後に実行されます。第4引数$contentにビルドされた結果が格納されています。&$contentとして値を受け取ることで、ビルド結果に対して処理を行うことが出来ます。
build\_typeが'binary\_data'以外の場合、このコールバックの直後にクライアントに対してデータが返されます。
この時、引数$mtはSmartyを拡張したクラスMTが、$contentにはビルドされたコンテンツが入っていますので、既存のSmartyプラグイン(outputfilterやmodifier)をそのまま利用することが出来ます。

*例:ホワイトスペースをトリミングする*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        require_once( 'outputfilter.trimwhitespace.php' );
        $content = smarty_outputfilter_trimwhitespace( $content, $mt );
    ?>

---------------------------------------

**post\_return($mt,$ctx,$args,$content)**

クライアントへデータをリプライした直後に呼ばれます。build\_typeが'binary\_data(サイズの大きなバイナリファイル)'の時、$contentには何も含まれていません。

---------------------------------------

**pre\_save\_cache($mt,$ctx,$args,$content)**

キャッシュが保存される設定になっている時、キャッシュを保存する直前に呼ばれます。$app->stash('cache')でキャッシュのパス、$contentにキャッシュされる内容が取得できます。

---------------------------------------

**take\_down($mt,$ctx,$args,$content)**

すべての処理が成功したケースで処理の一番最後に呼ばれます。

---------------------------------------

**take_down_error()**

MT::get\_instanceに失敗した状態で最後まで処理が実行された場合に呼ばれます。

---------------------------------------

## コールバック(MT::WeblogPublisher互換メソッドによる再構築時のコールバック)

静的生成の際に呼ばれているかどうかは$app->stash('build\_type') で判別できます。WeblogPublisher互換メソッドによる再構築の時、build\_typeは 'rebuild\_static' または 'publish\_queue'(再構築キューの登録)です。

---------------------------------------

**build\_file\_filter($mt,$ctx,$args)**

再構築の直前に呼ばれます。0を返すと再構築処理は行われません。

---------------------------------------

**build\_page($mt,$ctx,$args,$content)**

再構築の直後、ファイルが書き出される直前に呼ばれます。$contentには再構築後のコンテンツがセットされています。

---------------------------------------

**build\_file($mt,$ctx,$args,$content)**

再構築後、静的ファイルが出力された直後に呼ばれます。

---------------------------------------

### コールバックプラグインのサンプル

---------------------------------------

**init\_request(Basic認証をかける)**

*/plugins/PluginName/php/callbacks/pluginname\_init\_request.php*

    <?php
    function pluginname_init_request () {
        if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] )
            && ( $_SERVER[ 'PHP_AUTH_USER' ] 
                === 'username' && $_SERVER[ 'PHP_AUTH_PW' ]
                === 'password' ) ) {
        } else {
            header( 'WWW-Authenticate: Basic realm=""' );
            header( 'HTTP/1.0 401 Unauthorized' );
            exit();
        }
    }
    ?>

---------------------------------------

**init\_request(別のmt-config.cgiを使って初期化する)**

*/plugins/PluginName/php/callbacks/pluginname\_init\_request.php*

    <?php
    function pluginname_init_request () {
        global $mt_config;
        global $mt_dir;
        $new_config = $mt_dir . DIRECTORY_SEPARATOR .
                                        'mt-alt-config.cgi';
        if ( file_exists ( $new_config ) ) {
            $mt_config = $new_config;
        }
    }
    ?>

---------------------------------------

**init\_request(Perlによる動的ビルドを有効にする)**

*/plugins/PluginName/php/callbacks/pluginname\_init\_request.php*

    <?php
    function pluginname_init_request () {
        global $mt_dir;
        global $app;
        $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR .
            'tools' . DIRECTORY_SEPARATOR . 'rebuild-from-fi';
        if ( file_exists( $perlbuilder ) ) {
            $app->stash( 'perlbuild', 1 );
        }
        $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR .
            'tools' . DIRECTORY_SEPARATOR . 'build-template-file';
        if ( file_exists( $perlbuilder ) ) {
            $app->stash( 'perlbuild', 1 );
        }
    ?>

---------------------------------------

**mt\_init\_exception(デバッグモード指定時にエラー出力して終了する)**

*/plugins/PluginName/php/callbacks/pluginname\_mt\_init\_exception.php*

    <?php
    function pluginname_mt_init_exception
        ( &$mt, &$ctx, &$args, $error ) {
        global $app;
        if ( $app->config( 'DebugMode' ) ) {
            echo htmlspecialchars( $error );
            exit();
        }
    }
    ?>

---------------------------------------

**mt\_init\_exception(mt-alt-config.cgiの設定を使ってインスタンス生成をリトライする)**

*/plugins/PluginName/php/callbacks/pluginname\_mt\_init\_exception.php*

    <?php
    function pluginname_mt_init_exception ( &$mt, &$ctx, &$args, $error ) {
        global $app;
        global $mt_dir;
        $config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-alt-config.cgi';
        if ( file_exists ( $config ) ) {
            global $mt_config;
            global $blog_id;
            $mt_config = $config;
            try {
                $mt = MT::get_instance( $blog_id, $mt_config );
            } catch ( MTInitException $e ) {
                $mt = NULL;
            }
            if ( isset ( $mt ) ) {
                $app->configure( $mt_config );
            }
        }
    ?>

---------------------------------------

**post\_init(ログイン(username,passwordパラメタによるログインと$app->userのセット)/ログアウト)**

*/plugins/PluginName/php/pluginname\_post\_init.php*

    <?php
    function pluginname_post_init ( $mt, &$ctx, &$args ) {
        if ( $app->mode() == 'login' ) {
           $app->login();
        } elsif ( $app->mode() == 'logout' ) {
           $app->logout();
        }
    ?>

---------------------------------------

**post\_init(MTのユーザー名+パスワードを利用してBasic認証をかける)**

*/plugins/PluginName/php/callbacks/pluginname\_post\_init.php*

    <?php
    function pluginname_post_init ( $mt, &$ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) &&
           ( $app->is_valid_author( $ctx, $_SERVER[ 'PHP_AUTH_USER' ],
                                 $_SERVER[ 'PHP_AUTH_PW' ] ) ) ) {
        } else {
            header( 'WWW-Authenticate: Basic realm=""' );
            header( 'HTTP/1.0 401 Unauthorized' );
            exit();
        }
    }
    ?>

---------------------------------------

**post\_init(MTのログイン機能を利用した認証)**

*/plugins/PluginName/php/callbacks/pluginname\_post\_init.php*

    <?php
    function pluginname_post_init ( $mt, &$ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        if (! $timeout = $mt->config( 'UserSessionTimeout' ) ) {
            $timeout = 14400;
        }
        $client_author = $app->get_author( $ctx, $timeout, 'comment' );
        if (! $client_author ) {
            $url = $args[ 'url' ];
            $return_url  = $mt->config( 'CGIPath' );
            $return_url .= $mt->config( 'CommentScript' );
            $return_url .= '?__mode=login&blog_id=' . $ctx->stash( 'blog_id' );
            $return_url .= '&return_url=' . rawurlencode( $url );
            $app->redirect( $return_url );
            exit();
        }
    ?>

---------------------------------------

**post\_init(post_init(http://example.com/entry\_1/パス名/ へのリクエストをhttp://example.com/entry\_1/index.htmlへのリクエストとして処理する)**

例:ブログ記事のアーカイブマップを「entry\_<$mt:EntryID$>/%i」カテゴリのアーカイブマップを「category\_<$mt:CategoryID$>/%i」として、テンプレート内の「<$mt:EntryPermalink$>」を「<$mt:EntryPermalink$><$mt:EntryTitle make_seo_basename="50"$>/」に<$mt:CategoryArchiveLink$>を「<$mt:CategoryArchiveLink$><$mt:CategoryLabel make\_seo\_basename="50"$>/」に変更することで、日本語URLによるページへのアクセスが可能になります。

*/plugins/PluginName/php/callbacks/pluginname\_post\_init.php*

    <?php
    function pluginname_post_init ( $mt, &$ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        $file = $app->stash( 'file' );
        $url  = $app->stash( 'url' );
        $request = $app->stash( 'request' );
        if (! file_exists ( $file ) ) {
            $file = $app->path2index( $file, 'index.html' );
            if ( file_exists ( $file ) ) {
                $request = $app->path2index( $request );
                $url     = $app->path2index( $url );
                $app->stash( 'file', $file );
                $app->stash( 'request', $request );
                $app->stash( 'url', $url );
                $app->stash( 'contenttype', 'text/html' );
                $app->stash( 'extension', 'html' );
                $cache = $app->cache_filename( $ctx->stash( 'blog_id' ),
                                        $file, $app->query_string );
                $app->stash( 'cache', $cache );
            }
        }
    ?>

---------------------------------------

**pre\_build\_page(http://example.com/entry\_1/ へのリクエストをエントリー(またはカテゴリ)の名前を用いて生成したURL http://example.com/entry\_1/日本語を含む\_パス名(&lt;$mt:entrypermalink make\_seo\_basename=&quot;50&quot;$&gt;)へ恒久的リダイレクトする)**

*/plugins/PluginName/php/callbacks/pluginname\_pre\_build\_page.php*

    <?php
    function pluginname_pre_build_page ( $mt, &$ctx, &$args ) {
        $app = $ctx->stash( 'bootstrapper' );
        $request = $app->stash( 'request' );
        if ( preg_match( '!/$!', $request ) ) {
            $file = $app->stash( 'file' );
            $blog_id = $app->blog_id;
            if ( file_exists( $file ) 
                && preg_match( '!/index\.html$!', $file ) ) {
                $fileinfo = $app->stash( 'fileinfo' );
                require_once( 'MTUtil.php' );
                if (! isset( $fileinfo ) ) {
                    $fileinfo =
                        $mt->db()->resolve_url(
                            $mt->db()->escape( urldecode( $request ) ),
                                            $blog_id, array( 1, 2, 4 ) );
                }
                if ( isset( $fileinfo ) ) {
                    $app->stash( 'fileinfo', $fileinfo );
                    $entry_id = $fileinfo->entry_id;
                    $category_id = $fileinfo->category_id;
                    if ( $entry_id || $category_id ) {
                        $obj = NULL;
                        if ( $entry_id ) {
                            if ( $fileinfo->archive_type == 'Page' ) {
                                $obj =
                                    $mt->db()->fetch_page( $entry_id );
                            } else {
                                $obj =
                                    $mt->db()->fetch_entry( $entry_id );
                            }
                        } elseif ( $category_id ) {
                            $obj = $mt->db()->fetch_category( $category_id );
                        }
                        if ( isset( $obj ) ) {
                            $title = NULL;
                            if ( $entry_id ) {
                                $title = $obj->title;
                            } elseif ( $category_id ) {
                                $title = $obj->label;
                            }
                            $title = strip_tags( $title );
                            if ( $title ) {
                                require_once ( 'dynamicmtml.util.php' );
                                $title = make_seo_basename( $title, 50 );
                                $url = $request . $title . '/';
                                $app->moved_permanently( $url );
                                exit();
                            }
                        }
                    }
                }
            }
        }
    }
    ?>

---------------------------------------

**build\_page(build_page(携帯キャリアからのアクセス時にソースに含まれる画像をサムネイルに自動変換する)**

*/plugins/PluginName/php/callbacks/pluginname\_build\_page.php*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        $app = $ctx->stash( 'bootstrapper' );
        require_once ( 'dynamicmtml.util.php' );
        if ( $app->get_agent( 'keitai' ) ) {
            $type = 'auto';
            $scope = 'width';
            // $agent = $app->get_agent();
            // if ( $agent == 'DoCoMo' ) {
            //     $type = 'gif';
            // } else {
            //     $type = 'png';
            // }
            $content = convert2thumbnail( $content, $type, 100, 480, $scope );
        }
    }
    ?>

---------------------------------------

**build\_page(携帯キャリアからのアクセス時にShift_JIS変換する)**

*/plugins/PluginName/php/callbacks/pluginname\_build\_page.php*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        if ( $app->get_agent( 'keitai' ) ) {
            $charset = strtolower( $ctx->mt->config( 'PublishCharset' ) );
            $charset = preg_replace( '/\-/', '_', $charset );
            if ( $charset != 'shift_jis' ) {
                $pattern = '/<\?xml\s*version\s*=\s*"1.0"\s*' .
                 'encoding\s*=\s*"UTF-8"\s*\?>/s';
                $replace = '<?xml version="1.0" encoding="Shift_JIS"?>';
                $content = preg_replace( $pattern, $replace, $content );
                $pattern ='/<meta\s*http\-equiv\s*=\s*' .
                    '"Content\-Type"\s*content\s*=\s*"text\/html;\s*' .
                    'charset=UTF\-8"\s*\/>/s';
                $replace = '<meta http-equiv="Content-Type"' . 
                    ' content="text/html; charset=Shift_JIS" />';
                $content = preg_replace( $pattern, $replace, $content );
                $content
                    = mb_convert_encoding( $content, 'SJIS-WIN', 'UTF-8' );
            }
        }
    ?>

---------------------------------------

**build\_page(検索エンジン,サイト内検索から流入したユーザーの検索キーワードをハイライト表示する)**

*/plugins/PluginName/php/callbacks/pluginname\_build\_page.php*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
            $head = $html[1];
            $text = $html[2];
            require_once ( 'dynamicmtml.util.php' );
            $tag_start  = '<strong class="search-word">';
            $tag_end    = '</strong>';
            $qtag_start = preg_quote( $tag_start, '/' );
            $qtag_end   = preg_quote( $tag_end, '/' );
            $keywords   = array();
            $phrase = referral_search_keyword( $ctx, $keywords );
            foreach ( $keywords as $keyword ) {
                $keyword = htmlspecialchars( $keyword );
                $keyword = trim( $keyword );
                $keyword = preg_quote( $keyword, '/' );
                $pattern1 = "/(<[^>]*>[^<]*?)($keyword)/i";
                $replace1 = '$1' . $tag_start. '$2' . $tag_end;
                $pattern2 = "/($qtag_start)$qtag_start" .
                            "($keyword)$qtag_end($qtag_end)/i";
                $replace2 = '$2$3$4';
                $i = 0;
                while (! $end ) {
                    $original = $text;
                    $text = preg_replace( $pattern1, $replace1, $text );
                    //Nest tag
                    $text = preg_replace( $pattern2, $replace2, $text );
                    if ( $text == $original ) {
                        $end = 1;
                    }
                    $i++;
                    //Infinite loop
                    if ( $i > 20 ) $end = 1;
                }
                unset( $end );
            }
            $content = $head . $text;
        }
    ?>

---------------------------------------

**build\_page(電話番号をリンクに置換する)**

*/plugins/PluginName/php/callbacks/pluginname\_build\_page.php*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        if ( $app->get_agent( 'keitai' ) || $app->get_agent( 'smartphone' ) ) {
            if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
                $head = $html[1];
                $text = $html[2];
                require_once ( 'dynamicmtml.util.php' );
                $tag_1 = '<a href ="tel:';
                $tag_2 = '">';
                $tag_3 = '</a>';
                $pattern1 = '/(<[^>]*>[^<]*?)(0\d{1,4}-\d{1,4}-\d{3,4})/';
                $replace1 = '$1' . $tag_1 . '$2' . $tag_2 . '$2' . $tag_3;
                $pattern2 = '/(<a.*?>\/*)<a.*?>(0\d{1,4}-\d{1,4}-' .
                            '\d{3,4})<\/a>([^<]*?<\/a>)/';
                $replace2 = '$2$3$4';
                $i = 0;
                while (! $end ) {
                    $original = $text;
                    $text = preg_replace( $pattern1, $replace1, $text );
                    //Nest tag
                    $text = preg_replace( $pattern2, $replace2, $text );
                    if ( $text == $original ) {
                        $end = 1;
                    }
                    $i++;
                    //Infinite loop
                    if ( $i > 20 ) $end = 1;
                }
                unset( $end );
                $content = $head . $text;
            }
        }
    ?>

---------------------------------------

**build\_page(ホワイトスペースを除去する)**

*/plugins/PluginName/php/callbacks/pluginname\_build\_page.php*

    <?php
    function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
        require_once( 'outputfilter.trimwhitespace.php' );
        $content = smarty_outputfilter_trimwhitespace( $content, $mt );
    ?>

---------------------------------------

**post\_return(アクセスログを記録する)**

*/plugins/PluginName/php/callbacks/pluginname\_post\_return.php*

    <?php
    function pluginname_post_return ( $mt, &$ctx, &$args, &$content ) {
        $app = $ctx->stash( 'bootstrapper' );
        $url = $app->stash( 'url' );
        if ( $url ) {
            $app->log( $url );
        }
    }
    ?>

---------------------------------------

**post\_return(検索経由のアクセスの場合にキーワードをアクセスログに記録する)**

*/plugins/PluginName/php/callbacks/pluginname\_post\_return.php*

    <?php
    function pluginname_post_return ( $mt, &$ctx, &$args, &$content ) {
        $keyword = referral_search_keyword( $ctx );
        if ( $keyword ) {
            $keyword = trim( $keyword );
            $url = $app->stash( 'url' );
            $referral_site = referral_site();
            $app->log( "url : $url\nreferral_site : " .
                       "$referral_site\nkeyword : $keyword" );
        }
    }
    ?>


## 言語ファイルについて

日本語の場合、/plugins/PluginName/php/l10n/l10n\_ja.php に下記のように記述します。

    <?php
    $Lexicon = array(
        'Hi, [_1]' => 'こんにちは、[_1]さん',
        'Username' => 'ユーザー名',
        'Password' => 'パスワード',
        'Sign in'  => 'サインイン',
        'Sign out' => 'サインアウト',
    );
    ?>

このテーブルは初期化の際に読み込まれ、MTTransタグまたは$app->translate()を呼び出した時にユーザーの言語によって翻訳した結果を返します。


## アーカイブの再構築について

/plugins/PluginName/php/publishers/以下にアーカイブの再構築のためのプログラムを設置することで$app->rebuild_archivesメソッドによって静的ファイルを含むアーカイブを再構築することができます。
$app->rebuild_archivesに渡すパラメタについては $app->rebuild\_archives の項を参照してください。

*例:すべてのカテゴリーアーカイブを再構築する*

*/plugins/PluginName/php/publishers/AllCategories.php*

    <?php
        $categories = $this->load( 'Category', array( 'blog_id' => $blog->id );
        foreach ( $categories as $category ) {
            if (! $this->rebuild_category(
                    array( 'category' => $category,
                           'build_type' => $build_type ) ) ) {
                return $this->ctx()->error( 
                    $this->translate( 'Publish error at archivetype [_1].',
                        $this->translate( 'Category' ) ) );
            }
            $do = 1;
        }
    ?>

このファイルを設置して、次のように呼び出すことですべてのカテゴリーアーカイブを再構築できます。

    $app->rebuild_archives( array( 'blog' => $blog,
                                   'recipe' => ( 'AllCategories' )
                                   'build_type' => array( 1, 2, 3, 4 ) ) );
