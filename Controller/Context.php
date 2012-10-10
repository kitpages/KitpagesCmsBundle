<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

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
        $viewMode = $this->getSession()->get('kitpages_cms_context_view_mode');
        if (!$viewMode) {
            $this->getSession()->set('kitpages_cms_context_view_mode', self::VIEW_MODE_PROD);
            $viewMode = self::VIEW_MODE_PROD;
        }
        return $viewMode;
    }
    public function setViewMode($viewMode)
    {
        $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
    }
    
}