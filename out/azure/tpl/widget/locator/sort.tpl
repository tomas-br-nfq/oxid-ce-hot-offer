[{if $oView->showSorting()}]
    [{assign var="_listType" value=$oView->getListDisplayType()}]
    [{assign var="_additionalParams" value=$oView->getAdditionalParams()}]
    [{assign var="_artPerPage" value=$oViewConf->getArtPerPageCount()}]
    [{assign var="_sortColumnVarName" value=$oView->getSortOrderByParameterName()}]
    [{assign var="_sortDirectionVarName" value=$oView->getSortOrderParameterName()}]
    [{oxscript include="js/widgets/oxdropdown.js" priority=10 }]
    [{oxscript add="$('div.dropDown p').oxDropDown();"}]
    <div class="dropDown js-fnLink" id="sortItems">
        <p>
            <label>[{ oxmultilang ident="WIDGET_LOCATOR_SORT_SORTBY" }]</label>
            <span class="[{$oView->getListOrderDirection()}]">
                [{if $oView->getListOrderBy() }]
                    [{oxmultilang ident="WIDGET_LOCATOR_SORT_"|cat:$oView->getListOrderBy()|upper }]
                [{else}]
                    [{oxmultilang ident="WIDGET_LOCATOR_CHOOSE"}]
                [{/if}]
            </span>
        </p>
        <ul class="drop FXgradGreyLight shadow">
            [{foreach from=$oView->getSortColumns() item=sColumnName}]
                <li class="asc">
                    <a href="[{ $oView->getLink()|oxaddparams:"ldtype=$_listType&amp;_artperpage=$_artPerPage&amp;$_sortColumnVarName=$sColumnName&amp;$_sortDirectionVarName=asc&amp;pgNr=0&amp;$_additionalParams"}]" [{if $oView->getListOrderDirection() == 'asc' && $sColumnName == $oView->getListOrderBy()}] class="selected"[{/if}]><span>[{ oxmultilang ident="WIDGET_LOCATOR_SORT_"|cat:$sColumnName|upper }]</span></a>
                </li>
                <li class="desc">
                    <a href="[{ $oView->getLink()|oxaddparams:"ldtype=$_listType&amp;_artperpage=$_artPerPage&amp;$_sortColumnVarName=$sColumnName&amp;$_sortDirectionVarName=desc&amp;pgNr=0&amp;$_additionalParams"}]" [{if $oView->getListOrderDirection() == 'desc' && $sColumnName == $oView->getListOrderBy()}] class="selected"[{/if}]><span>[{ oxmultilang ident="WIDGET_LOCATOR_SORT_"|cat:$sColumnName|upper }]</span></a>
                </li>
            [{/foreach}]
        </ul>
    </div>
[{/if}]