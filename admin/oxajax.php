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
 * @package   admin
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: oxajax.php 40034 2011-11-18 07:54:26Z arvydas.vapsva $
 */

// shop path for includes
if ( !function_exists( 'getShopBasePath' )) {
    /**
     * Returns shop base path.
     *
     * @return string
     */
    function getShopBasePath()
    {
        return dirname(__FILE__).'/../';
    }
}

//to load the session correctly
if ( !function_exists( 'isAdmin' )) {
    /**
     * Method used to override default isAdmin. It simulates admin mode. Returns true
     *
     * @return bool
     */
    function isAdmin()
    {
        return true;
    }
}

require_once getShopBasePath() . 'core/oxsupercfg.php';

/**
 * AJAX call processor class
 */
class ajaxListComponent extends oxSuperCfg
{
    /**
     * Possible sort keys
     *
     * @var array
     */
    protected $_aPosDir = array( 'asc', 'desc' );

    /**
     * Array of DB table columns which are loaded from DB
     *
     * @var array
     */
    protected $_aColumns = array();

    /**
     * Default limit of DB entries to load from DB
     *
     * @var int
     */
    protected $_iSqlLimit = 2500;

    /**
     * Ajax container name
     *
     * @var string
     */
    protected $_sContainer = null;

    /**
     * If true extended column selection will be build
     * (currently checks if variants must be shown in lists and column name is "oxtitle")
     *
     * @var bool
     */
    protected $_blAllowExtColumns = false;

    /**
     * Initializes AJAX columns
     *
     * @param array $aColumns Array of DB table columns which are loaded from DB
     *
     * @return null
     */
    public function init( $aColumns )
    {
        $this->_aColumns = $aColumns;
    }

    /**
     * Required data fields are returned by indexes/position in _aColumns array. This method
     * translates "table_name.col_name" into index definition and fetches request data according
     * to it. This is usefull while using AJAX across versions.
     *
     * @param string $sId "table_name.col_name"
     *
     * @return array
     */
    protected function _getActionIds( $sId )
    {
        $aColumns = $this->_getColNames();
        foreach ( $aColumns as $iPos => $aCol ) {
            if ( isset($aCol[4]) && $aCol[4] == 1 && $sId == $aCol[1].'.'.$aCol[0] ) {
                return oxConfig::getParameter( '_'.$iPos );
            }
        }
    }

    /**
     * AJAX container name setter
     *
     * @param string $sName name of container
     *
     * @return null
     */
    public function setName( $sName )
    {
        $this->_sContainer = $sName;
    }

    /**
     * Empty function, developer should override this method according requirements
     *
     * @return string
     */
    protected function _getQuery()
    {
        return '';
    }

    /**
     * Return fully formatted query for data loading
     *
     * @param string $sQ part of initial query
     *
     * @return string
     */
    protected function _getDataQuery( $sQ )
    {
        return 'select ' . $this->_getQueryCols() . $sQ;
    }

    /**
     * Return fully formatted query for data records count
     *
     * @param string $sQ part of initial query
     *
     * @return string
     */
    protected function _getCountQuery( $sQ )
    {
        return 'select count( * ) ' . $sQ;
    }

    /**
     * AJAX call processor function
     *
     * @param string $sFunction name of action to execute (optional)
     *
     * @return null
     */
    public function processRequest( $sFunction = null )
    {
        if ( $sFunction ) {
            $this->$sFunction();
        } else {
            $sQAdd = $this->_getQuery();

            // formatting SQL queries
            $sQ      = $this->_getDataQuery( $sQAdd );
            $sCountQ = $this->_getCountQuery( $sQAdd );

            $this->_outputResponse( $this->_getData( $sCountQ, $sQ ) );
        }
    }

    /**
     * Returns column id to sort
     *
     * @return int
     */
    protected function _getSortCol()
    {
        $aVisibleNames = $this->_getVisibleColNames();
        $iCol = oxConfig::getParameter( 'sort' );
        $iCol = $iCol?( ( int ) str_replace( '_', '', $iCol )):0;
        $iCol = ( !isset( $aVisibleNames[$iCol] ) )?0:$iCol;

        return $iCol;
    }


