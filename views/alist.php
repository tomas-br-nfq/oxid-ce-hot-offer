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
 * @package   views
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: alist.php 38776 2011-09-15 12:21:20Z arvydas.vapsva $
 */

/**
 * List of articles for a selected product group.
 * Collects list of articles, according to it generates links for list gallery,
 * metatags (for search engines). Result - "list.tpl" template.
 * OXID eShop -> (Any selected shop product category).
 */
class aList extends oxUBase
{
    /**
     * Count of all articles in list.
     * @var integer
     */
    protected $_iAllArtCnt = 0;

    /**
     * Number of possible pages.
     * @var integer
     */
    protected $_iCntPages = 0;

    /**
     * Current class default template name.
     * @var string
     */
    protected $_sThisTemplate = 'page/list/list.tpl';

    /**
     * New layout list template
     * @var string
     */
    protected $_sThisMoreTemplate = 'page/list/morecategories.tpl';

    /**
     * Category path string
     * @var string
     */
    protected $_sCatPathString = null;

    /**
     * Marked which defines if current view is sortable or not
     * @var bool
     */
    protected $_blShowSorting = true;

    /**
     * Category attributes.
     * @var array
     */
    protected $_aAttributes = null;

    /**
     * Category article list
     * @var array
     */
    protected $_aCatArtList = null;

    /**
     * If category has subcategories
     * @var bool
     */
    protected $_blHasVisibleSubCats = null;

    /**
     * List of category's subcategories
     * @var array
     */
    protected $_aSubCatList = null;

    /**
     * Page navigation
     * @var object
     */
    protected $_oPageNavigation = null;

    /**
     * Active object is category.
     * @var bool
     */
    protected $_blIsCat = null;

    /**
     * Recomendation list
     * @var object
     */
    protected $_oRecommList = null;

    /**
     * Category title
     * @var string
     */
    protected $_sCatTitle = null;

    /**
     * Sign if to load and show top5articles action
     * @var bool
     */
    protected $_blTop5Action = true;
    /**
     * Show tags cloud
     * @var bool
     */
    protected $_blShowTagCloud = true;

    /**
     * Sign if to load and show bargain action
     * @var bool
     */
    protected $_blBargainAction = false;

    /**
     * Generates (if not generated yet) and returns view ID (for
     * template engine caching).
     *
     * @return string   $this->_sViewId view id
     */
    public function getViewId()
    {
        if ( !isset( $this->_sViewId ) ) {
            $sCatId   = oxConfig::getParameter( 'cnid' );
            $iActPage = $this->getActPage();
            $iArtPerPage = oxSession::getVar( '_artperpage' );
            $sListDisplayType = oxSession::getVar( 'ldtype' );
            $sParentViewId = parent::getViewId();

            // shorten it
                $this->_sViewId = md5( $sParentViewId.'|'.$sCatId.'|'.$iActPage.'|'.$iArtPerPage.'|'.$sListDisplayType );

        }

        return $this->_sViewId;
    }

