<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;

class DevCache extends CacheProvider
{
    /**
     * @var bool
     */
    public static $wasHit = false;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    public function doFetch($id)
    {
        $data = $this->cache->fetch($id);
        self::$wasHit = (bool) $data;

        return $data;
    }

    public function doContains($id)
    {
        return $this->cache->contains($id);
    }

    public function doSave($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    public function doDelete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * @return ?array<string>
     */
    public function doGetStats()
    {
        return $this->cache->getStats();
    }

    public function doFlush()
    {
        return true;
    }
}