    /**
     * Returns array of cotainer DB cols which must be loaded. If id is not
     * passed - all possible containers cols will be returned
     *
     * @param string $sId container id (optional)
     *
     * @return array
     */
    protected function _getColNames( $sId = null )
    {
        if ( $sId === null ) {
            $sId = oxConfig::getParameter( 'cmpid' );
        }

        if ( $sId && isset( $this->_aColumns[$sId] ) ) {
            return $this->_aColumns[$sId];
        }

        return $this->_aColumns;
    }

    /**
     * Returns array of identifiers which are used as identifiers for specific actions
     * in AJAX and further in this processor class
     *
     * @return array
     */
    protected function _getIdentColNames()
    {
        $aColNames = $this->_getColNames();
        $aCols = array();
        foreach ( $aColNames as $iKey =>  $aCol ) {
            if ( $aCol[4] ) // ident ?
                $aCols[$iKey] = $aCol;
        }
        return $aCols;
    }

    /**
     * Returns array of col names which are requested by AJAX call and will be fetched from DB
     *
     * @return array
     */
    protected function _getVisibleColNames()
    {
        $aColNames = $this->_getColNames();
        $aUserCols = oxConfig::getParameter( 'aCols' );
        $aVisibleCols = array();

        // user defined some cols to load ?
        if ( is_array( $aUserCols ) ) {
            foreach ( $aUserCols as $iKey => $sCol ) {
                $iCol = ( int ) str_replace( '_', '', $sCol );
                if ( isset( $aColNames[$iCol] ) && !$aColNames[$iCol][4] ) {
                    $aVisibleCols[$iCol] = $aColNames[$iCol];
                }
            }
        }

        // no user defined valid cols ? setting defauls ..
        if ( !count( $aVisibleCols ) ) {
            foreach ( $aColNames as $sName => $aCol ) {
                if ( $aCol[1] && !$aColNames[$sName][4]  ) // visible ?
                    $aVisibleCols[$sName] = $aCol;
            }
        }

        return $aVisibleCols;
    }

    /**
     * Formats and returns chunk of SQL query string with definition of
     * fields to load from DB
     *
     * @return string
     */
    protected function _getQueryCols()
    {
        $sQ  = $this->_buildColsQuery( $this->_getVisibleColNames(), false ).", ";
        $sQ .= $this->_buildColsQuery( $this->_getIdentColNames() );

        return " $sQ ";
    }

    /**
     * Builds column selection query
     *
     * @param array $aIdentCols  columns
     * @param bool  $blIdentCols if true, means ident columns part is build
     *
     * @return string
     */
    protected function _buildColsQuery( $aIdentCols, $blIdentCols = true )
    {
        $sQ = '';
        foreach ( $aIdentCols as $iCnt => $aCol ) {
            if ( $sQ ) {
                $sQ .= ', ';
            }

            $sViewTable = $this->_getViewName( $aCol[1] );
            if ( !$blIdentCols && $this->_isExtendedColumn( $aCol[0] ) ) {
                $sQ .= $this->_getExtendedColQuery( $sViewTable, $aCol[0], $iCnt );
            } else {
                $sQ .=  $sViewTable. '.' . $aCol[0] . ' as _' . $iCnt;
            }
        }

        return $sQ;
    }

    /**
     * Checks if current column is extended
     * (currently checks if variants must be shown in lists and column name is "oxtitle")
     *
     * @param string $sColumn column name
     *
     * @return bool
     */
    protected function _isExtendedColumn( $sColumn )
    {
        $blBuild = false;
        if ( $this->_blAllowExtColumns && oxConfig::getInstance()->getConfigParam( 'blVariantsSelection' ) && $sColumn == 'oxtitle' ) {
            $blBuild = true;
        }

        return $blBuild;
    }

