# Patreon feed

3rd party library to pull posts from a creator's Patreon feed and generate an RSS file.

### Usage

```php
// Get Patreon posts
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$posts = $patreon->getPosts();
print_r($posts);
```

```php
// Get Patreon user data
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$user = $patreon->getUser();
print_r($user);
```

```php
// Get Patreon campaign data
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$camp = $patreon->getCampaign();
print_r($camp);
```

```php
// Get rss feed 
$patreon = new \daemionfox\Patreon\Feed("SomeCreator");
$rss = $patreon->rss();
echo $rss;
```

### Caching

Feed has the ability to set a cache path and turn on caching via static methods.

Feed::setUseCache(true);
Feed::setCachePath('/path/to/cache');

It will automatically attempt to save and load from cache if caching is set, and the cache timeout is 15 minutes.  
By default, caching is off, and the cache path is set to /tmp

### Thanks to

The inspiration and base code for this came from @splitbrain at: https://github.com/splitbrain/patreon-rss

I've shamelessly lifted his code and included it as the base class for the Patreon/Feed class.


### Changelog

###### 0.1 
 * Initial Commit
 
###### 0.2
 * Changed to OOP architecture
 
###### 0.2.1
 * Updated readme, and moved cachePath to a static property
