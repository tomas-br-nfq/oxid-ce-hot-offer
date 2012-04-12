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
 * @copyright (C) OXID eSales AG 2003-2012
 * @version OXID eShop CE
 * @version   SVN: $Id: order_overview.php 41852 2012-01-28 13:54:58Z arvydas.vapsva $
 */

/**
 * Admin order overview manager.
 * Collects order overview information, updates it on user submit, etc.
 * Admin Menu: Orders -> Display Orders -> Overview.
 * @package admin
 */
class Order_Overview extends oxAdminDetails
{
    /**
     * executes parent mathod parent::render(), creates oxorder, passes
     * it's data to Smarty engine and returns name of template file
     * "order_overview.tpl".
     *
     * @return string
     */
    public function render()
    {
        $myConfig = $this->getConfig();
        parent::render();

        $oOrder = oxNew( "oxorder" );
        $oCur  = $myConfig->getActShopCurrencyObject();
        $oLang = oxLang::getInstance();

        $soxId = $this->getEditObjectId();
        if ( $soxId != "-1" && isset( $soxId)) {
            // load object
            $oOrder->load( $soxId);

            $this->_aViewData["edit"]          = $oOrder;
            $this->_aViewData["aProductVats"]  = $oOrder->getProductVats();
            $this->_aViewData["orderArticles"] = $oOrder->getOrderArticles();
            $this->_aViewData["giftCard"]      = $oOrder->getGiftCard();
            $this->_aViewData["paymentType"]   = $this->_getPaymentType( $oOrder );
            $this->_aViewData["deliveryType"]  = $oOrder->getDelSet();
            if ( $oOrder->oxorder__oxtsprotectcosts->value ) {
                $this->_aViewData["tsprotectcosts"]  = $oLang->formatCurrency( $oOrder->oxorder__oxtsprotectcosts->value, $oCur);
            }
        }

        // orders today
        $dSum  = $oOrder->getOrderSum(true);
        $this->_aViewData["ordersum"] = $oLang->formatCurrency($dSum, $oCur);
        $this->_aViewData["ordercnt"] = $oOrder->getOrderCnt(true);

        // ALL orders
        $dSum = $oOrder->getOrderSum();
        $this->_aViewData["ordertotalsum"] = $oLang->formatCurrency( $dSum, $oCur);
        $this->_aViewData["ordertotalcnt"] = $oOrder->getOrderCnt();
        $this->_aViewData["afolder"] = $myConfig->getConfigParam( 'aOrderfolder' );
        $this->_aViewData["sfolder"] = $myConfig->getConfigParam( 'aOrderfolder' );
            $this->_aViewData["alangs"] = $oLang->getLanguageNames();

        $this->_aViewData["currency"] = $oCur;

        return "order_overview.tpl";
    }

    /**
     * Returns user payment used for current order. In case current order was executed using
     * credit card and user payment info is not stored in db (if oxConfig::blStoreCreditCardInfo = false),
     * just for preview user payment is set from oxpayment
     *
     * @param object $oOrder Order object
     *
     * @return oxuserpayment
     */
    protected function _getPaymentType( $oOrder )
    {
        if ( !( $oUserPayment = $oOrder->getPaymentType() ) && $oOrder->oxorder__oxpaymenttype->value ) {
            $oPayment = oxNew( "oxpayment" );
            if ( $oPayment->load( $oOrder->oxorder__oxpaymenttype->value ) ) {
                // in case due to security reasons payment info was not kept in db
                $oUserPayment = oxNew( "oxuserpayment" );
                $oUserPayment->oxpayments__oxdesc = new oxField( $oPayment->oxpayments__oxdesc->value );
            }
        }

        return $oUserPayment;
    }

    /**
     * Performs Lexware export to user (outputs file to save).
     *
     * @return null
     */
    public function exportlex()
    {
        $sOrderNr   = oxConfig::getParameter( "ordernr");
        $sToOrderNr = oxConfig::getParameter( "toordernr");
        $oImex = oxNew( "oximex" );
        if ( ( $sLexware = $oImex->exportLexwareOrders( $sOrderNr, $sToOrderNr ) ) ) {
            $oUtils = oxUtils::getInstance();
            $oUtils->setHeader( "Pragma: public" );
            $oUtils->setHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            $oUtils->setHeader( "Expires: 0" );
            $oUtils->setHeader( "Content-type: application/x-download" );
            $oUtils->setHeader( "Content-Length: ".strlen( $sLexware ) );
            $oUtils->setHeader( "Content-Disposition: attachment; filename=intern.xml" );
            $oUtils->showMessageAndExit( $sLexware );
        }
    }
    /**
     * Performs PDF export to user (outputs file to save).
     *
     * @return null
     */
    public function createPDF()
    {
        $soxId = $this->getEditObjectId();
        if ( $soxId != "-1" && isset( $soxId ) ) {
            // load object
            $oOrder = oxNew( "oxorder" );
            if ( $oOrder->load( $soxId ) ) {
                $oUtils = oxUtils::getInstance();
                $sFilename = $oOrder->oxorder__oxordernr->value . "_" . $oOrder->oxorder__oxbilllname->getRawValue() . ".pdf";

                ob_start();
                $oOrder->genPDF( $sFilename, oxConfig::getParameter( "pdflanguage" ) );
                $sPDF = ob_get_contents();
                ob_end_clean();

                $oUtils->setHeader( "Pragma: public" );
                $oUtils->setHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
                $oUtils->setHeader( "Expires: 0" );
                $oUtils->setHeader( "Content-type: application/pdf" );
                $oUtils->setHeader( "Content-Disposition: attachment; filename=".$sFilename );
                oxUtils::getInstance()->showMessageAndExit( $sPDF );
            }
        }
    }

