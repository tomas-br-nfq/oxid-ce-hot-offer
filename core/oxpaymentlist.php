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
 * @package   core
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: oxpaymentlist.php 32007 2010-12-17 15:10:27Z sarunas $
 */

/**
 * Payment list manager.
 * @package core
 */
class oxPaymentList extends oxList
{
    /**
     * oxDeliveryList instance
     * @var oxDeliveryList
     */
    protected static $_instance = null;

    /**
     * Home country id
     *
     * @var string
     */
    protected $_sHomeCountry = null;

    /**
     * Class Constructor
     *
     * @param string $sObjectsInListName Associated list item object type
     */
    public function __construct( $sObjectsInListName = 'oxpayment' )
    {
        $this->setHomeCountry( $this->getConfig()->getConfigParam( 'aHomeCountry' ) );
        parent::__construct( 'oxpayment');
    }

    /**
     * Home country setter
     *
     * @param string $sHomeCountry country id
     *
     * @return null
     */
    public function setHomeCountry( $sHomeCountry )
    {
        if ( is_array( $sHomeCountry ) ) {
            $this->_sHomeCountry = current( $sHomeCountry );
        } else {
            $this->_sHomeCountry = $sHomeCountry;
        }
    }

    /**
     * Returns oxPaymentList instance
     *
     * @return oxpaymentList
     */
    public static function getInstance()
    {
        // disable cashing for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            self::$_instance = modInstances::getMod( __CLASS__ );
        }

        if ( !isset( self::$_instance ) ) {
            // allow modules
            self::$_instance = oxNew( 'oxPaymentList' );

            if ( defined( 'OXID_PHP_UNIT' ) ) {
                modInstances::addMod( __CLASS__, self::$_instance);
            }
        }
        return self::$_instance;
    }

    /**
     * Creates payment list filter SQL to load current state payment list
     *
     * @param string $sShipSetId user chosen delivery set
     * @param double $dPrice     basket products price
     * @param oxuser $oUser      session user object
     *
     * @return string
     */
    protected function _getFilterSelect( $sShipSetId, $dPrice, $oUser )
    {
        $oDb = oxDb::getDb();
        $sBoni = ($oUser && $oUser->oxuser__oxboni->value )?$oUser->oxuser__oxboni->value:0;

        $sTable = getViewName( 'oxpayments' );
        $sQ  = "select {$sTable}.* from ( select distinct {$sTable}.* from {$sTable}, oxobject2group, oxobject2payment ";
        $sQ .= "where {$sTable}.oxactive='1' and oxobject2group.oxobjectid = {$sTable}.oxid ";
        $sQ .= "and oxobject2payment.oxpaymentid = {$sTable}.oxid and oxobject2payment.oxobjectid = ".$oDb->quote( $sShipSetId );
        $sQ .= " and {$sTable}.oxfromboni <= ".$oDb->quote( $sBoni ) ." and {$sTable}.oxfromamount <= ".$oDb->quote( $dPrice ) ." and {$sTable}.oxtoamount >= ".$oDb->quote( $dPrice );

        // defining initial filter parameters
        $sGroupIds  = '';
        $sCountryId = $this->getCountryId( $oUser );

        // checking for current session user which gives additional restrictions for user itself, users group and country
        if ( $oUser ) {
            // user groups ( maybe would be better to fetch by function oxuser::getUserGroups() ? )
            foreach ( $oUser->getUserGroups() as $oGroup ) {
                if ( $sGroupIds ) {
                    $sGroupIds .= ', ';
                }
                $sGroupIds .= "'".$oGroup->getId()."'";
            }
        }

        $sGroupTable   = getViewName( 'oxgroups' );
        $sCountryTable = getViewName( 'oxcountry' );

        $sCountrySql = $sCountryId ? "exists( select 1 from oxobject2payment as s1 where s1.oxpaymentid={$sTable}.OXID and s1.oxtype='oxcountry' and s1.OXOBJECTID=".$oDb->quote( $sCountryId )." limit 1 )":'0';
        $sGroupSql   = $sGroupIds ? "exists( select 1 from oxobject2group as s3 where s3.OXOBJECTID={$sTable}.OXID and s3.OXGROUPSID in ( {$sGroupIds} ) limit 1 )":'0';

        $sQ .= " ) as $sTable where (
            select
                if( exists( select 1 from oxobject2payment as ss1, $sCountryTable where $sCountryTable.oxid=ss1.oxobjectid and ss1.oxpaymentid={$sTable}.OXID and ss1.oxtype='oxcountry' limit 1 ),
                    {$sCountrySql},
                    1) &&
                if( exists( select 1 from oxobject2group as ss3, $sGroupTable where $sGroupTable.oxid=ss3.oxgroupsid and ss3.OXOBJECTID={$sTable}.OXID limit 1 ),
                    {$sGroupSql},
                    1)
                )  order by {$sTable}.oxsort asc ";

        return $sQ;
    }

    /**
     * Returns user country id for for payment selection
     *
     * @param oxuser $oUser oxuser object
     *
     * @return string
     */
    public function getCountryId( $oUser )
    {
        $sCountryId = null;
        if ( $oUser ) {
            $sCountryId = $oUser->getActiveCountry();
        }

        if ( !$sCountryId ) {
            $sCountryId = $this->_sHomeCountry;
        }

        return $sCountryId;
    }

    /**
     * Loads and returns list of user payments.
     *
     * @param string $sShipSetId user chosen delivery set
     * @param double $dPrice     basket product price excl. discount
     * @param oxuser $oUser      session user object
     *
     * @return array
     */
    public function getPaymentList( $sShipSetId, $dPrice, $oUser = null )
    {
        $this->selectString( $this->_getFilterSelect( $sShipSetId, $dPrice, $oUser ) );
        return $this->_aArray;
    }
}
