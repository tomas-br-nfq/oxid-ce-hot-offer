[{oxscript include="js/widgets/oxagbcheck.js" priority=10 }]
[{oxscript add="$('#checkAgbTop, #checkAgbBottom').oxAGBCheck();"}]

[{capture append="oxidBlock_content"}]

    [{block name="checkout_order_errors"}]
        [{ if $oView->isConfirmAGBActive() && $oView->isConfirmAGBError() == 1 }]
            [{include file="message/error.tpl" statusMessage="PAGE_CHECKOUT_ORDER_READANDCONFIRMTERMS"|oxmultilangassign }]
        [{/if}]
    [{/block}]

    [{* ordering steps *}]
    [{include file="page/checkout/inc/steps.tpl" active=4 }]

    [{block name="checkout_order_main"}]
        [{if !$oView->showOrderButtonOnTop()}]
            <div class="lineBox clear">
                <span>&nbsp;</span>
                <span class="title">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_TITLE2" }]</span>
            </div>
        [{/if}]
        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post">
            <h3 class="section">
                <strong>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_BASKET" }]</strong>
                [{ $oViewConf->getHiddenSid() }]
                <input type="hidden" name="cl" value="basket">
                <input type="hidden" name="fnc" value="">
                <button type="submit" class="submitButton largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MODIFY4" }]</button>
            </h3>
        </form>
        [{block name="checkout_order_details"}]
            [{ if !$oxcmp_basket->getProductsCount()  }]
                <div>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_BASKETEMPTY" }]</div>
            [{else}]
                [{assign var="currency" value=$oView->getActCurrency() }]

                [{if $oView->isLowOrderPrice()}]
                    [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MINORDERPRICE" }] [{ $oView->getMinOrderPrice() }] [{ $currency->sign }]
                [{elseif $oView->showOrderButtonOnTop()}]
                    <div class="lineBox clear">
                        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post" id="orderConfirmAgbTop">
                            [{ $oViewConf->getHiddenSid() }]
                            [{ $oViewConf->getNavFormParams() }]
                            <input type="hidden" name="cl" value="order">
                            <input type="hidden" name="fnc" value="[{$oView->getExecuteFnc()}]">
                            <input type="hidden" name="challenge" value="[{$challenge}]">
                            <div class="agbInner">
                            [{if $oView->isConfirmAGBActive()}]
                                <input type="hidden" name="ord_agb" value="0">
                                <input id="checkAgbTop" class="checkbox" type="checkbox" name="ord_agb" value="1">
                                [{oxifcontent ident="oxrighttocancellegend" object="oContent"}]
                                    [{ $oContent->oxcontents__oxcontent->value }]
                                [{/oxifcontent}]
                                 <p class="errorMsg" name="agbError">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_READANDCONFIRMTERMS" }]</p>
                            [{else}]
                                <input type="hidden" name="ord_agb" value="1">
                                [{oxifcontent ident="oxrighttocancellegend2" object="oContent"}]
                                    [{ $oContent->oxcontents__oxcontent->value }]
                                [{/oxifcontent}]
                            [{/if}]
                            </div>
                            <div >
                                <button type="submit" class="submitButton largeButton nextStep">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_SUBMITORDER" }]</button>
                            </div>
                        </form>
                    </div>
                [{/if}]

                [{block name="order_basket"}]
                    [{include file="page/checkout/inc/basketcontents.tpl" editable=false}]
                [{/block}]

                [{block name="checkout_order_vouchers"}]
                    [{ if $oViewConf->getShowVouchers() && $oxcmp_basket->getVouchers()}]
                        [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_USEDCOUPONS" }]
                        <div>
                            [{foreach from=$Errors.basket item=oEr key=key }]
                                [{if $oEr->getErrorClassType() == 'oxVoucherException'}]
                                    [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_COUPONNOTACCEPTED1" }] [{ $oEr->getValue('voucherNr') }] [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_COUPONNOTACCEPTED2" }]<br>
                                    [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_REASON" }]
                                    [{ $oEr->getOxMessage() }]<br>
                                [{/if}]
                            [{/foreach}]
                            [{foreach from=$oxcmp_basket->getVouchers() item=sVoucher key=key name=aVouchers}]
                                [{ $sVoucher->sVoucherNr }]<br>
                            [{/foreach }]
                        </div>
                    [{/if}]
                [{/block}]

                [{block name="checkout_order_address"}]
                    <div id="orderAddress">
                        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post">
                            <h3 class="section">
                            <strong>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_ADDRESSES" }]</strong>
                            [{ $oViewConf->getHiddenSid() }]
                            <input type="hidden" name="cl" value="user">
                            <input type="hidden" name="fnc" value="">
                            <button type="submit" class="submitButton largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MODIFYADDRESS" }]</button>
                            </h3>
                        </form>
                        <dl>
                            <dt>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_BILLINGADDRESS" }]</dt>
                            <dd>
                                [{include file="widget/address/billing_address.tpl"}]
                            </dd>
                            [{assign var="oDelAdress" value=$oView->getDelAddress() }]
                            [{if $oDelAdress }]
                                <dt>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_SHIPPINGADDRESS" }]</dt>
                                <dd>
                                    [{include file="widget/address/shipping_address.tpl" delivadr=$oDelAdress}]
                                </dd>
                            [{/if}]
                        </dl>

                        [{if $oView->getOrderRemark() }]
                            <div>
                                [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_WHATIWANTEDTOSAY" }] [{ $oView->getOrderRemark() }]
                            </div>
                        [{/if}]

                    </div>
                [{/block}]


                [{block name="shippingAndPayment"}]
                    <div id="orderShipping">
                    <form action="[{ $oViewConf->getSslSelfLink() }]" method="post">
                        <h3 class="section">
                            <strong>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_SHIPPINGCARRIER" }]</strong>
                            [{ $oViewConf->getHiddenSid() }]
                            <input type="hidden" name="cl" value="payment">
                            <input type="hidden" name="fnc" value="">
                            <button type="submit" class="submitButton largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MODIFY2" }]</button>
                        </h3>
                    </form>
                    [{assign var="oShipSet" value=$oView->getShipSet() }]
                    [{ $oShipSet->oxdeliveryset__oxtitle->value }]
                    </div>

                    <div id="orderPayment">
                        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post">
                            <h3 class="section">
                                <strong>[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_PAYMENTMETHOD" }]</strong>
                                [{ $oViewConf->getHiddenSid() }]
                                <input type="hidden" name="cl" value="payment">
                                <input type="hidden" name="fnc" value="">
                                <button type="submit" class="submitButton largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MODIFY3" }]</button>
                            </h3>
                        </form>
                        [{assign var="payment" value=$oView->getPayment() }]
                        [{ $payment->oxpayments__oxdesc->value }]
                    </div>
                [{/block}]

                [{if $oView->isLowOrderPrice() }]
                    [{ oxmultilang ident="PAGE_CHECKOUT_ORDER_MINORDERPRICE" }] [{ $oView->getMinOrderPrice() }] [{ $currency->sign }]
                [{else}]
                    [{block name="checkout_order_btn_confirm_bottom"}]
                        <form action="[{ $oViewConf->getSslSelfLink() }]" method="post" id="orderConfirmAgbBottom">
                            [{ $oViewConf->getHiddenSid() }]
                            [{ $oViewConf->getNavFormParams() }]
                            <input type="hidden" name="cl" value="order">
                            <input type="hidden" name="fnc" value="[{$oView->getExecuteFnc()}]">
                            <input type="hidden" name="challenge" value="[{$challenge}]">
                            <input type="hidden" name="ord_agb" value="1">
                                <div class="agb">
                                    [{if $oView->isActive('PsLogin') }]
                                        <input type="hidden" name="ord_agb" value="1">
                                    [{else}]
                                        [{if $oView->isConfirmAGBActive()}]
                                            [{oxifcontent ident="oxrighttocancellegend" object="oContent"}]
                                                <h3 class="section">
                                                    <strong>[{ $oContent->oxcontents__oxtitle->value }]</strong>
                                                </h3>
                                                <input type="hidden" name="ord_agb" value="0">
                                                <input id="checkAgbBottom" class="checkbox" type="checkbox" name="ord_agb" value="1">
                                                [{ $oContent->oxcontents__oxcontent->value }]
                                            [{/oxifcontent}]
                                            <p class="errorMsg" name="agbError">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_READANDCONFIRMTERMS" }]</p>
                                        [{else}]
                                            [{oxifcontent ident="oxrighttocancellegend2" object="oContent"}]
                                                <h3 class="section">
                                                    <strong>[{ $oContent->oxcontents__oxtitle->value }]</strong>
                                                </h3>
                                                <input type="hidden" name="ord_agb" value="1">
                                                [{ $oContent->oxcontents__oxcontent->value }]
                                            [{/oxifcontent}]
                                        [{/if}]

                                    [{/if}]
                                </div>
                            <div class="lineBox clear">
                                <a href="[{ oxgetseourl ident=$oViewConf->getPaymentLink() }]" class="prevStep submitButton largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_BACKSTEP" }]</a>
                                <button type="submit" class="submitButton nextStep largeButton">[{ oxmultilang ident="PAGE_CHECKOUT_ORDER_SUBMITORDER" }]</button>
                            </div>
                        </form>
                    [{/block}]
                [{/if}]
            [{/if}]
        [{/block}]
    [{/block}]
    [{insert name="oxid_tracker" title=$template_title }]
[{/capture}]

[{assign var="template_title" value="PAGE_CHECKOUT_ORDER_TITLE"|oxmultilangassign}]
[{include file="layout/page.tpl" title=$template_title location=$template_title}]