<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

/**
 * Trait CurlTrait
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
trait CurlTrait
{
    private function defaultOpts()
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HEADERFUNCTION => array($this, 'headerCallback'),
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ];
        if ($this->_autoCookie) {
            if (strncasecmp(PHP_OS, 'Win', 3) === 0) {
                $options[CURLOPT_COOKIEJAR] = 'NUL';
            } else {
                $options[CURLOPT_COOKIEJAR] = '/dev/null';
            }
        }
        return $options;
    }

    /**
     * CURL Options
     * @see curl_setopt_array
     * @var array
     */
    private $_options;

    /**
     * Header recieved with self::headerCallback() function
     * @see CURLOPT_HEADERFUNCTION
     * @var string
     */
    private $_header;

    /**
     * Content
     * @see curl_exec
     * @var string
     */
    protected $_content;

    /**
     * @see curl_getinfo
     * @var array
     */
    protected $_info;

    /**
     * Error code
     * @see curl_errno
     * @var integer
     */
    protected $_errorCode;

    /**
     * Error message
     * @see curl_error
     * @var string
     */
    protected $_errorMessage;

    /**
     * Use /dev/null as a cookie storage.
     * This will prevent the cookies from being written to disk,
     * but it will keep them around in memory as long as you reuse the handle and don't call curl_easy_cleanup()
     * @link http://stackoverflow.com/questions/1486099/any-way-to-keep-curls-cookies-in-memory-and-not-on-disk
     * @var boolean
     */
    protected $_autoCookie = false;

    /**
     * @see CURLOPT_HEADERFUNCTION
     * @param resource $ch
     * @param string $headerLine
     * @return number
     */
    public function headerCallback($ch, $headerLine)
    {
        $this->_header .= $headerLine;
        return strlen($headerLine);
    }

    /**
     * Returns the cookies of executed request
     * @return string|NULL
     */
    public function getCookies()
    {
        if (preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $this->header, $matches)) {
            return implode('; ', array_unique($matches[1]));
        }
        return null;
    }

    /**
     * Checks if it is good http code
     * @return boolean
     */
    public function isHttpOK()
    {
        if (isset($this->_info['http_code'])) {
            return (strncmp($this->_info['http_code'], '2', 1) === 0);
        } else {
            return false;
        }
    }

    /**
     * Getter for CURL Options
     * Use curl_setopt_array() for the CURL resourse
     * @see curl_setopt_array()
     * @return array
     */
    public function getOptions()
    {
        foreach ($this->defaultOpts() as $k => $v) {
            if (!isset($this->_options[$k])) {
                $this->_options[$k] = $v;
            }
        }
        // !important see headerCallback() function
        $this->_options[CURLOPT_HEADER] = false;
        return $this->_options;
    }

    /**
     * Setter for CURL Options
     * Warning! setoptions clears all previously setted options and post data
     * @see curl_setopt_array
     * @param array $options
     * @return static
     */
    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $this->_options[$k] = $v;
        }
        return $this;
    }

    /**
     * Resets all options to defaults
     * @return static
     */
    public function resetOptions()
    {
        $this->_options = [];
        return $this;
    }

    /**
     *
     * @param string $header
     * @return static
     */
    public function addHttpHeader($header)
    {
        $this->_options[CURLOPT_HTTPHEADER][] = $header;
        return $this;
    }

    /**
     * Adds post data to options
     * @param mixed $postData
     * @return $this;
     */
    public function setPostData($postData)
    {
        if (is_null($postData)) {
            unset($this->_options[CURLOPT_POST]);
            unset($this->_options[CURLOPT_POSTFIELDS]);
        } else {
            $this->_options[CURLOPT_POST] = true;
            $this->_options[CURLOPT_POSTFIELDS] = $postData;
        }
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * Url getter
     * @see CURLOPT_URL
     * @var string
     * @return null
     */
    public function getUrl()
    {
        return isset($this->_options[CURLOPT_URL]) ? $this->_options[CURLOPT_URL] : null;
    }

    /**
     * Url setter
     * @see CURLOPT_URL
     * @param string $url
     * @return static
     */
    public function setUrl($url)
    {
        $this->_options[CURLOPT_URL] = $url;
        return $this;
    }

    /**
     * Executes the single curl
     * @return boolean
     */
    protected function curl_execute()
    {
        $ch = curl_init();

        curl_setopt_array($ch, $this->getOptions());

        $this->_content = curl_exec($ch);

        $this->_errorCode = curl_errno($ch);

        $this->_info = curl_getinfo($ch);

        if ($this->_errorCode) {
            $this->_errorMessage = curl_error($ch);
        }
        curl_close($ch);
        return $this->isHttpOK();
    }

    /**
     * Executes parallels curls
     * @param CurlTrait[] $urls
     */
    protected static function curl_multi_exec($urls)
    {
        $nodes = [];
        /* @var $url CurlTrait */
        foreach ($urls as $url) {
            $ch = curl_init();
            $nodes[] = ['ch' => $ch, 'url' => $url];

            curl_setopt_array($ch, $url->getOptions());
        }

        $mh = curl_multi_init();
        foreach ($nodes as $node) {
            curl_multi_add_handle($mh, $node['ch']);
        }

        //execute the handles
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        foreach ($nodes as $node) {
            /* @var $url Curl */
            $url = $node['url'];

            $ch = $node['ch'];

            $url->_content = curl_multi_getcontent($ch);

            $url->_errorCode = curl_errno($ch);
            if (!empty($url->_errorCode)) {
                $url->_errorMessage = curl_error($ch);
            }
            $url->_info = curl_getinfo($ch);
        }

        //close the handles
        foreach ($nodes as $node) {
            curl_multi_remove_handle($mh, $node['ch']);
        }
        curl_multi_close($mh);
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function getAutoCookie()
    {
        return $this->_autoCookie;
    }

    public function setAutoCookie($value)
    {
        $this->_autoCookie = $value;
        return $this;
    }
}