    /**
     * Executes parent::render(), loads active category, prepares article
     * list sorting rules. According to category type loads list of
     * articles - regular (oxarticlelist::LoadCategoryArticles()) or price
     * dependent (oxarticlelist::LoadPriceArticles()). Generates page navigation data
     * such as previous/next window URL, number of available pages, generates
     * metatags info (oxubase::_convertForMetaTags()) and returns name of
     * template to render. Also checks if actual pages count does not exceed real
     * articles page count. If yes - calls error_404_handler().
     *
     * @return  string  $this->_sThisTemplate   current template file name
     */
    public function render()
    {
        $myConfig = $this->getConfig();

        $oCategory  = null;
        $blContinue = true;
        $this->_blIsCat = false;

        // A. checking for fake "more" category
        if ( 'oxmore' == oxConfig::getParameter( 'cnid' ) ) {
            // overriding some standard value and parameters
            $this->_sThisTemplate = $this->_sThisMoreTemplate;
            $oCategory = oxNew( 'oxcategory' );
            $oCategory->oxcategories__oxactive = new oxField( 1, oxField::T_RAW );
            $this->setActCategory( $oCategory );

            $this->_blShowTagCloud = true;

        } elseif ( ( $oCategory = $this->getActCategory() ) ) {
            $blContinue = ( bool ) $oCategory->oxcategories__oxactive->value;
            $this->_blIsCat = true;
            $this->_blBargainAction = true;
        }


        // category is inactive ?
        if ( !$blContinue || !$oCategory ) {
            oxUtils::getInstance()->redirect( $myConfig->getShopURL().'index.php', true, 302 );
        }

        $oCat = $this->getActCategory();
        if ($oCat && $myConfig->getConfigParam( 'bl_rssCategories' )) {
            $oRss = oxNew('oxrssfeed');
            $this->addRssFeed($oRss->getCategoryArticlesTitle($oCat), $oRss->getCategoryArticlesUrl($oCat), 'activeCategory');
        }

        //checking if actual pages count does not exceed real articles page count
        $this->getArticleList();

        if ( $this->_blIsCat ) {
            $this->_checkRequestedPage();
        }

        parent::render();

        // processing list articles
        $this->_processListArticles();

        return $this->getTemplateName();
    }

    /**
     * Checks if requested page is valid and:
     * - redirecting to first page in case requested page does not exist
     * or
     * - displays 404 error if category has no products
     *
     * @return null
     */
    protected function _checkRequestedPage()
    {
        $iPageCnt = $this->getPageCount();
        // redirecting to first page in case requested page does not exist
        if ( $iPageCnt && ( ( $iPageCnt - 1 ) < $this->getActPage() ) ) {
            oxUtils::getInstance()->redirect( $this->getActiveCategory()->getLink(), false );
        }
    }

    /**
     * Iterates through list articles and performs list view specific tasks:
     *  - sets type of link whicn needs to be generated (Manufacturer link)
     *
     * @return null
     */
    protected function _processListArticles()
    {
        if ( $aArtList = $this->getArticleList() ) {
            $iLinkType = $this->_getProductLinkType();
            $sAddDynParams = $this->getAddUrlParams();
            $sAddSeoParams = $this->getAddSeoUrlParams();

            foreach ( $aArtList as $oArticle ) {
                $oArticle->setLinkType( $iLinkType );

                // appending dynamic urls
                if ( $sAddDynParams ) {
                    $oArticle->appendStdLink( $sAddDynParams );
                }

                // appending seo urls
                if ( $sAddSeoParams ) {
                    $oArticle->appendLink( $sAddSeoParams );
                }
            }
        }
    }


    /**
     * Returns additional URL parameters which must be added to list products dynamic urls
     *
     * @return string
     */
    public function getAddUrlParams()
    {
        $sParams = parent::getAddUrlParams();
        if ( !oxUtils::getInstance()->seoIsActive() ) {
            $iPgNr = (int) oxConfig::getParameter( 'pgNr' );
            if ( $iPgNr > 0 ) {
                $sParams .= ($sParams?'&amp;':'') . "pgNr={$iPgNr}";
            }
        }

        return $sParams;
    }

    /**
     * Returns additional URL parameters which must be added to list products seo urls
     *
     * @return string
     */
    public function getAddSeoUrlParams()
    {
    }

    /**
     * Returns product link type:
     *  - OXARTICLE_LINKTYPE_PRICECATEGORY - when active category is price category
     *  - OXARTICLE_LINKTYPE_CATEGORY - when active category is regular category
     *
     * @return int
     */
    protected function _getProductLinkType()
    {
        $iCatType = OXARTICLE_LINKTYPE_CATEGORY;
        if ( ( $oCat = $this->getActCategory() ) && $oCat->isPriceCategory() ) {
            $iCatType =  OXARTICLE_LINKTYPE_PRICECATEGORY;
        }
        return $iCatType;
    }

