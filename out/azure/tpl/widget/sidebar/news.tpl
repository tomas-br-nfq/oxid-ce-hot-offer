<div id="newsBox" class="box">
    <h3>[{ oxmultilang ident="WIDGET_NEWS_HEADER" }]</h3>
    <ul class="content">
        [{foreach from=$oNews item=_oNewsItem name=_sNewsList }]
            <li >
                [{oxeval var=$_oNewsItem->oxnews__oxlongdesc assign='_sNewsItem' force=1}]
                [{ $_sNewsItem|strip_tags|oxtruncate:100 }]<br>
                <a href="[{ oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=news" }]#[{$_oNewsItem->oxnews__oxid->value}]" class="readMore">[{ oxmultilang ident="WIDGET_NEWS_LINKMORE" }]</a>
            </li>
        [{/foreach}]
    </ul>
</div>