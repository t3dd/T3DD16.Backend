<?php
namespace TYPO3\T3DD16\SignalSlot;

use TYPO3\CMS\Core\SingletonInterface;

class BackendSlot implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     * @inject
     */
    protected $dataMapper;

    /**
   	 * @var \TYPO3\CMS\Core\Cache\CacheManager
     * @inject
   	 */
   	protected $cacheManager;

    /**
     * flushCacheForObject
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $object
     * @return void
     */
    public function flushCacheForObject(\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $object)
    {
        foreach ($this->identifyCacheTagForObject($object) as $cacheTag) {
            $this->cacheManager->flushCachesByTag($cacheTag);
        }
    }

    /**
     * Returns cache tag parts for the given object if known.
     *
     * @param $object
     * @return array
     */
    protected function identifyCacheTagForObject($object)
    {
        $cacheTags = [];
        if ($object instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject) {
            $tableName = $this->dataMapper->convertClassNameToTableName(get_class($object));
            $recordUid = $object->getUid();

            $cacheTags[] = $tableName;
            $cacheTags[] = $tableName . '_' . $recordUid;
        } elseif ($object instanceof \TYPO3\CMS\Extbase\Persistence\QueryResultInterface) {
            $cacheTags[] = $this->dataMapper->convertClassNameToTableName($object->getQuery()->getType());
        }
        return $cacheTags;
    }

}