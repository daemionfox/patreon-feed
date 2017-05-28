Patreon feed
============

3rd party library to pull posts from a creator's Patreon feed and generate an RSS file.

Usage
----

```php
// Get Patreon posts
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$posts = $patreon->getPosts();
print_r($posts);
```

```php
// Get rss feed 
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$rss = $patreon->rss();
echo $rss;
```

Thanks to
----

The inspiration and base code for this came from @splitbrain at: https://github.com/splitbrain/patreon-rss

I've shamelessly lifted his code and included it as the base class for the Patreon/Feed class.


Changelog
----

0.1 
 * Initial Commit
