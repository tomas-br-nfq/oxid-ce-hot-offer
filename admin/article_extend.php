<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @package   admin
 * @copyright (C) OXID eSales AG 2003-2012
 * @version OXID eShop CE
 * @version   SVN: $Id: article_extend.php 41407 2012-01-16 14:35:55Z mindaugas.rimgaila $
 */

/**
 * Admin article extended parameters manager.
 * Collects and updates (on user submit) extended article properties ( such as
 * weight, dimensions, purchase Price and etc.). There is ability to assign article
 * to any chosen article group.
 * Admin Menu: Manage Products -> Articles -> Extended.
 * @package admin
 */
class Article_Extend extends oxAdminDetails
{
    /**
     * Collects available article axtended parameters, passes them to
     * Smarty engine and returns tamplate file name "article_extend.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData['edit'] = $oArticle = oxNew( 'oxarticle' );

        $soxId = $this->getEditObjectId();
        $sCatView = getViewName( 'oxcategories' );

        $sChosenArtCat = $this->_getCategoryTree( "artcattree", oxConfig::getParameter( "artcat"));

        // all categories
        if ( $soxId != "-1" && isset( $soxId ) ) {
            // load object
            $oArticle->loadInLang( $this->_iEditLang, $soxId );


            // load object in other languages
            $oOtherLang = $oArticle->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oArticle->loadInLang( key($oOtherLang), $soxId );
            }

            foreach ( $oOtherLang as $id => $language) {
                $oLang= new oxStdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] =  clone $oLang;
            }

            // variant handling
            if ( $oArticle->oxarticles__oxparentid->value) {
                $oParentArticle = oxNew( 'oxarticle' );
                $oParentArticle->load( $oArticle->oxarticles__oxparentid->value);
                $oArticle->oxarticles__oxnonmaterial = new oxField( $oParentArticle->oxarticles__oxnonmaterial->value );
                $oArticle->oxarticles__oxfreeshipping = new oxField( $oParentArticle->oxarticles__oxfreeshipping->value );
                $this->_aViewData["parentarticle"] = $oParentArticle;
                $this->_aViewData["oxparentid"]    = $oArticle->oxarticles__oxparentid->value;
            }

            $sO2CView = getViewName('oxobject2category');
        }


            $oDB = oxDb::getDB();
            $myConfig = $this->getConfig();

            $sArticleTable = getViewName( 'oxarticles', $this->_iEditLang );
            $sSelect  = "select $sArticleTable.oxtitle, $sArticleTable.oxartnum, $sArticleTable.oxvarselect from $sArticleTable where 1 ";
            // #546
            $sSelect .= $myConfig->getConfigParam( 'blVariantsSelection' )?'':" and $sArticleTable.oxparentid = '' ";
            $sSelect .= " and $sArticleTable.oxid = ".$oDB->quote( $oArticle->oxarticles__oxbundleid->value );

            $rs = $oDB->Execute( $sSelect);
            if ($rs != false && $rs->RecordCount() > 0) {
                while (!$rs->EOF) {
                    $sArtNum = new oxField($rs->fields[1]);
                    $sArtTitle = new oxField($rs->fields[0]." ".$rs->fields[2]);
                    $rs->MoveNext();
                }
            }
            $this->_aViewData['bundle_artnum'] = $sArtNum;
            $this->_aViewData['bundle_title'] = $sArtTitle;


        $aColumns = array();
        $iAoc = oxConfig::getParameter("aoc");
        if ( $iAoc == 1 ) {

            include_once 'inc/'.strtolower(__CLASS__).'.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/article_extend.tpl";
        } elseif ( $iAoc == 2 ) {

            include_once 'inc/article_bundle.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/article_bundle.tpl";
        }

        //load media files
        $this->_aViewData['aMediaUrls'] = $oArticle->getMediaUrls();

        return "article_extend.tpl";
    }

    /**
     * Saves modified extended article parameters.
     *
     * @return mixed
     */
    public function save()
    {
        parent::save();

        $soxId = $this->getEditObjectId();
        $aParams = oxConfig::getParameter( "editval");
        // checkbox handling
        if ( !isset( $aParams['oxarticles__oxissearch'])) {
            $aParams['oxarticles__oxissearch'] = 0;
        }
        if ( !isset( $aParams['oxarticles__oxblfixedprice'])) {
            $aParams['oxarticles__oxblfixedprice'] = 0;
        }

        // new way of handling bundled articles
        //#1517C - remove posibility to add Bundled Product
        //$this->setBundleId($aParams, $soxId);

        // default values
        $aParams = $this->addDefaultValues( $aParams);

        $oArticle = oxNew( "oxarticle" );
        $oArticle->loadInLang( $this->_iEditLang, $soxId);

        if ( $aParams['oxarticles__oxtprice'] != $oArticle->oxarticles__oxtprice->value &&  $aParams['oxarticles__oxtprice'] && $aParams['oxarticles__oxtprice'] <= $oArticle->oxarticles__oxprice->value) {
            //$aParams['oxarticles__oxtprice'] = $oArticle->oxarticles__oxtprice->value;
            $this->_aViewData["errorsavingtprice"] = 1;
        }

        //$aParams = $oArticle->ConvertNameArray2Idx( $aParams);
        $oArticle->setLanguage(0);
        $oArticle->assign( $aParams);
        $oArticle->setLanguage($this->_iEditLang);
        $oArticle = oxUtilsFile::getInstance()->processFiles( $oArticle );
        $oArticle->save();

        //saving media file
        $sMediaUrl  = oxConfig::getParameter( "mediaUrl");
        $sMediaDesc = oxConfig::getParameter( "mediaDesc");
        $aMediaFile = $this->getConfig()->getUploadedFile( "mediaFile");

        if ( ( $sMediaUrl && $sMediaUrl != 'http://' ) || $aMediaFile['name'] || $sMediaDesc ) {

            if ( !$sMediaDesc ) {
                return oxUtilsView::getInstance()->addErrorToDisplay( 'EXCEPTION_NODESCRIPTIONADDED' );
            }

            if ( ( !$sMediaUrl || $sMediaUrl == 'http://' ) && !$aMediaFile['name'] ) {
                return oxUtilsView::getInstance()->addErrorToDisplay( 'EXCEPTION_NOMEDIAADDED' );
            }

            $oMediaUrl = oxNew( "oxMediaUrl" );
            $oMediaUrl->setLanguage( $this->_iEditLang );
            $oMediaUrl->oxmediaurls__oxisuploaded = new oxField( 0, oxField::T_RAW );

            //handle uploaded file
            if ($aMediaFile['name']) {
                try {
                    $sMediaUrl = oxUtilsFile::getInstance()->processFile( 'mediaFile', 'out/media/' );
                    $oMediaUrl->oxmediaurls__oxisuploaded = new oxField(1, oxField::T_RAW);
                } catch (Exception $e) {
                    return oxUtilsView::getInstance()->addErrorToDisplay( $e->getMessage() );
                }
            }

            //save media url
            $oMediaUrl->oxmediaurls__oxobjectid = new oxField($soxId, oxField::T_RAW);
            $oMediaUrl->oxmediaurls__oxurl      = new oxField($sMediaUrl, oxField::T_RAW);
            $oMediaUrl->oxmediaurls__oxdesc     = new oxField($sMediaDesc, oxField::T_RAW);
            $oMediaUrl->save();
        }
    }

