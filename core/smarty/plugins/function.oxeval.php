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
 * @copyright (C) OXID eSales AG 2003-2012
 * @version OXID eShop CE
 * @version   SVN: $Id: function.oxgetseourl.php 33480 2011-02-23 14:43:14Z arvydas.vapsva $
 */

/**
 * Smarty function
 * -------------------------------------------------------------
 * Purpose: eval given string
 * add [{ oxeval var="..." }] where you want to display content
 * -------------------------------------------------------------
 *
 * @param array  $aParams  parameters to process
 * @param smarty &$oSmarty smarty object
 *
 * @return string
 */
function smarty_function_oxeval( $aParams, &$oSmarty )
{
    if ( $aParams['var'] && ( $aParams['var'] instanceof oxField ) ) {
        $aParams['var'] = trim($aParams['var']->getRawValue());
    }

    // processign only if enabled
    if ( oxConfig::getInstance()->getConfigParam( 'bl_perfParseLongDescinSmarty' ) || isset( $aParams['force'] ) ) {
        include_once $oSmarty->_get_plugin_filepath( 'function', 'eval' );
        return smarty_function_eval( $aParams, $oSmarty );
    }

    return $aParams['var'];
}