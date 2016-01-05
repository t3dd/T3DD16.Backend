<?php
namespace TYPO3\T3DD16\SignalSlot;

use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;

class ResourceStorage implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Service\EnvironmentService
     */
    protected $environmentService;

    /**
     * @param \TYPO3\CMS\Extbase\Service\EnvironmentService $environmentService
     */
    public function injectEnvironmentService(\TYPO3\CMS\Extbase\Service\EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\ResourceStorage $resourceStorage
     * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface $resourceObject
     * @param boolean $relativeToCurrentScript
     * @param string $urlData
     */
    public function getCdnPublicUrl($resourceStorage, $driver, $resourceObject, $relativeToCurrentScript, $urlData)
    {
        if (!($driver instanceof LocalDriver) || $this->environmentService->isEnvironmentInBackendMode()) {
            return;
        }

        if (($resourceObject instanceOf File || $resourceObject instanceOf ProcessedFile) && ($resourceStorage->getCapabilities() & ResourceStorageInterface::CAPABILITY_PUBLIC) == ResourceStorageInterface::CAPABILITY_PUBLIC) {
            $publicUrl = $driver->getPublicUrl($resourceObject->getIdentifier());
            $urlData['publicUrl'] = $GLOBALS['TSFE']->config['config']['cdnBaseUrl'] . $publicUrl;
            if ($resourceObject instanceOf File) {
                $urlData['publicUrl'] .= '?' . $resourceObject->getModificationTime();
            } else if ($resourceObject instanceOf ProcessedFile) {
                $urlData['publicUrl'] .= '?' . $resourceObject->getProperty('tstamp');
            }
        }
    }

}