    /**
     * Stores chosen category filter into session.
     *
     * Session variables:
     * <b>session_attrfilter</b>
     *
     * @return null
     */
    public function executefilter()
    {
        $iLang = oxLang::getInstance()->getBaseLanguage();
        // store this into session
        $aFilter = oxConfig::getParameter( 'attrfilter', 1 );
        $sActCat = oxConfig::getParameter( 'cnid' );

        if ( !empty( $aFilter ) ) {
            $aSessionFilter = oxSession::getVar( 'session_attrfilter' );
            //fix for #2904 - if language will be changed attributes of this category will be deleted from session
            //and new filters for active language set.
            $aSessionFilter[$sActCat] = null;
            $aSessionFilter[$sActCat][$iLang] = $aFilter;
            oxSession::setVar( 'session_attrfilter', $aSessionFilter );
        }
    }

    /**
     * Loads and returns article list of active category.
     *
     * @param string $oCategory category object
     *
     * @return array
     */
    protected function _loadArticles( $oCategory )
    {
        $myConfig = $this->getConfig();

        $iNrofCatArticles = (int) $myConfig->getConfigParam( 'iNrofCatArticles' );
        $iNrofCatArticles = $iNrofCatArticles?$iNrofCatArticles:1;

        // load only articles which we show on screen
        $oArtList = oxNew( 'oxarticlelist' );
        $oArtList->setSqlLimit( $iNrofCatArticles * $this->_getRequestPageNr(), $iNrofCatArticles );
        $oArtList->setCustomSorting( $this->getSortingSql( $oCategory->getId() ) );

        if ( $oCategory->isPriceCategory() ) {
            $dPriceFrom = $oCategory->oxcategories__oxpricefrom->value;
            $dPriceTo   = $oCategory->oxcategories__oxpriceto->value;

            $this->_iAllArtCnt = $oArtList->loadPriceArticles( $dPriceFrom, $dPriceTo, $oCategory );
        } else {
            $aSessionFilter = oxSession::getVar( 'session_attrfilter' );

            $sActCat = oxConfig::getParameter( 'cnid' );
            $this->_iAllArtCnt = $oArtList->loadCategoryArticles( $sActCat, $aSessionFilter );
        }

        $this->_iCntPages = round( $this->_iAllArtCnt/$iNrofCatArticles + 0.49 );

        return $oArtList;
    }

    /**
     * Get actual page number.
     *
     * @return int
     */
    public function getActPage()
    {
        return $this->_getRequestPageNr();
    }

    /**
     * Calls parent::getActPage();
     *
     * @todo this function is a temporary solution and should be rmeoved as
     * soon product list loading is refactored
     *
     * @return int
     */
    protected function _getRequestPageNr()
    {
        return parent::getActPage();
    }

    /**
     * Returns active product id to load its seo meta info
     *
     * @return string
     */
    protected function _getSeoObjectId()
    {
        if ( ( $oCategory = $this->getActCategory() ) ) {
            return $oCategory->getId();
        }
    }

    /**
     * Returns string built from category titles
     *
     * @return string
     */
    protected function _getCatPathString()
    {
        if ( $this->_sCatPathString === null ) {

            // marking as allready set
            $this->_sCatPathString = false;

            //fetching category path
            if ( is_array( $aPath = $this->getCatTreePath() ) ) {

                $oStr = getStr();
                $this->_sCatPathString = '';
                foreach ( $aPath as $oCat ) {
                    if ( $this->_sCatPathString ) {
                        $this->_sCatPathString .= ', ';
                    }
                    $this->_sCatPathString .= $oStr->strtolower( $oCat->oxcategories__oxtitle->value );
                }
            }
        }
        return $this->_sCatPathString;
    }

