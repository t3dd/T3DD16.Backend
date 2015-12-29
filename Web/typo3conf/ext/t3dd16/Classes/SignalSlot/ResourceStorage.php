<?php
namespace TYPO3\T3DD16\SignalSlot;

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
        if (!($driver instanceof \TYPO3\CMS\Core\Resource\Driver\LocalDriver) || $this->environmentService->isEnvironmentInBackendMode()) {
            return;
        }

        if (($resourceStorage->getCapabilities() & \TYPO3\CMS\Core\Resource\ResourceStorageInterface::CAPABILITY_PUBLIC) == \TYPO3\CMS\Core\Resource\ResourceStorageInterface::CAPABILITY_PUBLIC) {
            $publicUrl = $driver->getPublicUrl($resourceObject->getIdentifier());
            $urlData['publicUrl'] = $GLOBALS['TSFE']->config['config']['cdnBaseUrl'] . $publicUrl;
        }
    }

}