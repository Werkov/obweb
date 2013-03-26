<?php

namespace Ormion;

use Nette\Environment,
    Nette\Reflection\ClassType;
use dibi;
use Ormion\Association\IAssociation;

/**
 * Mapper with cache
 *
 * @author Michal Koutny
 */
class CachingMapper extends Mapper {

    /**
     * Find one result
     * @param array|int conditions
     * @return IRecord|false
     */
    public function find($conditions = null, $uncached = false) {
        if ($conditions === null) {
            return false;
        } elseif (!$uncached && is_scalar($conditions)) {
            $cache = $this->getCache();
            $key = $this->getCacheKey($conditions);
            $result = $cache->load($key);
            if ($result === null) {
                $result = parent::find($conditions);
            }
            return $result;
        } else {
            return parent::find($conditions);
        }
    }

    public function register(Record $record) {
        $primary = isset($record->{$this->getConfig()->getPrimaryColumn()}) ? $record->getPrimary() : null;
        if ($primary) {
            $cache = $this->getCache();
            $key = $this->getCacheKey($primary);
            $cached = $cache->load($key);
            if ($cached === null) {
                $cache->save($key, $record);
            } else {
                //$cached->setValues($record->getValues()); // cannot be enabled because will overwreite data when find invoked as uncached
            }
        }
    }

    public function delete(IRecord $record) {
        parent::delete($record);
        $primary = isset($record->{$this->getConfig()->getPrimaryColumn()}) ? $record->getPrimary() : null;
        if ($primary) {
            $cache = $this->getCache();
            $key = $this->getCacheKey($primary);
            $cache->save($key, null);
        }
    }

    public function update(IRecord $record, $asReference = false) {
        parent::update($record, $asReference);
        $primary = isset($record->{$this->getConfig()->getPrimaryColumn()}) ? $record->getPrimary() : null;
        if ($primary) {
            $cache = $this->getCache();
            $key = $this->getCacheKey($primary);
            $cache->save($key, $record);
        }
    }

    public function findAll($conditions = null, $uncached = false) {
        //return parent::findAll($conditions);
        if ($conditions === null && $uncached) {
            $cache = $this->getCache();
            $key = $this->getCacheKey();
            $result = $cache->load($key);
            if ($result === null) {
                $result = parent::findAll();
            }
            return $result;
        } else {
            return parent::findAll($conditions);
        }
    }

    /**
     *
     * @return \Nette\Caching\Cache
     */
    protected function getCache() {
        return Environment::getContext()->ORMCache;
    }

    protected function getCacheKey($primary = null) {
        if ($primary === null) {
            return $this->table;
        } else {
            return $this->getRowClass() . ':' . $primary;
        }
    }

}