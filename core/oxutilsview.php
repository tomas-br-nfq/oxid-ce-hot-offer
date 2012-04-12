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
 * @version   SVN: $Id: oxutilsview.php 42088 2012-02-08 14:24:08Z arvydas.vapsva $
 */

/**
 * View utility class
 */
class oxUtilsView extends oxSuperCfg
{
    /**
     * oxUtils class instance.
     *
     * @var oxutils* instance
     */
    private static $_instance = null;

    /**
     * Template processor object (smarty)
     *
     * @var smarty
     */
    protected static $_oSmarty = null;

    /**
     * Templates directories array
     *
     * @var array
     */
    protected $_aTemplateDir = array();

    /**
     * Templates directories array
     *
     * @var array
     */
    protected $_blIsTplBlocks = null;

    /**
     * Utility instance getter
     *
     * @return oxUtilsView
     */
    public static function getInstance()
    {
        // disable caching for test modules
        if ( defined( 'OXID_PHP_UNIT' ) ) {
            self::$_instance = modInstances::getMod( __CLASS__ );
        }

        if ( !self::$_instance instanceof oxUtilsView ) {


            self::$_instance = oxNew( 'oxUtilsView' );

            if ( defined( 'OXID_PHP_UNIT' ) ) {
                modInstances::addMod( __CLASS__, self::$_instance);
            }
        }
        return self::$_instance;
    }

    /**
     * returns existing or creates smarty object
     * Returns smarty object. If object not yet initiated - creates it. Sets such
     * default parameters, like cache lifetime, cache/templates directory, etc.
     *
     * @param bool $blReload set true to force smarty reload
     *
     * @return smarty
     */
    public function getSmarty( $blReload = false )
    {
        if ( !self::$_oSmarty || $blReload ) {
            self::$_oSmarty = new Smarty;
            $this->_fillCommonSmartyProperties( self::$_oSmarty );
            $this->_smartyCompileCheck( self::$_oSmarty );
        }

        return self::$_oSmarty;
    }

    /**
     * Returns renderd template output. According to debug configuration outputs
     * debug information.
     *
     * @param string $sTemplate template file name
     * @param object $oObject   object, witch template we wish to output
     *
     * @return string
     */
    public function getTemplateOutput( $sTemplate, $oObject )
    {
        $oSmarty = $this->getSmarty();
        $iDebug  = $this->getConfig()->getConfigParam( 'iDebug' );

        // assign
        $aViewData = $oObject->getViewData();
        if ( is_array( $aViewData ) ) {
            foreach ( array_keys( $aViewData ) as $sViewName ) {
                // show debbuging information
                if ( $iDebug == 4 ) {
                    echo( "TemplateData[$sViewName] : \n");
                    print_r( $aViewData[$sViewName] );
                }
                $oSmarty->assign_by_ref( $sViewName, $aViewData[$sViewName] );
            }
        }

        return $oSmarty->fetch( $sTemplate );
    }

    /**
     * adds the given errors to the view array
     *
     * @param array &$aView  view data array
     * @param array $aErrors array of errors to pass to view
     *
     * @return null
     */
    public function passAllErrorsToView( &$aView, $aErrors )
    {
        if ( count( $aErrors ) > 0 ) {
            foreach ( $aErrors as $sLocation => $aEx2 ) {
                foreach ( $aEx2 as $sKey => $oEr ) {
                    $aView['Errors'][$sLocation][$sKey] = unserialize( $oEr );
                }
            }
        }
    }

