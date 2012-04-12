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
 * @version   SVN: $Id: oxshopcontrol.php 40659 2011-12-16 13:24:46Z vilma $
 */

/**
 * Main shop actions controller. Processes user actions, logs
 * them (if needed), controlls output, redirects according to
 * processed methods logic. This class is initalized from index.php
 */
class oxShopControl extends oxSuperCfg
{
    /**
     * Profiler start time
     *
     * @var double
     */
    protected $_dTimeStart = null;

    /**
     * Profiler end time
     *
     * @var double
     */
    protected $_dTimeEnd = null;

    /**
     * errors to be displayed/returned
     *
     * @see _getErrors
     *
     * @var array
     */
    protected $_aErrors = null;

    /**
     * output handler object
     *
     * @see _getOuput
     *
     * @var oxOutput
     */
    protected $_oOutput = null;

    protected $_oCache = null;

    /**
     * Main shop manager, that sets shop status, executes configuration methods.
     * Executes oxShopControl::_runOnce(), if needed sets default class (according
     * to admin or regular activities).
     *
     * Session variables:
     * <b>actshop</b>
     *
     * @return null
     */
    public function start()
    {
        $myConfig = $this->getConfig();

        //perform tasks once per session
        $this->_runOnce();

        $sClass    = oxConfig::getParameter( 'cl' );
        $sFunction = oxConfig::getParameter( 'fnc' );

        if ( !$sClass ) {

            if ( !$this->isAdmin() ) {

                // first start of the shop
                // check wether we have to display mall startscreen or not
                if ( $myConfig->isMall() ) {

                    $iShopCount = oxDb::getDb()->getOne( 'select count(*) from oxshops where oxactive = 1' );

                    $sMallShopURL = $myConfig->getConfigParam( 'sMallShopURL' );
                    if ( $iShopCount && $iShopCount > 1 && $myConfig->getConfigParam( 'iMallMode' ) != 0 && !$sMallShopURL ) {
                        // no class specified so we need to change back to baseshop
                        $sClass = 'mallstart';
                    }
                }

                if ( !$sClass ) {
                    $sClass = 'start';
                }
            } else {
                $sClass = 'login';
            }

            oxSession::setVar( 'cl', $sClass );
        }

        try {
            $this->_process( $sClass, $sFunction );
        } catch( oxSystemComponentException $oEx ) {
            //possible reason: class does not exist etc. --> just redirect to start page
            if ( $this->_isDebugMode() ) {
                oxUtilsView::getInstance()->addErrorToDisplay( $oEx );
                $this->_process( 'exceptionError', 'displayExceptionError' );
            }
            $oEx->debugOut();

            if ( !$myConfig->getConfigParam( 'iDebug' ) ) {
                oxUtils::getInstance()->redirect( $myConfig->getShopHomeUrl() .'cl=start', true, 302 );
            }
        } catch ( oxCookieException $oEx ) {
            // redirect to start page and display the error
            if ( $this->_isDebugMode() ) {
                oxUtilsView::getInstance()->addErrorToDisplay( $oEx );
            }
            oxUtils::getInstance()->redirect( $myConfig->getShopHomeUrl() .'cl=start', true, 302 );
        }

        catch ( oxException $oEx) {
            //catching other not cought exceptions
            if ( $this->_isDebugMode() ) {
                oxUtilsView::getInstance()->addErrorToDisplay( $oEx );
                $this->_process( 'exceptionError', 'displayExceptionError' );
            }

            // log the exception
            $oEx->debugOut();
        }

    }

