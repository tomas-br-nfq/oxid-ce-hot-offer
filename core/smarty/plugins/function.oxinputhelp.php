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
 * @version   SVN: $Id: function.oxinputhelp.php 25466 2010-02-01 14:12:07Z alfonsas $
 */

/**
 * Smarty function
 * -------------------------------------------------------------
 * Purpose: Output help popup icon and help text
 * add [{ oxinputhelp ident="..." }] where you want to display content
 * -------------------------------------------------------------
 *
 * @param array  $params  params
 * @param Smarty &$smarty clever simulation of a method
 *
 * @return string
 */
function smarty_function_oxinputhelp($params, &$smarty)
{
    $sIdent = $params['ident'];
    $myConfig  = oxConfig::getInstance();
    $oLang = oxLang::getInstance();
    $iLang  = $oLang->getTplLanguage();

    try {
        $sTranslation = $oLang->translateString( $sIdent, $iLang, $blAdmin );
    } catch ( oxLanguageException $oEx ) {
        // is thrown in debug mode and has to be caught here, as smarty hangs otherwise!
    }

    if ( !$sTranslation || $sTranslation == $sIdent  ) {
        //no translation, return empty string
        return '';
    }

    //name of template file where is stored message text
    $sTemplate = 'inputhelp.tpl';

    $smarty->assign( 'sHelpId', $sIdent );
    $smarty->assign( 'sHelpText', $sTranslation );

    return $smarty->fetch( $sTemplate );
}
