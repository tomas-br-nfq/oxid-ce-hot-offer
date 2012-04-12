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
 * @version   SVN: $Id: actions_main.inc.php 22935 2009-10-05 12:26:25Z vilma $
 */

$aColumns = array( 'container1' => array(    // field , table,         visible, multilanguage, ident
                                        array( 'oxartnum', 'oxarticles', 1, 0, 0 ),
                                        array( 'oxtitle',  'oxarticles', 1, 1, 0 ),
                                        array( 'oxean',    'oxarticles', 1, 0, 0 ),
                                        array( 'oxmpn',    'oxarticles', 0, 0, 0 ),
                                        array( 'oxprice',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxstock',  'oxarticles', 0, 0, 0 ),
                                        array( 'oxid',     'oxarticles', 0, 0, 1 )
                                        )
                    );
/**
 * Class controls article assignment to attributes
 */
class ajaxComponent extends ajaxListComponent
{
    /**
     * If true extended column selection will be build
     *
     * @var bool
     */
    protected $_blAllowExtColumns = true;

    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function _getQuery()
    {
        $myConfig      = $this->getConfig();
        $oDb           = oxDb::getDb();
        $sArticleTable = $this->_getViewName( 'oxarticles' );
        $sO2CView      = $this->_getViewName( 'oxobject2category' );

        $sSelId      = oxConfig::getParameter( 'oxid' );
        $sSynchSelId = oxConfig::getParameter( 'synchoxid' );

        // category selected or not ?
        if ( !$sSelId ) {
            $sQAdd  = " from $sArticleTable where 1 ";
            $sQAdd .= $myConfig->getConfigParam( 'blVariantsSelection' )?'':" and $sArticleTable.oxparentid = '' ";
        } else {
            // selected category ?
            if ( $sSynchSelId ) {
                $sQAdd  = " from $sO2CView as oxobject2category left join $sArticleTable on ";
                $sQAdd .= $myConfig->getConfigParam( 'blVariantsSelection' )?" ($sArticleTable.oxid=oxobject2category.oxobjectid or $sArticleTable.oxparentid=oxobject2category.oxobjectid)":" $sArticleTable.oxid=oxobject2category.oxobjectid ";
                $sQAdd .= " where oxobject2category.oxcatnid = " . $oDb->quote( $sSelId ) . " ";
            }
        }
        // #1513C/#1826C - skip references, to not existing articles
        $sQAdd .= " and $sArticleTable.oxid IS NOT NULL ";

        // skipping self from list
        $sQAdd .= " and $sArticleTable.oxid != " . $oDb->quote( $sSynchSelId ) . " ";

        return $sQAdd;
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
        $sArtTable = $this->_getViewName('oxarticles');
        $sQ = parent::_addFilter( $sQ );

        // display variants or not ?
        $sQ .= $this->getConfig()->getConfigParam( 'blVariantsSelection' ) ? ' group by '.$sArtTable.'.oxid ' : '';
        return $sQ;
    }

    /**
     * Removing article assignment
     *
     * @return null
     */
    public function removeactionarticle()
    {
        $sActionId = oxConfig::getParameter( 'oxid');
        $oDb        = oxDb::getDb();

        $oDb->Execute(
              'delete from oxobject2action '
            . 'where oxactionid='.$oDb->quote($sActionId)
            . ' and oxclass = "oxarticle"'
        );
    }

    /**
     * Set article assignment
     *
     * @return null
     */
    public function setactionarticle()
    {
        $sArticleId = oxConfig::getParameter( 'oxarticleid' );
        $sActionId      = oxConfig::getParameter( 'oxid' );
        $oDb        = oxDb::getDb();

        $oDb->Execute(
              'delete from oxobject2action '
            . 'where oxactionid='.$oDb->quote($sActionId)
            . ' and oxclass = "oxarticle"'
        );

        $oObject2Promotion = oxNew( "oxbase" );
        $oObject2Promotion->init( 'oxobject2action' );
        $oObject2Promotion->oxobject2action__oxactionid = new oxField( $sActionId );
        $oObject2Promotion->oxobject2action__oxobjectid = new oxField( $sArticleId );
        $oObject2Promotion->oxobject2action__oxclass    = new oxField( "oxarticle" );
        $oObject2Promotion->save();
    }
}