    /**
     * Returns current view meta description data
     *
     * @param string $sMeta     category path
     * @param int    $iLength   max length of result, -1 for no truncation
     * @param bool   $blDescTag if true - performs additional dublicate cleaning
     *
     * @return  string  $sString    converted string
     */
    protected function _prepareMetaDescription( $sMeta, $iLength = 1024, $blDescTag = false )
    {
        // using language constant ..
        $sDescription = oxLang::getInstance()->translateString( 'ALIST_META_DESCRIPTION_PREFIX' );

        // appending parent title
        if ( $oCategory = $this->getActCategory() ) {
            if ( ( $oParent = $oCategory->getParentCategory() ) ) {
                $sDescription .= " {$oParent->oxcategories__oxtitle->value} -";
            }

            // adding cateogry title
            $sDescription .= " {$oCategory->oxcategories__oxtitle->value}.";
        }

        // and final component ..
        if ( ( $sSuffix = $this->getConfig()->getActiveShop()->oxshops__oxstarttitle->value ) ) {
            $sDescription .= " {$sSuffix}";
        }

        // making safe for output
        $sDescription = getStr()->cleanStr($sDescription);
        return trim( strip_tags( getStr()->html_entity_decode( $sDescription ) ) );
    }

    /**
     * Template variable getter. Returns meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $sMeta = parent::getMetaDescription();

        if ( $sTitlePageSuffix = $this->getTitlePageSuffix() ) {
            if ( $sMeta ) {
                $sMeta .= ", ";
            }
            $sMeta .= $sTitlePageSuffix;
        }

        return $sMeta;
    }

    /**
     * Metatags - description and keywords - generator for search
     * engines. Uses string passed by parameters, cleans HTML tags,
     * string dublicates, special chars. Also removes strings defined
     * in $myConfig->aSkipTags (Admin area).
     *
     * @param string $sMeta     category path
     * @param int    $iLength   max length of result, -1 for no truncation
     * @param bool   $blDescTag if true - performs additional dublicate cleaning
     *
     * @return  string  $sString    converted string
     */
    protected function _collectMetaDescription( $sMeta, $iLength = 1024, $blDescTag = false )
    {
        //formatting description tag
        $sAddText = ( $oCategory = $this->getActCategory() ) ? trim( $oCategory->getLongDesc() ):'';
        $aArticleList = $this->getArticleList();
        if ( !$sAddText && count($aArticleList)) {
            foreach ( $aArticleList as $oArticle ) {
                if ( $sAddText ) {
                    $sAddText .= ', ';
                }
                $sAddText .= $oArticle->oxarticles__oxtitle->value;
            }
        }

        if ( !$sMeta ) {
            $sMeta = trim( $this->_getCatPathString() );
        }

        if ( $sMeta ) {
            $sMeta = "{$sMeta} - {$sAddText}";
        } else {
            $sMeta = $sAddText;
        }

        return parent::_prepareMetaDescription( $sMeta, $iLength, $blDescTag );
    }

    /**
     * Returns current view keywords seperated by comma
     *
     * @param string $sKeywords               data to use as keywords
     * @param bool   $blRemoveDuplicatedWords remove dublicated words
     *
     * @return string
     */
    protected function _prepareMetaKeyword( $sKeywords, $blRemoveDuplicatedWords = true )
    {
        $sKeywords = '';
        if ( ( $oCategory = $this->getActCategory() ) ) {
            $aKeywords = array();

            if ( $oCatTree = $this->getCategoryTree() ) {
                foreach ( $oCatTree->getPath() as $oCat ) {
                    $aKeywords[] = trim( $oCat->oxcategories__oxtitle->value );
                }
            }

            if ( count( $aKeywords ) > 0 ) {
                $sKeywords = implode( ", ", $aKeywords );
            }

            $aSubCats  = $oCategory->getSubCats();
            if ( is_array( $aSubCats ) ) {
                foreach ( $aSubCats as $oSubCat ) {
                    $sKeywords .= ', '.$oSubCat->oxcategories__oxtitle->value;
                }
            }
        }

        $sKeywords = parent::_prepareMetaDescription( $sKeywords, -1, $blRemoveDuplicatedWords );

        return trim( $sKeywords );
    }

