# Patreon feed

3rd party library to pull posts from a creator's Patreon feed and generate an RSS file.

### Usage

#### Caching

To cache any of Campaigns, Posts or Members add this:

```php
\daemionfox\Patreon\API\Campaigns::setAllowCache(true); // Must be true to cache, default is false
\daemionfox\Patreon\API\Campaigns::setCacheDir('/path/to/cache');  // Must be set, cache will fail if not set
\daemionfox\Patreon\API\Campaigns::setCacheDir('numSeconds');  // default 14400 (4 hours)
```

To turn on caching across all things this will work:

```php 
\daemionfox\Patreon\API\APIAbstract::setAllowCache(true); // Must be true to cache, default is false
\daemionfox\Patreon\API\APIAbstract::setCacheDir('/path/to/cache');  // Must be set, cache will fail if not set
\daemionfox\Patreon\API\APIAbstract::setCacheDir('numSeconds');  // default 14400 (4 hours)
```


#### Data

```php
$creator_access_token = 'sometoken_gotten_from_patreon_api_auth';
```

```php
// Get Patreon posts
$patreon = \daemionfox\Patreon\API\Posts::init($creator_access_token);
$posts = $patreon->getPosts();
print_r($posts);
```

```php
// Get Patreon campaign data
$patreon = \daemionfox\Patreon\API\Campaigns::init($creator_access_token);
$campaignID = $patreon->getCampaignID();
$tiers = $patreon->getTiers();
$goals = $patreon->getGoals();
```

```php
// Get Patreon member data
$patreon = \daemionfox\Patreon\API\Members::init($creator_access_token);
$members = $patreon->getMembers();
print_r($members);
```

```php
// Get RSS feed
$patreon = new \daemionfox\Patreon\Feed($creator_access_token);

// Optional:
$patreon->setPostLimit(20); // Sets the number of posts returned in the feed.  Default 10
$patreon->setShowPrivatePosts(true); // Decide if you want to show non-public posts in the feed

// Return the feed
$rss = $patreon->rss();

echo $rss;
```


### Changelog

###### 0.1 
 * Initial Commit
 
###### 0.2
 * Changed to OOP architecture
 
###### 0.2.1
 * Updated readme, and moved cachePath to a static property

###### 3.0
 * Complete re-build from scratch using Patreon API to retrieve data
