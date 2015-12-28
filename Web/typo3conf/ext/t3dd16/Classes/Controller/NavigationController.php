<?php
namespace TYPO3\T3DD16\Controller;

use TYPO3\T3DD16\Domain\Model\DataTransfer\Page;

class NavigationController
{

    /**
     * @return string
     */
    public function listAction($content, $conf)
    {
        $navigation = [];

        foreach ($conf['export.'] as $name => $item) {
            $navigation[rtrim($name, '.')] = Page::create($item['id'], null, boolval($item['noCheck']));
        }

        return json_encode($navigation);
    }

}