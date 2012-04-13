<?php

class Nfq_Article_Hotoffer extends oxAdminList
{
    /**
     * Loads current settings of Hot Offer module
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData["edit"] = $oArticle = oxNew( "oxarticle" );
        $sOxId = $this->getEditObjectId();
        

        if ($sOxId != '-1') {

            $oArticle->load($sOxId);
        }

        $this->_aViewData["editlanguage"] = $this->_iEditLang;
        return "modules/hotoffer/settings.tpl";
    }
}