    /**
     * Performs DTAUS export to user (outputs file to save).
     *
     * @return null
     */
    public function exportDTAUS()
    {
        $oOrderList = oxNew( "oxlist" );
        $oOrderList->init( "oxorder" );
        $sSelect =  "select * from oxorder where oxpaymenttype = 'oxiddebitnote'";

        if ( ( $iFromOrderNr = oxConfig::getParameter( "ordernr") ) ) {
            $sSelect .= " and oxordernr >= $iFromOrderNr";
        }

        $oOrderList->selectString( $sSelect );
        if ( count( $oOrderList ) ) {
            $oUserPayment = oxNew( "oxuserpayment" );
            $oUtils = oxUtils::getInstance();
            $oShop  = $this->getConfig()->getActiveShop();

            $sCompany   = $oShop->oxshops__oxcompany->value;
            $sRoutingNr = $this->_cleanup( $oShop->oxshops__oxbankcode->value ) + 1 - 1;
            $sAccountNr = $this->_cleanup( $oShop->oxshops__oxbanknumber->value );
            $sSubject   = oxLang::getInstance()->translateString( "order" );

            // can't be called with oxnew, as it only supports single constructor parameter
            $oDtaus = oxNew( "oxDtausBuilder", $sCompany, $sRoutingNr, $sAccountNr );
            foreach ( $oOrderList as $oOrder ) {
                $oUserPayment->load( $oOrder->oxorder__oxpaymentid->value );
                $aDynValues = $oUtils->assignValuesFromText( $oUserPayment->oxuserpayments__oxvalue->value );

                $sCustName  = $aDynValues[3]->value;
                $sRoutingNr = $this->_cleanup( $aDynValues[1]->value );
                $sAccountNr = $this->_cleanup( $aDynValues[2]->value );

                $oDtaus->add( $sCustName, $sRoutingNr, $sAccountNr, $oOrder->getTotalOrderSum(), array( $oShop->oxshops__oxname->getRawValue(), $sSubject . " " . $oOrder->oxorder__oxordernr->value ) );
            }

            $oUtils->setHeader( "Content-Disposition: attachment; filename=\"dtaus0.txt\"" );
            $oUtils->setHeader( "Content-type: text/plain" );
            $oUtils->setHeader( "Cache-control: public" );
            $oUtils->showMessageAndExit( $oDtaus->create() );
        }
    }

    /**
     * Removes white spaces from given string
     *
     * @param string $sValue value to clean
     *
     * @return string
     */
    protected function _cleanup( $sValue )
    {
        return str_replace( " ", "", $sValue );
    }

    /**
     * Sends order.
     *
     * @return null
     */
    public function sendorder()
    {
        $oOrder = oxNew( "oxorder" );
        if ( $oOrder->load( $this->getEditObjectId() ) ) {
            $oOrder->oxorder__oxsenddate->setValue( date( "Y-m-d H:i:s", oxUtilsDate::getInstance()->getTime() ) );
            $oOrder->save();

            // #1071C
            $oOrderArticles = $oOrder->getOrderArticles();
            foreach ( $oOrderArticles as $sOxid => $oArticle ) {
                // remove canceled articles from list
                if ( $oArticle->oxorderarticles__oxstorno->value == 1 ) {
                    $oOrderArticles->offsetUnset( $sOxid );
                }
            }

            if ( ( $blMail = oxConfig::getParameter( "sendmail" ) ) ) {
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
            $oOrder->oxorder__oxsenddate->setValue( "0000-00-00 00:00:00" );
            $oOrder->save();
        }
    }

    /**
     * Returns pdf export state - can export or not
     *
     * @return bool
     */
    public function canExport()
    {
        $blCan = false;
        //V #529: check if PDF invoice module is active
        if ( oxUtilsObject::getInstance()->isModuleActive( 'oxorder', 'myorder' ) ) {
            $oDb = oxDb::getDb();
            $sOrderId = $this->getEditObjectId();
            $sTable = getViewName( "oxorderarticles" );
            $sQ = "select count(oxid) from {$sTable} where oxorderid = ".$oDb->quote( $sOrderId )." and oxstorno = 0";
            $blCan = (bool) $oDb->getOne( $sQ );
        }
        return $blCan;
    }

    /**
     * Get information about shipping status
     *
     * @return bool
     */
    public function canResetShippingDate()
    {
        $oOrder = oxNew( "oxorder" );
        $blCan = false;
        if ( $oOrder->load( $this->getEditObjectId() ) ) {
            $blCan = $oOrder->oxorder__oxstorno->value == "0" &&
                     !( $oOrder->oxorder__oxsenddate->value == "0000-00-00 00:00:00" || $oOrder->oxorder__oxsenddate->value == "-" );
        }
        return $blCan;
    }
}
