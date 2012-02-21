<?php
/**
 * @name		CodeIgniter Zen Cache Library
 * @author		Jens Segers
 * @link		http://www.jenssegers.be
 * @license		MIT License Copyright (c) 2011 Jens Segers
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class Zen {
    
    // default values
    public $expires = 60;
    public $adapter = 'file';
    
    // sub directory to store cached items (only for file adapter)
    private $folder = 'zen';
    
    private $default_expires;
    private $monks = array();
    private static $instance;
    private $ci;
    
    public function __construct($config = array()) {
        // singleton
        self::$instance = & $this;
        
        // load settings from config if available
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->default_expires = $this->expires;
        $this->folder = rtrim($this->folder, '/') . '/';
        $this->ci = &get_instance();
        
        // load the original cache driver
        $this->ci->load->driver('cache', array('adapter' => $this->adapter));
    }
    
    /**
     * Imitate actual requested object with Monk object that caches 
     * all attributes and function calls that are made on it
     * 
     * @param string $name
     * @return Zen
     */
    public function __get($name) {
        // create a new imitating monk object when needed
        if (!isset($this->monks[$name])) {
            $this->monks[$name] = new Monk($name);
        }
        
        return $this->monks[$name];
    }
    
    /**
     * Set the cache expire time in seconds for the next call
     * 
     * @param int $ttl
     * @return Zen
     */
    public function expires($expires = FALSE) {
        if (is_numeric($expires)) {
            $this->expires = $expires;
        } else {
            $this->expires = $this->default_expires;
        }
        
        return $this;
    }
    
    /**
     * Alias function for expires
     * 
     * @param int $ttl
     * @return Zen
     */
    public function ttl($ttl) {
        return $this->expires($ttl);
    }
    
    /**
     * Singleton
     * 
     * @return Zen
     */
    public static function &get_instance() {
        return self::$instance;
    }
    
    /**
     * Save item to cache
     * 
     * @param string $id
     * @param mixed $data
     * @param int $ttl
     */
    public function save($id, $data, $expires = FALSE) {
        if (!$expires) {
            $expires = $this->expires;
        }
        
        // place in sub directory when using file adapter
        if ($this->adapter == 'file') {
            $id = $this->folder . $id . '.zen';
        }
        
        return $this->ci->cache->save($id, $data, $expires);
    }
    
    /**
     * Get item from cache
     * 
     * @param string $id
     */
    public function get($id) {
        // place in sub directory when using file adapter
        if ($this->adapter == 'file') {
            $id = $this->folder . $id . '.zen';
        }
        
        return $this->ci->cache->get($id);
    }
    
    /**
     * Delete all or a group of cached items
     * NOTE: only supported for file caching!
     * 
     * @param string $group
     */
    public function clean($group = FALSE) {
        if ($this->adapter == 'file') {
            // get cache path from config
            $cache_path = $this->ci->config->item('cache_path');
            $cache_path = ($cache_path == '') ? APPPATH . 'cache/' . $this->folder : $cache_path . $this->folder;
            
            if (empty($cache_path)) {
                return FALSE;
            }
            
            if ($group) {
                // delete group
                $this->ci->load->helper('directory');
                $map = directory_map($cache_path, TRUE);
                foreach ($map as $file) {
                    if (strpos($file, $group) !== FALSE) {
                        unlink($cache_path . $file);
                    }
                }
            } else {
                // delete all
                $this->ci->load->helper('file');
                echo $cache_path;
                if (file_exists($cache_path)) {
                    delete_files($cache_path, TRUE);
                }
            }
            
            return TRUE;
        }
        
        return FALSE;
    }

}

class Monk {
    
    // name of class that we are imitating
    private $_class_;
    
    // reference to the actual object we are imitating
    private $_object_;
    
    // reference to the Zen object
    private $_zen_;
    
    /**
     * Constructor
     * 
     * @param string $class
     */
    public function __construct($class) {
        $ci = &get_instance();
        $this->_zen_ = Zen::get_instance();
        $this->_class_ = $class;
        $this->_object_ = &$ci->{$class};
    }
    
    /**
     * Magic function that caches all attributes
     * 
     * @param string $name
     */
    public function __get($name) {
        $id = $this->_class_ . '.' . hash('sha1', $name);
        
        if (!$call = $this->_zen_->get($id)) {
            $result = $this->_object_->{$name};
            $this->_zen_->save($id, $result);
        }
        
        // reset expire time to default value
        $this->_zen_->expires(FALSE);
        
        return $result;
    }
    
    /**
     * Magic function that caches all function calls
     * 
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public function __call($method, $args = array()) {
        $id = $this->_class_ . '.' . hash('sha1', $method . serialize($args));
        
        if (!$call = $this->_zen_->get($id)) {
            $call = call_user_func_array(array($this->_object_, $method), $args);
            $this->_zen_->save($id, $call);
        }
        
        // reset expire time to default value
        $this->_zen_->expires(FALSE);
        
        return $call;
    }
}