    /**
     * Creates a string of keyword filtered by the function prepareMetaDescription and without any duplicates
     * additional the admin defined strings are removed
     *
     * @param string $sKeywords category path
     *
     * @return string
     */
    protected function _collectMetaKeyword( $sKeywords )
    {
        $iMaxTextLength = 60;
        $sText = '';

        if ( count( $aArticleList = $this->getArticleList() ) ) {
            $oStr = getStr();
            foreach ( $aArticleList as $oProduct ) {
                $sDesc = strip_tags( trim( $oStr->strtolower( $oProduct->getArticleLongDesc()->value ) ) );

                //removing dots from string (they are not cleaned up during general string cleanup)
                $sDesc = $oStr->preg_replace( "/\./", " ", $sDesc );

                if ( $oStr->strlen( $sDesc ) > $iMaxTextLength ) {
                    $sMidText = $oStr->substr( $sDesc, 0, $iMaxTextLength );
                    $sDesc    = $oStr->substr( $sMidText, 0, ( $oStr->strlen( $sMidText ) - $oStr->strpos( strrev( $sMidText ), ' ' ) ) );
                }
                if ( $sText ) {
                    $sText .= ', ';
                }
                $sText .= $sDesc;
            }
        }

        if ( !$sKeywords ) {
            $sKeywords = $this->_getCatPathString();
        }

        if ( $sKeywords ) {
            $sText = "{$sKeywords}, {$sText}";
        }

        return parent::_prepareMetaKeyword( $sText );
    }

    /**
     * Assigns Template name ($this->_sThisTemplate) for article list
     * preview. Name of template can be defined in admin or passed by
     * URL ("tpl" variable).
     *
     * @return string
     */
    public function getTemplateName()
    {
        // assign template name
        if ( ( $sTplName = basename( oxConfig::getParameter( 'tpl' ) ) ) ) {
            $this->_sThisTemplate = $sTplName;
        } elseif ( ( $oCategory = $this->getActCategory() ) && $oCategory->oxcategories__oxtemplate->value ) {
            $this->_sThisTemplate = $oCategory->oxcategories__oxtemplate->value;
        }

        return $this->_sThisTemplate;
    }

    /**
     * Adds page number parameter to current Url and returns formatted url
     *
     * @param string $sUrl  url to append page numbers
     * @param int    $iPage current page number
     * @param int    $iLang requested language
     *
     * @return string
     */
    protected function _addPageNrParam( $sUrl, $iPage, $iLang = null)
    {
        if ( oxUtils::getInstance()->seoIsActive() && ( $oCategory = $this->getActCategory() ) ) {
            if ( $iPage ) {
                // only if page number > 0
                $sUrl = $oCategory->getBaseSeoLink( $iLang, $iPage );
            }
        } else {
            $sUrl = parent::_addPageNrParam( $sUrl, $iPage, $iLang );
        }
        return $sUrl;
    }

    /**
     * Returns true if we have category
     *
     * @return bool
     */
    protected function _isActCategory()
    {
        return $this->_blIsCat;
    }

    /**
     * Generates Url for page navigation
     *
     * @return string
     */
    public function generatePageNavigationUrl( )
    {
        if ( ( oxUtils::getInstance()->seoIsActive() && ( $oCategory = $this->getActCategory() ) ) ) {
            return $oCategory->getLink();
        }
        return parent::generatePageNavigationUrl( );
    }

    /**
     * Returns SQL sorting string with additional checking if category has its own sorting configuration
     *
     * @param string $sCnid sortable item id
     *
     * @return string
     */
    public function getSorting( $sCnid )
    {
        // category has own sorting
        $aSorting = parent::getSorting( $sCnid );
        $oActCat = $this->getActCategory();
        if ( !$aSorting && $oActCat && $oActCat->oxcategories__oxdefsort->value ) {
            $sSortBy  = $oActCat->oxcategories__oxdefsort->value;
            $sSortDir = ( $oActCat->oxcategories__oxdefsortmode->value ) ? "desc" : null;

            $this->setItemSorting( $sCnid, $sSortBy, $sSortDir );
            $aSorting = array ( 'sortby' => $sSortBy, 'sortdir' => $sSortDir );
        }
        return $aSorting;
    }

