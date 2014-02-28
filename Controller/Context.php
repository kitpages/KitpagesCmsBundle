<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

class Context
{
    const VIEW_MODE_PROD = 1;
    const VIEW_MODE_PREVIEW = 2;
    const VIEW_MODE_EDIT = 3;  
    private $_session = null;
    
    public function __construct(Session $session, SecurityContext $security, $viewModeDefault)
    {
        $this->_session = $session;
        $this->security = $security;
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
        $username = null;
        $viewMode = $this->getSession()->get('kitpages_cms_context_view_mode');
        $token = $this->security->getToken();
        if (!$viewMode && $token != null && $this->security->isGranted('ROLE_CMS_ADMIN')) {
            $viewMode = constant("self::$this->view_mode_default");
            $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
        } elseif($token == null || !$this->security->isGranted('ROLE_CMS_ADMIN')) {
            $viewMode = self::VIEW_MODE_PROD;
        }
        return $viewMode;
    }
    public function setViewMode($viewMode)
    {
        $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
    }
    
}