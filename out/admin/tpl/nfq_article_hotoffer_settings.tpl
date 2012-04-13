[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{ $oxid }]" />
    <input type="hidden" name="cl" value="nfq_article_hotoffer" />
</form>

<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post" onClick="Javascript:document.myedit.fnc.value='save'">
    <input type="hidden" name="cl" value="nfq_article_hotoffer" />
    <input type="hidden" name="oxid" value="[{ $oxid }]" />
    <input type="hidden" name="fnc" value="" />
    <table cellspacing="0" cellpadding="0" border="0" style="width:98%;">
        <tr>
            <td class="edittext" width="50">
                [{ oxmultilang ident="ARTICLE_MAIN_ACTIVE" }]
            </td>
            <td class="edittext">
                <input class="edittext" type="checkbox" name="editval[oxarticles__nfqhotofferactive]" value='1' [{if $edit->oxarticles__nfqhotofferactive->value == 1}]checked[{/if}] />
                [{ oxinputhelp ident="HELP_ARTICLE_MAIN_ACTIVE" }]
            </td>
        </tr>
        <tr>
            <td colspan="2" class="edittext">
                <input type="submit" class="edittext" name="save" value="[{ oxmultilang ident="GENERAL_SAVE" }]" />
            </td>
        </tr>
    </table>
</form>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
