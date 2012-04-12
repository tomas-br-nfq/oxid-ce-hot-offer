[{oxscript include="js/widgets/oxinputvalidator.js" priority=10 }]
[{oxscript add="$('form.js-oxValidate').oxInputValidator();"}]
<form class="js-oxValidate" name="login" action="[{ $oViewConf->getSslSelfLink() }]" method="post">
[{assign var="aErrors" value=$oView->getFieldValidationErrors()}]
    <ul class="form">
        <li>
            [{ $oViewConf->getHiddenSid() }]
            [{ $oViewConf->getNavFormParams() }]
            <input type="hidden" name="fnc" value="login_noredirect">
            <input type="hidden" name="cl" value="[{ $oViewConf->getActiveClassName() }]">
            <label class="short">[{ oxmultilang ident="WIDGET_LOGINBOX_EMAIL_ADDRESS" }]</label>
            <input type="text" name="lgn_usr" class="textbox js-oxValidate js-oxValidate_notEmpty" field="pair-xsmall">
            <p class="oxValidateError" style="padding: 5px 0 5px 90px;">
                <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            </p>
        </li>
        <li>
            <label class="short">[{ oxmultilang ident="WIDGET_LOGINBOX_PASSWORD" }]</label>
            <input type="password" name="lgn_pwd" class="textbox" field="pair-xsmall">
        </li>
        <li><button type="submit" class="submitButton">[{ oxmultilang ident="WIDGET_LOGINBOX_LOGIN" }]</button></li>
    </ul>
</form>