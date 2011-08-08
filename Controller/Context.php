<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Component\HttpFoundation\Session;

class Context
{
    const VIEW_MODE_PROD = 1;
    const VIEW_MODE_PREVIEW = 2;
    const VIEW_MODE_EDIT = 3;  
    private $_session = null;
    
    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    /**
     * @return Session $session
     */
    public function getSession() {
        return $this->_session;
    } 
    
    public function getViewMode()
    {
        return $this->getSession()->get('kitpages_cms_context_view_mode');
    }
    public function setViewMode($viewMode)
    {
        $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
    }
    
}