    /**
     * Returns title suffix used in template
     *
     * @return string
     */
    public function getTitleSuffix()
    {
        if ( $this->getActCategory()->oxcategories__oxshowsuffix->value ) {
            return $this->getConfig()->getActiveShop()->oxshops__oxtitlesuffix->value;
        }
    }

    /**
     * Returns title page suffix used in template
     *
     * @return string
     */
    public function getTitlePageSuffix()
    {
        if ( ( $iPage = $this->getActPage() ) ) {
            return oxLang::getInstance()->translateString( 'INC_HEADER_TITLEPAGE' ). ( $iPage + 1 );
        }
    }

    /**
     * returns object, assosiated with current view.
     * (the object that is shown in frontend)
     *
     * @param int $iLang language id
     *
     * @return object
     */
    protected function _getSubject( $iLang )
    {
        return $this->getActCategory();
    }

    /**
     * Template variable getter. Returns array of attribute values
     * we do have here in this category
     *
     * @return array
     */
    public function getAttributes()
    {
        $this->_aAttributes = false;
        if ( ( $oCategory = $this->getActCategory() ) ) {
            $aAttributes = $oCategory->getAttributes();
            if ( count( $aAttributes ) ) {
                $this->_aAttributes = $aAttributes;
            }
        }

        return $this->_aAttributes;
    }

    /**
     * Template variable getter. Returns category's article list
     *
     * @return array
     */
    public function getArticleList()
    {
        if ( $this->_aArticleList === null ) {
            if ( /*$this->_isActCategory() &&*/ ( $oCategory = $this->getActCategory() ) ) {
                $aArticleList = $this->_loadArticles( $oCategory );
                if ( count( $aArticleList ) ) {
                    $this->_aArticleList = $aArticleList;
                }
            }
        }

        return $this->_aArticleList;
    }

    /**
     * Template variable getter. Returns recommendation list
     *
     * @return object
     */
    public function getSimilarRecommLists()
    {
        if (!$this->getViewConfig()->getShowListmania()) {
            return false;
        }

        if ( $this->_oRecommList === null ) {
            $this->_oRecommList = false;
            if ( $aCatArtList = $this->getArticleList() ) {
                $oRecommList = oxNew('oxrecommlist');
                $this->_oRecommList = $oRecommList->getRecommListsByIds( $aCatArtList->arrayKeys());
            }
        }
        return $this->_oRecommList;
    }

    /**
     * Template variable getter. Returns category path
     *
     * @return string
     */
    public function getCatTreePath()
    {
        if ( $this->_sCatTreePath === null ) {
             $this->_sCatTreePath = false;
            // category path
            if ( $oCatTree = $this->getCategoryTree() ) {
                $this->_sCatTreePath = $oCatTree->getPath();
            }
        }
        return $this->_sCatTreePath;
    }

    /**
     * Template variable getter. Returns category path array
     *
     * @return array
     */
    public function getTreePath()
    {
        if ( $oCatTree = $this->getCategoryTree() ) {
            return $oCatTree->getPath();
        }
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = array();

        if ( 'oxmore' == oxConfig::getParameter( 'cnid' ) ) {
            $aPath = array();
            $aPath['title'] = oxLang::getInstance()->translateString( 'PAGE_PRODUCT_MORECATEGORIES', oxLang::getInstance()->getBaseLanguage(), false );
            $aPath['link']  = $this->getLink();

            $aPaths[] = $aPath;

            return $aPaths;
        }

        if ( ($oCatTree = $this->getCategoryTree()) && ($oCatPath = $oCatTree->getPath()) ) {
            foreach ( $oCatPath as $oCat ) {
                $aCatPath = array();

                $aCatPath['link'] = $oCat->getLink();
                $aCatPath['title'] = $oCat->oxcategories__oxtitle->value;

                $aPaths[] = $aCatPath;
            }
        }

        return $aPaths;
    }

