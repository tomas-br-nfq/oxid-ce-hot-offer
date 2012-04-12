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
 * @version   SVN: $Id: oxutilsserver.php 41881 2012-01-30 12:34:50Z mindaugas.rimgaila $
 */

/**
 * Server data manipulation class
 */
class oxUtilsServer extends oxSuperCfg
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils* instance
     */
    private static $_instance = null;

    /**
     * user cookies
     *
     * @var array
     */
    protected $_aUserCookie = array();

    /**
     * Session cookie parameter name
     *
     * @var string
     */
    protected $_sSessionCookiesName = 'aSessionCookies';

    /**
     * Session stored cookies
     *
     * @var array
     */
    protected $_sSessionCookies = array();

    /**
     * Returns server utils instance
     *
     * @return oxUtilsServer
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            self::$_instance = modInstances::getMod( __CLASS__ );
        }

        if ( !self::$_instance instanceof oxUtilsServer ) {
            self::$_instance = oxNew( 'oxUtilsServer');
            if ( defined( 'OXID_PHP_UNIT' ) ) {
                modInstances::addMod( __CLASS__, self::$_instance);
            }
        }
        return self::$_instance;
    }

    /**
     * sets cookie
     *
     * @param string $sName       cookie name
     * @param string $sValue      value
     * @param int    $iExpire     expire time
     * @param string $sPath       The path on the server in which the cookie will be available on
     * @param string $sDomain     The domain that the cookie is available.
     * @param bool   $blToSession is true, records cookie information to session
     * @param bool   $blSecure    if true, transfer cookie only via SSL
     *
     * @return bool
     */
    public function setOxCookie( $sName, $sValue = "", $iExpire = 0, $sPath = '/', $sDomain = null, $blToSession = true, $blSecure = false )
    {
        //TODO: since setcookie takes more than just 4 params..
        // would be nice to have it sending through https only, if in https mode
        // or allowing only http access to cookie [no JS access - reduces XSS attack possibility]
        // ref: http://lt.php.net/manual/en/function.setcookie.php

        if ( $blToSession && !$this->isAdmin() ) {
           $this->_saveSessionCookie( $sName, $sValue, $iExpire, $sPath, $sDomain );
        }

        if ( defined('OXID_PHP_UNIT')) {
            // do NOT set cookies in php unit.
            return;
        }

        return setcookie(
            $sName,
            $sValue,
            $iExpire,
            $this->_getCookiePath( $sPath ),
            $this->_getCookieDomain( $sDomain ),
            $blSecure,
            true
        );
    }

    protected $_blSaveToSession = null;

    /**
     * Checks if cookie must be saved to session in order to transfer it to different domain
     *
     * @return bool
     */
    protected function _mustSaveToSession()
    {
        if ( $this->_blSaveToSession === null ) {
            $this->_blSaveToSession = false;

            $myConfig = $this->getConfig();
            if ( $sSslUrl = $myConfig->getSslShopUrl() ) {
                $sUrl  = $myConfig->getShopUrl();

                $sHost    = parse_url( $sUrl, PHP_URL_HOST );
                $sSslHost = parse_url( $sSslUrl, PHP_URL_HOST );

                // testing if domains matches..
                if ( $sHost != $sSslHost ) {
                    $oUtils = oxUtils::getInstance();
                    $this->_blSaveToSession = $oUtils->extractDomain( $sHost ) != $oUtils->extractDomain( $sSslHost );
                }
            }
        }

        return $this->_blSaveToSession;
    }

    /**
     * Returns session cookie key
     *
     * @param bool $blGet mode - true - get, false - set cookie
     *
     * @return string
     */
    protected function _getSessionCookieKey( $blGet )
    {
        $blSsl = $this->getConfig()->isSsl();
        $sKey  = $blSsl ? 'nossl' : 'ssl';

        if ( $blGet ) {
            $sKey = $blSsl ? 'ssl' : 'nossl';
        }

        return $sKey;
    }

    /**
     * Copies cookie info to session
     *
     * @param string $sName   cookie name
     * @param string $sValue  cookie value
     * @param int    $iExpire expiration time
     * @param string $sPath   cookie path
     * @param string $sDomain cookie domain
     *
     * @return null
     */
    protected function _saveSessionCookie( $sName, $sValue, $iExpire, $sPath, $sDomain )
    {
        if ( $this->_mustSaveToSession() ) {
            $aCookieData = array( 'value' => $sValue, 'expire' => $iExpire, 'path' => $sPath, 'domain' => $sDomain );

            $aSessionCookies = ( array ) oxSession::getVar( $this->_sSessionCookiesName );
            $aSessionCookies[$this->_getSessionCookieKey( false )][$sName] = $aCookieData;

            oxSession::setVar( $this->_sSessionCookiesName, $aSessionCookies );
        }
    }

    /**
     * Stored all session cookie info to cookies
     *
     * @return mixed
     */
    public function loadSessionCookies()
    {
        if ( ( $aSessionCookies = oxSession::getVar( $this->_sSessionCookiesName ) ) ) {
            $sKey = $this->_getSessionCookieKey( true );
            if ( isset( $aSessionCookies[$sKey] ) ) {
                // writing session data to cookies
                foreach ( $aSessionCookies[$sKey] as $sName => $aCookieData ) {
                    $this->setOxCookie( $sName, $aCookieData['value'], $aCookieData['expire'], $aCookieData['path'], $aCookieData['domain'], false );
                    $this->_sSessionCookies[$sName] = $aCookieData['value'];
                }

                // cleanup
                unset( $aSessionCookies[$sKey] );
                oxSession::setVar( $this->_sSessionCookiesName, $aSessionCookies );
            }
        }
    }

    /**
     * Returns cookie path. If user did not set path, or set it to null, according to php
     * documentation empty string will be returned, marking to skip argument. Additionally
     * path can be defined in config.inc.php file as "sCookiePath" param. Please check cookie
     * documentation for more details about current parameter
     *
     * @param string $sPath user defined cookie path
     *
     * @return string
     */
    protected function _getCookiePath( $sPath )
    {
        // possibility for users to define cookie path
        // @deprecated use "aCookiePaths" instead
        if ( $sCookiePath = $this->getConfig()->getConfigParam( 'sCookiePath' ) ) {
            $sPath = $sCookiePath;
        } elseif ( $aCookiePaths = $this->getConfig()->getConfigParam( 'aCookiePaths' ) ) {
            // in case user wants to have shop specific setup
            $sShopId = $this->getConfig()->getShopId();
            $sPath = isset( $aCookiePaths[$sShopId] ) ? $aCookiePaths[$sShopId] : $sPath;
        }

        // from php doc: .. You may also replace an argument with an empty string ("") in order to skip that argument..
        return $sPath ? $sPath : "";
    }

    /**
     * Returns domain that cookie available. If user did not set domain, or set it to null, according to php
     * documentation empty string will be returned, marking to skip argument. Additionally domain can be defined
     * in config.inc.php file as "sCookieDomain" param. Please check cookie documentation for more details about
     * current parameter
     *
     * @param string $sDomain the domain that the cookie is available.
     *
     * @return string
     */
    protected function _getCookieDomain( $sDomain )
    {
        $sDomain = $sDomain ? $sDomain : "";

        // on special cases, like separate domain for SSL, cookies must be defined on domain specific path
        // please have a look at
        if ( !$sDomain ) {
            // @deprecated use "aCookieDomains" instead
            if ( $sCookieDomain = $this->getConfig()->getConfigParam( 'sCookieDomain' ) ) {
                $sDomain = $sCookieDomain;
            } elseif ( $aCookieDomains = $this->getConfig()->getConfigParam( 'aCookieDomains' ) ) {
                // in case user wants to have shop specific setup
                $sShopId = $this->getConfig()->getShopId();
                $sDomain = isset( $aCookieDomains[$sShopId] ) ? $aCookieDomains[$sShopId] : $sDomain;
            }
        }
        return $sDomain;
    }

    /**
     * Returns cookie $sName value.
     * If optional parameter $sName is not set then getCookie() returns whole cookie array
     *
     * @param string $sName cookie param name
     *
     * @return mixed
     */
    public function getOxCookie( $sName = null )
    {
        $sValue = null;
        if ( $sName && isset( $_COOKIE[$sName] ) ) {
            $sValue = oxConfig::checkSpecialChars($_COOKIE[$sName]);
        } elseif ( $sName && !isset( $_COOKIE[$sName] ) ) {
            $sValue = isset( $this->_sSessionCookies[$sName] ) ? $this->_sSessionCookies[$sName] : null;
        } elseif ( !$sName && isset( $_COOKIE ) ) {
            $sValue = $_COOKIE;
        }
        return $sValue;
    }

    /**
     * Returns remote IP address
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
            $sIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
            $sIP = preg_replace('/,.*$/', '', $sIP);
        } elseif ( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
            $sIP = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $sIP = $_SERVER["REMOTE_ADDR"];
        }
        return $sIP;
    }

    /**
     * returns a server constant
     *
     * @param string $sServVar optional - which server var should be returned, if null returns whole $_SERVER
     *
     * @return mixed
     */
    public function getServerVar( $sServVar = null )
    {
        $sValue = null;
        if ( isset( $_SERVER ) ) {
            if ( $sServVar && isset( $_SERVER[$sServVar] ) ) {
                $sValue = $_SERVER[$sServVar];
            } elseif ( !$sServVar ) {
                $sValue = $_SERVER;
            }
        }
        return $sValue;
    }

    /**
     * Sets user info into cookie
     *
     * @param string  $sUser     user ID
     * @param string  $sPassword password
     * @param string  $sShopId   shop ID (default null)
     * @param integer $iTimeout  timeout value (default 31536000)
     * @param string  $sSalt     Salt for password encryption
     *
     * @return null
     */
    public function setUserCookie( $sUser, $sPassword,  $sShopId = null, $iTimeout = 31536000, $sSalt = 'ox' )
    {
        $myConfig = $this->getConfig();
        $sShopId = ( !$sShopId ) ? $myConfig->getShopId() : $sShopId;
        $sSslUrl = $myConfig->getSslShopUrl();
        if (stripos($sSslUrl, 'https') === 0) {
            $blSsl = true;
        } else {
            $blSsl = false;
        }

        $this->_aUserCookie[$sShopId] = $sUser . '@@@' . crypt( $sPassword, $sSalt );
        $this->setOxCookie( 'oxid_' . $sShopId, $this->_aUserCookie[$sShopId], oxUtilsDate::getInstance()->getTime() + $iTimeout, '/', null, true, $blSsl );
        $this->setOxCookie( 'oxid_' . $sShopId.'_autologin', '1', oxUtilsDate::getInstance()->getTime() + $iTimeout, '/');
    }

    /**
     * Deletes user cookie data
     *
     * @param string $sShopId shop ID (default null)
     *
     * @return null
     */
    public function deleteUserCookie( $sShopId = null )
    {
        $myConfig = $this->getConfig();
        $sShopId = ( !$sShopId ) ? $this->getConfig()->getShopId() : $sShopId;
        $sSslUrl = $myConfig->getSslShopUrl();
        if (stripos($sSslUrl, 'https') === 0) {
            $blSsl = true;
        } else {
            $blSsl = false;
        }

        $this->_aUserCookie[$sShopId] = '';
        $this->setOxCookie( 'oxid_'.$sShopId, '', oxUtilsDate::getInstance()->getTime() - 3600, '/', null, true, $blSsl );
        $this->setOxCookie( 'oxid_' . $sShopId.'_autologin', '0', oxUtilsDate::getInstance()->getTime() - 3600, '/');
    }

    /**
     * Returns cookie stored used login data
     *
     * @param string $sShopId shop ID (default null)
     *
     * @return string
     */
    public function getUserCookie( $sShopId = null )
    {
        $myConfig = parent::getConfig();
        $sShopId = ( !$sShopId ) ? $myConfig->getShopId() : $sShopId;

        // check for SSL connection
        if (!$myConfig->isSsl() && $this->getOxCookie('oxid_'.$sShopId.'_autologin') == '1') {
            $sSslUrl = $myConfig->getSslShopUrl();
            if (stripos($sSslUrl, 'https') === 0) {
                oxUtils::getInstance()->redirect($sSslUrl, true, 302);
            }
        }

        if ( array_key_exists( $sShopId, $this->_aUserCookie ) && $this->_aUserCookie[$sShopId] !== null ) {
            return $this->_aUserCookie[$sShopId] ? $this->_aUserCookie[$sShopId] : null;
        }

        return $this->_aUserCookie[$sShopId] = $this->getOxCookie( 'oxid_'.$sShopId );
    }

    /**
     * Checks if current client ip is in trusted IPs list.
     * IP list is defined in config file as "aTrustedIPs" parameter
     *
     * @return bool
     */
    public function isTrustedClientIp()
    {
        $blTrusted = false;
        $aTrustedIPs = ( array ) $this->getConfig()->getConfigParam( "aTrustedIPs" );
        if ( count( $aTrustedIPs ) ) {
            $blTrusted = in_array( $this->getRemoteAddress(), $aTrustedIPs );
        }

        return $blTrusted;
    }

    /**
     * Removes MSIE(\s)?(\S)*(\s) from browser agent information
     *
     * @param string $sAgent browser user agent idenfitier
     *
     * @return string
     */
    public function processUserAgentInfo( $sAgent )
    {
        if ( $sAgent ) {
            $sAgent = getStr()->preg_replace( "/MSIE(\s)?(\S)*(\s)/", "", (string) $sAgent );
        }
        return $sAgent;
    }
}
