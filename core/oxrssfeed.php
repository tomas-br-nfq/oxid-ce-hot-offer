<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @package   core
 * @copyright (C) OXID eSales AG 2003-2012
 * @version OXID eShop CE
 * @version   SVN: $Id: oxrssfeed.php 43075 2012-03-22 08:57:11Z linas.kukulskis $
 */

/**
 * Rss feed manager
 * loads needed rss data
 *
 * @package core
 */
class oxRssFeed extends oxSuperCfg
{
    /**
     * timeout in seconds for regenerating data (3h)
     */
    const CACHE_TTL = 10800;

    /**
     * Rss data Ids for cache
     */
    const RSS_TOPSHOP    = 'RSS_TopShop';
    const RSS_NEWARTS    = 'RSS_NewArts';
    const RSS_CATARTS    = 'RSS_CatArts';
    const RSS_ARTRECOMMLISTS = 'RSS_ARTRECOMMLISTS';
    const RSS_RECOMMLISTARTS = 'RSS_RECOMMLISTARTS';
    const RSS_BARGAIN    = 'RSS_Bargain';

    /**
     * _aChannel channel data to be passed to view
     *
     * @var array
     * @access protected
     */
    protected $_aChannel = array();

    /**
     * getChannel retrieve channel data
     *
     * @access public
     * @return array
     */
    public function getChannel()
    {
        return $this->_aChannel;
    }

    /**
     * _loadBaseChannel loads basic channel data
     *
     * @access protected
     * @return void
     */
    protected function _loadBaseChannel()
    {
        $oShop = $this->getConfig()->getActiveShop();
        $this->_aChannel['title'] = $oShop->oxshops__oxname->value;
        $this->_aChannel['link']  = oxUtilsUrl::getInstance()->prepareUrlForNoSession($this->getConfig()->getShopUrl());
        $this->_aChannel['description'] = '';
        $oLang = oxLang::getInstance();
        $aLangIds = $oLang->getLanguageIds();
        $this->_aChannel['language']  = $aLangIds[$oLang->getBaseLanguage()];
        $this->_aChannel['copyright'] = $oShop->oxshops__oxname->value;
        $this->_aChannel['selflink'] = '';
        if ( oxUtils::getInstance()->isValidEmail( $oShop->oxshops__oxinfoemail->value )) {
            $this->_aChannel['managingEditor'] = $oShop->oxshops__oxinfoemail->value;
        }
        //$this->_aChannel['webMaster']      = '';

        $this->_aChannel['generator']      = 'OXID eShop '.$oShop->oxshops__oxversion->value;
        $this->_aChannel['image']['url']   = $this->getConfig()->getImageUrl().'logo.png';


        $this->_aChannel['image']['title'] = $this->_aChannel['title'];
        $this->_aChannel['image']['link']  = $this->_aChannel['link'];
    }

    /**
     * _getCacheId retrieve cache id
     *
     * @param string $name cache name
     *
     * @access protected
     * @return string
     */
    protected function _getCacheId($name)
    {
        $oConfig = $this->getConfig();
        return $name.'_'.$oConfig->getShopId().'_'.oxLang::getInstance()->getBaseLanguage().'_'.(int) $oConfig->getShopCurrency();
    }

    /**
     * _loadFromCache load data from cache, requires Rss data Id
     *
     * @param string $name Rss data Id
     *
     * @access protected
     * @return array
     */
    protected function _loadFromCache($name)
    {
        if ($aRes = oxUtils::getInstance()->fromFileCache($this->_getCacheId($name))) {
            if ($aRes['timestamp'] > time() - self::CACHE_TTL) {
                return $aRes['content'];
            }
        }
        return false;
    }