    /**
     * Template variable getter. Returns true if category has active
     * subcategories.
     *
     * @return bool
     */
    public function hasVisibleSubCats()
    {
        if ( $this->_blHasVisibleSubCats === null ) {
            $this->_blHasVisibleSubCats = false;
            if ( $oClickCat = $this->getActCategory() ) {
                $this->_blHasVisibleSubCats = $oClickCat->getHasVisibleSubCats();
            }
        }
        return $this->_blHasVisibleSubCats;
    }

    /**
     * Template variable getter. Returns list of subategories.
     *
     * @return array
     */
    public function getSubCatList()
    {
        if ( $this->_aSubCatList === null ) {
            $this->_aSubCatList = array();
            if ( $oClickCat = $this->getActCategory() ) {
                $this->_aSubCatList = $oClickCat->getSubCats();
            }
        }

        return $this->_aSubCatList;
    }

    /**
     * Template variable getter. Returns page navigation
     *
     * @return object
     */
    public function getPageNavigation()
    {
        if ( $this->_oPageNavigation === null ) {
            $this->_oPageNavigation = $this->generatePageNavigation();
        }
        return $this->_oPageNavigation;
    }

    /**
     * Template variable getter. Returns category title.
     *
     * @return string
     */
    public function getTitle()
    {
        if ( $this->_sCatTitle === null ) {
            $this->_sCatTitle = false;
            if ( ( $oCategory = $this->getActCategory() ) ) {
                $this->_sCatTitle = $oCategory->oxcategories__oxtitle->value;
            }
        }
        return $this->_sCatTitle;
    }

    /**
     * Template variable getter. Returns Top 5 article list
     *
     * @return array
     */
    public function getTop5ArticleList()
    {
        if ( $this->_aTop5ArticleList === null ) {
            $this->_aTop5ArticleList = false;
            $myConfig = $this->getConfig();
            if ( $myConfig->getConfigParam( 'bl_perfLoadAktion' ) && $this->_isActCategory() ) {
                // top 5 articles
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadTop5Articles();
                if ( $oArtList->count() ) {
                    $this->_aTop5ArticleList = $oArtList;
                }
            }
        }
        return $this->_aTop5ArticleList;
    }

    /**
     * Template variable getter. Returns bargain article list
     *
     * @return array
     */
    public function getBargainArticleList()
    {
        if ( $this->_aBargainArticleList === null ) {
            $this->_aBargainArticleList = array();
            if ( $this->getConfig()->getConfigParam( 'bl_perfLoadAktion' ) && $this->_isActCategory() ) {
                $oArtList = oxNew( 'oxarticlelist' );
                $oArtList->loadAktionArticles( 'OXBARGAIN' );
                if ( $oArtList->count() ) {
                    $this->_aBargainArticleList = $oArtList;
                }
            }
        }
        return $this->_aBargainArticleList;
    }

    /**
     * Template variable getter. Returns active search
     *
     * @return oxcategory
     */
    public function getActiveCategory()
    {
        return $this->getActCategory();
    }

    /**
     * Returns view canonical url
     *
     * @return string
     */
    public function getCanonicalUrl()
    {
        if ( ( $oCategory = $this->getActiveCategory() ) ) {
            $oUtils = oxUtilsUrl::getInstance();
            if ( oxUtils::getInstance()->seoIsActive() ) {
                $sUrl = $oUtils->prepareCanonicalUrl( $oCategory->getBaseSeoLink( $oCategory->getLanguage(), $this->getActPage() ) );
            } else {
                $sUrl = $oUtils->prepareCanonicalUrl( $oCategory->getBaseStdLink( $oCategory->getLanguage(), $this->getActPage() ) );
            }
            return $sUrl;
        }
    }

    /**
     * Returns cofig prameters blShowListDisplayType value
     *
     * @return boolean
     */
    public function canSelectDisplayType()
    {
        return $this->getConfig()->getConfigParam( 'blShowListDisplayType' );
    }

    /**
     * Get list articles pages count
     *
     * @return int
     */
    public function getPageCount()
    {
        return $this->_iCntPages;
    }

    /**
     * Should "More tags" link be visible.
     *
     * @return bool
     */
    public function isMoreTagsVisible()
    {
        return true;
    }
}