    /**
     * adds a exception to the array of displayed exceptions for the view
     * by default is displayed in the inc_header, but with the custom destination set to true
     * the exception won't be displayed by default but can be displayed where ever wanted in the tpl
     *
     * @param exception $oEr                 a exception object or just a language local (string) which will be converted into a oxExceptionToDisplay object
     * @param bool      $blFull              if true the whole object is add to display (default false)
     * @param bool      $blCustomDestination true if the exception shouldn't be displayed at the default position (default false)
     * @param string    $sCustomDestination  defines a name of the view variable containing the messages, overrides Parameter 'CustomError' ("default")
     *
     * @return null
     */
    public function addErrorToDisplay( $oEr, $blFull = false, $blCustomDestination = false, $sCustomDestination = "" )
    {
        if ( $blCustomDestination && ( oxConfig::getParameter( 'CustomError' ) || $sCustomDestination!= '' ) ) {
            // check if the current request wants do display exceptions on its own
            $sDestination = oxConfig::getParameter( 'CustomError' );
            if ( $sCustomDestination != '' ) {
                $sDestination = $sCustomDestination;
            }
        } else {
            //default
            $sDestination = 'default';
        }

        //starting session if not yet started as all exception
        //messages are stored in session
        $oSession = $this->getSession();
        if ( !$oSession->getId() && !$oSession->isHeaderSent() ) {
            $oSession->setForceNewSession();
            $oSession->start();
        }

        $aEx = oxSession::getVar( 'Errors' );
        if ( $oEr instanceof oxException ) {
             $oEx = oxNew( 'oxExceptionToDisplay' );
             $oEx->setMessage( $oEr->getMessage() );
             $oEx->setExceptionType( get_class( $oEr ) );

             if ( $oEr instanceof oxSystemComponentException ) {
                $oEx->setMessageArgs( $oEr->getComponent() );
             }

             $oEx->setValues( $oEr->getValues() );
             $oEx->setStackTrace( $oEr->getTraceAsString() );
             $oEx->setDebug( $blFull );
             $oEr = $oEx;
        } elseif ( $oEr && ! ( $oEr instanceof oxIDisplayError ) ) {
            // assuming that a string was given
            $sTmp = $oEr;
            $oEr = oxNew( 'oxDisplayError' );
            $oEr->setMessage( $sTmp );
        } elseif ( $oEr instanceof oxIDisplayError ) {
            // take the object
        } else {
            $oEr = null;
        }

        if ( $oEr ) {
            $aEx[$sDestination][] = serialize( $oEr );
            oxSession::setVar( 'Errors', $aEx );
        }
    }

    /**
     * Runs long description through smarty. If you pass array of data
     * to process, array will be returned, if you pass string - string
     * will be passed as result
     *
     * @param mixed  $sDesc       description or array of descriptions ( array( [] => array( _ident_, _value_to_process_ ) ) )
     * @param string $sOxid       current object id
     * @param oxview $oActView    view data to use its view data (optional)
     * @param bool   $blRecompile force to recompile if found in cache
     *
     * @return mixed
     */
    public function parseThroughSmarty( $sDesc, $sOxid = null, $oActView = null, $blRecompile = false )
    {
        startProfile("parseThroughSmarty");

        if (!is_array($sDesc) && strpos($sDesc, "[{") === false) {
            stopProfile("parseThroughSmarty");
            return $sDesc;
        }


        $iLang = oxLang::getInstance()->getTplLanguage();

        // now parse it through smarty
        $oSmarty = clone $this->getSmarty();

        // save old tpl data
        $sTplVars = $oSmarty->_tpl_vars;
        $blForceRecompile = $oSmarty->force_compile;

        $oSmarty->force_compile = $blRecompile;

        if ( !$oActView ) {
            $oActView = oxNew( 'oxubase' );
            $oActView->addGlobalParams();
        }

        $aViewData = $oActView->getViewData();
        foreach ( array_keys( $aViewData ) as $sName ) {
            $oSmarty->assign_by_ref( $sName, $aViewData[$sName] );
        }

        if ( is_array( $sDesc ) ) {
            foreach ( $sDesc as $sName => $aData ) {
                $oSmarty->oxidcache = new oxField( $aData[1], oxField::T_RAW );
                $sRes[$sName] = $oSmarty->fetch( "ox:".$aData[0].$iLang );
            }
        } else {
            $oSmarty->oxidcache = new oxField($sDesc, oxField::T_RAW);
            $sRes = $oSmarty->fetch( "ox:{$sOxid}{$iLang}" );
        }

        // restore tpl vars for continuing smarty processing if it is in one
        $oSmarty->_tpl_vars = $sTplVars;
        $oSmarty->force_compile = $blForceRecompile;

        stopProfile("parseThroughSmarty");

        return $sRes;
    }

