[{assign var="invadr" value=$oView->getInvoiceAddress()}]
    <li>
        <label [{if $oView->isFieldRequired(oxuser__oxsal)}]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_TITLE" }]</label>
        [{include file="form/fieldset/salutation.tpl" name="invadr[oxuser__oxsal]" value=$oxcmp_user->oxuser__oxsal->value }]
    </li>
    <li [{if $aErrors.oxuser__oxfname}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxfname) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_FIRSTNAME" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxfname) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="255" name="invadr[oxuser__oxfname]" value="[{if isset( $invadr.oxuser__oxfname ) }][{ $invadr.oxuser__oxfname }][{else }][{ $oxcmp_user->oxuser__oxfname->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxfname)}]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxfname}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxlname}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxlname) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_LASTNAME" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxlname) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="255" name="invadr[oxuser__oxlname]" value="[{if isset( $invadr.oxuser__oxlname ) }][{ $invadr.oxuser__oxlname }][{else }][{ $oxcmp_user->oxuser__oxlname->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxlname)}]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxlname}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxcompany}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxcompany) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_COMPANY" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxcompany) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="255" name="invadr[oxuser__oxcompany]" value="[{if isset( $invadr.oxuser__oxcompany ) }][{ $invadr.oxuser__oxcompany }][{else }][{ $oxcmp_user->oxuser__oxcompany->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxcompany) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxcompany}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxstreet}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxstreet) || $oView->isFieldRequired(oxuser__oxstreetnr) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_STREETANDSTREETNO" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxstreet) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" field="pair-xsmall" maxlength="255" name="invadr[oxuser__oxstreet]" value="[{if isset( $invadr.oxuser__oxstreet ) }][{ $invadr.oxuser__oxstreet }][{else }][{ $oxcmp_user->oxuser__oxstreet->value }][{/if}]">
          <input [{if $oView->isFieldRequired(oxuser__oxstreetnr) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" field="xsmall" maxlength="16" name="invadr[oxuser__oxstreetnr]" value="[{if isset( $invadr.oxuser__oxstreetnr ) }][{ $invadr.oxuser__oxstreetnr }][{else }][{ $oxcmp_user->oxuser__oxstreetnr->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxstreet) || $oView->isFieldRequired(oxuser__oxstreetnr) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxstreet}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxzip}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxzip) || $oView->isFieldRequired(oxuser__oxcity) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_POSTALCODEANDCITY" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxzip) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" field="small" maxlength="16" name="invadr[oxuser__oxzip]" value="[{if isset( $invadr.oxuser__oxzip ) }][{ $invadr.oxuser__oxzip }][{else }][{ $oxcmp_user->oxuser__oxzip->value }][{/if}]">
          <input [{if $oView->isFieldRequired(oxuser__oxcity) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" field="pair-small" maxlength="255" name="invadr[oxuser__oxcity]" value="[{if isset( $invadr.oxuser__oxcity ) }][{ $invadr.oxuser__oxcity }][{else }][{ $oxcmp_user->oxuser__oxcity->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxzip) || $oView->isFieldRequired(oxuser__oxcity) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxzip}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxustid}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxustid) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_VATIDNO" }]</label>
         <input [{if $oView->isFieldRequired(oxuser__oxustid) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="255" name="invadr[oxuser__oxustid]" value="[{if isset( $invadr.oxuser__oxustid ) }][{ $invadr.oxuser__oxustid }][{else}][{ $oxcmp_user->oxuser__oxustid->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxustid) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxustid}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxaddinfo}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxaddinfo) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_ADDITIONALINFO" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxaddinfo) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="255" name="invadr[oxuser__oxaddinfo]" value="[{if isset( $invadr.oxuser__oxaddinfo ) }][{ $invadr.oxuser__oxaddinfo }][{else }][{ $oxcmp_user->oxuser__oxaddinfo->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxaddinfo) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxaddinfo}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxcountryid}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxcountryid) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_COUNTRY" }]</label>
          <select [{if $oView->isFieldRequired(oxuser__oxcountryid) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}] id="invCountrySelect" name="invadr[oxuser__oxcountryid]">
               <option value="">-</option>
            [{foreach from=$oViewConf->getCountryList() item=country key=country_id }]
                <option value="[{ $country->oxcountry__oxid->value }]" [{if isset( $invadr.oxuser__oxcountryid ) && $invadr.oxuser__oxcountryid == $country->oxcountry__oxid->value}] selected[{elseif $oxcmp_user->oxuser__oxcountryid->value == $country->oxcountry__oxid->value}] selected[{/if}]>[{ $country->oxcountry__oxtitle->value }]</option>
            [{/foreach }]
          </select>
          [{if $oView->isFieldRequired(oxuser__oxcountryid) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxcountryid}]
        </p>
          [{/if}]
    </li>
    <li class="stateBox">
          [{include file="form/fieldset/state.tpl"
                countrySelectId="invCountrySelect"
                stateSelectName="invadr[oxuser__oxstateid]"
                selectedStateIdPrim=$invadr.oxuser__oxstateid
                selectedStateId=$oxcmp_user->oxuser__oxstateid->value
         }]
    </li>
    <li [{if $aErrors.oxuser__oxfon}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxfon) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_PHONE" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxfon) }]class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="128" name="invadr[oxuser__oxfon]" value="[{if isset( $invadr.oxuser__oxfon ) }][{ $invadr.oxuser__oxfon }][{else }][{ $oxcmp_user->oxuser__oxfon->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxfon) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxfon}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxfax}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxfax) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_FAX" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxfax) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}]type="text" size="37" maxlength="128" name="invadr[oxuser__oxfax]" value="[{if isset( $invadr.oxuser__oxfax ) }][{ $invadr.oxuser__oxfax }][{else }][{ $oxcmp_user->oxuser__oxfax->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxfax) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxfax}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxmobfon}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxmobfon) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_CELLUARPHONE" }]</label>
         <input [{if $oView->isFieldRequired(oxuser__oxmobfon) }] class="js-oxValidate js-oxValidate_notEmpty"[{/if}]type="text" size="37" maxlength="64" name="invadr[oxuser__oxmobfon]" value="[{if isset( $invadr.oxuser__oxmobfon ) }][{$invadr.oxuser__oxmobfon }][{else}][{$oxcmp_user->oxuser__oxmobfon->value }][{/if}]">
          [{if $oView->isFieldRequired(oxuser__oxmobfon) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxmobfon}]
        </p>
          [{/if}]
    </li>
    <li [{if $aErrors.oxuser__oxprivfon}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxprivfon) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_EVENINGPHONE" }]</label>
        <input [{if $oView->isFieldRequired(oxuser__oxprivfon) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}] type="text" size="37" maxlength="64" name="invadr[oxuser__oxprivfon]" value="[{if isset( $invadr.oxuser__oxprivfon ) }][{$invadr.oxuser__oxprivfon }][{else}][{$oxcmp_user->oxuser__oxprivfon->value }][{/if}]">
        [{if $oView->isFieldRequired(oxuser__oxprivfon) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxprivfon}]
        </p>
        [{/if}]
    </li>
    [{if $oViewConf->showBirthdayFields() }]
    <li [{if $aErrors.oxuser__oxbirthdate}]class="oxInValid"[{/if}]>
        <label [{if $oView->isFieldRequired(oxuser__oxbirthdate) }]class="req"[{/if}]>[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_BIRTHDATE" }]</label>
          <input [{if $oView->isFieldRequired(oxuser__oxbirthdate) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}] type="text" field="small" maxlength="2" name="invadr[oxuser__oxbirthdate][day]" value="[{if isset( $invadr.oxuser__oxbirthdate.day ) }][{$invadr.oxuser__oxbirthdate.day }][{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}][{$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]([0-9]{1,2})[-]/":"" }][{/if}]">
          <input [{if $oView->isFieldRequired(oxuser__oxbirthdate) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}] type="text" field="small" maxlength="2" name="invadr[oxuser__oxbirthdate][month]" value="[{if isset( $invadr.oxuser__oxbirthdate.month ) }][{$invadr.oxuser__oxbirthdate.month }][{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00" }][{$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]/":""|regex_replace:"/[-]([0-9]{1,2})$/":"" }][{/if}]">
         <input [{if $oView->isFieldRequired(oxuser__oxbirthdate) }] class="js-oxValidate js-oxValidate_notEmpty" [{/if}] type="text" field="small" maxlength="4" name="invadr[oxuser__oxbirthdate][year]" value="[{if isset( $invadr.oxuser__oxbirthdate.year ) }][{$invadr.oxuser__oxbirthdate.year }][{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00" }][{$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/[-]([0-9]{1,2})[-]([0-9]{1,2})$/":"" }][{/if}]">

          [{if $oView->isFieldRequired(oxuser__oxbirthdate) }]
        <p class="oxValidateError">
            <span class="js-oxError_notEmpty">[{ oxmultilang ident="EXCEPTION_INPUT_NOTALLFIELDS" }]</span>
            [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxbirthdate}]
        </p>
          [{/if}]
    </li>
    [{/if}]

    <li class="formNote">[{ oxmultilang ident="FORM_USER_COMPLETEMARKEDFIELDS" }]</li>
    [{if !$noFormSubmit}]
    <li class="formSubmit">
        <button id="accUserSaveTop" type="submit" name="save" class="submitButton" title="[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_SAVE" }]">[{ oxmultilang ident="FORM_FIELDSET_USER_BILLING_SAVE" }]</button>
    </li>
    [{/if}]