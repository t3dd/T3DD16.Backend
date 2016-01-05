<?php
namespace TYPO3\Sso\Domain\Model;

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser implements \JsonSerializable
{
    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $profileImage;

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $profileImage
     */
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'username' => $this->username,
            'name' => $this->name,
            'image' => $this->getImageUrl()
        ];
    }

    /**
     * @return string
     */
    protected function getImageUrl() {
        if ($this->profileImage !== NULL) {
            $file = $this->profileImage->getOriginalResource();
            $crop = $file instanceof FileReference ? $file->getProperty('crop') : null;
            $processingInstructions = array(
                'crop' => $crop,
            );
            $imageService = $this->getImageService();
            $processedImage = $imageService->applyProcessingInstructions($file, $processingInstructions);
            return $imageService->getImageUri($processedImage);
        } else {
            return 'https://typo3.org/services/userimage.php?username=' . $this->username . '&size=big';
        }
    }

    /**
     * @return ImageService
     */
    protected function getImageService()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(ImageService::class);
    }

}