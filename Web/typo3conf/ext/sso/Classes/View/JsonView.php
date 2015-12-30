<?php
namespace TYPO3\Sso\View;

class JsonView extends \TYPO3\CMS\Extbase\Mvc\View\JsonView
{

    /**
     * @inheritdoc
     * @return string
     */
    public function render()
    {
        if (count($this->variablesToRender) === 1) {
            $variableName = current($this->variablesToRender);
            $valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
            if ($valueToRender instanceof \JsonSerializable) {
                return json_encode($valueToRender);
            }
        }
        return parent::render();
    }

}