    /**
     * Deletes media url (with possible linked files)
     *
     * @return bool
     */
    public function deletemedia()
    {
        $soxId = $this->getEditObjectId();
        $sMediaId = oxConfig::getParameter( "mediaid");
        if ($sMediaId && $soxId) {
            $oMediaUrl = oxNew("oxMediaUrl");
            $oMediaUrl->load($sMediaId);
            $oMediaUrl->delete();
        }
    }

    /**
     * Adds default values for extended article parameters. Returns modified
     * parameters array.
     *
     * @param array $aParams Article marameters array
     *
     * @return array
     */
    public function addDefaultValues( $aParams)
    {
        $aParams['oxarticles__oxexturl'] = str_replace( "http://", "", $aParams['oxarticles__oxexturl']);

        return $aParams;
    }

    /**
     * Updates existing media descriptions
     *
     * @return null
     */
    public function updateMedia()
    {
        $aMediaUrls = oxConfig::getParameter( 'aMediaUrls' );
        if ( is_array( $aMediaUrls ) ) {
            foreach ( $aMediaUrls as $sMediaId => $aMediaParams ) {
                $oMedia = oxNew("oxMediaUrl");
                if ( $oMedia->load( $sMediaId ) ) {
                    $oMedia->setLanguage(0);
                    $oMedia->assign( $aMediaParams );
                    $oMedia->setLanguage( $this->_iEditLang );
                    $oMedia->save();
                }
            }
        }
    }
}
