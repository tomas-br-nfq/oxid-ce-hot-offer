[{oxscript include="js/widgets/oxinputvalidator.js" priority=10 }]
[{oxscript add="$('form.js-oxValidate').oxInputValidator();"}]
[{block name="user_checkout_noregistration"}]
    <form class="js-oxValidate" action="[{ $oViewConf->getSslSelfLink() }]" name="order" method="post">
    [{block name="user_checkout_noregistration_form"}]
        [{assign var="aErrors" value=$oView->getFieldValidationErrors()}]
        [{ $oViewConf->getHiddenSid() }]
        [{ $oViewConf->getNavFormParams() }]
        <input type="hidden" name="cl" value="user">
        <input type="hidden" name="option" value="1">
        [{if !$oxcmp_user->oxuser__oxpassword->value }]
        <input type="hidden" name="fnc" value="createuser">
        [{else}]
        <input type="hidden" name="fnc" value="changeuser">
        <input type="hidden" name="lgn_cook" value="0">
        [{/if}]
        <input type="hidden" id="reloadAddress" name="reloadaddress" value="">
        <input type="hidden" name="blshowshipaddress" value="1">

        <div class="lineBox clear">
            <a href="[{ oxgetseourl ident=$oViewConf->getBasketLink() }]" class="prevStep submitButton largeButton" id="userBackStepTop">[{ oxmultilang ident="FORM_USER_CHECKOUT_NOREGISTRATION_BACKSTEP" }]</a>
            <button id="userNextStepTop" class="submitButton largeButton nextStep" name="userform" type="submit">[{ oxmultilang ident="FORM_USER_CHECKOUT_NOREGISTRATION_NEXTSTEP" }]</button>
        </div>

        <div class="checkoutCollumns clear">
            <div class="row">
                <h3 class="blockHead">[{ oxmultilang ident="FORM_REGISTER_ACCOUNTINFO" }]</h3>
                <ul class="form">
                    [{ include file="form/fieldset/user_noaccount.tpl" }]
                </ul>
            </div>
            <div class="collumn">
                <h3 class="blockHead">[{ oxmultilang ident="FORM_REGISTER_BILLINGADDRESS" }]</h3>
                <ul class="form">
                [{ include file="form/fieldset/user_billing.tpl" noFormSubmit=true blOrderRemark=true}]
                </ul>
            </div>
            <div class="collumn">
                <h3 class="blockHead">[{ oxmultilang ident="FORM_REGISTER_SHIPPINGADDRESS" }]</h3>
                <p><input type="checkbox" name="blshowshipaddress" id="showShipAddress" [{if !$oView->showShipAddress()}]checked[{/if}] value="0"><label for="showShipAddress">[{ oxmultilang ident="FORM_REGISTER_USE_BILLINGADDRESS_FOR_SHIPPINGADDRESS" }]</label></p>
                <ul id="shippingAddress" class="form" [{if !$oView->showShipAddress()}]style="display: none;"[{/if}]>
                [{ include file="form/fieldset/user_shipping.tpl" noFormSubmit=true}]
                </ul>
                <ul class="form">
                    <li>
                        [{include file="form/fieldset/order_remark.tpl" blOrderRemark=true}]
                    </li>
                </ul>
            </div>
        </div>

        [{oxscript add="$('#showShipAddress').change( function() { $('#shippingAddress').toggle($(this).is(':not(:checked)'));});"}]

        <div class="lineBox clear">
            <a href="[{ oxgetseourl ident=$oViewConf->getBasketLink() }]" class="prevStep submitButton largeButton" id="userBackStepBottom">[{ oxmultilang ident="FORM_USER_CHECKOUT_NOREGISTRATION_BACKSTEP" }]</a>
            <button id="userNextStepBottom" class="submitButton largeButton nextStep" name="userform" type="submit">[{ oxmultilang ident="FORM_USER_CHECKOUT_NOREGISTRATION_NEXTSTEP" }]</button>
        </div>
    [{/block}]
    </form>
[{/block}]