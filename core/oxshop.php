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
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: oxshop.php 39228 2011-10-12 14:09:15Z arvydas.vapsva $
 */

/**
 * Shop manager.
 * Performs configuration and object loading or deletion.
 *
 * @package core
 */
class oxShop extends oxI18n
{
    /**
     * Core database table name. $sCoreTbl could be only original data table name and not view name.
     *
     * @var string
     */
    protected $_sCoreTbl = 'oxshops';

    /**
     * Name of current class.
     *
     * @var string
     */
    protected $_sClassName = 'oxshop';

    /**
     * Multi shop tables, set in config.
     *
     * @var array
     */
    protected $_aMultiShopTables = array();


    /**
     * Class constructor, initiates parent constructor (parent::oxBase()).
     */
    public function __construct()
    {
        parent::__construct();
        $this->init( 'oxshops' );
    }

    /**
     * Sets multi shop tables
     *
     * @param string $aMultiShopTables multi shop tables
     *
     * @return null
     */
    public function setMultiShopTables( $aMultiShopTables )
    {
        $this->_aMultiShopTables = $aMultiShopTables;
    }


    /**
     * (Re)generates shop views
     *
     * @param bool  $blMultishopInherit config option blMultishopInherit
     * @param array $aMallInherit       array of config options blMallInherit
     *
     * @return null
     */
    public function generateViews( $blMultishopInherit = false, $aMallInherit = null )
    {
        $oDB        = oxDb::getDb();
        $aLanguages = oxLang::getInstance()->getLanguageIds();

        $aTables = $aMultilangTables = oxLang::getInstance()->getMultiLangTables();

        $aQ = array();

        // Generate multitable views
        foreach ( $aTables as $sTable ) {
            $aQ[] = 'CREATE OR REPLACE VIEW oxv_'.$sTable.' AS SELECT * FROM '.$sTable.' '.$this->_getViewJoinAll($sTable);

            if (in_array($sTable, $aMultilangTables)) {
                foreach ($aLanguages as $iLang => $sLang) {
                    $aQ[] = 'CREATE OR REPLACE VIEW oxv_'.$sTable.'_'.$sLang.' AS SELECT '.$this->_getViewSelect($sTable, $iLang).' FROM '.$sTable.' '.$this->_getViewJoinLang($sTable, $iLang);
                }
            }
        }

        foreach ($aQ as $sQ) {
            $oDB->execute( $sQ );
        }
    }

    /**
     * Returns table field name mapping sql section for single language views
     *
     * @param string $sTable table name
     * @param array  $iLang  language id
     *
     * @return string $sSQL
     */
    protected function _getViewSelect($sTable,$iLang)
    {
        $oMetaData = oxNew('oxDbMetaDataHandler');
        $aFields = $oMetaData->getSinglelangFields($sTable, $iLang);
        foreach ($aFields as $sCoreField => $sField) {
            if ($sCoreField !== $sField) {
                $aFields[$sCoreField] = $sField.' AS '.$sCoreField;
            }
        }

        return implode(',', $aFields);
    }

    /**
     * Returns all language table view JOIN section
     *
     * @param string $sTable table name
     *
     * @return string $sSQL
     */
    protected function _getViewJoinAll($sTable)
    {
        $sJoin = ' ';
        $oMetaData = oxNew('oxDbMetaDataHandler');
        $aTables = $oMetaData->getAllMultiTables($sTable);
        if (count($aTables)) {
            foreach ($aTables as $sTableKey => $sTableName) {
                $sJoin .= "LEFT JOIN {$sTableName} USING (OXID) ";
            }
        }
        return $sJoin;
    }

    /**
     * Returns language table view JOIN section
     *
     * @param string $sTable table name
     * @param array  $iLang  language id
     *
     * @return string $sSQL
     */
    protected function _getViewJoinLang($sTable,$iLang)
    {
        $sJoin = ' ';
        $sLangTable = getLangTableName($sTable, $iLang);
        if ($sLangTable && $sLangTable !== $sTable) {
            $sJoin .= "LEFT JOIN {$sLangTable} USING (OXID) ";
        }
        return $sJoin;
    }

}