    /**
     * Returns extended query part for given view/column combination
     * (if variants must be shown in lists and column name is "oxtitle")
     *
     * @param string $sViewTable view name
     * @param string $sColumn    column name
     * @param int    $iCnt       column count
     *
     * @return string
     */
    protected function _getExtendedColQuery( $sViewTable, $sColumn, $iCnt )
    {
        // multilanguage
        $sVarSelect = "$sViewTable.oxvarselect";
        return " IF( {$sViewTable}.{$sColumn} != '', {$sViewTable}.{$sColumn}, CONCAT((select oxart.{$sColumn} from {$sViewTable} as oxart where oxart.oxid = {$sViewTable}.oxparentid),', ',{$sVarSelect})) as _{$iCnt}";
    }

    /**
     * Formats and returns part of SQL query for sorting
     *
     * @return string
     */
    protected function _getSorting()
    {
        return ' order by _' . $this->_getSortCol() . ' '.$this->_getSortDir().' ';
    }

    /**
     * Returns part of SQL query for limiting number of entries from DB
     *
     * @param int $iStart start position
     *
     * @return string
     */
    protected function _getLimit( $iStart )
    {
        $iLimit = (int) oxConfig::getParameter("results");
        $iLimit = $iLimit?$iLimit:$this->_iSqlLimit;

        return " limit $iStart, $iLimit ";
    }

    /**
     * Returns part of SQL query for filtering DB data
     *
     * @return string
     */
    protected function _getFilter()
    {
        $sQ = '';
        $aFilter = oxConfig::getParameter( 'aFilter' );
        if ( is_array( $aFilter ) && count( $aFilter ) ) {
            $aCols = $this->_getVisibleColNames();
            $blSep = false;
            $oDb = oxDb::getDb();
            $oLang = oxLang::getInstance();
            $oStr = getStr();

            $blIsUtf  = $this->getConfig()->isUtf();
            $sCharset = $oLang->translateString( "charset" );

            foreach ( $aFilter as $sCol => $sValue ) {

                // skipping empty filters
                if ( !$sValue ) {
                    continue;
                }

                $iCol = (int) str_replace( '_', '', $sCol );
                if ( isset( $aCols[ $iCol ] ) ) {
                    if ( $sQ )
                        $sQ .= ' and ';

                    if ( !$blIsUtf ) {
                        $sValue = iconv( 'UTF-8', $sCharset, $sValue );
                    }

                    // escaping special characters
                    $sValue = str_replace( array( '%', '_' ), array( '\%', '\_' ), $sValue );

                    // possibility to search in the middle ..
                    $sValue = $oStr->preg_replace( '/^\*/', '%', $sValue );

                    $sQ .= $this->_getViewName( $aCols[ $iCol ][1] ) . '.' . $aCols[ $iCol ][0];
                    $sQ .= ' like ' . $oDb->Quote( $sValue . '%' ). ' ';
                }

            }
        }
        return $sQ;
    }

    /**
     * Adds filter SQL to current query
     *
     * @param string $sQ query to add filter condition
     *
     * @return string
     */
    protected function _addFilter( $sQ )
    {
        if ( $sQ && ( $sFilter = $this->_getFilter() ) ) {
            $sQ .= ( ( stristr( $sQ, 'where' ) === false )?'where':' and ' ) . $sFilter;
        }
        return $sQ;
    }

    /**
     * Returns DB records as plain indexed array
     *
     * @param string $sQ SQL query
     *
     * @return array
     */
    protected function _getAll( $sQ )
    {
        $aReturn = array();
        $rs = oxDb::getDb()->execute( $sQ );
        if ($rs != false && $rs->recordCount() > 0) {
            while (!$rs->EOF) {
                $aReturn[] = $rs->fields[0];
                $rs->moveNext();
            }
        }
        return $aReturn;
    }

    /**
     * Checks user input and returns SQL sorting direction key
     *
     * @return string
     */
    protected function _getSortDir()
    {
        $sDir = oxConfig::getParameter( 'dir' );
        if ( !in_array( $sDir, $this->_aPosDir ) ) {
            $sDir = $this->_aPosDir[0];
        }

        return $sDir;
    }

    /**
     * Returns position from where data must be loaded
     *
     * @return int
     */
    protected function _getStartIndex()
    {
        return (int) oxConfig::getParameter( 'startIndex' );
    }

