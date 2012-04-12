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
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: category_main.php 40450 2011-12-05 08:30:21Z ramunas.skarbalius $
 */

/**
 * Admin article main categories manager.
 * There is possibility to change categories description, sorting, range of price
 * and etc.
 * Admin Menu: Manage Products -> Categories -> Main.
 * @package admin
 */
class Category_Main extends oxAdminDetails
{
    /**
     * Loads article category data, passes it to Smarty engine, returns
     * name of template file "category_main.tpl".
     *
     * @return string
     */
    public function render()
    {
        $myConfig  = $this->getConfig();

        parent::render();

        $this->_aViewData["edit"] = $oCategory = oxNew( "oxcategory" );;
        $sChosenArtCat = oxConfig::getParameter( "artcat");
        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();
        if ( $soxId != "-1" && isset( $soxId)) {

            // generating category tree for select list
            $sChosenArtCat = $this->_getCategoryTree( "artcattree", $sChosenArtCat, $soxId );

            // load object
            $oCategory->loadInLang( $this->_iEditLang, $soxId );


            $oOtherLang = $oCategory->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oCategory->loadInLang( key($oOtherLang), $soxId );
            }

            // remove already created languages
            $aLang = array_diff ( oxLang::getInstance()->getLanguageNames(), $oOtherLang );
            if ( count( $aLang))
                $this->_aViewData["posslang"] = $aLang;

            foreach ( $oOtherLang as $id => $language) {
                $oLang = new oxStdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] =  clone $oLang;
            }

            if ( $oCategory->oxcategories__oxparentid->value == 'oxrootid')
                $oCategory->oxcategories__oxparentid->setValue('');

            $this->_getCategoryTree( "cattree", $oCategory->oxcategories__oxparentid->value, $oCategory->oxcategories__oxid->value, true, $oCategory->oxcategories__oxshopid->value);

            $this->_aViewData["defsort"] = $oCategory->oxcategories__oxdefsort->value;
        } else
            $this->_getCategoryTree( "cattree", "", "", true, $myConfig->getShopId());

        $this->_aViewData["sortableFields"] = $this->getSortableFields();

        if ( oxConfig::getParameter("aoc") ) {

            $aColumns = array();
            include_once 'inc/'.strtolower(__CLASS__).'.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/category_main.tpl";
        }
        return "category_main.tpl";
    }

    /**
     * Returns an array of article object DB fields, without multilanguage and unsortible fields.
     *
     * @return array
     */
    public function getSortableFields()
    {
        $aSkipFields = array( "OXID", "OXSHOPID", "OXSHOPINCL", "OXSHOPEXCL", "OXPARENTID", "OXACTIVE", "OXACTIVEFROM"
            , "OXACTIVETO", "OXSHORTDESC"
            , "OXUNITNAME", "OXUNITQUANTITY", "OXEXTURL", "OXURLDESC", "OXURLIMG", "OXVAT"
            , "OXTHUMB", "OXPICSGENERATED", "OXPIC1", "OXPIC2", "OXPIC3", "OXPIC4", "OXPIC5"
            , "OXPIC6", "OXPIC7", "OXPIC8", "OXPIC9", "OXPIC10", "OXPIC11", "OXPIC12", "OXSTOCKFLAG"
            , "OXSTOCKTEXT", "OXNOSTOCKTEXT", "OXDELIVERY", "OXFILE", "OXSEARCHKEYS", "OXTEMPLATE"
            , "OXQUESTIONEMAIL", "OXISSEARCH", "OXISCONFIGURABLE", "OXBUNDLEID", "OXFOLDER", "OXSUBCLASS"
            , "OXREMINDACTIVE", "OXREMINDAMOUNT", "OXVENDORID", "OXMANUFACTURERID", "OXSKIPDISCOUNTS"
            , "OXBLFIXEDPRICE", "OXICON", "OXVARSELECT", "OXAMITEMID", "OXAMTASKID", "OXPIXIEXPORT", "OXPIXIEXPORTED", "OXSORT"
        );

        $oDbHandler = oxNew( "oxDbMetaDataHandler" );
        $aFields = array_merge( $oDbHandler->getMultilangFields( 'oxarticles' ), $oDbHandler->getSinglelangFields( 'oxarticles', 0 ) );
        $aFields = array_diff( $aFields, $aSkipFields );
        $aFields = array_unique( $aFields );

        return $aFields;
    }

    /**
     * Saves article category data.
     *
     * @return mixed
     */
    public function save()
    {
        parent::save();

        $myConfig  = $this->getConfig();

        $soxId = $this->getEditObjectId();
        $aParams = oxConfig::getParameter( "editval");

        // checkbox handling
        if ( !isset( $aParams['oxcategories__oxactive']))
            $aParams['oxcategories__oxactive'] = 0;
        if ( !isset( $aParams['oxcategories__oxhidden']))
            $aParams['oxcategories__oxhidden'] = 0;
        if ( !isset( $aParams['oxcategories__oxdefsortmode']))
            $aParams['oxcategories__oxdefsortmode'] = 0;

        // null values
        if ($aParams['oxcategories__oxvat'] === '')
            $aParams['oxcategories__oxvat'] = null;

            // shopid
            $aParams['oxcategories__oxshopid'] = oxSession::getVar( "actshop" );

        $oCategory = oxNew( "oxcategory" );

        if ( $soxId != "-1") {
            $this->resetCounter( "catArticle", $soxId );
            $oCategory->load( $soxId);
            $oCategory->loadInLang( $this->_iEditLang, $soxId );
                $myUtilsPic = oxUtilsPic::getInstance();
                // #1173M - not all pic are deleted, after article is removed
                $myUtilsPic->overwritePic( $oCategory, 'oxcategories', 'oxthumb', 'TC', '0', $aParams, $myConfig->getPictureDir(false) );
                $myUtilsPic->overwritePic( $oCategory, 'oxcategories', 'oxicon', 'CICO', 'icon', $aParams, $myConfig->getPictureDir(false) );
                $myUtilsPic->overwritePic( $oCategory, 'oxcategories', 'oxpromoicon', 'PICO', 'icon', $aParams, $myConfig->getPictureDir(false) );

        } else {
            //#550A - if new category is made then is must be default activ
            $aParams['oxcategories__oxactive'] = 1;
            $aParams['oxcategories__oxid'] = null;
        }


        $oCategory->setLanguage(0);
        if ( isset( $aParams["oxcategories__oxlongdesc"] ) ) {
            $aParams["oxcategories__oxlongdesc"] = $this->_processLongDesc( $aParams["oxcategories__oxlongdesc"] );
        }


        $oCategory->assign( $aParams );
        $oCategory->setLanguage($this->_iEditLang);

        $oCategory = oxUtilsFile::getInstance()->processFiles( $oCategory );
        $oCategory->save();

        $this->setEditObjectId( $oCategory->getId() );
    }

    /**
     * Fixes html broken by html editor
     *
     * @param string $sValue value to fix
     *
     * @return string
     */
    protected function _processLongDesc( $sValue )
    {
        // workaround for firefox showing &lang= as &9001;= entity, mantis#0001272
        return str_replace( '&lang=', '&amp;lang=', $sValue );
    }

    /**
     * Saves article category data to different language (eg. english).
     *
     * @return null
     */
    public function saveinnlang()
    {
        $this->save();
    }
}
