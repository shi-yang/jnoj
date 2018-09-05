<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;
/**
 * Class Temporary
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
class Temporary
{
    /**
     *
     * @var Temporary[]
     */
    private static $_temporaries = [];

    private $_name;

    private $_prefix;

    /**
     *
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->_prefix = $prefix;
        self::$_temporaries[] = $this;

        static $registered = false;
        if (!$registered) {
            register_shutdown_function(get_class($this) . '::clearAll');
            $registered = true;
        }
    }

    public function __toString()
    {
        return $this->_name;
    }

    /**
     * Creates temporary directory
     *
     * @throws \Exception
     * @return Temporary
     */
    public function dir()
    {
        $this->_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid($this->_prefix);
        if (!mkdir($this->_name)) {
            throw new \Exception('Cannot create temporary directory');
        }
        return $this;
    }

    /**
     * Creates temporary file
     *
     * @return Temporary
     */
    public function file()
    {
        $this->_name = tempnam(sys_get_temp_dir(), $this->_prefix);
        return $this;
    }

    public function clear()
    {
        if (is_dir($this->_name)) {
            static::rmDir($this->_name);
        } elseif (file_exists($this->_name)) {
            unlink($this->_name);
        }
    }

    protected static function rmDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $filename = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filename)) {
                static::rmDir($filename);
            } else {
                unlink($filename);
            }
        }
        return rmdir($dir);
    }

    public static function clearAll()
    {
        foreach (self::$_temporaries as $temporary) {
            $temporary->clear();
        }
    }
}
