<?php

class Nfq_Article_Hotoffer extends oxAdminList
{
    /**
     * Loads current settings of the article
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $oArticle = oxNew('oxarticle');
        $sOxId    = $this->getEditObjectId();
        
        if ($sOxId != '-1') {
            $oArticle->load($sOxId);
        }

        $this->_aViewData['edit'] = $oArticle;
        return 'nfq_article_hotoffer_settings.tpl';
    }
    
    
    /**
     * Action to save user provided settings of the article
     * 
     * @return null
     */
    public function save()
    {
    	$sOxId    = $this->getEditObjectId();
    	$aParams  = oxConfig::getParameter('editval');
    	$oArticle = oxNew('oxarticle');
    	
        if (!isset( $aParams['oxarticles__nfqhotofferactive'])) {
            $aParams['oxarticles__nfqhotofferactive'] = 0;
        }

        if ($sOxId != '-1') {
            $oArticle->load($sOxId);
            $oArticle->assign($aParams);
            $oArticle->save();
        }
    }
    
}
