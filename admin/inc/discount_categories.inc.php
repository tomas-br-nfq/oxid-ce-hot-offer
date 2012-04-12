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
 * @version   SVN: $Id: discount_categories.inc.php 39182 2011-10-12 13:18:54Z arvydas.vapsva $
 */

$aColumns = array( 'container1' => array(    // field , table,         visible, multilanguage, ident
                                        array( 'oxtitle', 'oxcategories', 1, 1, 0 ),
                                        array( 'oxdesc',  'oxcategories', 1, 1, 0 ),
                                        array( 'oxid',    'oxcategories', 0, 0, 1 )
                                        ),
                     'container2' => array(
                                        array( 'oxtitle', 'oxcategories', 1, 1, 0 ),
                                        array( 'oxdesc',  'oxcategories', 1, 1, 0 ),
                                        array( 'oxid',    'oxobject2discount', 0, 0, 1 ),
                                        array( 'oxid',    'oxcategories',      0, 0, 1 )
                                        ),
                   );
/**
 * Class manages discount categories
 */
class ajaxComponent extends ajaxListComponent
{
    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function _getQuery()
    {
        $oDb = oxDb::getDb();
        $sId = oxConfig::getParameter( 'oxid' );
        $sSynchId = oxConfig::getParameter( 'synchoxid' );

        $sCategoryTable = $this->_getViewName('oxcategories');

        // category selected or not ?
        if ( !$sId) {
            $sQAdd  = " from $sCategoryTable";
        } else {
            $sQAdd  = " from oxobject2discount, $sCategoryTable where $sCategoryTable.oxid=oxobject2discount.oxobjectid ";
            $sQAdd .= " and oxobject2discount.oxdiscountid = ".$oDb->quote( $sId )." and oxobject2discount.oxtype = 'oxcategories' ";
        }

        if ( $sSynchId && $sSynchId != $sId) {
            // dodger performance
            $sSubSelect  = " select $sCategoryTable.oxid from oxobject2discount, $sCategoryTable where $sCategoryTable.oxid=oxobject2discount.oxobjectid ";
            $sSubSelect .= " and oxobject2discount.oxdiscountid = ".$oDb->quote( $sSynchId )." and oxobject2discount.oxtype = 'oxcategories' ";
            if ( stristr( $sQAdd, 'where' ) === false )
                $sQAdd .= ' where ';
            else
                $sQAdd .= ' and ';
            $sQAdd .= " $sCategoryTable.oxid not in ( $sSubSelect ) ";
        }

        return $sQAdd;
    }

    /**
     * Removes selected category (categories) from discount list
     *
     * @return null
     */
    public function removedisccat()
    {
        $aChosenCat = $this->_getActionIds( 'oxobject2discount.oxid' );
        if ( oxConfig::getParameter( 'all' ) ) {

            $sQ = $this->_addFilter( "delete oxobject2discount.* ".$this->_getQuery() );
            oxDb::getDb()->Execute( $sQ );

        } elseif ( is_array( $aChosenCat ) ) {
            $sQ = "delete from oxobject2discount where oxobject2discount.oxid in (" . implode( ", ", oxDb::getInstance()->quoteArray( $aChosenCat ) ) . ") ";
            oxDb::getDb()->Execute( $sQ );
        }
    }

     /**
     * Adds selected category (categories) to discount list
     *
     * @return null
     */
    public function adddisccat()
    {
        $aChosenCat = $this->_getActionIds( 'oxcategories.oxid' );
        $soxId      = oxConfig::getParameter( 'synchoxid');

        if ( oxConfig::getParameter( 'all' ) ) {
            $sCategoryTable = $this->_getViewName('oxcategories');
            $aChosenCat = $this->_getAll( $this->_addFilter( "select $sCategoryTable.oxid ".$this->_getQuery() ) );
        }
        if ( $soxId && $soxId != "-1" && is_array( $aChosenCat ) ) {
            foreach ( $aChosenCat as $sChosenCat) {
                $oObject2Discount = oxNew( "oxbase" );
                $oObject2Discount->init( 'oxobject2discount' );
                $oObject2Discount->oxobject2discount__oxdiscountid = new oxField($soxId);
                $oObject2Discount->oxobject2discount__oxobjectid   = new oxField($sChosenCat);
                $oObject2Discount->oxobject2discount__oxtype       = new oxField("oxcategories");
                $oObject2Discount->save();
            }
        }
    }
}