    /**
     * Returns amount of records which can be found according to passed SQL query
     *
     * @param string $sQ SQL query
     *
     * @return int
     */
    protected function _getTotalCount( $sQ )
    {
        // TODO: implement caching here

        // we can cache total count ...

        // $sCountCacheKey = md5( $sQ );

        return (int) oxDb::getDb()->getOne( $sQ );
    }

    /**
     * Returns array with DB records
     *
     * @param string $sQ SQL query
     *
     * @return array
     */
    protected function _getDataFields( $sQ )
    {
        return oxDb::getDb(true)->getArray( $sQ );
    }

    /**
     * Outputs JSON encoded data
     *
     * @param array $aData data to output
     *
     * @return null
     */
    protected function _outputResponse( $aData )
    {
        if ( !$this->getConfig()->isUtf() ) {
            // TODO: improve this
            if ( is_array( $aData['records'] ) && ( $iRecSize = count( $aData['records'] ) ) ) {
                $aKeys = array_keys( current( $aData['records'] ) );
                $iKeySize = count( $aKeys );
                $sCharset = oxLang::getInstance()->translateString("charset");
                for ( $i = 0; $i < $iRecSize; $i++ ) {
                    for ( $c = 0; $c < $iKeySize; $c++ ) {
                        $aData['records'][$i][$aKeys[$c]] = iconv( $sCharset, "UTF-8", $aData['records'][$i][$aKeys[$c]] );
                    }
                }
            }
        }

        $this->_output( json_encode( $aData ) );
    }

    /**
     * Echoes given string
     *
     * @param string $sOut string to echo
     *
     * @return null
     */
    protected function _output( $sOut )
    {
        echo $sOut;
    }

     /**
     * Return the view name of the given table if a view exists, otherwise the table name itself
     *
     * @param string $sTable table name
     *
     * @return string
     */
    protected function _getViewName( $sTable )
    {
        return getViewName( $sTable, oxConfig::getParameter('editlanguage'));
    }

    /**
     * Formats data array which later will be processed by _outputResponse method
     *
     * @param string $sCountQ count query
     * @param string $sQ      data load query
     *
     * @return array
     */
    protected function _getData( $sCountQ, $sQ )
    {
        $sQ = $this->_addFilter( $sQ );
        $sCountQ = $this->_addFilter( $sCountQ );

        $aResponse['startIndex'] = $iStart = $this->_getStartIndex();
        $aResponse['sort'] = '_' . $this->_getSortCol();
        $aResponse['dir']  = $this->_getSortDir();

        $iDebug = $this->getConfig()->getConfigParam( 'iDebug' );
        if ( $iDebug ) {
            $aResponse['countsql'] = $sCountQ;
        }

        $aResponse['records'] = array();

        // skip further execution if no records were found ...
        if ( ( $iTotal = $this->_getTotalCount( $sCountQ ) ) ) {
            $sQ .= $this->_getSorting();
            $sQ .= $this->_getLimit( $iStart );

            if ( $iDebug ) {
                $aResponse['datasql'] = $sQ;
            }

            $aResponse['records'] = $this->_getDataFields( $sQ );
        }

        $aResponse['totalRecords'] = $iTotal;

        return $aResponse;
    }

    /**
     * Marks article seo url as expired
     *
     * @param array $aArtIds article id's
     * @param array $aCatIds ids if categories, which must be removed from oxseo
     *
     * @return null
     */
    public function resetArtSeoUrl( $aArtIds, $aCatIds = null )
    {
        if ( empty( $aArtIds ) ) {
            return;
        }

        if ( !is_array( $aArtIds ) ) {
            $aArtIds = array( $aArtIds );
        }

        $blCleanCats = false;
        if ( $aCatIds ) {
            if ( !is_array( $aCatIds ) ) {
                $aCatIds = array( $aCatIds );
            }
            $sShopId = $this->getConfig()->getShopId();
            $sQ = "delete from oxseo where oxtype='oxarticle' and oxobjectid='%s' and
                   oxshopid='{$sShopId}' and oxparams in ('" . implode( ",", oxDb::getInstance()->quoteArray( $aCatIds ) ) . "')";
            $oDb = oxDb::getDb();
            $blCleanCats = true;
        }

        $sShopId = $this->getConfig()->getShopId();
        foreach ( $aArtIds as $sArtId ) {
            oxSeoEncoder::getInstance()->markAsExpired( $sArtId, $sShopId, 1, null, "oxtype='oxarticle'" );
            if ( $blCleanCats ) {
                $oDb->execute( sprintf( $sQ, $sArtId ) );
            }
        }
    }