    /**
     * Templates directory setter
     *
     * @param string $sTplDir templates path
     *
     * @return null
     */
    public function setTemplateDir( $sTplDir )
    {
        if ( $sTplDir && !in_array( $sTplDir, $this->_aTemplateDir ) ) {
            $this->_aTemplateDir[] = $sTplDir;
        }
    }

    /**
     * Initializes and returns templates directory info array
     *
     * @return array
     */
    public function getTemplateDirs()
    {
        $myConfig = $this->getConfig();

        //T2010-01-13
        //#1531
        $this->setTemplateDir( $myConfig->getTemplateDir( $this->isAdmin() ) );

        if ( !$this->isAdmin() ) {
            $this->setTemplateDir( $myConfig->getOutDir( true ) . $myConfig->getConfigParam( 'sTheme' ) . "/tpl/" );
        }

        return $this->_aTemplateDir;
    }

    /**
     * sets properties of smarty object
     *
     * @param object $oSmarty template processor object (smarty)
     *
     * @return null
     */
    protected function _fillCommonSmartyProperties( $oSmarty )
    {
        $myConfig = $this->getConfig();
        $oSmarty->left_delimiter  = '[{';
        $oSmarty->right_delimiter = '}]';

        $oSmarty->register_resource( 'ox', array( 'ox_get_template',
                                                  'ox_get_timestamp',
                                                  'ox_get_secure',
                                                  'ox_get_trusted' ) );

        // $myConfig->blTemplateCaching; // DODGER #655 : permanently switched off as it doesnt work good enough
        $oSmarty->caching      = false;
        $oSmarty->compile_dir  = $myConfig->getConfigParam( 'sCompileDir' );
        $oSmarty->cache_dir    = $myConfig->getConfigParam( 'sCompileDir' );
        $oSmarty->template_dir = $this->getTemplateDirs();
        $oSmarty->compile_id   = md5( $oSmarty->template_dir[0].'__'.$myConfig->getShopId() );

        $oSmarty->default_template_handler_func = array(oxUtilsView::getInstance(),'_smartyDefaultTemplateHandler');

        include_once dirname(__FILE__).'/smarty/plugins/prefilter.oxblock.php';
        $oSmarty->register_prefilter('smarty_prefilter_oxblock');

        $iDebug = $myConfig->getConfigParam( 'iDebug' );
        if (  $iDebug == 1 || $iDebug == 3 || $iDebug == 4 ) {
            $oSmarty->debugging = true;
        }

        //demoshop security
        if ( !$myConfig->isDemoShop() ) {
            $oSmarty->php_handling = (int) $myConfig->getConfigParam( 'iSmartyPhpHandling' );
            $oSmarty->security     = false;
        } else {
            $oSmarty->php_handling = SMARTY_PHP_REMOVE;
            $oSmarty->security     = true;
            $oSmarty->security_settings['IF_FUNCS'][] = 'XML_ELEMENT_NODE';
            $oSmarty->security_settings['MODIFIER_FUNCS'][] = 'round';
            $oSmarty->security_settings['MODIFIER_FUNCS'][] = 'floor';
            $oSmarty->security_settings['MODIFIER_FUNCS'][] = 'trim';
            $oSmarty->security_settings['MODIFIER_FUNCS'][] = 'implode';
            $oSmarty->security_settings['MODIFIER_FUNCS'][] = 'is_array';
            $oSmarty->security_settings['ALLOW_CONSTANTS'] = true;
            $oSmarty->secure_dir = $oSmarty->template_dir;
        }


    }

