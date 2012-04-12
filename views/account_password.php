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
 * @package   views
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: account_password.php 35529 2011-05-23 07:31:20Z arunas.paskevicius $
 */


/**
 * Current user password change form.
 * When user is logged in he may change his Billing and Shipping
 * information (this is important for ordering purposes).
 * Information as email, password, greeting, name, company, address,
 * etc. Some fields must be entered. OXID eShop -> MY ACCOUNT
 * -> Update your billing and delivery settings.
 */
class Account_Password extends Account
{
    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'page/account/password.tpl';

    /**
     * Whether the password had been changed.
     *
     * @var bool
     */
    protected $_blPasswordChanged = false;

    /**
     * If user is not logged in - returns name of template account_user::_sThisLoginTemplate,
     * or if user is allready logged in additionally loads user delivery address
     * info and forms country list. Returns name of template account_user::_sThisTemplate
     *
     * @return string $_sThisTemplate current template file name
     */
    public function render()
    {

        parent::render();

        // is logged in ?
        $oUser = $this->getUser();
        if ( !$oUser ) {
            return $this->_sThisTemplate = $this->_sThisLoginTemplate;
        }

        return $this->_sThisTemplate;

    }

    /**
     * changes current user password
     *
     * @return null
     */
    public function changePassword()
    {
        $oUser = $this->getUser();
        if ( !$oUser ) {
            return;
        }

        $sOldPass  = oxConfig::getParameter( 'password_old' );
        $sNewPass  = oxConfig::getParameter( 'password_new' );
        $sConfPass = oxConfig::getParameter( 'password_new_confirm' );

        if ( ( $oExcp = $oUser->checkPassword( $sNewPass, $sConfPass, true ) ) ) {
            switch ( $oExcp->getMessage() ) {
                case 'EXCEPTION_INPUT_EMPTYPASS':
                case 'EXCEPTION_INPUT_PASSTOOSHORT':
                    return oxUtilsView::getInstance()->addErrorToDisplay('ACCOUNT_PASSWORD_ERRPASSWORDTOSHORT', false, true);
                default:
                    return oxUtilsView::getInstance()->addErrorToDisplay('ACCOUNT_PASSWORD_ERRPASSWDONOTMATCH', false, true);
            }
        }

        if ( !$sOldPass || !$oUser->isSamePassword( $sOldPass ) ) {
            return oxUtilsView::getInstance()->addErrorToDisplay('ACCOUNT_PASSWORD_ERRINCORRECTCURRENTPASSW', false, true, 'user');
        }

        // testing passed - changing password
        $oUser->setPassword( $sNewPass );
        if ( $oUser->save() ) {
            $this->_blPasswordChanged = true;
        }
    }

    /**
     * Template variable getter. Returns true when password had been changed.
     *
     * @return bool
     */
    public function isPasswordChanged()
    {
        return $this->_blPasswordChanged;
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = array();
        $aPath = array();

        $aPath['title'] = oxLang::getInstance()->translateString( 'PAGE_ACCOUNT_MY_ACCOUNT', oxLang::getInstance()->getBaseLanguage(), false );
        $aPath['link']  = oxSeoEncoder::getInstance()->getStaticUrl( $this->getViewConfig()->getSelfLink() . 'cl=account' );
        $aPaths[] = $aPath;

        $aPath['title'] = oxLang::getInstance()->translateString( 'PAGE_ACCOUNT_PASSWORD_PERSONALSETTINGS', oxLang::getInstance()->getBaseLanguage(), false );
        $aPath['link']  = $this->getLink();
        $aPaths[] = $aPath;

        return $aPaths;
    }
}
