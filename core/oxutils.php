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
 * @version   SVN: $Id: oxutils.php 42933 2012-03-16 09:49:42Z linas.kukulskis $
 */

/**
 * Includes Smarty engine class.
 */
require_once getShopBasePath()."core/smarty/Smarty.class.php";

/**
 * general utils class, used as a singelton
 *
 */
class oxUtils extends oxSuperCfg
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils instance
     */
    private static $_instance = null;

    /**
     * Cached currency precision
     *
     * @var int
     */
    protected $_iCurPrecision = null;

    /**
     * Some files, like object structure should not be deleted, because thay are changed rarely
     * and each regeneration eats additional page load time. This array keeps patterns of file
     * names which should not be deleted on regular cache cleanup
     *
     * @var string
     */
    protected $_sPermanentCachePattern = "/c_fieldnames_/";

    /**
     * File cache contents.
     *
     * @var array
     */
    protected $_aLockedFileHandles = array();

    /**
     * Local cache
     *
     * @var array
     */
    protected $_aFileCacheContents = array();

    /**
     * Search engine indicator
     *
     * @var bool
     */
    protected $_blIsSe = null;

    /**
     * resturns a single instance of this class
     *
     * @return oxUtils
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            self::$_instance = modInstances::getMod( __CLASS__ );
        }

        if ( !(self::$_instance instanceof oxUtils) ) {

            self::$_instance = oxNew( 'oxUtils' );

            if ( defined( 'OXID_PHP_UNIT' ) ) {
                modInstances::addMod( __CLASS__, self::$_instance);
            }
        }
        return self::$_instance;
    }

    /**
     * Statically cached data
     *
     * @var array
     */
    protected $_aStaticCache;

    /**
     * Seo mode marker - SEO is active or not
     *
     * @var bool
     */
    protected $_blSeoIsActive = null;

    /**
     * Strips magic quotes
     *
     * @return null
     */
    public function stripGpcMagicQuotes()
    {
        if (!get_magic_quotes_gpc()) {
            return;
        }
        $_REQUEST = self::_stripQuotes($_REQUEST);
        $_POST = self::_stripQuotes($_POST);
        $_GET = self::_stripQuotes($_GET);
        $_COOKIE = self::_stripQuotes($_COOKIE);
    }

    /**
     * OXID specific string manipulation method
     *
     * @param string $sVal string
     * @param string $sKey key
     *
     * @return string
     */
    public function strMan( $sVal, $sKey = null )
    {
        $sKey = $sKey ? $sKey : $this->getConfig()->getConfigParam('sConfigKey');
        $sVal = "ox{$sVal}id";

        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );
        $sVal = $this->strRot13( $sVal );
        $sVal = $sVal ^ $sKey;
        $sVal = base64_encode ( $sVal );
        $sVal = str_replace( "=", "!", $sVal );

        return "ox_$sVal";
    }

    /**
     * OXID specific string manipulation method
     *
     * @param string $sVal string
     * @param string $sKey key
     *
     * @return string
     */
    public function strRem( $sVal, $sKey = null )
    {
        $sKey = $sKey ? $sKey : $this->getConfig()->getConfigParam('sConfigKey');
        $sKey = str_repeat( $sKey, strlen( $sVal ) / strlen( $sKey ) + 5 );

        $sVal = substr( $sVal, 3 );
        $sVal = str_replace( '!', '=', $sVal );
        $sVal = base64_decode( $sVal );
        $sVal = $sVal ^ $sKey;
        $sVal = $this->strRot13( $sVal );

        return substr( $sVal, 2, -2 );
    }

    /**
     * Returns string witch "." symbols were replaced with "__".
     *
     * @param string $sName String to search replaceble char
     *
     * @return string
     */
    public function getArrFldName( $sName)
    {
        return str_replace( ".", "__", $sName);
    }

    /**
     * Takes a string and assign all values, returns array with values.
     *
     * @param string $sIn  Initial string
     * @param double $dVat Article VAT (optional)
     *
     * @return array
     */
    public function assignValuesFromText( $sIn, $dVat = null)
    {
        $aRet = array();
        $aPieces = explode( '@@', $sIn );
        while ( list( $sKey, $sVal ) = each( $aPieces ) ) {
            if ( $sVal ) {
                $aName = explode( '__', $sVal );
                if ( isset( $aName[0] ) && isset( $aName[1] ) ) {
                    $aRet[] = $this->_fillExplodeArray( $aName, $dVat );
                }
            }
        }
        return $aRet;
    }

    /**
     * Takes an array and builds again a string. Returns string with values.
     *
     * @param array $aIn Initial array of strings
     *
     * @return string
     */
    public function assignValuesToText( $aIn)
    {
        $sRet = "";
        reset( $aIn );
        while (list($sKey, $sVal) = each($aIn)) {
            $sRet .= $sKey;
            $sRet .= "__";
            $sRet .= $sVal;
            $sRet .= "@@";
        }
        return $sRet;
    }

    /**
     * Returns formatted currency string, according to formatting standards.
     *
     * @param string $sValue Formatted price
     *
     * @return string
     */
    public function currency2Float( $sValue)
    {
        $fRet = $sValue;
        $iPos = strrpos( $sValue, ".");
        if ($iPos && ((strlen($sValue)-1-$iPos) < 2+1)) {
            // replace decimal with ","
            $fRet = substr_replace( $fRet, ",", $iPos, 1);
        }
        // remove thousands
        $fRet = str_replace( array(" ","."), "", $fRet);

        $fRet = str_replace( ",", ".", $fRet);
        return (float) $fRet;
    }

    /**
     * Checks if current web client is Search Engine. Returns true on success.
     *
     * @param string $sClient user browser agent
     *
     * @return bool
     */
    public function isSearchEngine( $sClient = null )
    {

        if (!is_null($this->_blIsSe)) {
            return $this->_blIsSe;
        }

        startProfile("isSearchEngine");

        $myConfig = $this->getConfig();
        $blIsSe   = false;

        if ( !( $myConfig->getConfigParam( 'iDebug' ) && $this->isAdmin() ) ) {

            // caching
            $blIsSe = $myConfig->getGlobalParameter( 'blIsSearchEngine' );
            if ( !isset( $blIsSe ) ) {

                $aRobots = $myConfig->getConfigParam( 'aRobots' );
                $aRobots = is_array( $aRobots )?$aRobots:array();

                $aRobotsExcept = $myConfig->getConfigParam( 'aRobotsExcept' );
                $aRobotsExcept = is_array( $aRobotsExcept )?$aRobotsExcept:array();

                $sClient = $sClient?$sClient:strtolower( getenv( 'HTTP_USER_AGENT' ) );
                $blIsSe  = false;
                $aRobots = array_merge( $aRobots, $aRobotsExcept );
                foreach ( $aRobots as $sRobot ) {
                    if ( strpos( $sClient, $sRobot ) !== false ) {
                        $blIsSe = true;
                        break;
                    }
                }
                $myConfig->setGlobalParameter( 'blIsSearchEngine', $blIsSe );
            }
        }

        stopProfile("isSearchEngine");

        $this->_blIsSe = $blIsSe;

        return $blIsSe;
    }

    /**
     * User email validation function. Returns true if email is OK otherwise - false;
     * Syntax validation is performed only.
     *
     * @param string $sEmail user email
     *
     * @return bool
     */
    public function isValidEmail( $sEmail )
    {
        $blValid = true;
        if ( $sEmail != 'admin' ) {
            $sEmailTpl = "/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/i";
            $blValid = ( getStr()->preg_match( $sEmailTpl, $sEmail ) != 0 );
        }

        return $blValid;
    }

    /**
     * Clears Smarty cache data.
     *
     * @deprecated since v4.5.9 (2012-03-06); Not needed from 3.0
     *
     * @return null
     */
    public function rebuildCache()
    {
        // not needed from 3.0 on and unused <- MK: not correct, its used for example in shop_config.php, oxbase.php

        //$smarty  = & oxUtils::getInstance()->getSmarty();
        //$smarty->clear_all_cache();

        if ( function_exists( "UserdefinedRebuildCache")) {
            UserdefinedRebuildCache();
        }
    }

    /**
     * Parses profile configuration, loads stored info in cookie
     *
     * @param array $aInterfaceProfiles ($myConfig->getConfigParam( 'aInterfaceProfiles' ))
     *
     * @return null
     */
    public function loadAdminProfile($aInterfaceProfiles)
    {
        // improved #533
        // checking for available profiles list
        $aInterfaceProfiles = $aInterfaceProfiles;
        if ( is_array( $aInterfaceProfiles ) ) {
            //checking for previous profiles
            $sPrevProfile = oxUtilsServer::getInstance()->getOxCookie('oxidadminprofile');
            if (isset($sPrevProfile)) {
                $aPrevProfile = @explode("@", trim($sPrevProfile));
            }

            //array to store profiles
            $aProfiles = array();
            foreach ( $aInterfaceProfiles as $iPos => $sProfile) {
                $aProfileSettings = array($iPos, $sProfile);
                $aProfiles[] = $aProfileSettings;
            }
            // setting previous used profile as active
            if (isset($aPrevProfile[0]) && isset($aProfiles[$aPrevProfile[0]])) {
                $aProfiles[$aPrevProfile[0]][2] = 1;
            }

            oxSession::setVar("aAdminProfiles", $aProfiles);
            return $aProfiles;
        }
        return null;
    }

    /**
     * Rounds the value to currency cents
     *
     * @param string $sVal the value that should be rounded
     * @param object $oCur Currenncy Object
     *
     * @return float
     */
    public function fRound($sVal, $oCur = null)
    {
        startProfile('fround');

        //cached currency precision, this saves about 1% of execution time
        $iCurPrecision = null;
        if (! defined('OXID_PHP_UNIT')) {
            $iCurPrecision = $this->_iCurPrecision;
        }

        if (is_null($iCurPrecision)) {
            if ( !$oCur ) {
                $oCur = $this->getConfig()->getActShopCurrencyObject();
            }

            $iCurPrecision = $oCur->decimal;
            $this->_iCurPrecision = $iCurPrecision;
        }

        // this is a workaround for #36008 bug in php - incorrect round() & number_format() result (R)
        static $dprez = null;
        if (!$dprez) {
            $prez = @ini_get("precision");
            if (!$prez) {
                $prez = 9;
            }
            $dprez = pow(10, -$prez);
        }
        stopProfile('fround');

        return round($sVal + $dprez * ( $sVal >= 0 ? 1 : -1 ), $iCurPrecision);
    }

    /**
     * Stores something into static cache to avoid double loading
     *
     * @param string $sName    name of the content
     * @param mixed  $sContent the content
     * @param string $sKey     optional key, where to store the content
     *
     * @return null
     */
    public function toStaticCache( $sName, $sContent, $sKey = null )
    {
        // if it's an array then we add
        if ( $sKey ) {
            $this->_aStaticCache[$sName][$sKey] = $sContent;
        } else {
            $this->_aStaticCache[$sName] = $sContent;
        }
    }

    /**
     * Retrieves something from static cache
     *
     * @param string $sName name under which the content is stored in the satic cache
     *
     * @return mixed
     */
    public function fromStaticCache( $sName)
    {
        if ( isset( $this->_aStaticCache[$sName])) {
            return $this->_aStaticCache[$sName];
        }
        return null;
    }

    /**
     * Cleans all or specific data from static cache
     *
     * @param string $sCacheName Cache name
     *
     * @return null
     */
    public function cleanStaticCache($sCacheName = null)
    {
        if ($sCacheName) {
            unset($this->_aStaticCache[$sCacheName]);
        } else {
            $this->_aStaticCache = null;
        }
    }

    /**
     * Generates php file, which could later be loaded as include instead of paresed data.
     * Currenntly this method supports simple arrays only.
     *
     * @param string $sKey      Cache key
     * @param mixed  $mContents Cache contents. At this moment only simple array type is supported.
     *
     * @return null;
     */
    public function toPhpFileCache( $sKey, $mContents )
    {
        //only simple arrays are supported
        if ( is_array( $mContents ) && ( $sCachePath = $this->getCacheFilePath( $sKey, false, 'php' ) ) ) {

            // setting meta
            $this->setCacheMeta( $sKey, array( "serialize" => false, "cachepath" => $sCachePath ) );

            // caching..
            $this->toFileCache( $sKey, $mContents );
        }
    }

    /**
     * Includes cached php file and loads stored contents.
     *
     * @param string $sKey Cache key.
     *
     * @return null;
     */
    public function fromPhpFileCache( $sKey )
    {
        // setting meta
        $this->setCacheMeta( $sKey, array( "include" => true, "cachepath" => $this->getCacheFilePath( $sKey, false, 'php' ) ) );
        return $this->fromFileCache( $sKey );
    }

    /**
     * If available returns cache meta data array
     *
     * @param string $sKey meta data/cache key
     *
     * @return mixed
     */
    public function getCacheMeta( $sKey )
    {
        return isset( $this->_aFileCacheMeta[$sKey] ) ? $this->_aFileCacheMeta[$sKey] : false;
    }

    /**
     * Saves cache meta data (information)
     *
     * @param string $sKey  meta data/cache key
     * @param array  $aMeta meta data array
     *
     * @return null
     */
    public function setCacheMeta( $sKey, $aMeta )
    {
        // cache meta data
        $this->_aFileCacheMeta[$sKey] = $aMeta;
    }

    /**
     * Adds contents to cache contents by given key. Returns true on success.
     * All file caches are supposed to be written once by commitFileCache() method.
     *
     * @param string $sKey      Cache key
     * @param mixed  $mContents Contents to cache
     *
     * @return bool
     */
    public function toFileCache( $sKey, $mContents )
    {
        $this->_aFileCacheContents[$sKey] = $mContents;
        $aMeta = $this->getCacheMeta( $sKey );

        // looking for cache meta
        $sCachePath = isset( $aMeta["cachepath"] ) ? $aMeta["cachepath"] : $this->getCacheFilePath( $sKey );
        return ( bool ) $this->_lockFile( $sCachePath, $sKey );
    }

    /**
     * Fetches contents from file cache.
     *
     * @param string $sKey Cache key
     *
     * @return mixed
     */
    public function fromFileCache( $sKey )
    {
        if ( !array_key_exists( $sKey, $this->_aFileCacheContents ) ) {
            $sRes = null;

            $aMeta = $this->getCacheMeta( $sKey );
            $blInclude  = isset( $aMeta["include"] ) ? $aMeta["include"] : false;
            $sCachePath = isset( $aMeta["cachepath"] ) ? $aMeta["cachepath"] : $this->getCacheFilePath( $sKey );

            // trying to lock
            $this->_lockFile( $sCachePath, $sKey, LOCK_SH );

            clearstatcache();
            if ( is_readable( $sCachePath ) ) {
                $sRes = $blInclude ? $this->_includeFile( $sCachePath ) : $this->_readFile( $sCachePath );
            }

            // release lock
            $this->_releaseFile( $sKey, LOCK_SH );

            // caching
            $this->_aFileCacheContents[$sKey] = $sRes;
        }

        return $this->_aFileCacheContents[$sKey];
    }

    /**
     * Reads and returns cache file contents
     *
     * @param string $sFilePath cache fiel path
     *
     * @return string
     */
    protected function _readFile( $sFilePath )
    {
        $sRes = file_get_contents( $sFilePath );
        return $sRes ? unserialize( $sRes ) : null;
    }

    /**
     * Includes cache file
     *
     * @param string $sFilePath cache fiel path
     *
     * @return mixed
     */
    protected function _includeFile( $sFilePath )
    {
        $_aCacheContents = null;
        include $sFilePath;
        return $_aCacheContents;
    }

    /**
     * Serializes or writes php array for class file cache
     *
     * @param string $sKey      cache key
     * @param mixed  $mContents cache data
     *
     * @return mixed
     */
    protected function _processCache( $sKey, $mContents )
    {
        // looking for cache meta
        $aCacheMeta  = $this->getCacheMeta( $sKey );
        $blSerialize = isset( $aCacheMeta["serialize"] ) ? $aCacheMeta["serialize"] : true;

        if ( $blSerialize ) {
            $mContents = serialize( $mContents );
        } else {
            $mContents = "<?php\n//automatically generated file\n//" . date( "Y-m-d H:i:s" ) . "\n\n\$_aCacheContents = " . var_export( $mContents, true ) . "\n?>";
        }

        return $mContents;
    }

    /**
     * Writes all cache contents to file at once. This method was introduced due to possible
     * race conditions. Cache is cleand up after commit
     *
     * @return null;
     */
    public function commitFileCache()
    {
        if ( count( $this->_aLockedFileHandles[LOCK_EX] ) ) {
            startProfile("!__SAVING CACHE__! (warning)");
            foreach ( $this->_aLockedFileHandles[LOCK_EX] as $sKey => $rHandle ) {
                if ( $rHandle !== false && isset( $this->_aFileCacheContents[$sKey] ) ) {

                    // #0002931A truncate file once more before writing
                    ftruncate( $rHandle, 0 );

                    // writing cache
                    fwrite( $rHandle, $this->_processCache( $sKey, $this->_aFileCacheContents[$sKey] ) );

                    // releasing locks
                    $this->_releaseFile( $sKey );
                }
            }

            stopProfile("!__SAVING CACHE__! (warning)");

            //empty buffer
            $this->_aFileCacheContents = array();
        }
    }

    /**
     * Locks cache file and returns its handle on success or false on failure
     *
     * @param string $sFilePath name of file to lock
     * @param string $sIdent    lock identifier
     * @param int    $iLockMode lock mode - LOCK_EX/LOCK_SH
     *
     * @return mixed lock file resource or false on error
     */
    protected function _lockFile( $sFilePath, $sIdent, $iLockMode = LOCK_EX )
    {
        $rHandle = isset( $this->_aLockedFileHandles[$iLockMode][$sIdent] ) ? $this->_aLockedFileHandles[$iLockMode][$sIdent] : null;
        if ( $rHandle === null ) {

            $blLocked = false;
            $rHandle = @fopen( $sFilePath, "a+" );

            if ( $rHandle !== false ) {

                if ( flock( $rHandle, $iLockMode | LOCK_NB ) ) {
                    if ( $iLockMode === LOCK_EX ) {
                        // truncate file
                        $blLocked = ftruncate( $rHandle, 0 );
                    } else {
                        // move to a start position
                        $blLocked = fseek( $rHandle, 0 ) === 0;
                    }
                }

                // on failure - closing and setting false..
                if ( !$blLocked ) {
                    fclose( $rHandle );
                    $rHandle = false;
                }
            }

            // in case system does not support file lockings
            if ( !$blLocked && $iLockMode === LOCK_EX ) {

                // clearing on first call
                if ( count( $this->_aLockedFileHandles ) == 0 ) {
                    clearstatcache();
                }

                // start a blank file to inform other processes we are dealing with it.
                if (!( file_exists( $sFilePath ) && !filesize( $sFilePath ) && abs( time() - filectime( $sFilePath ) < 40 ) ) ) {
                    $rHandle = @fopen( $sFilePath, "w" );
                }
            }

            $this->_aLockedFileHandles[$iLockMode][$sIdent] = $rHandle;
        }

        return $rHandle;
    }

    /**
     * Releases file lock and returns release state
     *
     * @param string $sIdent    lock ident
     * @param int    $iLockMode lock mode
     *
     * @return bool
     */
    protected function _releaseFile( $sIdent, $iLockMode = LOCK_EX )
    {
        $blSuccess = true;
        if ( isset( $this->_aLockedFileHandles[$iLockMode][$sIdent] ) &&
             $this->_aLockedFileHandles[$iLockMode][$sIdent] !== false ) {

             // release the lock and close file
            $blSuccess = flock( $this->_aLockedFileHandles[$iLockMode][$sIdent], LOCK_UN ) &&
                         fclose( $this->_aLockedFileHandles[$iLockMode][$sIdent] );
            unset( $this->_aLockedFileHandles[$iLockMode][$sIdent] );
        }

        return $blSuccess;
    }

    /**
     * Removes most files stored in cache (default 'tmp') folder. Some files
     * e.g. table fiels names description, are left. Excluded cache file name
     * patterns are defined in oxutils::_sPermanentCachePattern parameter
     *
     * @return null
     */
    public function oxResetFileCache()
    {
        $aPathes = glob( $this->getCacheFilePath( null, true ) . '*' );
        if ( is_array( $aPathes ) ) {
            // delete all the files, except cached tables fieldnames
            $aPathes = preg_grep( $this->_sPermanentCachePattern, $aPathes, PREG_GREP_INVERT );
            foreach ( $aPathes as $sFilename ) {
                @unlink( $sFilename );
            }
        }
    }

    /**
     * If $sLocal file is older than 24h or doesn't exist, trys to
     * download it from $sRemote and save it as $sLocal
     *
     * @param string $sRemote the file
     * @param string $sLocal  the adress of the remote source
     *
     * @return mixed
     */
    public function getRemoteCachePath($sRemote, $sLocal)
    {
        clearstatcache();
        if ( file_exists( $sLocal ) && filemtime( $sLocal ) && filemtime( $sLocal ) > time() - 86400 ) {
            return $sLocal;
        }
        $hRemote = @fopen( $sRemote, "rb");
        $blSuccess = false;
        if ( isset( $hRemote) && $hRemote ) {
            $hLocal = fopen( $sLocal, "wb");
            stream_copy_to_stream($hRemote, $hLocal);
            fclose($hRemote);
            fclose($hLocal);
            $blSuccess = true;
        } else {
            // try via fsockopen
            $aUrl = @parse_url( $sRemote);
            if ( !empty( $aUrl["host"])) {
                $sPath = $aUrl["path"];
                if ( empty( $sPath ) ) {
                    $sPath = "/";
                }
                $sHost = $aUrl["host"];

                $hSocket = @fsockopen( $sHost, 80, $iErrorNumber, $iErrStr, 5);
                if ( $hSocket) {
                    fputs( $hSocket, "GET ".$sPath." HTTP/1.0\r\nHost: $sHost\r\n\r\n");
                    $headers = stream_get_line($hSocket, 4096, "\r\n\r\n");
                    if ( ( $hLocal = @fopen( $sLocal, "wb") ) !== false ) {
                        rewind($hLocal);
                        // does not copy all the data
                        // stream_copy_to_stream($hSocket, $hLocal);
                        fwrite ( $hLocal, stream_get_contents( $hSocket ) );
                        fclose( $hLocal );
                        fclose( $hSocket );
                        $blSuccess = true;
                    }
                }
            }
        }
        if ( $blSuccess || file_exists( $sLocal ) ) {
            return $sLocal;
        }
        return false;
    }

    /**
     * Checks if preview mode is ON
     *
     * @return bool
     */
    public function canPreview()
    {
        $blCan = null;
        if ( ( $sPrevId = oxConfig::getParameter( 'preview' ) ) &&
             ( $sAdminSid = oxUtilsServer::getInstance()->getOxCookie( 'admin_sid' ) ) ) {

            $sTable = getViewName( 'oxuser' );
            $sQ = "select 1 from $sTable where MD5( CONCAT( ?, {$sTable}.oxid, {$sTable}.oxpassword, {$sTable}.oxrights ) ) = ?";
            $blCan = (bool) oxDb::getDb()->getOne( $sQ, array( $sAdminSid, $sPrevId ) );
        }

        return $blCan;
    }

    /**
     * Returns id which is used for product preview in shop during administration
     *
     * @return string
     */
    public function getPreviewId()
    {
        $sAdminSid = oxUtilsServer::getInstance()->getOxCookie( 'admin_sid' );
        if ( ( $oUser = $this->getUser() ) ) {
            return md5( $sAdminSid . $oUser->getId() . $oUser->oxuser__oxpassword->value . $oUser->oxuser__oxrights->value );
        }
    }

    /**
     * This function checks if logged in user has access to admin or not
     *
     * @return bool
     */
    public function checkAccessRights()
    {
        $myConfig  = $this->getConfig();

        $blIsAuth = false;

        $sUserID = oxSession::getVar( "auth");

        // deleting admin marker
        oxSession::setVar( "malladmin", 0);
        oxSession::setVar( "blIsAdmin", 0);
        oxSession::deleteVar( "blIsAdmin" );
        $myConfig->setConfigParam( 'blMallAdmin', false );
        //#1552T
        $myConfig->setConfigParam( 'blAllowInheritedEdit', false );

        if ( $sUserID) {
            // escaping
            $oDb = oxDb::getDb();
            $sRights = $oDb->getOne("select oxrights from oxuser where oxid = ".$oDb->quote($sUserID));

            if ( $sRights != "user") {
                // malladmin ?
                if ( $sRights == "malladmin") {
                    oxSession::setVar( "malladmin", 1);
                    $myConfig->setConfigParam( 'blMallAdmin', true );

                    //#1552T
                    //So far this blAllowSharedEdit is Equal to blMallAdmin but in future to be solved over rights and roles
                    $myConfig->setConfigParam( 'blAllowSharedEdit', true );

                    $sShop = oxSession::getVar( "actshop");
                    if ( !isset($sShop)) {
                        oxSession::setVar( "actshop", $myConfig->getBaseShopId());
                    }
                    $blIsAuth = true;
                } else {
                    // Shopadmin... check if this shop is valid and exists
                    $sShopID = $oDb->getOne("select oxid from oxshops where oxid = " . $oDb->quote( $sRights ) );
                    if ( isset( $sShopID) && $sShopID) {
                        // success, this shop exists

                        oxSession::setVar( "actshop", $sRights);
                        oxSession::setVar( "currentadminshop", $sRights);
                        oxSession::setVar( "shp", $sRights);

                        // check if this subshop admin is evil.
                        if ('chshp' == oxConfig::getParameter( 'fnc' )) {
                            // dont allow this call
                            $blIsAuth = false;
                        } else {
                            $blIsAuth = true;

                            $aShopIdVars = array('actshop', 'shp', 'currentadminshop');
                            foreach ($aShopIdVars as $sShopIdVar) {
                                if ($sGotShop = oxConfig::getParameter( $sShopIdVar )) {
                                    if ($sGotShop != $sRights) {
                                        $blIsAuth = false;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                // marking user as admin
                oxSession::setVar( "blIsAdmin", 1);
            }
        }
        return $blIsAuth;
    }

    /**
     * Checks if Seo mode should be used
     *
     * @param bool   $blReset  used to reset cached SEO mode
     * @param string $sShopId  shop id (optional; if not passed active session shop id will be used)
     * @param int    $iActLang language id (optional; if not passed active session language will be used)
     *
     * @return bool
     */
    public function seoIsActive( $blReset = false, $sShopId = null, $iActLang = null )
    {
        if ( !is_null( $this->_blSeoIsActive ) && !$blReset ) {
            return $this->_blSeoIsActive;
        }

        $myConfig = $this->getConfig();

        if ( ( $this->_blSeoIsActive = $myConfig->getConfigParam( 'blSeoMode' ) ) === null ) {
            $this->_blSeoIsActive = true;

            $aSeoModes  = $myConfig->getconfigParam( 'aSeoModes' );
            $sActShopId = $sShopId ? $sShopId : $myConfig->getActiveShop()->getId();
            $iActLang   = $iActLang ? $iActLang : (int) oxLang::getInstance()->getBaseLanguage();

            // checking special config param for active shop and language
            if ( is_array( $aSeoModes ) && isset( $aSeoModes[$sActShopId] ) && isset( $aSeoModes[$sActShopId][$iActLang] ) ) {
                $this->_blSeoIsActive = (bool) $aSeoModes[$sActShopId][$iActLang];
            }
        }

        return $this->_blSeoIsActive;
    }

    /**
     * Returns integer number with bit set according to $iShopId.
     * The acttion performed could be represented as pow(2, $iShopId - 1)
     * We use mySQL to calculate that, as currently php int size is only 32 bit.
     *
     * @param int $iShopId current shop id
     *
     * @return int
     */
    public function getShopBit( $iShopId )
    {
        $iShopId = (int) $iShopId;
        //this works for large numbers when $sShopNr is up to (inclusive) 64
        $iRes = oxDb::getDb()->getOne( "select 1 << ( $iShopId - 1 ) as shopbit" );

        //as php ints supports only 32 bits, we return string.
        return $iRes;
    }

    /**
     * Binary AND implementation.
     * We use mySQL to calculate that, as currently php int size is only 32 bit.
     *
     * @param int $iVal1 value nr 1
     * @param int $iVal2 value nr 2
     *
     * @return int
     */
    public function bitwiseAnd( $iVal1, $iVal2 )
    {
        //this works for large numbers when $sShopNr is up to (inclusive) 64
        $iRes = oxDb::getDb()->getOne( "select ($iVal1 & $iVal2) as bitwiseAnd" );

        //as php ints supports only 32 bits, we return string.
        return $iRes;
    }

    /**
     * Binary OR implementation.
     * We use mySQL to calculate that, as currently php integer size is only 32 bit.
     *
     * @param int $iVal1 value nr 1
     * @param int $iVal2 value nr 2
     *
     * @return int
     */
    public function bitwiseOr( $iVal1, $iVal2 )
    {
        //this works for large numbers when $sShopNr is up to (inclusive) 64
        $iRes = oxDb::getDb()->getOne( "select ($iVal1 | $iVal2) as bitwiseOr" );

        //as php ints supports only 32 bits, we return string.
        return $iRes;
    }

    /**
     * Checks if string is only alpha numeric  symbols
     *
     * @param string $sField fieldname to test
     *
     * @return bool
     */
    public function isValidAlpha( $sField )
    {
        return (boolean) getStr()->preg_match( '/^[a-zA-Z0-9_]*$/', $sField );
    }

    /**
     * redirects browser to given url, nothing else done just header send
     * may be used for redirection in case of an exception or similar things
     *
     * @param string $sUrl        code to add to the header(e.g. "HTTP/1.1 301 Moved Permanently", or "HTTP/1.1 500 Internal Server Error"
     * @param string $sHeaderCode the URL to redirect to
     *
     * @return null
     */
    protected function _simpleRedirect( $sUrl, $sHeaderCode )
    {
        header( $sHeaderCode );
        header( "Location: $sUrl" );
        header( "Connection: close" );
    }

    /**
     * redirect user to the specified URL
     *
     * @param string $sUrl               URL to be redirected
     * @param bool   $blAddRedirectParam add "redirect" param
     * @param int    $iHeaderCode        header code, default 301
     *
     * @TODO change $iHeaderCode default value to 302, because
     * ONLY if page was removed permanently 301 header must be
     * send. On most redirects we only transfer to different page
     *
     * @return null or exit
     */
    public function redirect( $sUrl, $blAddRedirectParam = true, $iHeaderCode = 301 )
    {
        //preventing possible cyclic redirection
        //#M341 and check only if redirect paramater must be added
        if ( $blAddRedirectParam && oxConfig::getParameter( 'redirected' ) ) {
            return;
        }

        if ( $blAddRedirectParam ) {
            $sUrl = $this->_addUrlParameters( $sUrl, array( 'redirected' => 1 ) );
        }

        $sUrl = str_ireplace( "&amp;", "&", $sUrl );

        $sHeaderCode = '';
        switch ($iHeaderCode) {
            case 301:
                $sHeaderCode = "HTTP/1.1 301 Moved Permanently";
                break;
            case 302:
            default:
                $sHeaderCode = "HTTP/1.1 302 Found";
        }

        $this->_simpleRedirect( $sUrl, $sHeaderCode );

        try {//may occur in case db is lost
            $this->getSession()->freeze();
        } catch( oxException $oEx ) {
            $oEx->debugOut();
            //do nothing else to make sure the redirect takes place
        }

        if ( defined( 'OXID_PHP_UNIT' ) ) {
            return;
        }

        $this->showMessageAndExit( '' );
    }

    /**
     * shows given message and quits
     *
     * @param string $sMsg message to show
     *
     * @return null dies
     */
    public function showMessageAndExit( $sMsg )
    {
        $this->getSession()->freeze();
        $this->commitFileCache();

        if ( defined( 'OXID_PHP_UNIT' ) ) {
            return;
        }

        exit( $sMsg );
    }

    /**
     * set header sent to browser
     *
     * @param string $sHeader header to sent
     *
     * @return null
     */
    public function setHeader($sHeader)
    {
        header($sHeader);
    }

    /**
     * adds the given paramters at the end of the given url
     *
     * @param string $sUrl    a url
     * @param array  $aParams the params which will be added
     *
     * @return string
     */
    protected function _addUrlParameters( $sUrl, $aParams )
    {
        $sDelim = ( ( getStr()->strpos( $sUrl, '?' ) !== false ) )?'&':'?';
        foreach ( $aParams as $sName => $sVal ) {
            $sUrl = $sUrl . $sDelim . $sName . '=' . $sVal;
            $sDelim = '&';
        }

        return $sUrl;
    }

    /**
     * Fill array.
     *
     * @param array  $aName Initial array of strings
     * @param double $dVat  Article VAT
     *
     * @return string
     *
     * @todo rename function more closely to actual purpose
     * @todo finish refactoring
     */
    protected function _fillExplodeArray( $aName, $dVat = null)
    {
        $myConfig = $this->getConfig();
        $oObject = new oxStdClass();
        $aPrice = explode( '!P!', $aName[0]);

        if ( ( $myConfig->getConfigParam( 'bl_perfLoadSelectLists' ) && $myConfig->getConfigParam( 'bl_perfUseSelectlistPrice' ) && isset( $aPrice[0] ) && isset( $aPrice[1] ) ) || $this->isAdmin() ) {

            // yes, price is there
            $oObject->price = isset( $aPrice[1] ) ? $aPrice[1] : 0;
            $aName[0] = isset( $aPrice[0] ) ? $aPrice[0] : '';

            $iPercPos = getStr()->strpos( $oObject->price, '%' );
            if ( $iPercPos !== false ) {
                $oObject->priceUnit = '%';
                $oObject->fprice = $oObject->price;
                $oObject->price  = substr( $oObject->price, 0, $iPercPos );
            } else {
                $oCur = $myConfig->getActShopCurrencyObject();
                $oObject->price = str_replace(',', '.', $oObject->price);
                $oObject->fprice = oxLang::getInstance()->formatCurrency( $oObject->price  * $oCur->rate, $oCur);
                $oObject->priceUnit = 'abs';
            }

            // add price info into list
            if ( !$this->isAdmin() && $oObject->price != 0 ) {
                $aName[0] .= " ";
                if ( $oObject->price > 0 ) {
                    $aName[0] .= "+";
                }
                //V FS#2616
                if ( $dVat != null && $oObject->priceUnit == 'abs' ) {
                    $oPrice = oxNew('oxPrice');
                    $oPrice->setPrice($oObject->price, $dVat);
                    $aName[0] .= oxLang::getInstance()->formatCurrency( $oPrice->getBruttoPrice() * $oCur->rate, $oCur);
                } else {
                    $aName[0] .= $oObject->fprice;
                }
                if ( $oObject->priceUnit == 'abs' ) {
                    $aName[0] .= " ".$oCur->sign;
                }
            }
        } elseif ( isset( $aPrice[0] ) && isset($aPrice[1] ) ) {
            // A. removing unused part of information
            $aName[0] = getStr()->preg_replace( "/!P!.*/", "", $aName[0] );
        }

        $oObject->name  = $aName[0];
        $oObject->value = $aName[1];
        return $oObject;
    }

    /**
     * returns manually set mime types
     *
     * @param string $sFileName the file
     *
     * @return string
     */
    public function oxMimeContentType( $sFileName )
    {
        $sFileName = strtolower( $sFileName );
        $iLastDot  = strrpos( $sFileName, '.' );

        if ( $iLastDot !== false ) {
            $sType = substr( $sFileName, $iLastDot + 1 );
            switch ( $sType ) {
                case 'gif':
                    $sType = 'image/gif';
                    break;
                case 'jpeg':
                case 'jpg':
                    $sType = 'image/jpeg';
                    break;
                case 'png':
                    $sType = 'image/png';
                    break;
                default:
                    $sType = false;
                    break;
            }
        }
        return $sType;
    }

    /**
     * Processes logging.
     *
     * @param string $sText     Log message text
     * @param bool   $blNewline If true, writes message to new line (default false)
     *
     * @return null
     */
    public function logger( $sText, $blNewline = false )
    {   $myConfig = $this->getConfig();

        if ( $myConfig->getConfigParam( 'iDebug' ) == -2) {
            if ( gettype( $sText ) != 'string' ) {
                $sText = var_export( $sText, true);
            }
            $sLogMsg = "----------------------------------------------\n{$sText}".( ( $blNewline ) ?"\n":"" )."\n";
            $this->writeToLog( $sLogMsg, "log.txt" );
        }

    }

    /**
     * Recursively removes slashes from arrays
     *
     * @param mixed $mInput the input from which the slashes should be removed
     *
     * @return mixed
     */
    protected function _stripQuotes($mInput)
    {
        return is_array($mInput) ? array_map( array( $this, '_stripQuotes' ), $mInput) : stripslashes( $mInput );
    }

    /**
    * Applies ROT13 encoding to $sStr
    *
    * @param string $sStr to encoding string
    *
    * @return string
    */
    public function strRot13( $sStr )
    {
        $sFrom = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sTo   = 'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM';

        return strtr( $sStr, $sFrom, $sTo );
    }

    /**
     * Returns full path (including file name) to cache file
     *
     * @param string $sCacheName cache file name
     * @param bool   $blPathOnly if TRUE, name parameter will be ignored and only cache folder will be returned (default FALSE)
     * @param string $sExtension cache file extension
     *
     * @return string
     */
    public function getCacheFilePath( $sCacheName, $blPathOnly = false, $sExtension = 'txt' )
    {
        $sVersionPrefix = "";


            $sVersionPrefix = 'pe';

        $sPath = realpath($this->getConfig()->getConfigParam( 'sCompileDir' ));

        if (!$sPath) {
            return false;
        }

        return $blPathOnly ? "{$sPath}/" : "{$sPath}/ox{$sVersionPrefix}c_{$sCacheName}." . $sExtension;
    }

    /**
     * Tries to load lang cache array from cache file
     *
     * @param string $sCacheName cache file name
     *
     * @return array
     */
    public function getLangCache( $sCacheName )
    {
        $aLangCache = null;
        $sFilePath = $this->getCacheFilePath( $sCacheName );
        if ( file_exists( $sFilePath ) && is_readable( $sFilePath ) ) {
            include $sFilePath;
        }
        return $aLangCache;
    }

    /**
     * Writes languge array to file cache
     *
     * @param string $sCacheName name of cache file
     * @param array  $aLangCache language array
     *
     * @return null
     */
    public function setLangCache( $sCacheName, $aLangCache )
    {
        $sCache = "<?php\n\$aLangCache = ".var_export( $aLangCache, true ).";";
        $blRes = file_put_contents($this->getCacheFilePath($sCacheName), $sCache);
        return $blRes;
    }

    /**
     * Cheks if url has ending slash / - if not, adds it
     *
     * @param string $sUrl url string
     *
     * @return string
     */
    public function checkUrlEndingSlash( $sUrl )
    {
        if ( !getStr()->preg_match("/\/$/", $sUrl) ) {
            $sUrl .= '/';
        }

        return $sUrl;
    }

    /**
     * Writes given log message. Returns write state
     *
     * @param string $sLogMessage  log message
     * @param string $sLogFileName log file name
     *
     * @return bool
     */
    public function writeToLog( $sLogMessage, $sLogFileName )
    {
        $sLogDist = $this->getConfig()->getLogsDir().$sLogFileName;
        $blOk = false;

        if ( ( $oHandle = fopen( $sLogDist, 'a' ) ) !== false ) {
            fwrite( $oHandle, $sLogMessage );
            $blOk = fclose( $oHandle );
        }

        return $blOk;
    }

    /**
     * handler for 404 (page not found) error
     *
     * @param string $sUrl url wich was given, can be not specified in some cases
     *
     * @return void
     */
    public function handlePageNotFoundError($sUrl = '')
    {
        $this->setHeader("HTTP/1.0 404 Not Found");
        if ( oxConfig::getInstance()->isUtf() ) {
            $this->setHeader("Content-Type: text/html; charset=UTF-8");
        }

        $sReturn = "Page not found.";
        try {
            $oView = oxNew('oxubase');
            $oView->init();
            $oView->render();
            $oView->addTplParam('sUrl', $sUrl);
            if ($sRet = oxUtilsView::getInstance()->getTemplateOutput('message/err_404.tpl', $oView)) {
                $sReturn = $sRet;
            }
        } catch (Exception $e) {
        }
        $this->showMessageAndExit( $sReturn );
    }

    /**
     * Extracts domain name from given host
     *
     * @param string $sHost host name
     *
     * @return string
     */
    public function extractDomain( $sHost )
    {
        $oStr = getStr();
        if ( !$oStr->preg_match( '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $sHost ) &&
             ( $iLastDot = strrpos( $sHost, '.' ) ) !== false ) {
            $iLen = $oStr->strlen( $sHost );
            if ( ( $iNextDot = strrpos( $sHost, '.', ( $iLen - $iLastDot + 1 ) * - 1 ) ) !== false ) {
                $sHost = trim( $oStr->substr( $sHost, $iNextDot ), '.' );
            }
        }

        return $sHost;
    }
}
