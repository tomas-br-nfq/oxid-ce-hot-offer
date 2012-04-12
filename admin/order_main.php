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
 * @version   SVN: $Id: order_main.php 39907 2011-11-14 08:37:38Z arvydas.vapsva $
 */

/**
 * Admin article main order manager.
 * Performs collection and updatind (on user submit) main item information.
 * Admin Menu: Orders -> Display Orders -> Main.
 * @package admin
 */
class Order_Main extends oxAdminDetails
{
    /**
     * Executes parent method parent::render(), creates oxorder and
     * oxuserpayment objects, passes data to Smarty engine and returns
     * name of template file "order_main.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $soxId = $this->_aViewData["oxid"] = $this->getEditObjectId();
        if ( $soxId != "-1" && isset( $soxId ) ) {
            // load object
            $oOrder = oxNew( "oxorder" );
            $oOrder->load( $soxId);

            // paid ?
            if ( $oOrder->oxorder__oxpaid->value != "0000-00-00 00:00:00") {
                $oOrder->blIsPaid = true;
                $oOrder->oxorder__oxpaid = new oxField( oxUtilsDate::getInstance()->formatDBDate( $oOrder->oxorder__oxpaid->value ) );
            }

            $this->_aViewData["edit"] =  $oOrder;
            $this->_aViewData["paymentType"] =  $oOrder->getPaymentType();

            $this->_aViewData["oShipSet"] =  $oOrder->getShippingSetList();
            if ( $oOrder->oxorder__oxdeltype->value ) {

                // order user
                $oUser = oxNew( 'oxuser' );
                $oUser->load( $oOrder->oxorder__oxuserid->value );

                // order sum in default currency
                $dPrice = $oOrder->oxorder__oxtotalbrutsum->value / $oOrder->oxorder__oxcurrate->value;

                $this->_aViewData["oPayments"] = oxPaymentList::getInstance()->getPaymentList( $oOrder->oxorder__oxdeltype->value, $dPrice, $oUser );
            }

            // any voucher used ?
            $this->_aViewData["aVouchers"] =  $oOrder->getVoucherNrList();
        }

        $this->_aViewData["sNowValue"] = date("Y-m-d H:i:s", oxUtilsDate::getInstance()->getTime());
        return "order_main.tpl";
    }

    /**
     * Saves main orders configuration parameters.
     *
     * @return string
     */
    public function save()
    {
        parent::save();

        $soxId = $this->getEditObjectId();
        $aParams    = oxConfig::getParameter( "editval" );

            // shopid
            $sShopID = oxSession::getVar( "actshop" );
            $aParams['oxorder__oxshopid'] = $sShopID;

        $oOrder = oxNew( "oxorder" );
        if ( $soxId != "-1") {
            $oOrder->load( $soxId);
        } else {
            $aParams['oxorder__oxid'] = null;
        }

        $oOrder->assign( $aParams);

        $aDynvalues = oxConfig::getParameter( "dynvalue" );
        if ( isset( $aDynvalues ) ) {
            // #411 Dodger
            $oPayment = oxNew( "oxuserpayment" );
            $oPayment->load( $oOrder->oxorder__oxpaymentid->value);
            $oPayment->oxuserpayments__oxvalue->setValue(oxUtils::getInstance()->assignValuesToText( $aDynvalues));
            $oPayment->save();
        }

        // keeps old delivery cost
        $oOrder->reloadDelivery( false );

        // keeps old discount
        $oOrder->reloadDiscount( false );

        $oOrder->recalculateOrder();

        // set oxid if inserted
        $this->setEditObjectId( $oOrder->getId() );
    }

    /**
     * Sends order.
     *
     * @return null
     */
    public function sendorder()
    {
        $soxId = $this->getEditObjectId();
        $oOrder = oxNew( "oxorder" );
        if ( $oOrder->load( $soxId ) ) {

            // #632A
            $oOrder->oxorder__oxsenddate->setValue( date( "Y-m-d H:i:s", oxUtilsDate::getInstance()->getTime() ) );
            $oOrder->save();

            // #1071C
            $oOrderArticles = $oOrder->getOrderArticles();
            foreach ( $oOrderArticles as $sOxId => $oArticle ) {
                // remove canceled articles from list
                if ( $oArticle->oxorderarticles__oxstorno->value == 1 ) {
                    $oOrderArticles->offsetUnset( $sOxId );
                }
            }

            if ( oxConfig::getParameter( "sendmail" ) ) {
                // send eMail
                $oEmail = oxNew( "oxemail" );
                $oEmail->sendSendedNowMail( $oOrder );
            }

        }
    }

    /**
     * Resets order shipping date.
     *
     * @return null
     */
    public function resetorder()
    {
        $oOrder = oxNew( "oxorder" );
        if ( $oOrder->load( $this->getEditObjectId() ) ) {

            $oOrder->oxorder__oxsenddate->setValue("0000-00-00 00:00:00");
            $oOrder->save();

        }
    }

    /**
     * Changes delivery set for this order and
     * resets current payment.
     *
     * @return null
     */
    public function changeDelSet()
    {
        $oOrder = oxNew( "oxorder" );
        if ( ( $sDelSetId = oxConfig::getParameter( "setDelSet" ) ) &&
             $oOrder->load( $this->getEditObjectId() ) ) {
            $oOrder->oxorder__oxpaymenttype->setValue( "oxempty" );
            // keeps old discount
            $oOrder->reloadDiscount( false );
            $oOrder->setDelivery( $sDelSetId );
            $oOrder->recalculateOrder();
        }
    }

    /**
     * Changes delivery set for this order and
     * resets current payment.
     *
     * @return null
     */
    public function changePayment()
    {
        $oOrder = oxNew( "oxorder" );
        if ( ( $sPayId = oxConfig::getParameter( "setPayment") ) &&
             $oOrder->load( $this->getEditObjectId() ) ) {
            $oOrder->oxorder__oxpaymenttype->setValue( $sPayId );
            // keeps old discount
            $oOrder->reloadDiscount( false );
            $oOrder->recalculateOrder();
        }
    }
}