    /**
     * Logs user performad actions to DB. Skips action loggin if
     * it's search engine.
     *
     * @param string $sClass Name of class
     * @param srring $sFnc   Name of executed class method
     *
     * @return null
     */
    protected function _log( $sClass, $sFnc )
    {
        $oDb = oxDb::getDb();
        $sShopID = oxSession::getVar( 'actshop' );
        $sTime   = date( 'Y-m-d H:i:s' );
        $sSidQuoted    = $oDb->quote( $this->getSession()->getId() );
        $sUserIDQuoted = $oDb->quote( oxSession::getVar( 'usr' ) );
        $sCnid = oxConfig::getParameter( 'cnid' );
        $sAnid = oxConfig::getParameter( 'aid' ) ? oxConfig::getParameter( 'aid' ) : oxConfig::getParameter( 'anid' );
        $sParameter = '';

        if ( $sClass == 'info' ) {
            $sParameter = str_replace( '.tpl', '', oxConfig::getParameter('tpl') );
        } elseif ( $sClass == 'search' ) {
            $sParameter = oxConfig::getParameter( 'searchparam' );
        }

        $sFncQuoted = $oDb->quote( $sFnc );
        $sClassQuoted = $oDb->quote( $sClass );
        $sParameterQuoted = $oDb->quote( $sParameter );

        $sQ = "insert into oxlogs (oxtime, oxshopid, oxuserid, oxsessid, oxclass, oxfnc, oxcnid, oxanid, oxparameter) ".
              "values( '$sTime', '$sShopID', $sUserIDQuoted, $sSidQuoted, $sClassQuoted, $sFncQuoted, ".$oDb->quote( $sCnid ).", ".$oDb->quote( $sAnid ).", $sParameterQuoted )";
        $oDb->execute( $sQ );
    }

    // OXID : add timing
    /**
     * Starts resource monitor
     *
     * @return null
     */
    protected function _startMonitor()
    {
        if ( $this->_isDebugMode() ) {
            $this->_dTimeStart = microtime(true);
        }
    }

    /**
     * Stops resource monitor, summarizes and outputs values
     *
     * @param bool  $blIsCache  Is content cache
     * @param bool  $blIsCached Is content cached
     * @param bool  $sViewID    View ID
     * @param array $aViewData  View data
     *
     * @return null
     */
    protected function _stopMonitor( $blIsCache = false, $blIsCached = false, $sViewID = null, $aViewData = array() )
    {
        if ( $this->_isDebugMode() ) {
            $myConfig = $this->getConfig();
            /* @var $oDebugInfo oxDebugInfo */
            $oDebugInfo = oxNew('oxDebugInfo');

            $blHidden = ($this->getConfig()->getConfigParam( 'iDebug' ) == -1);

            $sLog = '';
            $sLogId = md5(time().rand().rand());
            $sLog .= "<div style='color:#630;margin:15px 0 0;cursor:pointer' onclick='var el=document.getElementById(\"debugInfoBlock_$sLogId\"); if (el.style.display==\"block\")el.style.display=\"none\"; else el.style.display = \"block\";'> ".$oDebugInfo->formatGeneralInfo()."(show/hide)</div>";
            $sLog .= "<div id='debugInfoBlock_$sLogId' style='display:".($blHidden?'none':'block')."' class='debugInfoBlock' align='left'>";

            // outputting template params
            if ( $myConfig->getConfigParam( 'iDebug' ) == 4 ) {
                $sLog .= $oDebugInfo->formatTemplateData($aViewData);
            }

            // output timing
            $this->_dTimeEnd = microtime(true);


            $sLog .= $oDebugInfo->formatMemoryUsage();
            $sLog .= $oDebugInfo->formatExecutionTime($this->getTotalTime());

            if (!isAdmin() && ($iDebug == 7)) {
                $sLog .= $oDebugInfo->formatDbInfo();
            }

            if (!isAdmin() && ($iDebug == 2 || $iDebug == 3 || $iDebug == 4)) {
                $sLog .= $oDebugInfo->formatAdoDbPerf();
            }

            $sLog .= '</div>';

            $this->_getOutputManager()->output('debuginfo', $sLog);
        }
    }

    /**
     * Returns the differnece between stored profiler end time and start time. Works only after _stopMonitor() is called, otherwise returns 0.
     *
     * @return  double
     */
    public function getTotalTime()
    {
        if ($this->_dTimeEnd && $this->_dTimeStart) {
            return $this->_dTimeEnd - $this->_dTimeStart;
        }

        return 0;
    }

