# About MTPlugin Wordpress

## Synopsis

MTPlugin Wordpress is Wordpress's template tag to MTML.
This plugin works on DynamicMTML or MT's dynamic publishing.

## Getting Started

1.Install addons/DynamicMTML.pack and plugins/Wordpress.
2.Edit wp-config.cgi(DATABASE SETTINGS).
3.Edit wp-prefix.php(WP_PREFIX).

## Template Tags

*Example:*

    <mt:wp:Entries category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
            <a href="<$mt:wp:EntryPermalink$>">
                <$mt:wp:EntryTitle escape="html"$>
                (<$mt:wp:ENtryAuthorDisplayName$> / <$mt:wp:EntryDate format="%b %Y"$>)
            </a>
            <mt:wp:EntryCategories glue=",">
                <mt:if name="__first__">Categories:</mt:if>
                <a href="<$mt:wp:CategoryLink$>">
                    <$mt:wp:CategoryLabel$>
                </a>
            </mt:wp:EntryCategories>
            <mt:wp:EntryTags glue=",">
                <mt:if name="__first__">Tags:</mt:if>
                <a href="<$mt:wp:TagLink$>">
                    <$mt:wp:TagName$>
                </a>
            </mt:wp:EntryTags>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

---------------------------------------

**mt:wp:Entries(Block Tag)**

*Alias: mt:wp:get\_posts, mt:wp:posts*

A block tag which iterates over a list of published posts from a blog.

*Attributes:*

    id            : Outputs a single post matching the given entry ID.
    category      : Category name
    category_id   : Category id
    tag           : Tag
    status        : Status text or '*'(all), 'publish' is the default.
    type          : Type text or '*'(all), 'post' is the default.
    sort_by       : Defines the data to sort posts.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N posts. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N posts from the start of the list.
    limit         : Load the N posts. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:Entries>
        <$mt:wp:EntryTitle$>
        <$mt:wp:EntryBody$>
    </mt:wp:Entries>

*Example:*

    <mt:wp:Entries category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<$mt:wp:EntryPermalink$>">
                <$mt:wp:EntryTitle escape="html"$>
            </a>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

*You can write like this.*

    <mt:wp:get_posts category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<$mt:wp:the_permalink$>">
                <$mt:wp:the_title escape="html"$>
            </a>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

---------------------------------------

**mt:wp:EntryNext(Block Tag)**

*Alias: mt:wp:next\_post*

A block tag providing a context for the post immediately following the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:EntryPrevious(Block Tag)**

*Alias: mt:wp:previous\_post*

A block tag providing a context for the post immediately preceding the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:EntryPrevious(Block Tag)**

*Alias: mt:wp:previous\_post*

A block tag providing a context for the post immediately preceding the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:Categories(Block Tag)**

*Alias: mt:wp:list\_categories*

A block tag which iterates over a list of category from a blog.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude categories with no posts assigned.
                    The default is false (0).
    toplevel      : Same as mt:wp:SubCategories.
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N categories. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N categories from the start of the list.
    limit         : Load the N categories. N is a positive integer.
    glue          : A string that is output between post.

---------------------------------------

**mt:wp:SubCategories(Block Tag)**

A specialized version of the mt:wp:Categories block tag that respects the hierarchical structure of categories.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude categories with no posts assigned.
                    The default is false (0).
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N categories. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N categories from the start of the list.
    limit         : Load the N categories. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:SubCategories hide_empty="1">
    <mt:wp:SubCatisFirst><ul></mt:wp:SubCatisFirst>
        <li>
            <a href="<mt:wp:CategoryLink>">
            <mt:wp:CategoryLabel> (<mt:wp:CategoryCount>)
            </a>
        <mt:wp:SubCatsRecurse></li>
    <mt:wp:SubCatisLast></ul></mt:wp:SubCatisLast>
    </mt:wp:SubCategories>

---------------------------------------

**mt:wp:Tags(Block Tag)**

A block tag which iterates over a list of 'tag' from a blog.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude tags with no posts assigned.
                    The default is false (0).
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N tags. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N tags from the start of the list.
    limit         : Load the N tags. N is a positive integer.
    glue          : A string that is output between post.

---------------------------------------

**mt:wp:EntryCategories(Block Tag)**

*Alias: mt:wp:get\_the\_category*

A container tag that lists all of the categories to which the post is assigned.
This tagset creates a category context within which any category tags may be used.

---------------------------------------

**mt:wp:EntryTags(Block Tag)**

*Alias: mt:wp:get\_the\_tags*

A container tag that lists all of the tags to which the post is assigned.
This tagset creates a tag context within which any 'tag' tags may be used.

---------------------------------------

**mt:wp:IfCommentsActive**

*Alias: mt:wp:IfCommentsAccepted, *Alias: mt:wp:EntryIfAllowComments*

Conditional tag that displays its contents if comments are enabled for the post in context.

---------------------------------------

**mt:wp:IfPingsAccepted**

*Alias: mt:wp:EntryIfAllowPings*

Conditional tag that displays its contents if trackback are enabled for the post in context.

---------------------------------------

**mt:wp:ArchiveList**

A container tag representing a list of 'Monthly' archive pages.

*Attributes:*

    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N tags. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:ArchiveList archive_type="Monthly">
    <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<mt:wp:ArchiveLink archive_type="Monthly">">
                <mt:wp:archivetitle format="%b %Y">(<mt:wp:ArchiveCount>)
            </a>
        </li>
    <mt:if name="__last__"></ul></mt:if>
    </mt:wp:ArchiveList>

