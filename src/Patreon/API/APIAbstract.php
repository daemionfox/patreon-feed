<?php


namespace daemionfox\Patreon\API;


use daemionfox\Traits\CacheTrait;
use Patreon\API;

abstract class APIAbstract implements PatreonAPIInterface
{
    use CacheTrait;

    protected static $patreonURL = "https://patreon.com";

    protected $accessToken;
    protected $patreon;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->patreon = new API($accessToken);
        $this->get();
    }

    abstract public static function init($token):PatreonAPIInterface;
    abstract public function get():PatreonAPIInterface;

    public function getAPIData($suffix)
    {
        // Fetches details about a campaign - the membership tiers, benefits, creator and goals.  Requires the current user to be creator of the campaign or requires a creator access token
        $data = $this->patreon->get_data($suffix);
        if (isset($data['links']['next'])) {
            $endpoint = $this->patreon->api_endpoint;
            $nextSuffix = str_replace($endpoint, "", $data['links']['next']);
            $newData = $this->getAPIData($nextSuffix);
            $data['data'] = array_merge($data['data'], $newData['data']);
        }
        return $data;
    }

}