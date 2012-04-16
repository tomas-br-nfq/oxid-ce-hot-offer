<?php

class nfqoxarticle extends nfqoxarticle_parent
{
    /**
     * Returns absolute URL of the "hot offer" imgage file that is displayed
     * on top of the product image in the detailed product info page
     *
     * @return string
     */
    public function getDetailedHotOfferImageUrl()
    {
        $aPicsGallery = $this->getPictureGallery();
        $aPicsSizes   = $this->getConfig()->getConfigParam('aDetailImageSizes');
        $aActPicSize  = oxPictureHandler::getInstance()->getImageSize($aPicsSizes, 'oxpic' . $aPicsGallery['ActPicID']);

        return $this->_getHotOfferImageUrl($aActPicSize);
    }


    /**
     * Returns absolute URL of the "hot offer" imgage file that is displayed
     * on top of the product image in the category lists
     *
     * @return string
     */
    public function getCategoryHotOfferImageUrl()
    {
        $sThumbnailConfigSize = $this->getConfig()->getConfigParam('sThumbnailsize');
        $aThumbnailSize       = oxPictureHandler::getInstance()->getImageSize($sThumbnailConfigSize);

        return $this->_getHotOfferImageUrl($aThumbnailSize);
    }


    /**
     * Returns absolute URL of the "hot offer" image file
     *
     * @param array $aRelImgSize Relevance image size
     * @return string
     */
    protected function _getHotOfferImageUrl($aRelImgSize)
    {
        $iArea = $aRelImgSize[0] * $aRelImgSize[1];

        if ($iArea > 90000) { // Larger than 300x300 px
            $sImageName = 'medium';
        }
        else if ($iArea > 10000) { // Large than 100x100 px
            $sImageName = 'small';
        }
        else {
            // No smaller image is available
            return '';
        }

        $sImgFinalPath = 'modules/hotoffer/' . $sImageName . '.png';
        return oxConfig::getInstance()->getPictureUrl($sImgFinalPath, false, null, null, null, '');
    }

}
