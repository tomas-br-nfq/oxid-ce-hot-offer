[{block name="widget_product_listitem_grid"}]
    [{assign var="currency" value=$oView->getActCurrency()}]
    [{if $showMainLink}]
        [{assign var='_productLink' value=$product->getMainLink()}]
    [{else}]
        [{assign var='_productLink' value=$product->getLink()}]
    [{/if}]
    [{assign var="blShowToBasket" value=true}] [{* tobasket or more info ? *}]
    [{if $blDisableToCart || $product->isNotBuyable()||($aVariantSelections&&$aVariantSelections.selections)||$product->hasMdVariants()||($oViewConf->showSelectListsInList() && $product->getSelections(1))||$product->getVariants()}]
        [{assign var="blShowToBasket" value=false}]
    [{/if}]
    [{capture name=product_price}]
        [{block name="widget_product_listitem_grid_price"}]
            [{oxhasrights ident="SHOWARTICLEPRICE"}]
                [{if $product->getFTPrice() > $product->getFPrice()}]
                <span class="priceOld">
                    [{ oxmultilang ident="WIDGET_PRODUCT_PRODUCT_REDUCEDFROM" }] <del>[{ $product->getFTPrice()}] [{ $currency->sign}]</del>
                </span>
                [{/if}]
                [{block name="widget_product_listitem_grid_price_value"}]
                    [{if $product->getFPrice()}]
                        <strong><span itemprop="price">[{ $product->getFPrice() }]</span> [{ $currency->sign}] <meta itemprop="priceCurrency" content="[{ $currency->name}]">[{if !($product->hasMdVariants() || ($oViewConf->showSelectListsInList() && $product->getSelections(1)) || $product->getVariantList())}] *[{/if}]</strong>
                    [{/if}]
                [{/block}]
                [{if $product->getPricePerUnit()}]
                    <span id="productPricePerUnit_[{$testid}]" class="pricePerUnit">
                        [{$product->oxarticles__oxunitquantity->value}] [{$product->oxarticles__oxunitname->value}] | [{$product->getPricePerUnit()}] [{ $currency->sign}]/[{$product->oxarticles__oxunitname->value}]
                    </span>
                [{elseif $product->oxarticles__oxweight->value  }]
                    <span id="productPricePerUnit_[{$testid}]" class="pricePerUnit">
                        <span title="weight">[{ oxmultilang ident="WIDGET_PRODUCT_PRODUCT_ARTWEIGHT" }]</span>
                        <span class="value">[{ $product->oxarticles__oxweight->value }] [{ oxmultilang ident="WIDGET_PRODUCT_PRODUCT_ARTWEIGHT2" }]</span>
                    </span>
                [{/if }]
            [{/oxhasrights}]
        [{/block}]
    [{/capture}]
    <meta itemprop='productID' content='sku:[{ $product->oxarticles__oxartnum->value }]'>
    <a id="[{$testid}]" itemprop="url" href="[{$_productLink}]" class="titleBlock title fn" title="[{ $product->oxarticles__oxtitle->value}]">
        <span itemprop="name">[{ $product->oxarticles__oxtitle->value }]</span>
        <div class="gridPicture">
            <img itemprop="image" src="[{$product->getThumbnailUrl()}]" alt="[{ $product->oxarticles__oxtitle->value }]">
        </div>
    </a>
    [{block name="widget_product_listitem_grid_tobasket"}]
        <div class="priceBlock" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            [{oxhasrights ident="TOBASKET"}]
                [{$smarty.capture.product_price}]
                [{if !$blShowToBasket }]
                    <a href="[{ $_productLink }]" class="toCart button">[{ oxmultilang ident="WIDGET_PRODUCT_PRODUCT_MOREINFO" }]</a>
                [{else}]
                    [{assign var="listType" value=$oView->getListType()}]
                    <a href="[{$oView->getLink()|oxaddparams:"listtype=`$listType`&amp;fnc=tobasket&amp;aid=`$product->oxarticles__oxid->value`&amp;am=1" }]" class="toCart button" title="[{oxmultilang ident="WIDGET_PRODUCT_PRODUCT_ADDTOCART" }]">[{oxmultilang ident="WIDGET_PRODUCT_PRODUCT_ADDTOCART" }]</a>
                [{/if}]
            [{/oxhasrights}]
        </div>
   [{/block}]
[{/block}]