[{oxscript add="$('a.js-external').attr('target', '_blank');"}]
[{assign var="_oRecommendationList" value=$oView->getSimilarRecommLists()}]

[{ if $_oRecommendationList || $oView->getRecommSearch() }]
<div class="box" id="recommendationsBox">
    <h3>[{ oxmultilang ident="WIDGET_RECOMMENDATIONLIST_HEADER" }]
    [{assign var='rsslinks' value=$oView->getRssLinks() }]
    [{if $rsslinks.recommlists}]
        <a class="rss js-external" id="rssRecommLists" href="[{$rsslinks.recommlists.link}]" title="[{$rsslinks.recommlists.title}]">
            <img src="[{$oViewConf->getImageUrl('rss.png')}]" alt="[{$rsslinks.recommlists.title}]"><span class="FXgradOrange corners glowShadow">[{$rsslinks.recommlists.title}]</span>
        </a>
    [{/if}]
    </h3>

    <div>
    [{ if $_oRecommendationList }]
        [{$_oRecommendationList->rewind()}]

        [{if $_oRecommendationList->current()}]
               [{assign var="_oFirstRecommendationList" value=$_oRecommendationList->current()}]
            [{assign var="_oBoxTopProduct" value=$_oFirstRecommendationList->getFirstArticle()}]
            [{assign var="_sTitle" value="`$_oBoxTopProduct->oxarticles__oxtitle->value` `$_oBoxTopProduct->oxarticles__oxvarselect->value`"|strip_tags}]
            <a href="[{$_oBoxTopProduct->getMainLink()}]" class="featured" title="[{$_sTitle}]">
                <img src="[{$_oBoxTopProduct->getIconUrl()}]" alt="[{$_sTitle}]">
            </a>
        [{/if}]
    [{/if}]
        <ul class="featuredList">
        [{ if $_oRecommendationList }]
            [{foreach from=$_oRecommendationList item=_oListItem name="testRecommendationsList"}]
                <li>
                    <a href="[{ $_oListItem->getLink() }]"><b>[{ $_oListItem->oxrecommlists__oxtitle->value|strip_tags }]</b></a>
                    <div class="desc">[{ oxmultilang ident="WIDGET_RECOMMENDATIONLIST_LISTBY" }]: [{ $_oListItem->oxrecommlists__oxauthor->value|strip_tags }]</div>
                </li>
            [{/foreach}]
        [{/if}]
            [{ if $_oRecommendationList || $oView->getRecommSearch() }]
            <li>
                <form name="basket" class="recommendationsSearchForm" action="[{ $oViewConf->getSelfActionLink() }]" method="post">
                    <div>
                        <input type="hidden" name="cl" value="recommlist">
                        [{ $oViewConf->getHiddenSid() }]
                    </div>
                    <label>[{ oxmultilang ident="WIDGET_RECOMMENDATIONLIST_SEARCHFORLISTS" }]</label>
                    <input type="text" name="searchrecomm" id="searchRecomm" value="[{$oView->getRecommSearch()}]" class="searchInput">
                    <button class="submitButton largeButton" type="submit">[{ oxmultilang ident="WIDGET_RECOMMENDATIONLIST_SEARCHBUTTON" }]</button>
                </form>
            </li>
            [{/if}]
        </ul>
    </div>
</div>
[{/if}]