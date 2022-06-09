<?php
/*
* FILE: classes/Tweeter.class.php
* DATE: 01/08/2022
* DESCRIPTION: class for authentication and posting tweet
* AUTHOR: stucombs at icloud dot com
*/

class Tweeter {
    protected $oauth_access_token;
    protected $oauth_access_token_secret;
    protected $consumer_key;
    protected $consumer_secret;
    protected $url;
    protected $read_file;

    /**
     * @param array $settings array of config settings for account
     * @param string $read_file path to readable file
    */
    public function __construct($settings, $read_file){
        $this->url                          = 'https://api.twitter.com/1.1/statuses/update.json';
        $this->read_file                    = $read_file;
        $this->account_id                   = $settings['ACCOUNT_ID'];
        $this->oauth_access_token           = $settings['ACCESS_TOKEN'];
        $this->oauth_access_token_secret    = $settings['ACCESS_TOKEN_SECRET'];
        $this->consumer_key                 = $settings['CONSUMER_KEY'];
        $this->consumer_secret              = $settings['CONSUMER_SECRET'];
    }

    /**
     * Authenticates and posts a $tweet
     * @param string $tweet a character string in which to post as a 'tweet'
     * @return string (Json object)
    */
    public function postTweet($tweet){
        $oauth_nonce = $this->createNonce();
        $timestamp = time();
        $header_sig = $this->createSignature($oauth_nonce, $timestamp, $tweet);
        $auth_header = $this->createAuthHeader($oauth_nonce, $timestamp, $header_sig);
        $post_request = 'status=' . rawurlencode($tweet);

        //set up cURL request
        $curl_header = array('Authorization: ' . $auth_header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_header);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        //execute
        $results = curl_exec($ch);
        if( curl_errno($ch) ){ return 'ERROR: ' . curl_error($ch); }
        curl_close($ch);
        return $results;
    }

    /**
     * Create authentication header with OAuth1.0a standards 
     * @param string $nonce unique token generated for each request
     * @param string $timestamp unique timestamp for authentication
     * @param string $signature genereated authentication signature from $this->createSignature
     * @return string $oauthheader
    */
    public function createAuthHeader($nonce, $timestamp, $signature){
        $oauth_header = '';
        $oauth_header .= 'OAuth oauth_consumer_key="' . rawurlencode($this->consumer_key) . '",';
        $oauth_header .= 'oauth_nonce="' . rawurlencode($nonce) . '",';
        $oauth_header .= 'oauth_signature="' . rawurlencode($signature) . '",';
        $oauth_header .= 'oauth_signature_method="HMAC-SHA1",';
        $oauth_header .= 'oauth_timestamp="' . rawurlencode($timestamp) . '",';
        $oauth_header .= 'oauth_token="' . rawurlencode($this->oauth_access_token) . '",';
        $oauth_header .= 'oauth_version="1.0"';

        return $oauth_header;
    }

    /**
     * Create authentication signature to Twitter APIv2 standards 
     * @param string $nonce unique token generated for each request
     * @param string $timestamp timestamp for authentication
     * @param string $tweet string to be posted as a 'tweet'
     * @return string $signature
    */
    public function createSignature($nonce, $timestamp, $tweet){
        $oauth_hash = '';
        $oauth_hash .= 'oauth_consumer_key=' . $this->consumer_key;
        $oauth_hash .= '&oauth_nonce=' . $nonce;
        $oauth_hash .= '&oauth_signature_method=HMAC-SHA1';
        $oauth_hash .= '&oauth_timestamp=' . $timestamp;
        $oauth_hash .= '&oauth_token=' . $this->oauth_access_token;
        $oauth_hash .= '&oauth_version=1.0';
        $oauth_hash .= '&status=' . rawurlencode($tweet);

        $base = 'POST&' . rawurlencode($this->url) . '&' . rawurlencode($oauth_hash);
        $key = rawurlencode($this->consumer_secret) . '&' . rawurlencode($this->oauth_access_token_secret);

        $signature = base64_encode(hash_hmac('sha1', $base, $key, true));
        return $signature;
    }

    /**
     * Generate a unique token for each request
     * @return string $nonce
    */
    public function createNonce(){
        $nonce = base64_encode(uniqid());
        $nonce = preg_replace('~[\W]~','',$nonce);
        return $nonce;
    }

    /**
     * Retrieve random line from readfile contents
     * @return string $line
    */
    public function getTweetText(){
        $f_contents = file($this->read_file);
        $line = '';
        while( $line === '' ){
            $line = $f_contents[array_rand($f_contents)];
        }
        return $line;
    }
}

?>
