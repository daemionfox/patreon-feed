<?php
/**
 * Created by PhpStorm.
 * User: sowersm
 * Date: 5/27/17
 * Time: 5:04 PM
 */

namespace daemionfox\Patreon;


/**
 * Class PatreonRSS
 * @package daemionfox\Patreon
 *
 * Originally taken from https://github.com/splitbrain/patreon-rss
 * This is a cleaned up version of splitbrain's PatreonRSS class
 *
 * - Changed the constructor to accept no/null input for the creator_id
 * - Added a setter for the creator_id
 * - Uncommented certain fields to give more info
 * - Changed hardcoded API uri to constant
 */
class PatreonRSS
{

    const PATREON_API = "https://api.patreon.com/stream?json-api-version=1.0";

    /** @var array which fields to include in the response, for now we don't need much */
    protected $fields = array(
        'post' =>
            array(
                'post_type',
                'title',
                'content',
                'min_cents_pledged_to_view',
                'published_at',
                'url',
                //'comment_count',
                //'like_count',
                'post_file',
                'image',
                'thumbnail_url',
                'embed',
                //'is_paid',
                //'pledge_url',
                //'patreon_url',
                //'current_user_has_liked',
                //'patron_count',
                //'current_user_can_view',
                //'current_user_can_delete',
                //'upgrade_url',
            ),
        'user' =>
            array(
                'image_url',
                'full_name',
                'url',
            )
    );

    /** @var array haven't really played with those, except the creator id */
    protected $filter = array(
        'is_following' => true,
        // other keys, see #setCreatorId
        // 'is_by_creator'
        // 'creator_id'
        // 'contains_exclusive_posts'
    );

    /** @var string|null which contains the session key for this user, if available, otherwise null. */
    protected $session_id = null;

    /**
     * PatreonRSS constructor.
     * @param string $id
     *
     * Changed - Allow the creator id to be null.  We want to be able to set it later if need be
     */
    public function __construct($id = null)
    {
        if (!empty($id)) {
            $this->setCreatorID($id);
        }
    }

    /**
     * @param $id
     * @return $this
     */
    public function setCreatorID($id)
    {
        $this->filter = array_merge($this->filter, array(
            'is_by_creator' => true,
            'is_following' => false,
            'creator_id' => $id,
            'contains_exclusive_posts' => true
        ));
        return $this;
    }

    /**
     * Output the RSS directly to the browser
     */
    public function rss()
    {
        $data = $this->getData();
        echo '<?xml version="1.0"?>';
        echo '<rss version="2.0">';
        echo '<channel>';
        $this->printRssChannelInfo($data['campaign'], $data['user']);
        foreach ($data['posts'] as $item) {
            $this->printRssItem($item);
        }
        echo '</channel>';
        echo '</rss>';
    }

    /**
     * Output the RSS but use a cache
     *
     * Note: this does absolutely no error checking and will just ignore errors. You have
     * to make sure the given $dir exists and is writable. Otherwise there will be no caching
     *
     * @param string $dir directory in which to store cache files - has to be writable
     * @param int $maxage maximum age for the cache in seconds
     */
    public function cachedRSS($dir, $maxage)
    {
        $cachefile = $dir.'/'.$this->filter['creator_id'].'.xml';
        $lastmod = @filemtime($cachefile);
        if(time() - $lastmod < $maxage) {
            echo file_get_contents($cachefile);
            return;
        }
        ob_start();
        $this->rss();
        $rss = ob_get_clean();
        @file_put_contents($cachefile, $rss); // we just ignore any errors
        echo $rss;
    }

    /**
     * Constructs the URL based on the fields and filter config at the top
     *
     * @return string
     */
    protected function getURL()
    {
        $url = self::PATREON_API;

        foreach ($this->fields as $type => $set) {
            $url .= '&fields[' . $type . ']=' . rawurlencode(join(',', $set));
        }

        foreach ($this->filter as $key => $val) {
            if ($val === true) $val = 'true';
            if ($val === false) $val = 'false';

            $url .= '&filter[' . $key . ']=' . $val;
        }

        $url .= '&page[cursor]=null';

        return $url;
    }

    /**
     * Fetches the data from Patreon and cleans it up for our usecase
     *
     * @return array
     */
    public function getData()
    {
        $url = $this->getURL();
        $json = $this->getStream($url);
        $data = json_decode($json, true);

        $clean = array(
            'posts' => array(),
            'user' => array(),
            'campaign' => array()
        );
        foreach ($data['data'] as $item) {
            $clean['posts'][] = $item['attributes'];
        }
        foreach ($data['included'] as $item) {
            if ($item['type'] == 'user') {
                $clean['user'] = $item['attributes'];
                $clean['user']['id'] = $item['id'];
                continue;
            }
            if ($item['type'] == 'campaign') {
                $clean['campaign'] = $item['attributes'];
                $clean['campaign']['id'] = $item['id'];
            }
        }

        return $clean;
    }

    /**
     * Obtains the stream JSON
     * @return string
     */
    private function getStream($url)
    {
        if($this->session_id == null) // No session key, no cookies required
        {
            return file_get_contents($url);
        } else {

            $opts = array('http' =>
                array(
                    'header'  => array(
                        "Cookie: session_id={$this->session_id}"
                    )
                )
            );

            $context = stream_context_create($opts);
            $handle = fopen($url, "rb", false, $context);

            $contents = stream_get_contents($handle);
            fclose($handle);

            return $contents;
        }
    }

    /**
     * Print a single post as RSS item
     *
     * @param array $item
     */
    protected function printRssItem($item)
    {
        echo '<item>';
        echo '<title>';
        echo htmlspecialchars($item['title']);
        echo '</title>';
        echo '<description>';
        echo htmlspecialchars($item['content']);
        echo '</description>';
        echo '<link>';
        echo htmlspecialchars($item['url']);
        echo '</link>';
        echo '<guid>';
        echo htmlspecialchars($item['url']);
        echo '</guid>';
        echo '<pubDate>';
        echo date('r', strtotime($item['published_at']));
        echo '</pubDate>';
        echo '</item>';
    }

    /**
     * Print the channel info from our campaign and user data
     *
     * @param array $campaign
     * @param array $user
     */
    protected function printRssChannelInfo($campaign, $user)
    {
        echo '<title>';
        echo htmlspecialchars($campaign['creation_name'] . ' Patreon Posts');
        echo '</title>';
        echo '<description>';
        echo htmlspecialchars(strip_tags($campaign['summary']));
        echo '</description>';
        echo '<link>';
        echo htmlspecialchars($user['url']);
        echo '</link>';
    }
}