    /**
     * sets compile check property to smarty object
     *
     * @param object $oSmarty template processor object (smarty)
     *
     * @return null
     */
    protected function _smartyCompileCheck( $oSmarty )
    {
        $myConfig = $this->getConfig();
        $oSmarty->compile_check  = $myConfig->getConfigParam( 'blCheckTemplates' );

    }

    /**
     * is called when a template cannot be obtained from its resource.
     *
     * @param string $sResourceType      template type
     * @param string $sResourceName      template file name
     * @param string $sResourceContent   template file content
     * @param int    $sResourceTimestamp template file timestamp
     * @param object $oSmarty            template processor object (smarty)
     *
     * @return bool
     */
    public function _smartyDefaultTemplateHandler($sResourceType, $sResourceName, $sResourceContent, $sResourceTimestamp, $oSmarty)
    {
        $myConfig = oxConfig::getInstance();
        if ( $sResourceType == 'file' && !is_readable($sResourceName) ) {
            $sResourceName      = $myConfig->getTemplatePath($sResourceName, $myConfig->isAdmin());
            $sResourceContent   = $oSmarty->_read_file($sResourceName);
            $sResourceTimestamp = filemtime($sResourceName);
            return is_file($sResourceName) && is_readable($sResourceName);
        }
        return false;
    }

    /**
     * retrieve module block contents
     *
     * @param string $sModule module name
     * @param string $sFile   module block file name without .tpl ending
     *
     * @see getTemplateBlocks
     * @throws oxException if block is not found
     *
     * @return string
     */
    protected function _getTemplateBlock($sModule, $sFile)
    {
        $sFileName = $this->getConfig()->getConfigParam( 'sShopDir' )."/modules/$sModule/out/blocks/$sFile.tpl";
        if (file_exists($sFileName) && is_readable($sFileName)) {
            return file_get_contents($sFileName);
        } else {
            throw oxNew( "oxException", "Template block file ($sFileName) not found for '$sModule' module." );
        }
    }

    /**
     * template blocks getter: retrieve sorted blocks for overriding in templates
     *
     * @param string $sFile filename of rendered template
     *
     * @see smarty_prefilter_oxblock
     *
     * @return array
     */
    public function getTemplateBlocks($sFile)
    {
        $oConfig = $this->getConfig();

        $sTplDir = trim($oConfig->getConfigParam('_sTemplateDir'), '/\\');
        $sFile = str_replace(array('\\', '//'), '/', $sFile);
        if (preg_match('@/'.preg_quote($sTplDir, '@').'/(.*)$@', $sFile, $m)) {
            $sFile = $m[1];
        }

        $aRet = array();
        $oDb = oxDb::getDb(true);
        $sFileParam = $oDb->quote($sFile);
        $sShpIdParam = $oDb->quote($oConfig->getShopId());

        if ( $this->_blIsTplBlocks === null ) {
            $this->_blIsTplBlocks = false;
            $sSql = "select COUNT(*) from oxtplblocks where oxactive=1 and oxshopid=$sShpIdParam";
            $rs = $oDb->getOne( $sSql );
            if ( $rs ) {
                $this->_blIsTplBlocks = true;
            }
        }

        if ( $this->_blIsTplBlocks ) {
            $sSql = "select * from oxtplblocks where oxactive=1 and oxshopid=$sShpIdParam and oxtemplate=$sFileParam order by oxpos asc";
            $rs = $oDb->Execute($sSql);

            if ($rs != false && $rs->recordCount() > 0) {
                while (!$rs->EOF) {
                    try {
                        if (!is_array($aRet[$rs->fields['OXBLOCKNAME']])) {
                            $aRet[$rs->fields['OXBLOCKNAME']] = array();
                        }
                        $aRet[$rs->fields['OXBLOCKNAME']][] = $this->_getTemplateBlock($rs->fields['OXMODULE'], $rs->fields['OXFILE']);
                    } catch (oxException $oE) {
                        $oE->debugOut();
                    }

                    $rs->moveNext();
                }
            }
        }

        return $aRet;
    }
}
