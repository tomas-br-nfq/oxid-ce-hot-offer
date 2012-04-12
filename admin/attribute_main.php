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
 * @version   SVN: $Id: attribute_main.php 39918 2011-11-14 08:40:27Z arvydas.vapsva $
 */

/**
 * Admin article main attributes manager.
 * There is possibility to change attribute description, assign articles to
 * this attribute, etc.
 * Admin Menu: Manage Products -> Attributes -> Main.
 * @package admin
 */
class Attribute_Main extends oxAdminDetails
{
    /**
     * Loads article Attributes info, passes it to Smarty engine and
     * returns name of template file "attribute_main.tpl".
     *
     * @return string
     */
    public function render()
    {   $myConfig = $this->getConfig();

        parent::render();
        $oAttr = oxNew( "oxattribute" );
        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();
        $sArticleTable = getViewName('oxarticles');

        // copy this tree for our article choose
        $sChosenArtCat = oxConfig::getParameter( "artcat");
        if ( $soxId != "-1" && isset( $soxId)) {
            // generating category tree for select list
            $sChosenArtCat = $this->_getCategoryTree( "artcattree", $sChosenArtCat, $soxId);
            // load object
            $oAttr->loadInLang( $this->_iEditLang, $soxId );


            $oOtherLang = $oAttr->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oAttr->loadInLang( key($oOtherLang), $soxId );
            }

            // remove already created languages
            $aLang = array_diff ( oxLang::getInstance()->getLanguageNames(), $oOtherLang);
            if ( count( $aLang))
                $this->_aViewData["posslang"] = $aLang;

            foreach ( $oOtherLang as $id => $language) {
                $oLang= new oxStdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] =  clone $oLang;
            }
        }

        $this->_aViewData["edit"] =  $oAttr;

        if ( oxConfig::getParameter("aoc") ) {

            $aColumns = array();
            include_once 'inc/'.strtolower(__CLASS__).'.inc.php';
            $this->_aViewData['oxajax'] = $aColumns;

            return "popups/attribute_main.tpl";
        }
        return "attribute_main.tpl";
    }

    /**
     * Saves article attributes.
     *
     * @return mixed
     */
    public function save()
    {
        parent::save();

        $soxId = $this->getEditObjectId();
        $aParams = oxConfig::getParameter( "editval");

            // shopid
            $aParams['oxattribute__oxshopid'] = oxSession::getVar( "actshop" );
        $oAttr = oxNew( "oxattribute" );

        if ( $soxId != "-1")
            $oAttr->loadInLang( $this->_iEditLang, $soxId );
        else
            $aParams['oxattribute__oxid'] = null;
        //$aParams = $oAttr->ConvertNameArray2Idx( $aParams);


        $oAttr->setLanguage(0);
        $oAttr->assign( $aParams);
        $oAttr->setLanguage($this->_iEditLang);
        $oAttr = oxUtilsFile::getInstance()->processFiles( $oAttr );
        $oAttr->save();

        $this->setEditObjectId( $oAttr->getId() );
    }

    /**
     * Saves attribute data to different language (eg. english).
     *
     * @return null
     */
    public function saveinnlang()
    {
        parent::save();

        $soxId = $this->getEditObjectId();
        $aParams = oxConfig::getParameter( "editval");

            // shopid
            $aParams['oxattribute__oxshopid'] = oxSession::getVar( "actshop");
        $oAttr = oxNew( "oxattribute" );

        if ( $soxId != "-1") {
            $oAttr->loadInLang( $this->_iEditLang, $soxId );
        } else {
            $aParams['oxattribute__oxid'] = null;
        }


        $oAttr->setLanguage(0);
        $oAttr->assign( $aParams);

        // apply new language
        $oAttr->setLanguage( oxConfig::getParameter( "new_lang" ) );
        $oAttr->save();

        // set oxid if inserted
        $this->setEditObjectId( $oAttr->getId() );
    }
}
