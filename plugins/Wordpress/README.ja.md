# MTPlugin Wordpress について

## インストール

1. DynamicMTML.packをインストールします。
2. Wordpress ディレクトリを MT\_DIR/plugins へアップロードします。
3. MT\_DIR/plugins/Wordpress/wp-config.cgi にWordpressのデータベース設定を記述します。
4. MT\_DIR/plugins/Wordpress/wp_prefix.php を編集して WP\_PREFIX を適切な値に書き換えます。

**WordpressプラグインはDynamicMTML、ダイナミックパブリッシングまたはAlfie上で動作します。**

*例:*

    <mt:DynamicMTML>
        <mt:wp:Entries limit="20" category="Foo">
            <mt:if name="__first__"><ul></mt:if>
                <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                    <a href="<$mt:wp:EntryPermalink>">
                        <$mt:wp:EntryTitle escape="html"$>
                    </a>
                    <mt:wp:EntryCategories glue=",">
                        <mt:if name="__first__">カテゴリ:</mt:if>
                        <a href="<mt:wp:CategoryLink>">
                            <mt:wp:CategoryLabel>
                        </a>
                    </mt:wp:EntryCategories>
                    <mt:wp:EntryTags glue=",">
                        <mt:if name="__first__">タグ:</mt:if>
                        <a href="<mt:wp:TagLink>">
                            <mt:wp:TagName>
                        </a>
                    </mt:wp:EntryTags>
                        作成者:<$mt:wp:EntryAuthorDisplayName$>
                        公開日:<$mt:wp:EntryDate format="%Y年%m月%d日 %H:%I:%S"$>
                </li>
            <mt:if name="__last__"></ul></mt:if>
        </mt:wp:Entries>
    </mt:DynamicMTML>

## テンプレート・タグ

---------------------------------------

**mt:wp:Entries(ブロックタグ)**

*別名: mt:wp:get\_posts, mt:wp:posts*

ブログのブログ記事の一覧のためのブロックタグです。モディファイアを指定することで、特定のカテゴリのブログ記事だけを抜き出すことや、日付順で指定した数のブログ記事を一覧することができます。

*モディファイア*

    id           : 単一のブログ記事にフィルタリングしたいときに使用します。
    category     : カテゴリ名
    category_id  : カテゴリID
    tag          : タグ名
    type         : タイプ または '*'(すべて)。 デフォルト値は'post(ブログ記事)'です。
    status       : ステータス または '*'(すべて)。 デフォルト値は'publish(公開)'です。
    sort_by      : ブログ記事を並び替える対象を指定します。デフォルト値は"ID"です。
    sort_order   : 並べる順序を指定します。ascend を指定すると昇順 (新しいものが下)、
                   descend は降順 (新しいものが上) です。初期値は descend です。
    offset       : 指定した数のブログ記事を除外して表示します。
                   limit モディファイアと組み合わせて使ってください。
    limit        : 指定数を最大件数としてオブジェクトをロードします。
    lastn        : 指定された条件でフィルタリングした結果を指定数を最大件数として表示します。
    glue         : ループの出力の後に設定する区切り文字を指定します。

*例:*

    <mt:wp:Entries>
        <$mt:wp:EntryTitle$>
        <$mt:wp:EntryBody$>
    </mt:wp:Entries>

