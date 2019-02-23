<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\caching\Cache;

class Setting extends Component
{
    /**
     * @var Cache|string the cache object or the application component ID of the cache object.
     * Settings will be cached through this cache object, if it is available.
     *
     * After the Settings object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Set this property to null if you do not want to cache the settings.
     */
    public $cache = 'cache';

    /**
     * To be used by the cache component.
     *
     * @var string cache key
     */
    public $cacheKey = 'settings';

    /**
     * Holds a cached copy of the data for the current request
     *
     * @var mixed
     */
    private $_data = null;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }
    }

    /**
     * Get's the value for the given key.
     * You can use dot notation to separate the section from the key:
     * $value = $settings->get('key');
     * are equivalent
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        if ($this->_data === null) {
            if ($this->cache instanceof Cache) {
                $data = $this->cache->get($this->cacheKey);
                if ($data === false) {
                    $data = $this->getData();
                    $this->cache->set($this->cacheKey, $data);
                }
            } else {
                $data = $this->getData();
            }
            $this->_data = $data;
        }
        return $this->_data[$key];
    }

    /**
     * Set value
     * @param array $date
     * @return boolean
     */
    public function set($date)
    {
        foreach ($date as $key => $value) {
            Yii::$app->db->createCommand()->update('{{%setting}}', [
                'value' => $value
            ], '`key`=:key', [':key' => $key])->execute();
        }
        return $this->clearCache();
    }

    /**
     * Clears the settings cache on demand.
     * If you haven't configured cache this does nothing.
     *
     * @return boolean True if the cache key was deleted and false otherwise
     */
    public function clearCache()
    {
        $this->_data = null;
        if ($this->cache instanceof Cache) {
            return $this->cache->delete($this->cacheKey);
        }
        return true;
    }

    /**
     * Returns the data array
     *
     * @return array
     */
    public function getData()
    {
        $settings = Yii::$app->db->createCommand("SELECT * FROM {{%setting}}")->queryAll();
        return \yii\helpers\ArrayHelper::map($settings, 'key', 'value');
    }

    /**
     * Returns a string representing the current version of the JNOJ.
     * @return string the version of JNOJ
     */
    public static function getVersion()
    {
        return '0.8.0';
    }
}
