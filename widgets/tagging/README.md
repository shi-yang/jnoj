#Tagging for Yii2

Turn delimited data into tags

There are multiple extensions for brining tagging functionality into Yii but **Tagging** aims to be the simplest. Built for Yii2, **Tagging** does not use additional tables, models, or relationships to store or manage tags or how often they appear.  Instead, it works with an existing field of your choosing and turns its contents into tags.

We'll use the following `posts` table as an example.

<table>
    <thead>
        <tr>
            <th>id</th>
            <th>post_title</th>
            <th>post_body</th>
            <th>tags</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>My first post!</td>
            <td>body of new blog</td>
            <td>welcome</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Why Tagging is great</td>
            <td>some more content</td>
            <td>yii2-tagging,tutorial</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Advanced Tagging tips</td>
            <td>most recent post</td>
            <td>yii2-tagging,tutorial,advanced</td>
        </tr>
    </tbody>
</table>

Tagging includes two classes: _TaggingQuery_ and _TaggingWidget_.  _TaggingQuery_ returns a php array which can be used in form's select field or as input to _TaggingWidget_ which can display the tags as a list or tag cloud.

Keep in mind that your tags don't need to be comma-delimited. You can use practically any delimiter you like!

##Installation

###Install the extension

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist justinvoelker/yii2-tagging "*"
```

or add

```
"justinvoelker/yii2-tagging": "*"
```

to the require section of your `composer.json` file.

### Styles

Follow one of the directions below to use the styles necessary for the tag_cloud

#### Option 1: Manually combine stylesheets

Open your the `vendors\justinvoelker\yii2-tagging\css` directory and add the styles found in `tagging.css` to your own stylesheet.

#### Option 2: Include the delivered asset bundle

Add `justinvoelker\tagging\TaggingAsset` as a dependency in your `assets\AppAsset` file. It should look similar to the following:

```
...
public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'justinvoelker\tagging\TaggingAsset',
];
...
```

##Usage

###TaggingQuery

To create a php array of key=>value pairs (where key is the tag and value is the frequency of that tag), use TaggingQuery:

```php
$query = new TaggingQuery;
$tags = $query
    ->select('tags')
    ->from('posts')
    ->getTags();
```

###TaggingWidget

To create a tag cloud or list, use TaggingWidget:

```php
echo TaggingWidget::widget([
    'items' => $tags,
]);
```

###TaggingQuery and TaggingWidget

If necessary, you can combine the two. If you have multiple widgets on the same page that will use roughly the same set of data, simple pass the results of an initial _TaggingQuery_ into another _TaggingQuery_ for further limiting or ordering as follows:

```php
$query = new TaggingQuery;

$tagsA = $query
    ->select('tags')
    ->from('posts')
    ->displaySort(['name' => SORT_ASC])
    ->getTags();
echo TaggingWidget::widget([
    'items' => $tagsA,
    'format' => 'cloud',

$tagsB = $query
    ->items($tagsA)
    ->displaySort(['freq' => SORT_DESC])
    ->getTags();
echo TaggingWidget::widget([
    'items' => $tagsB,
    'format' => 'list',
]);
```

The above example will only use one database query but will display two TaggingWidgets. The first will be a tag cloud of tags sorted by name whereas the second will be an unordered list (ul) sorted from most to least frequent.

##Available Options

Many options are available for selecting and formatting the data and results. The following examples show which options are available. Exploring the code will give further descriptions of each option.

###TaggingQuery

Only `select` and `from` or an array of `items` are required. If all three are specified, the `items` values will be used. Note that `exclude` and `includeOnly` would likely not be used at the same time which is why `includeOnly` is commented out in the examples below.  Also commented out for this example is `items`.

Since _TaggingQuery_ extends `yii\db\Query` you can use any other properties available to that class as well.  While using `where` may come in handy, be careful not to use properties such as `distinct` which could have unintended consequences.

The following _TaggingQuery_ would result in an array of the 50 most frequent comma-separated tags from the post table, except for the 'junk' tag, displayed alphabetically.

```php
$query = new TaggingQuery;
$tags = $query
    // used to refine an existing list
    //->items($existingTaggingQueryResults)
    // field that contains the desired tags
    ->select('tags')
    // table to select from
    ->from('post')
    // delimiter (default: `,` (comma))
    ->delimiter(',')
    // number of tags to display (omit for all tags)
    ->limit(50)
    // order used when a limit is being applied
    ->limitSort(['freq' => SORT_DESC, 'name' => SORT_ASC])
    // order of the resulting list
    ->displaysort(['name' => SORT_ASC])
    // array of tags to be excluded
    ->exclude(['junk'])
    // array of tags to be included (all other omitted)
    //->includeOnly(['feature', 'special'])
    // return the array of tag=>frequency pairs
    ->getTags();
```

###TaggingWidget

Only `items` is required.

All of the selecting, limiting, sorting, and excluding/including of tags is performed by _TaggingQuery_. Once a list of items is returned from _TaggingQuery_, it may be passed into a _TaggingWidget_ for display.

Continuing with the previous example, the following _TaggingWidget_ would result in the previously selected tags being displayed in a tag cloud whose text will be justified, with tags ranging in size from 14-22px, and linking to pages in the format of `/index.php?r=post/index&tag=TAGNAME`

```php
echo TaggingWidget::widget([
    // TaggingQuery results
    'items' => $tags,
    // smallest tag size (default: 14)
    'smallest' => '14',
    // largest tag size (default: 22)
    'largest' => '22',
    // unit for tag sizes (default: px)
    'unit' => 'px',
    // display format of 'cloud', 'ul', or 'ol' (default: cloud)
    'format' => 'cloud',
    // url for links (omit for no link)
    'url' => ['post/index'],
    // parameter to be appended to url with tag
    'urlParam' => 'tag',
    // options applied to the list
    'ulOptions' => ['style' => 'text-align:justify'],
    // options applied to the list item
    'liOptions' => [],
    // options applied to the link (if present)
    'linkOptions' => [],
]);
```