*例:*

    <mt:wp:Entries limit="20" category="Foo">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<$mt:wp:EntryPermalink>">
                    <$mt:wp:EntryTitle escape="html"$>
                            (<$mt:wp:EntryDate format="%Y年%m月%d日 %H:%I:%S"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

*以下のように書くこともできます。*

    <mt:wp:get_posts limit="20" category="Foo">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<$mt:wp:the_permalink>">
                    <$mt:wp:the_title escape="html"$>
                            (<$mt:wp:the_date format="%Y年%m月%d日 %H:%I:%S"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:get_posts>

---------------------------------------

**mt:wp:Categories(ブロックタグ)**

*別名: mt:wp:list\_categories*

カテゴリの一覧を表示するためのブロックタグです。
このタグは、メインカテゴリもサブカテゴリも区別せずに表示します。階層的に表示したいときは、mt:wp:SubCategories タグを利用します。

*例:*

    <mt:wp:Categories>
        <$mt:wp:CategoryLabel escape="html"$>
        <$mt:wp:CategoryDescription escape="html"$>
    </mt:wp:Categories>

*モディファイア*

    hide_empty   : ブログ記事が登録されているカテゴリのみを表示します。
    toplevel     : トップレベルカテゴリのみを表示します。
                   mt:wp:SubCategories タグと同様の出力となります。
    sort_by      : カテゴリを並び替える対象を指定します。デフォルト値は"ID"です。
    sort_order   : 並べる順序を指定します。ascend を指定すると昇順 (新しいものが下)、
                   descend は降順 (新しいものが上) です。初期値は descend です。
    offset       : 指定した数のカテゴリを除外して表示します。
                   limit モディファイアと組み合わせて使ってください。
    limit        : 指定数を最大件数としてオブジェクトをロードします。
    lastn        : 指定された条件でフィルタリングした結果を指定数を最大件数として表示します。
    glue         : ループの出力の後に設定する区切り文字を指定します。

---------------------------------------

**mt:wp:SubCategories(ブロックタグ)**

サブカテゴリを階層化してリスト表示するブロックタグです。

    hide_empty   : ブログ記事が登録されているカテゴリのみを表示します。
    sort_by      : カテゴリを並び替える対象を指定します。デフォルト値は"ID"です。
    sort_order   : 並べる順序を指定します。ascend を指定すると昇順 (新しいものが下)、
                   descend は降順 (新しいものが上) です。初期値は descend です。
    offset       : 指定した数のカテゴリを除外して表示します。
                   limit モディファイアと組み合わせて使ってください。
    limit        : 指定数を最大件数としてオブジェクトをロードします。
    lastn        : 指定された条件でフィルタリングした結果を指定数を最大件数として表示します。
    glue         : ループの出力の後に設定する区切り文字を指定します。

*例:(mt:wp:SubCatsRecurse, mt:wp:SubCatisFirst and mt:wp:SubCatisLastの項も参照してください)*

    <mt:wp:SubCategories hide_empty="1">
    <mt:wp:SubCatisFirst><ul></mt:wp:SubCatisFirst>
        <li>
            <a href="<mt:wp:CategoryLink>">
                <mt:wp:CategoryLabel escape="html">
                            (<$mt:wp:CategoryCount$>)
            </a>
        <mt:wp:SubCatsRecurse></li>
    <mt:wp:SubCatisLast></ul></mt:wp:SubCatisLast>
    </mt:wp:SubCategories>

---------------------------------------

**mt:wp:SubcatIsFirst(ブロックタグ)**

カテゴリの一覧の中で現在のサブカテゴリがそのレベルにおいて最初にリストされているときに実行する条件タグです。サブカテゴリを含む一覧を &lt;ul&gt; タグで階層化するときなどに &lt;mt:wp:SubCatIsLast&gt; と組み合わせて利用します。

---------------------------------------

**mt:wp:SubcatIsLast(ブロックタグ)**

カテゴリの一覧の中で現在のサブカテゴリがそのレベルにおいて最後にリストされているときに実行する条件タグです。サブカテゴリを含む一覧を &lt;ul&gt; タグで階層化するときなどに &lt;mt:wp:SubcatIsFirst&gt; と組み合わせて利用します。

---------------------------------------

**mt:wp:EntryCategories(ブロックタグ)**

*別名: mt:wp:get\_the\_category*

ブログ記事に指定したカテゴリの一覧のためのブロックタグです。
このブロックタグの中では &lt;$mt:wp:Category...$&gt; ファンクションタグを利用できます。
モディファイア glue を使うと、カテゴリの区切り文字を指定できます。

---------------------------------------

**mt:wp:Tags(ブロックタグ)**

タグ一覧を表示するためのブロックタグです。

*例:*

    <mt:wp:Tags hide_empty="1">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<mt:wp:TagArchiveLink>">
                    <$mt:wp:TagName escape="html"$>
                                (<$mt:wp:TagCount escape="html"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Tags>

*モディファイア*

    hide_empty   : ブログ記事が登録されているタグのみを表示します。
    sort_by      : タグを並び替える対象を指定します。デフォルト値は"ID"です。
    sort_order   : 並べる順序を指定します。ascend を指定すると昇順 (新しいものが下)、
                   descend は降順 (新しいものが上) です。初期値は descend です。
    offset       : 指定した数のタグを除外して表示します。
                   limit モディファイアと組み合わせて使ってください。
    limit        : 指定数を最大件数としてオブジェクトをロードします。
    lastn        : 指定された条件でフィルタリングした結果を指定数を最大件数として表示します。
    glue         : ループの出力の後に設定する区切り文字を指定します。

---------------------------------------

**mt:wp:EntryTags(ブロックタグ)**

*別名: mt:wp:get\_the\_tags*

特定のブログ記事に設定されたタグ一覧のためのブロックタグです。
モディファイア glue を使うと、タグの区切り文字を指定できます。

---------------------------------------

**mt:wp:EntryNext(ブロックタグ)**

*別名: mt:wp:next\_post*

A block tag providing a context for the post immediately following the current post in context (in terms of date).

---------------------------------------

**mt:wp:EntryPrevious(ブロックタグ)**

*別名: mt:wp:previous\_post*

現在のブログ記事の前のブログ記事の内容を表示するためのブロックタグです。
このタグの中の &lt;$mt:wp:EntryTitle$&gt; ファンクションタグは、前のブログ記事のタイトルを表示します。

---------------------------------------

**mt:wp:IfCommentsAccepted(ブロックタグ)**



*別名: mt:wp:EntryIfAllowComments,mt:wp:IfCommentsActive*

ブログ記事の設定で、コメントを受け付ける設定になっている場合に表示する条件タグです。

---------------------------------------

**mt:wp:IfPingsAccepted(ブロックタグ)**

*別名: mt:wp:EntryIfAllowPings*

ブログ記事の設定でトラックバックが許可されているときに実行する条件タグです。

---------------------------------------

**mt:wp:ArchiveList(ブロックタグ)**

月別('Monthly')または年別('Yearly')アーカイブの一覧を出力させるためのブロックタグです。

*モディファイア*

    archive_type : 'Monthly' または 'Yearly'
    sort_order   : 並べる順序を指定します。ascend を指定すると昇順 (新しいものが下)、
                   descend は降順 (新しいものが上) です。初期値は descend です。
    lastn        : 指定された条件でフィルタリングした結果を指定数を最大件数として表示します。
    glue         : ループの出力の後に設定する区切り文字を指定します。

*例:*

    <mt:wp:ArchiveList archive_type="Monthly">
    <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<mt:wp:ArchiveLink archive_type="Monthly">">
                <mt:wp:ArchiveTitle format="%Y年%m月">
                                (<mt:wp:ArchiveCount>)
            </a>
        </li>
    <mt:if name="__last__"></ul></mt:if>
    </mt:wp:ArchiveList>

---------------------------------------

**mt:wp:BlogInfo(Function Tag)**

ブログに関する情報を出力します(siteurl, blogname, blogdescription, admin\_email and others)。

*例:*

    <mt:wp:BlogInfo name="blogdescription" filters="__default__">

---------------------------------------

**mt:wp:BlogName(Function Tag)**

ブログの名前を表示します。

---------------------------------------

**mt:wp:BlogURL(Function Tag)**

*別名: mt:wp:site\_url*

ブログのURL(サイト URL)を http:// から始まる絶対URLで表示します。

---------------------------------------

**mt:wp:EntryID(Function Tag)**

*別名: mt:wp:the\_ID*

ブログ記事のID番号を表示します。

---------------------------------------

**mt:wp:EntryTitle(Function Tag)**

*別名: mt:wp:the\_title*

ブログ記事のタイトルを表示します。

---------------------------------------

**mt:wp:EntryBody(Function Tag)**

*別名: mt:wp:the\_content*

ブログ記事の本文を表示します。

---------------------------------------

**mt:wp:EntryExcerpt(Function Tag)**

*別名: mt:wp:the\_excerpt*

ブログ記事の概要に入力した内容を表示します。

---------------------------------------

**mt:wp:EntryDate(Function Tag)**

*別名: mt:wp:the\_date*

ブログ記事の作成日時を表示します。モディファイア format と language を指定して、日時の表示方法を変更できます。
[詳しくは、日付に関するテンプレートタグのモディファイアリファレンスを参照ください。](http://www.movabletype.jp/documentation/appendices/date-formats.html)

---------------------------------------

**mt:wp:EntryModifiedDate(Function Tag)**

*別名: mt:wp:the\_modified*

ブログ記事の更新日時を表示します。モディファイア format と language を指定して、日時の表示方法を変更できます。
[詳しくは、日付に関するテンプレートタグのモディファイアリファレンスを参照ください。](http://www.movabletype.jp/documentation/appendices/date-formats.html)

---------------------------------------

**mt:wp:EntryAuthorDisplayName(Function Tag)**

ブログ記事を作成したユーザーの表示名を表示します。

---------------------------------------

**mt:wp:EntryAuthorID(Function Tag)**

*別名: mt:wp:the\_author*

ブログ記事を作成したユーザーのID番号を表示します。

---------------------------------------

**mt:wp:EntryPermalink(Function Tag)**

*別名: mt:wp:the\_permalink*

ブログ記事の絶対URLを表示します(Wordpressのデフォルト形式 http://your.domain.com/wordpress/?p=1)。

---------------------------------------

**mt:wp:EntryGUID(Function Tag)**

*別名: mt:wp:the\_guid*

ブログ記事の guid 値を表示します。

---------------------------------------

**mt:wp:EntryCommentCount(Function Tag)**

*別名: mt:wp:the\_comment\_count*

特定のブログ記事で受け付けたコメントの数を表示します。

---------------------------------------

**mt:wp:EntryMeta(Function Tag)**

*別名: mt:wp:the\_meta*

keyモディファイアを指定してブログ記事のカスタムフィールド値を出力します。

---------------------------------------

**mt:wp:EntriesCount(Function Tag)**

*別名: mt:wp:count\_posts*

ブログ記事の数を出力します。

*モディファイア*

    category     : カテゴリ名
    category_id  : カテゴリID
    tag          : タグ名
    type         : タイプ または '*'(すべて)。 デフォルト値は'post(ブログ記事)'です。
    status       : ステータス または '*'(すべて)。 デフォルト値は'publish(公開)'です。

---------------------------------------

**mt:wp:CategoryLabel(Function Tag)**

*別名: mt:wp:cat\_name*

カテゴリ名を表示します。

---------------------------------------

**mt:wp:CategoryID(Function Tag)**

*別名: mt:wp:cat\_ID*

カテゴリのID番号を表示します。

---------------------------------------

**mt:wp:CategorySlug(Function Tag)**

カテゴリの"スラッグ"を表示します。

---------------------------------------

**mt:wp:CategoryDescription(Function Tag)**

カテゴリの"説明"を表示します。

---------------------------------------

**mt:wp:CategoryCount(Function Tag)**

カテゴリに属するpostの数を表示します。

---------------------------------------

**mt:wp:CategoryArchiveLink(Function Tag)**

*別名: mt:wp:CategoryLink, mt:wp:get_category_link*

カテゴリアーカイブの絶対URLを表示します(Wordpressのデフォルト形式 http://your.domain.com/wordpress/?cat=1)。

---------------------------------------

**mt:wp:SubCatsRecurse(Function Tag)**

現在のカテゴリに属するサブカテゴリの mt:wp:SubCategories ブロックを再帰的に表示します。モディファイア max_depth で、再帰的に表示する深さを指定できます。max_depth="1" と指定すると、このカテゴリの下のサブカテゴリを表示しません。
*モディファイア*

    max_depth    : 展開する繰り返し数を指定します。
                   直下のサブカテゴリのみ展開するときは max_depth="1"、
                   孫カテゴリまで展開するときは max_depth="2" を指定します。

---------------------------------------

**mt:wp:TagName(Function Tag)**

特定のタグの名前を表示します。

---------------------------------------

**mt:wp:TagID(Function Tag)**

タグのID番号を表示します。

---------------------------------------

**mt:wp:TagSlug(Function Tag)**

タグの"スラッグ"を出力します。

---------------------------------------

**mt:wp:TagDescription(Function Tag)**

タグの"説明"を出力します。

---------------------------------------

**mt:wp:TagCount(Function Tag)**

タグがつけられたpostの数を出力します。

---------------------------------------

**mt:wp:TagArchiveLink(Function Tag)**

*別名: mt:wp:TagLink, mt:wp:get\_tag\_link*

タグの絶対URLを表示します(Wordpressのデフォルト形式 http://your.domain.com/wordpress/?tag=foo)。

---------------------------------------

**mt:wp:ArchiveTitle(Function Tag)**

mt:wp:ArchiveListブロックタグの中で、アーカイブタイプのタイトルとなる日付を出力します。
現在のところ月別("Monthly")アーカイブのみ対応しています。

[詳しくは、日付に関するテンプレートタグのモディファイアリファレンスを参照ください。](http://www.movabletype.jp/documentation/appendices/date-formats.html)

---------------------------------------

**mt:wp:ArchiveCount(Function Tag)**

mt:wp:ArchiveListブロックタグの中で、対応するアーカイブに含まれるブログ記事の数を表示します。

---------------------------------------

**mt:wp:ArchiveLink(Function Tag)**

mt:wp:ArchiveListブロックタグの中で、対応するアーカイブの絶対URLを表示します(Wordpressのデフォルト形式 http://your.domain.com/wordpress/?m=yyyymm).

*モディファイア*

    archive_type : 'Monthly'

---------------------------------------
