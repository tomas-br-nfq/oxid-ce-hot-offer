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
 * @version   SVN: $Id: account_newsletter.php 40060 2011-11-21 09:51:38Z arunas.paskevicius $
 */

/**
 * Current user newsletter manager.
 * When user is logged in in this manager window he can modify
 * his newletter subscription status - simply register or
 * unregister from newsletter. OXID eShop -> MY ACCOUNT -> Newsletter.
 */
class Account_Newsletter extends Account
{
    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'page/account/newsletter.tpl';

    /**
     * Whether the newsletter option had been changed.
     *
     * @var bool
     */
    protected $_blNewsletter = null;

    /**
     * Whether the newsletter option had been changed give some affirmation.
     *
     * @var integer
     */
    protected $_iSubscriptionStatus = 0;

    /**
     * If user is not logged in - returns name of template account_newsletter::_sThisLoginTemplate,
     * or if user is allready logged in - returns name of template
     * Account_Newsletter::_sThisTemplate
     *
     * @return string
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
     * Template variable getter. Returns true when newsletter had been changed.
     *
     * @return bool
     */
    public function isNewsletter()
    {
        if ( $this->_blNewsletter === null ) {

            // initiating status
            $this->_blNewsletter = false;

            // now checking real subscription status
            $oUser = $this->getUser();
            if ( $oUser &&  $oUser->inGroup( 'oxidnewsletter' ) && ( $oUser->getNewsSubscription()->getOptInStatus() == 1 ) ) {
                $this->_blNewsletter = true;
            }
        }

        return $this->_blNewsletter;
    }

    /**
     * Removes or adds user to newsletter group according to
     * current subscription status. Returns true on success.
     *
     * @return bool
     */
    public function subscribe()
    {
        // is logged in ?
        $oUser = $this->getUser();
        if ( !$oUser ) {
            return false;
        }
        
        $oSubscription = $oUser->getNewsSubscription();
        $iStatus = oxConfig::getParameter( 'status' );
        
        if ( $iStatus == 0 && $iStatus !== null ) {
            $oUser->removeFromGroup( 'oxidnewsletter' );
            $oSubscription->setOptInStatus( 0 );
            $this->_iSubscriptionStatus = -1;
        }  if ( $iStatus == 1 ) {
            // assign user to newsletter group
            $oUser->addToGroup( 'oxidnewsletter' );
            $oSubscription->setOptInEmailStatus( 0 );
            $oSubscription->setOptInStatus( 1 );
            $this->_iSubscriptionStatus = 1;
        } 
    }

    /**
     * Template variable getter. Returns 1 when newsletter had been changed to "yes"
     * else return -1 if had been changed to "no".
     *
     * @return integer
     */
    public function getSubscriptionStatus()
    {
        return $this->_iSubscriptionStatus;
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
        $oUtils = oxUtilsUrl::getInstance();
        $aPath['title'] = oxLang::getInstance()->translateString( 'PAGE_ACCOUNT_MY_ACCOUNT', oxLang::getInstance()->getBaseLanguage(), false );
        $aPath['link']  = oxSeoEncoder::getInstance()->getStaticUrl( $this->getViewConfig()->getSelfLink() . 'cl=account' );
        $aPaths[] = $aPath;

        $aPath['title'] = oxLang::getInstance()->translateString( 'PAGE_ACCOUNT_NEWSLETTER_SETTINGS', oxLang::getInstance()->getBaseLanguage(), false );
        $aPath['link']  = $oUtils->cleanUrl( $this->getLink(), array( 'fnc' ));
        $aPaths[] = $aPath;    
        
        return $aPaths;
    }
}
