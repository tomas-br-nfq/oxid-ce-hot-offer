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
 * @version   SVN: $Id: oxnewslist.php 25467 2010-02-01 14:14:26Z alfonsas $
 */

/**
 * News list manager.
 * Creates news objects, fetches its data.
 * @package core
 */
class oxNewslist extends oxList
{
    /**
     * List Object class name
     *
     * @var string
     */
    protected $_sObjectsInListName = 'oxnews';

    /**
     * Ref. to user object
     */
    protected $_oUser = null;

    /**
     * Loads news stored in DB, filtered by user groups, returns array, filled with
     * objects, that keeps news data.
     *
     * @param integer $iLimit Limit of records to fetch from DB(default 0)
     *
     * @return array
     */
    public function loadNews( $iLimit = 0 )
    {
        if ( $iLimit ) {
            $this->setSqlLimit( 0, $iLimit );
        }

        $sNewsViewName = getViewName( 'oxnews' );
        $oBaseObject   = $this->getBaseObject();
        $sSelectFields = $oBaseObject->getSelectFields();

        if ( $oUser = $this->getUser() ) {
            // performance - only join if user is logged in
            $sSelect  = "select $sSelectFields from $sNewsViewName ";
            $sSelect .= "left join oxobject2group on oxobject2group.oxobjectid=$sNewsViewName.oxid where ";
            $sSelect .= "oxobject2group.oxgroupsid in ( select oxgroupsid from oxobject2group where oxobjectid='".$oUser->getId()."' ) or ";
            $sSelect .= "( oxobject2group.oxgroupsid is null ) ";
        } else {
            $sSelect  = "select $sSelectFields, oxobject2group.oxgroupsid from $sNewsViewName ";
            $sSelect .= "left join oxobject2group on oxobject2group.oxobjectid=$sNewsViewName.oxid where oxobject2group.oxgroupsid is null ";
        }

        $sSelect .= " and ".$oBaseObject->getSqlActiveSnippet();
        $sSelect .= " group by $sNewsViewName.oxid order by $sNewsViewName.oxdate desc ";

        $this->selectString( $sSelect );
    }

    /**
     * News list user setter
     *
     * @param oxuser $oUser user object
     *
     * @return null
     */
    public function setUser( $oUser )
    {
        $this->_oUser = $oUser;
    }

    /**
     * News list user getter
     *
     * @return oxuser
     */
    public function getUser()
    {
        if ( $this->_oUser == null ) {
            $this->_oUser = parent::getUser();
        }

        return $this->_oUser;
    }
}