    /**
     * _getLastBuildDate check if changed data and renew last build date if needed
     * returns result as string
     *
     * @param string $name  Rss data Id
     * @param array  $aData channel data
     *
     * @access protected
     * @return string
     */
    protected function _getLastBuildDate($name, $aData)
    {
        if ($aData2 = oxUtils::getInstance()->fromFileCache($this->_getCacheId($name))) {
            $sLastBuildDate = $aData2['content']['lastBuildDate'];
            $aData2['content']['lastBuildDate'] = '';
            $aData['lastBuildDate'] = '';
            if (!strcmp(serialize($aData), serialize($aData2['content']))) {
                return $sLastBuildDate;
            }
        }
        return date('D, d M Y H:i:s O');
    }

    /**
     * _saveToCache writes generated rss data to cache
     * returns true on successfull write, false otherwise
     * A successfull write means only write ok AND data has actually changed
     * if give
     *
     * @param string $name     cache name
     * @param array  $aContent data to be saved
     *
     * @access protected
     * @return void
     */
    protected function _saveToCache($name, $aContent)
    {
        $aData = array( 'timestamp' => time(), 'content'   => $aContent );
        return oxUtils::getInstance()->toFileCache($this->_getCacheId($name), $aData);
    }


    /**
     * _getArticleItems create channel items from article list
     *
     * @param oxArticleList $oList article list
     *
     * @access protected
     * @return array
     */
    protected function _getArticleItems(oxArticleList $oList)
    {
        $myUtilsUrl = oxUtilsUrl::getInstance();
        $aItems = array();
        $oLang = oxLang::getInstance();
        $oStr  = getStr();

        foreach ($oList as $oArticle) {
            $oItem = new oxStdClass();
            $oActCur = $this->getConfig()->getActShopCurrencyObject();
            $sPrice = '';
            if ( $oPrice = $oArticle->getPrice() ) {
                $sPrice =  " " . $oArticle->getPriceFromPrefix().$oLang->formatCurrency( $oPrice->getBruttoPrice(), $oActCur ) . " ". $oActCur->sign;
            }
            $oItem->title                   = strip_tags($oArticle->oxarticles__oxtitle->value . $sPrice);
            $oItem->guid     = $oItem->link = $myUtilsUrl->prepareUrlForNoSession($oArticle->getLink());
            $oItem->isGuidPermalink         = true;
            $oItem->description             = $oArticle->getArticleLongDesc()->value; //oxarticles__oxshortdesc->value;
            if (trim(str_replace('&nbsp;', '', (strip_tags($oItem->description)))) == '') {
                $oItem->description             = $oArticle->oxarticles__oxshortdesc->value;
            }

            $oItem->description = trim($oItem->description);
            if ($sIcon = $oArticle->getIconUrl()) {
                $oItem->description = "<img src='$sIcon' border=0 align='left' hspace=5>".$oItem->description;
            }
            $oItem->description = $oStr->htmlspecialchars( $oItem->description );

            $aItems[] = $oItem;
        }
        return $aItems;
    }

    /**
     * _prepareUrl make url from uri
     *
     * @param string $sUri   standart uri
     * @param string $sTitle page title
     *
     * @access protected
     *
     * @return string
     */
    protected function _prepareUrl($sUri, $sTitle)
    {
        $iLang = oxLang::getInstance()->getBaseLanguage();
        $sUrl  = $this->_getShopUrl();
        $sUrl .= $sUri.'&amp;lang='.$iLang;

        if ( oxUtils::getInstance()->seoIsActive() ) {
            $oEncoder = oxSeoEncoder::getInstance();
            $sUrl = $oEncoder->getDynamicUrl( $sUrl, "rss/{$sTitle}/", $iLang );
        }

        return oxUtilsUrl::getInstance()->prepareUrlForNoSession( $sUrl );
    }

    /**
     * _prepareFeedName adds shop name to feed title
     *
     * @param string $sTitle page title
     *
     * @access protected
     *
     * @return string
     */
    protected function _prepareFeedName($sTitle)
    {
        $oShop = $this->getConfig()->getActiveShop();

        return $oShop->oxshops__oxname->value . "/" . $sTitle;
    }

