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
 * @version   SVN: $Id: oxwrapping.php 37306 2011-07-25 11:48:28Z arvydas.vapsva $
 */

/**
 * Wrapping manager.
 * Performs Wrapping data/objects loading, deleting.
 * @package core
 */
class oxWrapping extends oxI18n
{
    /**
     * Core table name
     *
     * @var string name of object core table
     */
    protected $_sCoreTbl = 'oxwrapping';

    /**
     * Class name
     *
     * @var string name of current class
     */
    protected $_sClassName = 'oxwrapping';

    /**
     * Wrapping oxprice object.
     *
     * @var oxprice
     */
    protected $_oPrice = null;

    /**
     * Wrapping Vat
     *
     * @var double
     */
    protected $_dVat = 0;

    /**
     * Class constructor, initiates parent constructor (parent::oxBase()), loads
     * base shop objects.
     *
     * @return null
     */
    public function __construct()
    {
        $this->setWrappingVat( $this->getConfig()->getConfigParam( 'dDefaultVAT' ) );
        parent::__construct();
        $this->init( 'oxwrapping' );
    }

    /**
     * Wrapping Vat setter
     *
     * @param double $dVat vat
     *
     * @return null
     */
    public function setWrappingVat( $dVat )
    {
        $this->_dVat = $dVat;
    }

    /**
     * Assigns oxwrapping object data and calculates dprice/fprice
     *
     * @param array $dbRecord object data
     *
     * @return null
     */
    public function assign( $dbRecord )
    {
        // loading object from database
        parent::assign( $dbRecord );

        // setting image path
        $myConfig = $this->getConfig();
    }

    /**
     * Returns oxprice object for wrapping
     *
     * @param int $dAmount article amount
     *
     * @return object
     */
    public function getWrappingPrice( $dAmount = 1 )
    {
        if ( $this->_oPrice === null ) {
            $this->_oPrice = oxNew( 'oxprice' );

            $oCur = $this->getConfig()->getActShopCurrencyObject();
            $this->_oPrice->setPrice( $this->oxwrapping__oxprice->value * $oCur->rate, $this->_dVat );
            $this->_oPrice->multiply( $dAmount );
        }

        return $this->_oPrice;
    }

    /**
     * Loads wrapping list for specific wrap type
     *
     * @param string $sWrapType wrap type
     *
     * @return array $oEntries wrapping list
     */
    public function getWrappingList( $sWrapType )
    {
        // load wrapping
        $oEntries = oxNew( 'oxlist' );
        $oEntries->init( 'oxwrapping' );
        $sWrappingViewName = getViewName( 'oxwrapping' );
        $sSelect =  "select * from $sWrappingViewName where $sWrappingViewName.oxactive = '1' and $sWrappingViewName.oxtype = " . oxDb::getDb()->quote( $sWrapType );
        $oEntries->selectString( $sSelect );

        return $oEntries;
    }

    /**
     * Counts amount of wrapping/card options
     *
     * @param string $sWrapType type - wrapping paper (WRAP) or card (CARD)
     *
     * @return int
     */
    public function getWrappingCount( $sWrapType )
    {
        $sWrappingViewName = getViewName( 'oxwrapping' );
        $sQ = "select count(*) from $sWrappingViewName where $sWrappingViewName.oxactive = '1' and $sWrappingViewName.oxtype = " . oxDb::getDb()->quote( $sWrapType );
        return (int) oxDb::getDb()->getOne( $sQ );
    }

    /**
     * Returns formatted wrapping price
     *
     * @return string
     */
    public function getFPrice()
    {
        return oxLang::getInstance()->formatCurrency( $this->getWrappingPrice()->getBruttoPrice(), $this->getConfig()->getActShopCurrencyObject() );
    }

    /**
     * Returns returns dyn image dir (not ssl)
     *
     * @return string
     */
    public function getNoSslDynImageDir()
    {
        return $this->getConfig()->getPictureUrl(null, false, false, null, $this->oxwrapping__oxshopid->value);
    }

    /**
     * Returns returns dyn image dir
     *
     * @return string
     */
    public function getPictureUrl()
    {
        if ( $this->oxwrapping__oxpic->value ) {
           return $this->getConfig()->getPictureUrl( "master/wrapping/".$this->oxwrapping__oxpic->value, false, $this->getConfig()->isSsl(), null, $this->oxwrapping__oxshopid->value );
        }
    }
}
