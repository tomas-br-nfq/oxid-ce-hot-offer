<?php

class nfq_oxarticlelist extends nfq_oxarticlelist_parent
{
	/**
	 * Loads list of articles
	 * 
	 * @return void
	 */
    public function loadList()
    {
        $sArticleTable = getViewName('oxarticles');
        
        $this->_aArray = array();
        $sSelect = 'SELECT * FROM ' . $sArticleTable
                . ' WHERE nfqhotofferactive=1 AND ' . $this->getBaseObject()->getSqlActiveSnippet() . ' AND oxissearch=1'
                . ' LIMIT 10';  // TODO Configurable limit
        
        $this->selectString($sSelect);
    }
    
}
