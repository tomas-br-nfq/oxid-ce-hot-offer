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
 * @package   smarty_plugins
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: insert.oxid_newbasketitem.php 25466 2010-02-01 14:12:07Z alfonsas $
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File: insert.oxid_newbasketitem.php
 * Type: string, html
 * Name: newbasketitem
 * Purpose: Used for tracking in econda, etracker etc.
 * -------------------------------------------------------------
 *
 * @param array  $params  params
 * @param Smarty &$smarty clever simulation of a method
 *
 * @return string
 */
function smarty_insert_oxid_fblogin($params, &$smarty)
{
    $myConfig  = oxConfig::getInstance();
    $oView = $myConfig->getActiveView();

    if ( !$myConfig->getConfigParam( "bl_showFbConnect") ) {
        return;
    }

    // user logged in using facebook account so showing additional
    // popup about connecting facebook user id to existing shop account
    $oFb = oxFb::getInstance();

    if ( $oFb->isConnected() && $oFb->getUser() ) {

        //name of template
        $sTemplate = 'inc/popup_fblogin.tpl';

        // checking, if Facebeook User Id was successfully added
        if ( oxSession::getVar( '_blFbUserIdUpdated' ) ) {
            $sTemplate = 'inc/popup_fblogin_msg.tpl';
            oxSession::deleteVar( '_blFbUserIdUpdated' );
        }

        return $smarty->fetch( $sTemplate );
    }
}
