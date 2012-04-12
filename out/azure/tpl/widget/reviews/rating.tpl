[{oxscript include="js/widgets/oxrating.js" priority=10 }]
[{oxscript add="$( '#itemRating' ).oxRating();"}]

<ul id="itemRating" class="rating" itemprop="aggregateRating" itemscope itemprop="http://schema.org/AggregateRating">
    [{math equation="x*y" x=20 y=$oView->getRatingValue() assign="iRatingAverage"}]

    [{if !$oxcmp_user}]
        [{assign var="_star_title" value="DETAILS_LOGIN"|oxmultilangassign}]
    [{elseif !$oView->canRate()}]
        [{assign var="_star_title" value="DETAILS_ALREADYRATED"|oxmultilangassign}]
    [{else}]
        [{assign var="_star_title" value="DETAILS_RATETHISARTICLE"|oxmultilangassign}]
    [{/if}]

    <li class="currentRate" style="width: [{$iRatingAverage}]%;">
        <a title="[{$_star_title}]"></a>
        <meta itemprop="ratingValue" content="[{$oView->getRatingValue()}]">
        <span title="[{$iRatingAverage}]"></span>
    </li>
    [{section name=star start=1 loop=6}]
        <li class="s[{$smarty.section.star.index}]">
            <a  class="[{if $oView->canRate()}]ox-write-review[{/if}] ox-rateindex-[{$smarty.section.star.index}]" rel="nofollow"
                [{if !$oxcmp_user}]
                    href="[{ oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=account" params="anid=`$oDetailsProduct->oxarticles__oxnid->value`"|cat:"&amp;sourcecl="|cat:$oViewConf->getActiveClassName()|cat:$oViewConf->getNavUrlParams() }]"
                [{elseif $oView->canRate()}]
                    href="#review"
                [{/if}]
                title="[{$_star_title}]">
            </a>
         </li>
    [{/section}]
    <li class="ratingValue">
        <a id="itemRatingText" class="rates" rel="nofollow" rel="nofollow" [{if $sRateUrl}]href="[{if !$oxcmp_user}][{oxgetseourl ident=$sRateUrl params=$sRateUrlParams}][{else}][{$sRateUrl}][{/if}]#review"[{/if}]>
            [{if $oView->getRatingCount()}]
                (<span itemprop="ratingCount">[{$oView->getRatingCount()}]</span>)
            [{else}]
                [{oxmultilang ident="DETAILS_NORATINGS"}]
            [{/if}]
        </a>
    </li>
</ul>