    /**
     * _getShopUrl returns shop home url
     *
     * @access protected
     * @return string
     */
    protected function _getShopUrl()
    {
        $sUrl = $this->getConfig()->getShopUrl();
        $oStr = getStr();
        if ( $oStr->strpos($sUrl, '?') !== false ) {
            if ( !$oStr->preg_match('/[?&](amp;)?$/i', $sUrl)) {
                $sUrl .= '&amp;';
            }
        } else {
            $sUrl .= '?';
        }
        return $sUrl;
    }

    /**
     * _loadData loads given data to channel
     *
     * @param string $sTag       tag
     * @param string $sTitle     object title
     * @param string $sDesc      object description
     * @param array  $aItems     items data to be put to rss
     * @param string $sRssUrl    url of rss page
     * @param string $sTargetUrl url of page rss represents
     *
     * @access protected
     * @return void
     */
    protected function _loadData($sTag, $sTitle, $sDesc, $aItems, $sRssUrl, $sTargetUrl = null)
    {
        $this->_loadBaseChannel();

        $this->_aChannel['selflink'] = $sRssUrl;

        if ($sTargetUrl) {
            $this->_aChannel['link'] = $this->_aChannel['image']['link'] = $sTargetUrl;
        }

        $this->_aChannel['image']['title']        = $this->_aChannel['title']       = $sTitle;
        $this->_aChannel['image']['description']  = $this->_aChannel['description'] = $sDesc;

        $this->_aChannel['items'] = $aItems;

        if ($sTag) {
            $this->_aChannel['lastBuildDate'] = $this->_getLastBuildDate($sTag, $this->_aChannel);
            $this->_saveToCache($sTag, $this->_aChannel);
        } else {
            $this->_aChannel['lastBuildDate'] = date( 'D, d M Y H:i:s O', oxUtilsDate::getInstance()->getTime() );
        }
    }

