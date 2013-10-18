<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class Context
{
    const VIEW_MODE_PROD = 1;
    const VIEW_MODE_PREVIEW = 2;
    const VIEW_MODE_EDIT = 3;  
    private $_session = null;
    
    public function __construct(Session $session, $viewModeDefault)
    {
        $this->_session = $session;
        $this->view_mode_default = $viewModeDefault;
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
            $viewMode = constant("self::$this->view_mode_default");
            $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
        }
        return $viewMode;
    }
    public function setViewMode($viewMode)
    {
        $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
    }
    
}