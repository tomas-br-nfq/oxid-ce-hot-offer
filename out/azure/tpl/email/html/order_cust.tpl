[{ assign var="shop"      value=$oEmailView->getShop() }]
[{ assign var="oViewConf" value=$oEmailView->getViewConfig() }]
[{ assign var="currency"  value=$oEmailView->getCurrency() }]
[{ assign var="user"      value=$oEmailView->getUser() }]
[{ assign var="oDelSet"   value=$order->getDelSet() }]
[{ assign var="basket"    value=$order->getBasket() }]
[{ assign var="payment"   value=$order->getPayment() }]


[{include file="email/html/header.tpl" title=$shop->oxshops__oxordersubject->value}]

    [{block name="email_html_order_cust_orderemail"}]
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 10px 0;">
            [{if $payment->oxuserpayments__oxpaymentsid->value == "oxempty"}]
              [{oxcontent ident="oxuserordernpemail"}]
            [{else}]
              [{oxcontent ident="oxuserorderemail"}]
            [{/if}]
        </p>
    [{/block}]

        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <tr>
            <td height="15" width="100" style="padding: 5px; border-bottom: 4px solid #ddd;">
                <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ORDERNOMBER" }] [{ $order->oxorder__oxordernr->value }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PRODUCT" }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_UNITPRICE" }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_QUANTITY" }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_VAT" }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTAL" }]</b></p>
            </td>
            <td style="padding: 5px; border-bottom: 4px solid #ddd;">
              <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 0; color: #555;"><b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PRODUCTREVIEW" }]</b></p>
            </td>
          </tr>

        [{assign var="basketitemlist" value=$basket->getBasketArticles() }]

        [{foreach key=basketindex from=$basket->getContents() item=basketitem}]
            [{block name="email_html_order_cust_basketitem"}]
                [{assign var="basketproduct" value=$basketitemlist.$basketindex }]

                <tr valign="top">
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;">
                        <img src="[{$basketproduct->getThumbnailUrl() }]" border="0" hspace="0" vspace="0" alt="[{ $basketproduct->oxarticles__oxtitle->value|strip_tags }]" align="texttop">
                        [{if $oViewConf->getShowGiftWrapping() }]
                            [{assign var="oWrapping" value=$basketitem->getWrapping() }]
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 10px 0;">
                                <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPING" }]&nbsp;</b>
                                [{ if !$basketitem->getWrappingId() }]
                                    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_NONE" }]
                                [{else}]
                                    [{$oWrapping->oxwrapping__oxname->value}]
                                [{/if}]
                            </p>
                        [{/if}]
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;">
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 10px 0;">
                            <b>[{ $basketproduct->oxarticles__oxtitle->value }][{ if $basketproduct->oxarticles__oxvarselect->value}], [{ $basketproduct->oxarticles__oxvarselect->value}][{/if}]</b>
                            [{ if $basketitem->getChosenSelList() }]
                                <ul style="padding: 0 10px; margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                                    [{foreach from=$basketitem->getChosenSelList() item=oList}]
                                        <li style="padding: 3px;">[{ $oList->name }] [{ $oList->value }]</li>
                                    [{/foreach}]
                                </ul>
                            [{/if}]
                            [{ if $basketitem->getPersParams() }]
                                <ul style="padding: 0 10px; margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                                    [{foreach key=sVar from=$basketitem->getPersParams() item=aParam}]
                                        <li style="padding: 3px;">[{$sVar}] : [{$aParam}]</li>
                                    [{/foreach}]
                                </ul>
                            [{/if}]
                            <br>
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 10px 0;">
                                <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ARTNOMBER" }] [{ $basketproduct->oxarticles__oxartnum->value }]</b>
                            </p>
                        </p>
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;" align="right">
                      <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                        <b>[{$basketitem->getAmount()}]</b>
                      </p>
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;" align="right">
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                            <b>[{if $basketitem->getFUnitPrice() }][{ $basketitem->getFUnitPrice() }] [{ $currency->sign}][{/if}]</b>
                        </p>

                        [{if $basketitem->aDiscounts}]
                            <p>
                                <em style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_DISCOUNT" }]
                                    [{foreach from=$basketitem->aDiscounts item=oDiscount}]
                                      <br>[{ $oDiscount->sDiscount }]
                                    [{/foreach}]
                                </em>
                            </p>
                        [{/if}]

                        [{ if $basketproduct->oxarticles__oxorderinfo->value }]
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                                [{ $basketproduct->oxarticles__oxorderinfo->value }]
                            </p>
                        [{/if}]
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;" align="right">
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                            [{$basketitem->getVatPercent() }]%
                        </p>
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;" align="right">
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                            <b>[{ $basketitem->getFTotalPrice() }] [{ $currency->sign}]</b>
                        </p>
                    </td>
                    <td style="padding: 5px; border-bottom: 4px solid #ddd;">
                        <a href="[{ $oViewConf->getBaseDir() }]index.php?shp=[{$shop->oxshops__oxid->value}]&amp;anid=[{ $basketproduct->oxarticles__oxid->value }]&amp;cl=review&amp;reviewuserhash=[{$user->getReviewUserHash($user->getId())}]" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;" target="_blank">[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_REVIEW" }]</a>
                    </td>
                </tr>
            [{/block}]
        [{/foreach}]
      </table>

      [{block name="email_html_order_cust_giftwrapping"}]
          [{if $oViewConf->getShowGiftWrapping() && $basket->getCard() }]
              [{assign var="oCard" value=$basket->getCard() }]
              <br><br>

              <table border="0" cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                      <td colspan="2" style="padding: 5px; border-bottom: 4px solid #ddd;">
                          <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                              <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_YOURGREETINGCARD" }]</b>
                          </p>
                      </td>
                  </tr>
                  <tr valign="top">
                      <td style="padding: 5px; border-bottom: 4px solid #ddd;" valign="top" width="1%">
                          <img src="[{$oCard->getPictureUrl()}]" alt="[{$oCard->oxwrapping__oxname->value}]" hspace="0" vspace="0" border="0" align="top">
                      </td>
                      <td style="padding: 5px; padding-left: 15px; border-bottom: 4px solid #ddd;">
                          <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                          [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_YOURMESSAGE" }]<br><br>
                          [{$basket->getCardMessage()}]
                          </p>
                      </td>
                  </tr>
              </table>
          [{/if}]
      [{/block}]

      <br><br>

    <table border="0" cellspacing="0" cellpadding="2" width="100%">
        <tr valign="top">
            <td width="50%" style="padding-right: 40px;">
                [{block name="email_html_order_cust_voucherdiscount"}]
                    <table border="0" cellspacing="0" cellpadding="0">
                        [{if $oViewConf->getShowVouchers() && $basket->getVoucherDiscValue() }]
                            <tr valign="top">
                                <td style="padding: 5px 20px 5px 5px; border-bottom: 2px solid #ccc;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;  color: #555;">
                                        <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_USEDCOUPONS" }]</b>
                                    </p>
                                </td>
                                <td style="padding: 5px 20px 5px 5px; border-bottom: 2px solid #ccc;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;  color: #555;">
                                        <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_REBATE" }]</b>
                                    </p>
                                </td>
                            </tr>
                            [{ foreach from=$order->getVoucherList() item=voucher}]
                                <tr valign="top">
                                    <td style="padding: 5px 20px 5px 5px;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{$voucher->oxmodvouchers__oxvouchernr->value}]
                                        </p>
                                    </td>
                                    <td style="padding: 5px 20px 5px 5px;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{$voucher->oxmodvouchers__oxdiscount->value}] [{ if $voucher->oxmodvouchers__oxdiscounttype->value == "absolute"}][{ $currency->sign}][{else}]%[{/if}]
                                        </p>
                                    </td>
                                </tr>
                            [{/foreach }]
                        [{/if}]
                    </table>
                [{/block}]
            </td>
            <td width="50%" valign="top" align="right">
                <table border="0" cellspacing="0" cellpadding="2" width="300">
                    [{if !$basket->getDiscounts()}]
                        [{block name="email_html_order_cust_nodiscounttotalnet"}]
                            <!-- netto price -->
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALNET" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right" width="60">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getProductsNetPrice() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                        [{/block}]
                        [{block name="email_html_order_cust_nodiscountproductvats"}]
                            <!-- VATs -->
                            [{foreach from=$basket->getProductVats() item=VATitem key=key}]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX1" }] [{ $key }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX2" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $VATitem }] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/foreach}]
                        [{/block}]
                    [{/if}]

                    [{block name="email_html_order_cust_totalgross"}]
                        <!-- brutto price -->
                        <tr valign="top">
                            <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                    [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALGROSS" }]
                                </p>
                            </td>
                            <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                    [{ $basket->getFProductsPrice() }] [{ $currency->sign}]
                                </p>
                            </td>
                        </tr>
                    [{/block}]

                    <!-- applied discounts -->
                    [{if $basket->getDiscounts()}]

                        [{block name="email_html_order_cust_discounts"}]
                            <!-- discounts -->
                            [{foreach from=$basket->getDiscounts() item=oDiscount}]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{if $oDiscount->dDiscount < 0 }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_CHARGE" }][{else}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_DICOUNT" }][{/if}] <em>[{ $oDiscount->sDiscount }]</em> :
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{if $oDiscount->dDiscount < 0 }][{ $oDiscount->fDiscount|replace:"-":"" }][{else}]-[{ $oDiscount->fDiscount }][{/if}] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/foreach}]
                        [{/block}]

                        [{block name="email_html_order_cust_totalnet"}]
                            <!-- netto price -->
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TOTALNET" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getProductsNetPrice() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                        [{/block}]

                        [{block name="email_html_order_cust_productvats"}]
                            <!-- VATs -->
                            [{foreach from=$basket->getProductVats() item=VATitem key=key}]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX1" }] [{ $key }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX2" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $VATitem }] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/foreach}]
                        [{/block}]
                    [{/if}]

                    [{block name="email_html_order_cust_voucherdiscount"}]
                        <!-- voucher discounts -->
                        [{if $oViewConf->getShowVouchers() && $basket->getVoucherDiscValue() }]
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_COUPON" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ if $basket->getFVoucherDiscountValue() > 0 }]-[{/if}][{ $basket->getFVoucherDiscountValue()|replace:"-":"" }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                        [{/if}]
                    [{/block}]

                    [{block name="email_html_order_cust_delcosts"}]
                        <!-- delivery costs -->
                        [{if $basket->getDelCostVat() > 0}]
                            <!-- delivery VAT (if available) -->
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGNET" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getDelCostNet() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TAX1" }] [{ $basket->getDelCostVatPercent() }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TAX2" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getDelCostVat() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                        [{/if}]
                    [{/block}]

                    <tr valign="top">
                        <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGGROSS1" }] [{if $basket->getDelCostVat() > 0}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGGROSS2" }] [{/if}]:
                            </p>
                        </td>
                        <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                [{ $basket->getFDeliveryCosts() }] [{ $currency->sign}]
                            </p>
                        </td>
                    </tr>

                    [{block name="email_html_order_cust_paymentcosts"}]
                        <!-- payment sum -->
                        [{ if $basket->getPaymentCosts() }]
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;[{ if $basket->getDelCostVat() }]border-bottom: 1px solid #ddd;[{/if}]">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{if $basket->getPaymentCosts() >= 0}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT1" }][{else}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT2" }][{/if}] [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEDISCOUNT3" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;[{ if $basket->getDelCostVat() }]border-bottom: 1px solid #ddd;[{/if}]" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getPayCostNet() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                            <!-- payment sum VAT (if available) -->
                            [{ if $basket->getDelCostVat() }]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEVAT1" }] [{ $basket->getPayCostVatPercent()}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTCHARGEVAT2" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $basket->getPayCostVat() }] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/if}]
                        [{/if}]
                    [{/block}]

                    [{block name="email_html_order_cust_ts"}]
                        [{ if $basket->getTsProtectionCosts() }]
                            <!-- Trusted Shops -->
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;[{ if $basket->getTsProtectionVat() }]border-bottom: 1px solid #ddd;[{/if}]">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TSPROTECTION" }]
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;[{ if $basket->getTsProtectionVat() }]border-bottom: 1px solid #ddd;[{/if}]" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getTsProtectionNet() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                            [{ if $basket->getTsProtectionVat() }]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TSPROTECTIONCHARGETAX1" }] [{ $basket->getTsProtectionVatPercent()}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TSPROTECTIONCHARGETAX2" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $basket->getTsProtectionVat() }]&nbsp;[{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/if}]
                        [{/if}]
                    [{/block}]

                    [{ if $oViewConf->getShowGiftWrapping() && $basket->getFWrappingCosts() }]
                        [{block name="email_html_order_cust_wrappingcosts"}]
                            <!-- Gift wrapping -->
                            [{if $basket->getWrappCostVat()}]
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGNET" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $basket->getWrappCostNet() }] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX21" }] [{ $basket->getWrappCostVatPercent() }][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PLUSTAX22" }]
                                        </p>
                                    </td>
                                    <td style="padding: 5px; border-bottom: 1px solid #ddd;" align="right">
                                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                            [{ $basket->getWrappCostVat() }] [{ $currency->sign}]
                                        </p>
                                    </td>
                                </tr>
                            [{/if}]
                        [{/block}]
                        [{block name="email_html_order_cust_giftwrapping"}]
                            <tr valign="top">
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGANDGREETINGCARD1" }][{if $basket->getWrappCostVat()}] [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_WRAPPINGANDGREETINGCARD2" }][{/if}] :
                                    </p>
                                </td>
                                <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                        [{ $basket->getFWrappingCosts() }] [{ $currency->sign}]
                                    </p>
                                </td>
                            </tr>
                        [{/block}]
                    [{/if}]

                    [{block name="email_html_order_cust_grandtotal"}]
                        <!-- grand total price -->
                        <tr valign="top">
                            <td style="padding: 5px; border-bottom: 2px solid #ccc;">
                                <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                    <b>[{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_GRANDTOTAL" }]</b>
                                </p>
                            </td>
                            <td style="padding: 5px; border-bottom: 2px solid #ccc;" align="right">
                                <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                                    <b>[{ $basket->getFPrice() }] [{ $currency->sign}]</b>
                                </p>
                            </td>
                        </tr>
                    [{/block}]
                </table>
            </td>
        </tr>
    </table>

    [{block name="email_html_order_cust_userremark"}]
        [{ if $order->oxorder__oxremark->value }]
            <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_YOURMESSAGE" }]
            </h3>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                [{ $order->oxorder__oxremark->value|oxescape }]
            </p>
        [{/if}]
    [{/block}]

    [{block name="email_html_order_cust_paymentinfo"}]
        [{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}]
            <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PAYMENTMETHOD" }]
            </h3>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                <b>[{ $payment->oxpayments__oxdesc->value }] [{ if $basket->getPaymentCosts() }]([{ $basket->getFPaymentCosts() }] [{ $currency->sign}])[{/if}]</b>
                <br>
                [{ $payment->oxpayments__oxlongdesc->value }]
            </p>
        [{/if}]
    [{/block}]

    [{block name="email_html_order_cust_username"}]
        <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_EMAILADDRESS" }]
        </h3>
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
             [{ $user->oxuser__oxusername->value }]
        </p>
    [{/block}]

    [{block name="email_html_order_cust_address"}]
        <!-- Address info -->
        <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ADDRESS" }]
        </h3>
        <table colspan="0" rowspan="0" border="0">
            <tr valign="top">
                <td style="padding-right: 30xp">
                    <h4 style="font-weight: bold; margin: 0; padding: 0 0 15px; line-height: 20px; font-size: 11px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase;">
                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BILLINGADDRESS" }]
                    </h4>
                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                        [{ $order->oxorder__oxbillcompany->value }]<br>
                        [{ $order->oxorder__oxbillsal->value|oxmultilangsal}] [{ $order->oxorder__oxbillfname->value }] [{ $order->oxorder__oxbilllname->value }]<br>
                        [{if $order->oxorder__oxbilladdinfo->value }][{ $order->oxorder__oxbilladdinfo->value }]<br>[{/if}]
                        [{ $order->oxorder__oxbillstreet->value }] [{ $order->oxorder__oxbillstreetnr->value }]<br>
                        [{ $order->oxorder__oxbillstateid->value }]
                        [{ $order->oxorder__oxbillzip->value }] [{ $order->oxorder__oxbillcity->value }]<br>
                        [{ $order->oxorder__oxbillcountry->value }]<br>
                        [{if $order->oxorder__oxbillustid->value}][{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_VATIDNOMBER" }] [{ $order->oxorder__oxbillustid->value }]<br>[{/if}]
                        [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_PHONE" }] [{ $order->oxorder__oxbillfon->value }]<br><br>
                    </p>
                </td>
                [{ if $order->oxorder__oxdellname->value }]
                    <td>
                        <h4 style="font-weight: bold; margin: 0; padding: 0 0 15px; line-height: 20px; font-size: 11px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase;">
                            [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGADDRESS" }]
                        </h4>
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;">
                            [{ $order->oxorder__oxdelcompany->value }]<br>
                            [{ $order->oxorder__oxdelsal->value|oxmultilangsal }] [{ $order->oxorder__oxdelfname->value }] [{ $order->oxorder__oxdellname->value }]<br>
                            [{if $order->oxorder__oxdeladdinfo->value }][{ $order->oxorder__oxdeladdinfo->value }]<br>[{/if}]
                            [{ $order->oxorder__oxdelstreet->value }] [{ $order->oxorder__oxdelstreetnr->value }]<br>
                            [{ $order->oxorder__oxdelstateid->value }]
                            [{ $order->oxorder__oxdelzip->value }] [{ $order->oxorder__oxdelcity->value }]<br>
                            [{ $order->oxorder__oxdelcountry->value }]
                        </p>
                    </td>
                [{/if}]
            </tr>
        </table>
    [{/block}]

    [{block name="email_html_order_cust_deliveryinfo"}]
        [{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}]
            <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }]
            </h3>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                <b>[{ $order->oDelSet->oxdeliveryset__oxtitle->value }]</b>
            </p>
        [{/if}]
    [{/block}]

    [{block name="email_html_order_cust_paymentinfo"}]
        [{if $payment->oxuserpayments__oxpaymentsid->value == "oxidpayadvance"}]
            <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_SHIPPINGCARRIER" }]
            </h3>
            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BANK" }] [{$shop->oxshops__oxbankname->value}]<br>
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ROUTINGNOMBER" }] [{$shop->oxshops__oxbankcode->value}]<br>
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_ACCOUNTNOMBER" }] [{$shop->oxshops__oxbanknumber->value}]<br>
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_BIC" }] [{$shop->oxshops__oxbiccode->value}]<br>
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_IBAN" }] [{$shop->oxshops__oxibannumber->value}]
            </p>
        [{/if}]
    [{/block}]

    [{block name="email_html_order_cust_orderemailend"}]
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; padding-top: 15px;">
            [{ oxcontent ident="oxuserorderemailend" }]
        </p>
    [{/block}]

    [{block name="email_html_order_cust_tsinfo"}]
        [{if $oViewConf->showTs("ORDEREMAIL") && $oViewConf->getTsId() }]
            <h3 style="font-weight: bold; margin: 20px 0 7px; padding: 0; line-height: 35px; font-size: 12px;font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; border-bottom: 4px solid #ddd;">
                [{ oxmultilang ident="EMAIL_ORDER_CUST_HTML_TS_RATINGS_RATEUS" }]
            </h3>

            <a href="[{ $oViewConf->getTsRatingUrl() }]" target="_blank" title="[{ oxmultilang ident="TS_RATINGS_URL_TITLE" }]">
                <img src="https://www.trustedshops.com/bewertung/widget/img/bewerten_de.gif" border="0" alt="[{ oxmultilang ident="TS_RATINGS_BUTTON_ALT" }]" align="middle">
            </a>
        [{/if}]
    [{/block}]

[{include file="email/html/footer.tpl"}]