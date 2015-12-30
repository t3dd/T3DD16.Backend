<?php
namespace TYPO3\Sso\Configuration;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

class ConfigurationManager implements SingletonInterface
{

    /**
     * @var array
     */
    protected $settings;

    public function __construct()
    {
        $tsService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->settings = $tsService->convertTypoScriptArrayToPlainArray((array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sso']));
    }

    /**
     * @param string $propertyPath
     * @return mixed
     */
    public function getExtensionSetting($propertyPath)
    {
        return ObjectAccess::getPropertyPath($this->settings, $propertyPath);
    }

}