<?php

class Xhgui_Saver_Mongo implements Xhgui_Saver_Interface
{
    /**
     * @var MongoCollection
     */
    private $_collection;

    /**
     * @var MongoId lastProfilingId
     */
    private static $lastProfilingId;

    public function __construct(MongoCollection $collection)
    {
        $this->_collection = $collection;
    }

    public function save(array $data)
    {
        $data['_id'] = self::getLastProfilingId();

        return $this->insert($data);
        // return $this->_collection->insert($data, array('w' => 0));
    }
    
    /**
     * Insert a profile run.
     *
     * Does unchecked inserts.
     *
     * @param array $profile The profile data to save.
     */
    public function insert($profile)
    {
        $profile['profile'] = $this->encodeProfile($profile['profile']);
        return $this->_collection->insert($profile, array('w' => 0));
    }
    
    /**
     * Encodes a profile to avoid mongodb key errors.
     * @param array $profile
     *
     * @return array
     */
    protected function encodeProfile($profile)
    {
        if (!is_array($profile)) {
            return $profile;
        }
        $target = array(
          '__encoded' => true,
        );
        foreach ($profile as $k => $v) {
            if (is_array($v)) {
                $v = $this->encodeProfile($v);
            }
            $replacementKey = strtr($k, array(
              '.' => '．',
            ));
            $target[$replacementKey] = $v;
        }
        return $target;
    }

    /**
     * Return profiling ID
     * @return MongoId lastProfilingId
     */
    public static function getLastProfilingId() {
        if (!self::$lastProfilingId) {
            self::$lastProfilingId = new MongoId();
        }
        return self::$lastProfilingId;
    }
}
