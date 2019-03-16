<?php return [
    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be listed as possible RSS feed data sources
    | Routes to rss feeds look like this:
    |       /rss/<name of the feed>/<extra optional parameter>
    | The format of this array is:
    |       <name of the feed as defined in the route parameter> => <namespaced class name to instantiate>
    |
    | Example: we want the rss feed at url "rss/home", we add a 'home' => MyNamespace\MyClassname::class
    | which will be instantiated by the rss controller to display the rss xml.
    |
    */
    'aliases' => [
        'home' => \Naraki\Blog\Support\Rss\Home::class,
        'category' => \Naraki\Blog\Support\Rss\Category::class,
        'author' => \Naraki\Blog\Support\Rss\Author::class,
        'tag' => \Naraki\Blog\Support\Rss\Tag::class,
        'search' => \Naraki\Blog\Support\Rss\Search::class
    ]
];