    /**
     * Initiates object (object::init()), executes passed function
     * (oxShopControl::executeFunction(), if method returns some string - will
     * redirect page and will call another function according to returned
     * parameters), renders object (object::render()). Performs output processing
     * oxOutput::ProcessViewArray(). Passes template variables to template
     * engine witch generates output. Output is additionally processed
     * (oxOutput::Process()), fixed links according search engines optimization
     * rules (configurable in Admin area). Finally echoes the output.
     *
     * @param string $sClass    Name of class
     * @param string $sFunction Name of function
     *
     * @return null
     */
    protected function _process( $sClass, $sFunction )
    {
        startProfile('process');
        $myConfig = $this->getConfig();
        $myUtils  = oxUtils::getInstance();
        $sViewID = null;

        if ( !$myUtils->isSearchEngine() &&
             !( $this->isAdmin() || !$myConfig->getConfigParam( 'blLogging' ) ) ) {
            $this->_log( $sClass, $sFunction );
        }

        // starting resource monitor
        $this->_startMonitor();

        // caching params ...
        $sOutput      = null;
        $blIsCached   = false;

        $oViewObject = $this->_initializeViewObject($sClass, $sFunction);

        // executing user defined function
        $oViewObject->executeFunction( $oViewObject->getFncName() );


        // if no cache was stored before we should generate it
        if ( !$blIsCached ) {
            $sOutput = $this->_render($oViewObject);
        }


        $oOutput = $this->_getOutputManager();
        $oOutput->setCharset($oViewObject->getCharSet());

        if (oxConfig::getParameter('renderPartial')) {
            $oOutput->setOutputFormat(oxOutput::OUTPUT_FORMAT_JSON);
            $oOutput->output('errors', $this->_getFormattedErrors());
        }

        $oOutput->sendHeaders();

        $oOutput->output('content', $sOutput);

        $myConfig->pageClose();

        stopProfile('process');

        // stopping resource monitor
        $this->_stopMonitor( $oViewObject->getIsCallForCache(), $blIsCached, $sViewID, $oViewObject->getViewData() );

        // flush output (finalize)
        $oOutput->flushOutput();
    }

    /**
     * initialize and return view object
     *
     * @param string $sClass    view name
     * @param string $sFunction function name
     *
     * @return oxView
     */
    protected function _initializeViewObject($sClass, $sFunction)
    {
        $myConfig = $this->getConfig();

        // creating current view object
        $oViewObject = oxNew( $sClass );

        // store this call
        $oViewObject->setClassName( $sClass );
        $oViewObject->setFncName( $sFunction );

        $myConfig->setActiveView( $oViewObject );


        // init class
        $oViewObject->init();

        return $oViewObject;
    }


    /**
     * format error messages from _getErrors and return as array
     *
     * @return array
     */
    protected function _getFormattedErrors()
    {
        $aErrors = $this->_getErrors();
        $aFmtErrors = array();
        if ( is_array($aErrors) && count($aErrors) ) {
            foreach ( $aErrors as $sLocation => $aEx2 ) {
                foreach ( $aEx2 as $sKey => $oEr ) {
                    $oErr = unserialize( $oEr );
                    $aFmtErrors[$sLocation][$sKey] = $oErr->getOxMessage();
                }
            }
        }
        return $aFmtErrors;
    }

    /**
     * render oxView object
     *
     * @param oxView $oViewObject view object to render
     *
     * @return string
     */
    protected function _render($oViewObject)
    {
        // get Smarty is important here as it sets template directory correct
        $oSmarty = oxUtilsView::getInstance()->getSmarty();

        // render it
        $sTemplateName = $oViewObject->render();

        // check if template dir exists
        $sTemplateFile = $this->getConfig()->getTemplatePath( $sTemplateName, $this->isAdmin() ) ;
        if ( !file_exists( $sTemplateFile)) {
            $oEx = oxNew( 'oxSystemComponentException' );
            $oLang = oxLang::getInstance();
            $oEx->setMessage( 'EXCEPTION_SYSTEMCOMPONENT_TEMPLATENOTFOUND' );
            $oEx->setComponent( $sTemplateName );
            throw $oEx;
        }
        $aViewData = $oViewObject->getViewData();

        // Output processing. This is useful for modules. As sometimes you may want to process output manually.
        $oOutput = $this->_getOutputManager();
        $aViewData = $oOutput->processViewArray( $aViewData, $oViewObject->getClassName() );
        $oViewObject->setViewData( $aViewData );

        //add all exceptions to display
        $aErrors = $this->_getErrors();
        if ( is_array($aErrors) && count($aErrors) ) {
            oxUtilsView::getInstance()->passAllErrorsToView( $aViewData, $aErrors );
        }

        foreach ( array_keys( $aViewData ) as $sViewName ) {
            $oSmarty->assign_by_ref( $sViewName, $aViewData[$sViewName] );
        }

        // passing current view object to smarty
        $oSmarty->oxobject = $oViewObject;


        $sOutput = $oSmarty->fetch( $sTemplateName, $oViewObject->getViewId() );

        //Output processing - useful for modules as sometimes you may want to process output manually.
        $sOutput = $oOutput->process( $sOutput, $oViewObject->getClassName() );
        return $oOutput->addVersionTags( $sOutput );
    }

