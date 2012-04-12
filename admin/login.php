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
 * @version   SVN: $Id: login.php 33633 2011-03-03 11:43:12Z sarunas $
 */

/**
 * Administrator login form.
 * Performs administrator login form data collection.
 * @package admin
 */
class Login extends oxAdminView
{
    /**
     * Sets value for _sThisAction to "login".
     */
    public function __construct()
    {
        $this->getConfig()->setConfigParam( 'blAdmin', true );
        $this->_sThisAction  = "login";
    }

    /**
     * Executes parent method parent::render(), creates shop object, sets template parameters
     * and returns name of template file "login.tpl".
     *
     * @return string
     */
    public function render()
    {   $myConfig = $this->getConfig();

        //resets user once on this screen.
        $oUser = oxNew( "oxuser" );
        $oUser->logout();

        oxView::render();

        //if( $myConfig->blDemoMode)
        $oBaseShop = oxNew( "oxshop" );

        $oBaseShop->load( $myConfig->getBaseShopId());
            $sVersion = $oBaseShop->oxshops__oxversion->value;

        $this->getViewConfig()->setViewConfigParam( 'sShopVersion', $sVersion );

        if ( $myConfig->isDemoShop() ) {
            // demo
            $this->addTplParam( "user", "admin");
            $this->addTplParam( "pwd", "admin");
        }
        //#533 user profile
        $this->addTplParam( "profiles", oxUtils::getInstance()->loadAdminProfile( $myConfig->getConfigParam( 'aInterfaceProfiles' ) ) );

        $aLanguages = $this->_getAvailableLanguages();
        $this->addTplParam( "aLanguages", $aLanguages );

        // setting templates language to selected language id
        foreach ($aLanguages as $iKey => $oLang) {
            if ( $aLanguages[$iKey]->selected ) {
                oxLang::getInstance()->setTplLanguage( $iKey );
                break;
            }
        }

        return "login.tpl";
    }

    /**
     * Checks user login data, on success returns "admin_start".
     *
     * @return mixed
     */
    public function checklogin()
    {
        $myUtilsServer = oxUtilsServer::getInstance();
        $myUtilsView   = oxUtilsView::getInstance();

        $sUser    = oxConfig::getParameter( 'user', true );
        $sPass    = oxConfig::getParameter( 'pwd', true );
        $sProfile = oxConfig::getParameter( 'profile' );

        try { // trying to login
            $oUser = oxNew( "oxuser" );
            $oUser->login( $sUser, $sPass );
            $iSubshop = (int)$oUser->oxuser__oxrights->value;
            if ($iSubshop) {
                oxSession::setVar( "shp", $iSubshop );
                oxSession::setVar( 'currentadminshop', $iSubshop );
                oxConfig::getInstance()->setShopId($iSubshop);
            }
        } catch ( oxUserException $oEx ) {
            $myUtilsView->addErrorToDisplay('LOGIN_ERROR');
            $oStr = getStr();
            $this->addTplParam( 'user', $oStr->htmlspecialchars( $sUser ) );
            $this->addTplParam( 'pwd', $oStr->htmlspecialchars( $sPass ) );
            $this->addTplParam( 'profile', $oStr->htmlspecialchars( $sProfile ) );
            return;
        } catch ( oxCookieException $oEx ) {
            $myUtilsView->addErrorToDisplay('LOGIN_NO_COOKIE_SUPPORT');
            $oStr = getStr();
            $this->addTplParam( 'user', $oStr->htmlspecialchars( $sUser ) );
            $this->addTplParam( 'pwd', $oStr->htmlspecialchars( $sPass ) );
            $this->addTplParam( 'profile', $oStr->htmlspecialchars( $sProfile ) );
            return;
        } catch ( oxConnectionException $oEx ) {
            $myUtilsView->addErrorToDisplay($oEx);
        }

        // success
        oxUtils::getInstance()->logger( "login successful" );
        // #533
        if ( isset( $sProfile ) ) {
            $aProfiles = oxSession::getVar( "aAdminProfiles" );
            if ( $aProfiles && isset($aProfiles[$sProfile])) {
                // setting cookie to store last locally used profile
                $myUtilsServer->setOxCookie ("oxidadminprofile", $sProfile."@".implode( "@", $aProfiles[$sProfile]), time()+31536000, "/" );
                oxSession::setVar( "profile", $aProfiles[$sProfile] );
            }
        } else {
            //deleting cookie info, as setting profile to default
            $myUtilsServer->setOxCookie( "oxidadminprofile", "", time()-3600, "/" );
        }

        // languages
        $iLang = oxConfig::getParameter( "chlanguage" );
        $aLanguages = oxLang::getInstance()->getAdminTplLanguageArray();
        if ( !isset( $aLanguages[$iLang] ) ) {
            $iLang = key( $aLanguages );
        }

        $myUtilsServer->setOxCookie( "oxidadminlanguage", $aLanguages[$iLang]->abbr, time() + 31536000, "/" );

        //P
        //oxSession::setVar( "blAdminTemplateLanguage", $iLang );
        oxLang::getInstance()->setTplLanguage( $iLang );

        return "admin_start";
    }

    /**
     * authorization
     *
     * @return boolean
     */
    protected function _authorize()
    {
        // users are always authorized to use login page
        return true;
    }

    /**
     * Current view ID getter
     *
     * @return string
     */
    public function getViewId()
    {
        return strtolower( get_class( $this ) );
    }

    /**
     * Get available admin interface languages
     *
     * @return array
     */
    protected function _getAvailableLanguages()
    {
        $sDefLang = oxUtilsServer::getInstance()->getOxCookie( 'oxidadminlanguage' );
        $sDefLang = $sDefLang ? $sDefLang : $this->_getBrowserLanguage();

        $aLanguages = oxLang::getInstance()->getAdminTplLanguageArray();
        foreach ( $aLanguages as $oLang ) {
            $oLang->selected = ( $sDefLang == $oLang->abbr ) ? 1 : 0;
        }

        return $aLanguages;
    }

    /**
     * Get detected user browser language abbervation
     *
     * @return string
     */
    protected function _getBrowserLanguage()
    {
        return strtolower( substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) );
    }
}