    /**
     * Reset output cache
     *
     * @return null
     */
    public function resetContentCache()
    {
        $blDeleteCacheOnLogout = $this->getConfig()->getConfigParam( 'blClearCacheOnLogout' );


            if ( !$blDeleteCacheOnLogout ) {
                oxUtils::getInstance()->oxResetFileCache();
            }
    }

    /**
     * Resets counters values from cache. Resets price category articles, category articles,
     * vendor articles, manufacturer articles count.
     *
     * @param string $sCounterType counter type
     * @param string $sValue       reset value
     *
     * @return null
     */
    public function resetCounter( $sCounterType, $sValue = null )
    {
        $blDeleteCacheOnLogout = $this->getConfig()->getConfigParam( 'blClearCacheOnLogout' );

        if ( !$blDeleteCacheOnLogout ) {
            $myUtilsCount = oxUtilsCount::getInstance();
            switch ( $sCounterType ) {
                case 'priceCatArticle':
                    $myUtilsCount->resetPriceCatArticleCount( $sValue );
                    break;
                case 'catArticle':
                    $myUtilsCount->resetCatArticleCount( $sValue );
                    break;
                case 'vendorArticle':
                    $myUtilsCount->resetVendorArticleCount( $sValue );
                    break;
                case 'manufacturerArticle':
                    $myUtilsCount->resetManufacturerArticleCount( $sValue );
                    break;
            }
        }
    }

}

// processing ..
$blAjaxCall = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
if ( $blAjaxCall ) {


    // Setting error reporting mode
    error_reporting( E_ALL ^ E_NOTICE);

    //setting basic configuration parameters
    ini_set('session.name', 'sid' );
    ini_set('session.use_cookies', 0 );
    ini_set('session.use_trans_sid', 0);
    ini_set('url_rewriter.tags', '');
    ini_set('magic_quotes_runtime', 0);

    // Generic utility method file.
    $sBasePath = getShopBasePath();
    include_once $sBasePath . 'modules/functions.php';
    include_once $sBasePath . 'core/oxfunctions.php';
    include_once $sBasePath . 'core/adodblite/adodb.inc.php';
    include_once $sBasePath . 'core/oxconfig.php';
    include_once $sBasePath . 'core/oxsupercfg.php';


    include_once $sBasePath . "core/oxutils.php";

    $myConfig = oxConfig::getInstance();

    // Includes Utility module.
    $sUtilModule = $myConfig->getConfigParam( 'sUtilModule' );
    if ( $sUtilModule && file_exists( getShopBasePath()."modules/".$sUtilModule ) )
        include_once getShopBasePath()."modules/".$sUtilModule;

    $myConfig->setConfigParam( 'blAdmin', true );
    $myConfig->setConfigParam( 'blTemplateCaching', false );

    // authorization
    if ( !(oxSession::getInstance()->checkSessionChallenge() && count(oxUtilsServer::getInstance()->getOxCookie()) && oxUtils::getInstance()->checkAccessRights())) {
        header( "location:index.php");
        oxUtils::getInstance()->showMessageAndExit( "" );
    }

    if ( $sContainer = oxConfig::getParameter( 'container' ) ) {

        $sContainer = trim(strtolower( basename( $sContainer ) ));
        $aColumns = array();

        include_once 'inc/'.$sContainer.'.inc.php';

        //$oAjaxComponent = new ajaxcomponent( $aColumns );
        $oAjaxComponent = oxNew("ajaxcomponent");
        $oAjaxComponent->init($aColumns);

        $oAjaxComponent->setName( $sContainer );
        $oAjaxComponent->processRequest( oxConfig::getParameter( 'fnc' ) );

    } else {

    }

    $myConfig->pageClose();

    // closing session handlers
    // session_write_close();
    return;
}