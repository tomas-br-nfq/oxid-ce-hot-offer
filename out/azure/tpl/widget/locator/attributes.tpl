[{if $attributes }]
    <form method="post" action="[{ $oViewConf->getSelfActionLink() }]" name="_filterlist" id="filterList">
    <div class="listFilter js-fnSubmit clear">
        [{ $oViewConf->getHiddenSid() }]
        [{ $oViewConf->getNavFormParams() }]
        <input type="hidden" name="cl" value="[{ $oViewConf->getActiveClassName() }]">
        <input type="hidden" name="tpl" value="[{$oViewConf->getActTplName()}]">
        <input type="hidden" name="fnc" value="executefilter">
        <input type="hidden" name="fname" value="">
        [{oxscript include="js/widgets/oxdropdown.js" priority=10 }]
        [{oxscript add="$('div.dropDown p').oxDropDown();"}]
        [{foreach from=$attributes item=oFilterAttr key=sAttrID name=attr}]
            <div class="dropDown js-fnSubmit" id="attributeFilter[[{ $sAttrID }]]">
                <p>
                    <label>[{ $oFilterAttr->getTitle() }]: </label>
                    <span>
                        [{if $oFilterAttr->getActiveValue() }]
                            [{ $oFilterAttr->getActiveValue() }]
                        [{else}]
                            [{ oxmultilang ident="WIDGET_PRODUCT_ATTRIBUTES_PLEASECHOOSE" }]
                        [{/if}]
                    </span>
                </p>
                <input type="hidden" name="attrfilter[[{ $sAttrID }]]" value="[{$oFilterAttr->getActiveValue()}]">
                <ul class="drop FXgradGreyLight shadow">
                    [{if $oFilterAttr->getActiveValue() }]
                        <li><a rel="" href="#">[{ oxmultilang ident="WIDGET_PRODUCT_ATTRIBUTES_PLEASECHOOSE" }]</a></li>
                    [{/if}]
                    [{foreach from=$oFilterAttr->getValues() item=sValue}]
                        <li><a rel="[{ $sValue }]" href="#" [{if $oFilterAttr->getActiveValue() == $sValue }]class="selected"[{/if}] >[{ $sValue }]</a></li>
                    [{/foreach}]
                </ul>
            </div>
        [{/foreach}]
    </div>
    </form>
[{/if}]