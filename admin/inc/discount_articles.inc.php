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
 * @version   SVN: $Id: discount_articles.inc.php 39182 2011-10-12 13:18:54Z arvydas.vapsva $
 */

$aColumns = array( 'container1' => array(    // field , table,         visible, multilanguage, ident
                                        array( 'oxartnum', 'oxarticles', 1, 0, 0 ),
                                        array( 'oxtitle',  'oxarticles', 1, 1, 0 ),
                                        array( 'oxean',    'oxarticles', 1, 0, 0 ),
                                        array( 'oxmpn',    'oxarticles', 0, 0, 0 ),
                                        array( 'oxprice',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxstock',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxid',     'oxarticles', 0, 0, 1 )
                                        ),
                     'container2' => array(
                                        array( 'oxartnum', 'oxarticles', 1, 0, 0 ),
                                        array( 'oxtitle',  'oxarticles', 1, 1, 0 ),
                                        array( 'oxean',    'oxarticles', 1, 0, 0 ),
                                        array( 'oxmpn',    'oxarticles', 0, 0, 0 ),
                                        array( 'oxprice',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxstock',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxid',     'oxobject2discount', 0, 0, 1 )
                                        )
                    );

/**
 * Class manages discount articles
 */
class ajaxComponent extends ajaxListComponent
{
    /**
     * If true extended column selection will be build
     *
     * @var bool
     */
    protected $_blAllowExtColumns = true;

    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function _getQuery()
    {
        $myConfig = $this->getConfig();

        $sArticleTable = $this->_getViewName('oxarticles');
        $sCatTable     = $this->_getViewName('oxcategories');
        $sO2CView      = $this->_getViewName('oxobject2category');

        $oDb = oxDb::getDb();
        $sOxid = oxConfig::getParameter( 'oxid' );
        $sSynchOxid = oxConfig::getParameter( 'synchoxid' );

        // category selected or not ?
        if ( !$sOxid && $sSynchOxid ) {
            $sQAdd  = " from $sArticleTable where 1 ";
            $sQAdd .= $myConfig->getConfigParam( 'blVariantsSelection' )?'':"and $sArticleTable.oxparentid = '' ";
        } else {
            // selected category ?
            if ( $sSynchOxid && $sOxid != $sSynchOxid ) {
                $sQAdd  = " from $sO2CView left join $sArticleTable on ";
                $sQAdd .= $myConfig->getConfigParam( 'blVariantsSelection' )?"($sArticleTable.oxid=$sO2CView.oxobjectid or $sArticleTable.oxparentid=$sO2CView.oxobjectid)":" $sArticleTable.oxid=$sO2CView.oxobjectid ";
                $sQAdd .= " where $sO2CView.oxcatnid = ".$oDb->quote( $sOxid )." and $sArticleTable.oxid is not null ";

                // resetting
                $sId = null;
            } else {
                $sQAdd  = " from oxobject2discount, $sArticleTable where $sArticleTable.oxid=oxobject2discount.oxobjectid ";
                $sQAdd .= " and oxobject2discount.oxdiscountid = ".$oDb->quote( $sOxid )." and oxobject2discount.oxtype = 'oxarticles' ";
            }
        }

        if ( $sSynchOxid && $sSynchOxid != $sOxid) {
            // dodger performance
            $sSubSelect .= " select $sArticleTable.oxid from oxobject2discount, $sArticleTable where $sArticleTable.oxid=oxobject2discount.oxobjectid ";
            $sSubSelect .= " and oxobject2discount.oxdiscountid = ".$oDb->quote( $sSynchOxid )." and oxobject2discount.oxtype = 'oxarticles' ";

            if ( stristr( $sQAdd, 'where' ) === false )
                $sQAdd .= ' where ';
            else
                $sQAdd .= ' and ';
            $sQAdd .= " $sArticleTable.oxid not in ( $sSubSelect ) ";
        }

        return $sQAdd;
    }

    /**
     * Removes selected article (articles) from discount list
     *
     * @return null
     */
    public function removediscart()
    {
        $aChosenArt = $this->_getActionIds( 'oxobject2discount.oxid' );
        if ( oxConfig::getParameter( 'all' ) ) {

            $sQ = parent::_addFilter( "delete oxobject2discount.* ".$this->_getQuery() );
            oxDb::getDb()->Execute( $sQ );

        } elseif ( is_array( $aChosenArt ) ) {
            $sQ = "delete from oxobject2discount where oxobject2discount.oxid in (" . implode( ", ", oxDb::getInstance()->quoteArray( $aChosenArt ) ) . ") ";
            oxDb::getDb()->Execute( $sQ );
        }
    }

    /**
     * Adds selected article (articles) to discount list
     *
     * @return null
     */
    public function adddiscart()
    {
        $aChosenArt = $this->_getActionIds( 'oxarticles.oxid' );
        $soxId      = oxConfig::getParameter( 'synchoxid');

        // adding
        if ( oxConfig::getParameter( 'all' ) ) {
            $sArticleTable = $this->_getViewName('oxarticles');
            $aChosenArt = $this->_getAll( parent::_addFilter( "select $sArticleTable.oxid ".$this->_getQuery() ) );
        }
        if ( $soxId && $soxId != "-1" && is_array( $aChosenArt ) ) {
            foreach ( $aChosenArt as $sChosenArt) {
                $oObject2Discount = oxNew( "oxbase" );
                $oObject2Discount->init( 'oxobject2discount' );
                $oObject2Discount->oxobject2discount__oxdiscountid = new oxField($soxId);
                $oObject2Discount->oxobject2discount__oxobjectid   = new oxField($sChosenArt);
                $oObject2Discount->oxobject2discount__oxtype       = new oxField("oxarticles");
                $oObject2Discount->save();
            }
        }
    }
}