    /**
     * getTopShopTitle get title for 'Top of the Shop' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getTopInShopTitle()
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareFeedName( $oLang->translateString( 'RSS_TOPSHOP_TITLE', $iLang ) );
    }

    /**
     * getTopShopUrl get url for 'Top of the Shop' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getTopInShopUrl()
    {
        return $this->_prepareUrl("cl=rss&amp;fnc=topshop", $this->getTopInShopTitle());
    }

    /**
     * loadTopShop loads 'Top of the Shop' rss data
     *
     * @access public
     *
     * @return void
     */
    public function loadTopInShop()
    {
        if ( ( $this->_aChannel = $this->_loadFromCache( self::RSS_TOPSHOP ) ) ) {
            return;
        }

        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->loadTop5Articles( $this->getConfig()->getConfigParam( 'iRssItemsCount' ) );

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_TOPSHOP,
            $this->getTopInShopTitle(),
            $oLang->translateString( 'RSS_TOPSHOP_DESCRIPTION', $oLang->getBaseLanguage() ),
            $this->_getArticleItems($oArtList),
            $this->getTopInShopUrl()
        );
    }



    /**
     * get title for 'Newest Shop Articles' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getNewestArticlesTitle()
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareFeedName( $oLang->translateString( 'RSS_NEWESTARTICLES_TITLE', $iLang ) );
    }

    /**
     * getNewestArticlesUrl get url for 'Newest Shop Articles' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getNewestArticlesUrl()
    {
        return $this->_prepareUrl("cl=rss&amp;fnc=newarts", $this->getNewestArticlesTitle());
    }

    /**
     * loadNewestArticles loads 'Newest Shop Articles' rss data
     *
     * @access public
     *
     * @return void
     */
    public function loadNewestArticles()
    {
        if ( ( $this->_aChannel = $this->_loadFromCache(self::RSS_NEWARTS ) ) ) {
            return;
        }
        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->loadNewestArticles( $this->getConfig()->getConfigParam( 'iRssItemsCount' ) );

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_NEWARTS,
            $this->getNewestArticlesTitle( ),
            $oLang->translateString( 'RSS_NEWESTARTICLES_DESCRIPTION', $oLang->getBaseLanguage() ),
            $this->_getArticleItems($oArtList),
            $this->getNewestArticlesUrl()
        );
    }


    /**
     * get title for 'Category Articles' rss feed
     *
     * @param oxCategory $oCat category object
     *
     * @access public
     *
     * @return string
     */
    public function getCategoryArticlesTitle(oxCategory $oCat)
    {
        $oLang  = oxLang::getInstance();
        $iLang  = $oLang->getBaseLanguage();
        $sTitle = $this->_getCatPath($oCat);
        return $this->_prepareFeedName( $sTitle . $oLang->translateString( 'RSS_CATEGORYARTICLES_TITLE', $iLang ) );
    }

    /**
     * Returns string built from category titles
     *
     * @param oxCategory $oCat category object
     *
     * @return string
     */
    protected function _getCatPath( $oCat )
    {
        $sCatPathString = '';
        $sSep = '';
        while ( $oCat ) {
            // prepare oCat title part
            $sCatPathString = $oCat->oxcategories__oxtitle->value.$sSep.$sCatPathString ;
            $sSep = '/';
            // load parent
            $oCat = $oCat->getParentCategory();
        }
        return $sCatPathString;
    }

    /**
     * getCategoryArticlesUrl get url for 'Category Articles' rss feed
     *
     * @param oxCategory $oCat category object
     *
     * @access public
     *
     * @return string
     */
    public function getCategoryArticlesUrl(oxCategory $oCat)
    {
        $oLang = oxLang::getInstance();
        return $this->_prepareUrl("cl=rss&amp;fnc=catarts&amp;cat=".urlencode($oCat->getId()),
                sprintf($oLang->translateString( 'RSS_CATEGORYARTICLES_URL', $oLang->getBaseLanguage() ), $oCat->oxcategories__oxtitle->value));
    }

    /**
     * loadCategoryArticles loads 'Category Articles' rss data
     *
     * @param oxCategory $oCat category object
     *
     * @access public
     *
     * @return void
     */
    public function loadCategoryArticles( oxCategory $oCat )
    {
        $sId = $oCat->getId();
        if ( ( $this->_aChannel = $this->_loadFromCache( self::RSS_CATARTS.$sId ) ) ) {
            return;
        }

        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->setCustomSorting('oc.oxtime desc');
        $oArtList->loadCategoryArticles($oCat->getId(), null, $this->getConfig()->getConfigParam( 'iRssItemsCount' ));

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_CATARTS.$sId,
            $this->getCategoryArticlesTitle($oCat),
            sprintf($oLang->translateString( 'RSS_CATEGORYARTICLES_DESCRIPTION', $oLang->getBaseLanguage() ), $oCat->oxcategories__oxtitle->value),
            $this->_getArticleItems($oArtList),
            $this->getCategoryArticlesUrl($oCat),
            $oCat->getLink()
        );
    }


    /**
     * get title for 'Search Articles' rss feed
     *
     * @param string $sSearch         search string
     * @param string $sCatId          category id
     * @param string $sVendorId       vendor id
     * @param string $sManufacturerId Manufacturer id
     *
     * @access public
     *
     * @return string
     */
    public function getSearchArticlesTitle($sSearch, $sCatId, $sVendorId, $sManufacturerId)
    {
        return $this->_prepareFeedName( getStr()->htmlspecialchars($this->_getSearchParamsTranslation('RSS_SEARCHARTICLES_TITLE', $sSearch, $sCatId, $sVendorId, $sManufacturerId)) );
    }

    /**
     * _getSearchParamsUrl return search parameters for url
     *
     * @param string $sSearch         search string
     * @param string $sCatId          category id
     * @param string $sVendorId       vendor id
     * @param string $sManufacturerId Manufacturer id
     *
     * @access protected
     *
     * @return string
     */
    protected function _getSearchParamsUrl($sSearch, $sCatId, $sVendorId, $sManufacturerId)
    {
        $sParams = "searchparam=".urlencode($sSearch);
        if ($sCatId) {
            $sParams .= "&amp;searchcnid=".urlencode($sCatId);
        }

        if ($sVendorId) {
            $sParams .= "&amp;searchvendor=".urlencode($sVendorId);
        }

        if ($sManufacturerId) {
            $sParams .= "&amp;searchmanufacturer=".urlencode($sManufacturerId);
        }

        return $sParams;
    }

    /**
     * loads object and returns specified field
     *
     * @param string $sId     object id
     * @param string $sObject object class
     * @param string $sField  object field to be taken
     *
     * @access protected
     * @return string
     */
    protected function _getObjectField($sId, $sObject, $sField)
    {
        if (!$sId) {
            return '';
        }
        $oObj = oxNew($sObject);
        if ($oObj->load($sId)) {
            return $oObj->$sField->value;
        }
        return '';
    }

    /**
     * _getSearchParamsTranslation translates text for given lang id
     * loads category and vendor to take their titles.
     *
     * @param string $sId             language id
     * @param string $sSearch         search param
     * @param string $sCatId          category id
     * @param string $sVendorId       vendor id
     * @param string $sManufacturerId Manufacturer id
     *
     * @access protected
     * @return string
     */
    protected function _getSearchParamsTranslation($sId, $sSearch, $sCatId, $sVendorId, $sManufacturerId)
    {
        $oLang = oxLang::getInstance();
        $sCatTitle = '';
        if ($sTitle = $this->_getObjectField($sCatId, 'oxcategory', 'oxcategories__oxtitle')) {
            $sCatTitle = sprintf($oLang->translateString( 'RSS_SEARCHARTICLES_TAG_CATEGORY', $oLang->getBaseLanguage() ), $sTitle);
        }
        $sVendorTitle = '';
        if ($sTitle = $this->_getObjectField($sVendorId, 'oxvendor', 'oxvendor__oxtitle')) {
            $sVendorTitle = sprintf($oLang->translateString( 'RSS_SEARCHARTICLES_TAG_VENDOR', $oLang->getBaseLanguage() ), $sTitle);
        }
        $sManufacturerTitle = '';
        if ($sTitle = $this->_getObjectField($sManufacturerId, 'oxmanufacturer', 'oxmanufacturers__oxtitle')) {
            $sManufacturerTitle = sprintf($oLang->translateString( 'RSS_SEARCHARTICLES_TAG_MANUFACTURER', $oLang->getBaseLanguage() ), $sTitle);
        }

        $sRet = sprintf($oLang->translateString( $sId, $oLang->getBaseLanguage() ), $sSearch);

        $sRet = str_replace('<TAG_CATEGORY>', $sCatTitle, $sRet);
        $sRet = str_replace('<TAG_VENDOR>', $sVendorTitle, $sRet);
        $sRet = str_replace('<TAG_MANUFACTURER>', $sManufacturerTitle, $sRet);

        return $sRet;
    }

    /**
     * getSearchArticlesUrl get url for 'Search Articles' rss feed
     *
     * @param string $sSearch         search string
     * @param string $sCatId          category id
     * @param string $sVendorId       vendor id
     * @param string $sManufacturerId Manufacturer id
     *
     * @access public
     *
     * @return string
     */
    public function getSearchArticlesUrl( $sSearch, $sCatId, $sVendorId, $sManufacturerId )
    {
        $oLang = oxLang::getInstance();
        $sUrl = $this->_prepareUrl("cl=rss&amp;fnc=searcharts", $oLang->translateString( 'RSS_SEARCHARTICLES_URL', $oLang->getBaseLanguage()));

        $sJoin = '?';
        if (strpos($sUrl, '?') !== false) {
            $sJoin = '&amp;';
        }
        return $sUrl.$sJoin.$this->_getSearchParamsUrl($sSearch, $sCatId, $sVendorId, $sManufacturerId);
    }

    /**
     * loadSearchArticles loads 'Search Articles' rss data
     *
     * @param string $sSearch         search string
     * @param string $sCatId          category id
     * @param string $sVendorId       vendor id
     * @param string $sManufacturerId Manufacturer id
     *
     * @access public
     *
     * @return void
     */
    public function loadSearchArticles( $sSearch, $sCatId, $sVendorId, $sManufacturerId )
    {
        // dont use cache for search
        //if ($this->_aChannel = $this->_loadFromCache(self::RSS_SEARCHARTS.md5($sSearch.$sCatId.$sVendorId))) {
        //    return;
        //}

        $oConfig = $this->getConfig();
        $oConfig->setConfigParam('iNrofCatArticles', $oConfig->getConfigParam( 'iRssItemsCount' ));

        $oArtList = oxNew( 'oxsearch' )->getSearchArticles($sSearch, $sCatId, $sVendorId, $sManufacturerId, oxNew('oxarticle')->getViewName().'.oxtimestamp desc');

        $this->_loadData(
            // dont use cache for search
            null,
            //self::RSS_SEARCHARTS.md5($sSearch.$sCatId.$sVendorId),
            $this->getSearchArticlesTitle($sSearch, $sCatId, $sVendorId, $sManufacturerId),
            $this->_getSearchParamsTranslation('RSS_SEARCHARTICLES_DESCRIPTION', getStr()->htmlspecialchars( $sSearch ), $sCatId, $sVendorId, $sManufacturerId),
            $this->_getArticleItems($oArtList),
            $this->getSearchArticlesUrl($sSearch, $sCatId, $sVendorId, $sManufacturerId),
            $this->_getShopUrl()."cl=search&amp;".$this->_getSearchParamsUrl($sSearch, $sCatId, $sVendorId, $sManufacturerId)
        );
    }

    /**
     * get title for 'Recommendation lists' rss feed
     *
     * @param oxArticle $oArticle load lists for this article
     *
     * @return string
     */
    public function getRecommListsTitle(oxArticle $oArticle)
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareFeedName( sprintf($oLang->translateString( 'RSS_ARTRECOMMLISTS_TITLE', $iLang ), $oArticle->oxarticles__oxtitle->value) );
    }

    /**
     * get url for 'Recommendation lists' rss feed
     *
     * @param oxArticle $oArticle load lists for this article
     *
     * @return string
     */
    public function getRecommListsUrl(oxArticle $oArticle)
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareUrl("cl=rss&amp;fnc=recommlists&amp;anid=".$oArticle->getId(),
                sprintf($oLang->translateString( 'RSS_ARTRECOMMLISTS_URL', $iLang ), $oArticle->oxarticles__oxtitle->value));
    }

    /**
     * make rss data array from given oxlist
     *
     * @param oxList $oList recommlist object
     *
     * @return array
     */
    protected function _getRecommListItems($oList)
    {
        $myUtilsUrl = oxUtilsUrl::getInstance();
        $aItems = array();
        foreach ($oList as $oRecommList) {
            $oItem = new oxStdClass();
            $oItem->title                   = $oRecommList->oxrecommlists__oxtitle->value;
            $oItem->guid     = $oItem->link = $myUtilsUrl->prepareUrlForNoSession($oRecommList->getLink());
            $oItem->isGuidPermalink         = true;
            $oItem->description             = $oRecommList->oxrecommlists__oxdesc->value;

            $aItems[] = $oItem;
        }
        return $aItems;
    }

    /**
     * loads 'Recommendation lists' rss data
     *
     * @param oxArticle $oArticle load lists for this article
     *
     * @return null
     */
    public function loadRecommLists(oxArticle $oArticle)
    {
        if ( ( $this->_aChannel = $this->_loadFromCache( self::RSS_ARTRECOMMLISTS.$oArticle->getId() ) ) ) {
            return;
        }

        $oConfig = $this->getConfig();
        $oConfig->setConfigParam('iNrofCrossellArticles', $oConfig->getConfigParam( 'iRssItemsCount' ));

        $oList = oxNew( 'oxrecommlist' )->getRecommListsByIds(array($oArticle->getId()));
        if ($oList == null) {
            $oList = oxNew('oxlist');
        }

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_ARTRECOMMLISTS.$oArticle->getId(),
            $this->getRecommListsTitle($oArticle),
            sprintf($oLang->translateString( 'RSS_ARTRECOMMLISTS_DESCRIPTION', $oLang->getBaseLanguage() ), $oArticle->oxarticles__oxtitle->value),
            $this->_getRecommListItems($oList),
            $this->getRecommListsUrl($oArticle),
            $oArticle->getLink()
        );
    }

    /**
     * get title for 'Recommendation list articles' rss feed
     *
     * @param oxRecommList $oRecommList recomm list to load articles from
     *
     * @return string
     */
    public function getRecommListArticlesTitle(oxRecommList $oRecommList)
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareFeedName( sprintf($oLang->translateString( 'RSS_RECOMMLISTARTICLES_TITLE', $iLang ), $oRecommList->oxrecommlists__oxtitle->value) );
    }

    /**
     * get url for 'Recommendation lists' rss feed
     *
     * @param oxRecommList $oRecommList recomm list to load articles from
     *
     * @return string
     */
    public function getRecommListArticlesUrl(oxRecommList $oRecommList)
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareUrl("cl=rss&amp;fnc=recommlistarts&amp;recommid=".$oRecommList->getId(),
                sprintf($oLang->translateString( 'RSS_RECOMMLISTARTICLES_URL', $iLang ), $oRecommList->oxrecommlists__oxtitle->value));
    }

    /**
     * loads 'Recommendation lists' rss data
     *
     * @param oxRecommList $oRecommList recomm list to load articles from
     *
     * @return null
     */
    public function loadRecommListArticles(oxRecommList $oRecommList)
    {
        if ( ( $this->_aChannel = $this->_loadFromCache( self::RSS_RECOMMLISTARTS.$oRecommList->getId() ) ) ) {
            return;
        }

        $oList = oxNew( 'oxarticlelist' );
        $oList->loadRecommArticles( $oRecommList->getId(), ' order by oxobject2list.oxtimestamp desc limit '. $this->getConfig()->getConfigParam( 'iRssItemsCount' ) );

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_RECOMMLISTARTS.$oRecommList->getId(),
            $this->getRecommListArticlesTitle($oRecommList),
            sprintf($oLang->translateString( 'RSS_RECOMMLISTARTICLES_DESCRIPTION', $oLang->getBaseLanguage() ), $oRecommList->oxrecommlists__oxtitle->value),
            $this->_getArticleItems($oList),
            $this->getRecommListArticlesUrl($oRecommList),
            $oRecommList->getLink()
        );
    }

    /**
     * getBargainTitle get title for 'Bargain' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getBargainTitle()
    {
        $oLang = oxLang::getInstance();
        $iLang = $oLang->getBaseLanguage();
        return $this->_prepareFeedName( $oLang->translateString( 'RSS_BARGAIN_TITLE', $iLang ) );
    }

    /**
     * getBargainUrl get url for 'Bargain' rss feed
     *
     * @access public
     *
     * @return string
     */
    public function getBargainUrl()
    {
        return $this->_prepareUrl("cl=rss&amp;fnc=bargain", $this->getBargainTitle());
    }

    /**
     * loadBargain loads 'Bargain' rss data
     *
     * @access public
     *
     * @return void
     */
    public function loadBargain()
    {
        if ( ( $this->_aChannel = $this->_loadFromCache( self::RSS_BARGAIN ) ) ) {
            return;
        }

        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->loadAktionArticles( 'OXBARGAIN', $this->getConfig()->getConfigParam( 'iRssItemsCount' ) );

        $oLang = oxLang::getInstance();
        $this->_loadData(
            self::RSS_BARGAIN,
            $this->getBargainTitle(),
            $oLang->translateString( 'RSS_BARGAIN_DESCRIPTION', $oLang->getBaseLanguage() ),
            $this->_getArticleItems($oArtList),
            $this->getBargainUrl()
        );
    }

}

