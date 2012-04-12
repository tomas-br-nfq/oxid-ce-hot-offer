[{oxscript include="js/widgets/oxloginbox.js" priority=10 }]
[{oxscript add="$( '#loginBoxOpener' ).oxLoginBox();"}]
[{assign var="bIsError" value=0 }]
[{capture name=loginErrors}]
    [{foreach from=$Errors.loginBoxErrors item=oEr key=key }]
        <p id="errorBadLogin" class="errorMsg">[{ $oEr->getOxMessage()}]</p>
        [{assign var="bIsError" value=1 }]
    [{/foreach}]
[{/capture}]
[{if !$oxcmp_user->oxuser__oxpassword->value}]
    [{oxscript include="js/widgets/oxmodalpopup.js" priority=10 }]
    [{oxscript add="$( '#forgotPasswordOpener' ).oxModalPopup({ target: '#forgotPassword'});"}]
    <div id="forgotPassword" class="popupBox corners FXgradGreyLight glowShadow">
        <img src="[{$oViewConf->getImageUrl('x.png')}]" alt="" class="closePop">
        [{include file="form/forgotpwd_email.tpl" idPrefix="Popup"}]
    </div>
    <a href="#" id="loginBoxOpener" title="[{ oxmultilang ident="WIDGET_LOGINBOX_LOGIN" }]">[{ oxmultilang ident="WIDGET_LOGINBOX_LOGIN" }]</a>
    <form id="login" name="login" action="[{ $oViewConf->getSslSelfLink() }]" method="post">
        <div id="loginBox" class="loginBox" [{if $bIsError}]style="display: block;"[{/if}]>
            [{ $oViewConf->getHiddenSid() }]
            [{ $oViewConf->getNavFormParams() }]
            <input type="hidden" name="fnc" value="login_noredirect">
            <input type="hidden" name="cl" value="[{ $oViewConf->getActiveClassName() }]">
            <input type="hidden" name="pgNr" value="[{$oView->getActPage()}]">
            <input type="hidden" name="CustomError" value="loginBoxErrors">
            [{if $oView->getProduct()}]
                [{assign var="product" value=$oView->getProduct() }]
                <input type="hidden" name="anid" value="[{ $product->oxarticles__oxnid->value }]">
            [{/if}]
            <div class="loginForm corners">
                <h4>[{ oxmultilang ident="WIDGET_LOGINBOX_LOGIN" }]</h4>
                <p>
                    [{oxscript include="js/widgets/oxinnerlabel.js" priority=10 }]
                    [{oxscript add="$( '#loginEmail' ).oxInnerLabel();"}]
                    <label for="loginEmail" class="innerLabel">[{ oxmultilang ident="WIDGET_LOGINBOX_EMAIL_ADDRESS" }]</label>
                    <input id="loginEmail" type="text" name="lgn_usr" value="" class="textbox">
                </p>
                <p>
                    [{oxscript include="js/widgets/oxinnerlabel.js" priority=10 }]
                    [{oxscript add="$( '#loginPasword' ).oxInnerLabel();"}]
                    <label for="loginPasword" class="innerLabel">[{ oxmultilang ident="WIDGET_LOGINBOX_PASSWORD" }]</label>
                    <input id="loginPasword" type="password" name="lgn_pwd" class="textbox passwordbox" value=""><strong><a id="forgotPasswordOpener" href="#" title="[{ oxmultilang ident="WIDGET_LOGINBOX_FORGOT_PASSWORD" }]">?</a></strong>
                </p>
                [{$smarty.capture.loginErrors}]
                [{if $oView->showRememberMe()}]
                <p class="checkFields clear">
                    <input type="checkbox" class="checkbox" value="1" name="lgn_cook" id="remember"><label for="remember">[{ oxmultilang ident="WIDGET_LOGINBOX_REMEMBER_ME" }]</label>
                </p>
                [{/if}]
                <p>
                    <button type="submit" class="submitButton">[{ oxmultilang ident="WIDGET_LOGINBOX_LOGIN" }]</button>
                </p>
            </div>
            [{if $oViewConf->getShowFbConnect()}]
                <div class="altLoginBox corners clear">
                    <span>[{ oxmultilang ident="WIDGET_LOGINBOX_WITH" }]</span>
                    <fb:login-button size="medium" autologoutlink="true" length="short"></fb:login-button>
                </div>
            [{/if}]
        </div>
    </form>
[{else}]
    [{ oxmultilang ident="WIDGET_LOGINBOX_GREETING" }]
    [{assign var="fullname" value=$oxcmp_user->oxuser__oxfname->value|cat:" "|cat:$oxcmp_user->oxuser__oxlname->value }]
    <a href="[{ oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=account"}]">
    [{if $fullname}]
        [{ $fullname }]
    [{else}]
        [{ $oxcmp_user->oxuser__oxusername->value|oxtruncate:25:"...":true }]
    [{/if}]
    </a>
    <a id="logoutLink" class="logoutLink" href="[{ $oViewConf->getLogoutLink() }]" title="[{ oxmultilang ident="WIDGET_LOGINBOX_LOGOUT" }]">[{ oxmultilang ident="WIDGET_LOGINBOX_LOGOUT" }]</a>
[{/if}]