    /**
     * return output handler
     *
     * @return oxOutput
     */
    protected function _getOutputManager()
    {
        if (!$this->_oOutput) {
            $this->_oOutput = oxNew('oxOutput');
        }
        return $this->_oOutput;
    }

    /**
     * Outputs passed data
     *
     * @param string $sOutput data to output
     *
     * @deprecated from 4.5.0, when removing, use oxoutput directly
     *
     * @see oxOutput
     *
     * @return null
     */
    protected function _output( $sOutput )
    {
        echo $sOutput;
    }

    /**
     * return page errors array
     *
     * @return array
     */
    protected function _getErrors()
    {
        if (null === $this->_aErrors) {
            $this->_aErrors = oxSession::getVar( 'Errors' );
            if (null === $this->_aErrors) {
                $this->_aErrors = array();
            }
            // resetting errors from session
            oxSession::setVar( 'Errors', array() );
        }
        return $this->_aErrors;
    }

    /**
     * This function is only executed one time here we perform checks if we
     * only need once per session
     *
     * @return null
     */
    protected function _runOnce()
    {
        $myConfig = $this->getConfig();
        $blProductive = true;
        $blRunOnceExecuted = oxSession::getVar( 'blRunOnceExecuted' );

            $iErrorReporting = error_reporting();
            if ( version_compare(PHP_VERSION, '5.3.0', '>=') ) {
                // some 3rd party libraries still use deprecated functions
                $iErrorReporting = E_ALL ^ E_NOTICE ^ E_DEPRECATED;
            } else {
                $iErrorReporting = E_ALL ^ E_NOTICE;
            }
            // A. is it the right place for this code ?
            // productive mode ?
            if ( ! ( $blProductive = $myConfig->isProductiveMode() ) ) {
                if ( is_null($myConfig->getConfigParam( 'iDebug' )) ) {
                    $myConfig->setConfigParam( 'iDebug', -1 );
                }
            } else {
                // disable error logging if server is misconfigured
                // #2015 E_NONE replaced with 0
                if ( !ini_get( 'log_errors' ) ) {
                    $iErrorReporting = 0;
                }
            }
            error_reporting($iErrorReporting);


        if ( !$blRunOnceExecuted && !$this->isAdmin() && $blProductive ) {

            $sTpl = false;
            // perform stuff - check if setup is still there
            if ( file_exists( $myConfig->getConfigParam( 'sShopDir' ) . '/setup/index.php' ) ) {
                $sTpl = 'message/err_setup.tpl';
            }

            if ( $sTpl ) {
                $oActView = oxNew( 'oxubase' );
                $oSmarty = oxUtilsView::getInstance()->getSmarty();
                $oSmarty->assign('oView', $oActView );
                $oSmarty->assign('oViewConf', $oActView->getViewConfig() );
                oxUtils::getInstance()->showMessageAndExit( $oSmarty->fetch( $sTpl ) );
            }

            oxSession::setVar( 'blRunOnceExecuted', true );
        }
    }

    /**
     * Checks if shop is in debug mode
     *
     * @return bool
     */
    protected function _isDebugMode()
    {
        if ( !$this->isAdmin() && $this->getConfig()->getConfigParam( 'iDebug' ) ) {
            return true;
        }

        return false;
    }
}
