<?php

class nfq_hotoffer extends oxUBase
{
	/**
	 * View template
	 * 
	 * @var string
	 */
	protected $_sThisTemplate = 'modules/hotoffer/list.tpl';
	
	
	/**
	 * List of articles
	 * 
	 * @var nfq_oxarticlelist
	 */
	protected $_oArticleList;
	
	
	/**
	 * Renders view object
	 * 
	 * @return void
	 */
    public function render()
    {
        parent::render();
        return $this->_sThisTemplate;
    }
    
    
    /**
     * Returns limited list of articles
     * 
     * @return nfq_oxarticlelist
     */
    public function getList()
    {
        if ( $this->_oArticleList === null ) {
        	
            $this->_oArticleList = array(); // nfq_oxarticlelist implements Iterator, the response must be iteratable
            
            $oArticleList = oxNew('nfq_oxarticlelist');
            $oArticleList->loadList();
            if ($oArticleList->count() ) {
                $this->_oArticleList = $oArticleList;
            }
        }
        
        return $this->_oArticleList;
    }
    
}
