<?php
namespace TYPO3\T3DD16\ContentObject;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class JsonContentObject
{

    const CONTENT_OBJECT_NAME = 'JSON';

    /**
     * @param string $name
     * @param array $configuration
     * @param string $typoscriptKey
     * @param ContentObjectRenderer $contentObject
     * @return string
     */
    public function cObjGetSingleExt($name, array $configuration, $typoscriptKey, $contentObject)
    {
        $result = [];
        foreach ($configuration as $key => $contentObjectName) {
            if (strpos($key, '.') === false) {
                $conf = $configuration[$key . '.'];
                $result[$key] = $contentObject->cObjGetSingle($contentObjectName, $conf, $contentObjectName);
            }
        }
        return json_encode($result);
    }

}