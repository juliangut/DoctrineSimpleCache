<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;
use Psr\SimpleCache\CacheInterface as PsrCache;

final class SimpleCacheAdapter implements PsrCache
{
    /**
     * @var DoctrineCache|ClearableCache|MultiGetCache|MultiPutCache
     */
    private $doctrineCache;

    /**
     * @param DoctrineCache $doctrineCache
     * @throws \Roave\DoctrineSimpleCache\CacheException
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;

        if (!$this->doctrineCache instanceof ClearableCache) {
            throw CacheException::fromNonClearableCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiGetCache) {
            throw CacheException::fromNonMultiGetCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiPutCache) {
            throw CacheException::fromNonMultiPutCache($this->doctrineCache);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        $this->checkKey($key);

        return $this->doctrineCache->fetch($key);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null) : bool
    {
        $this->checkKey($key);

        return $this->doctrineCache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function delete($key) : bool
    {
        $this->checkKey($key);

        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function clear() : bool
    {
        return $this->doctrineCache->deleteAll();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
    {
        $this->checkKeys($keys);

        return $this->doctrineCache->fetchMultiple($keys);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null) : bool
    {
        return $this->doctrineCache->saveMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys) : bool
    {
        $this->checkKeys($keys);

        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    public function has($key) : bool
    {
        $this->checkKey($key);

        return $this->doctrineCache->contains($key);
    }

    /**
     * @param iterable $keys
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    private function checkKeys(iterable $keys) : void
    {
        foreach ($keys as $key => $value) {
            $this->checkKey(is_int($key) ? $value : $key);
        }
    }

    /**
     * Checks key validity.
     *
     * @param string $key
     *
     * @throws \Roave\DoctrineSimpleCache\InvalidArgumentException
     */
    private function checkKey(string $key) : void
    {
        if (preg_match('![{}()/\@]!', $key)) {
            throw InvalidArgumentException::invalidKeyFormat($key);
        }
    }
}
