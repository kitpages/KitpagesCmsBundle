<?php

namespace Kitpages\CmsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Kitpages\CmsBundle\Controller\Context;

class PageListener {
    
    ////
    // dependency injection
    ////
    protected $doctrine = null;
    protected $context = null;
    protected $router = null;
    protected $logger = null;
    
    public function __construct(
        Registry $doctrine,
        Context $context,
        RouterInterface $router,
        LoggerInterface $logger
    )
    {
        $this->context = $context;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->router = $router;
    }
    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }
    /**
     * @return LoggerInterface $logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    }    
    /**
     * @return Context $context
     */
    public function getContext() {
        return $this->context;
    }    

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        $request = $event->getRequest();
        $baseUrl = rtrim($request->getBaseUrl(), '/');
        $requestUri = $request->getRequestUri();
        $relativeRequestUri = str_replace($baseUrl, '', $requestUri);
        $relativeRequestUri = '/'.ltrim($relativeRequestUri, '/');
        
        $viewMode = $this->getContext()->getViewMode();
        
        $this->getLogger()->debug("************ PageListener, relativeRequestUri=$relativeRequestUri, viewMode=$viewMode");
        
        if ($viewMode == Context::VIEW_MODE_PROD) {
            $pagePublishRepo = $this->getDoctrine()->getEntityManager()->getRepository('KitpagesCmsBundle:PagePublish');
            $pagePublish = $pagePublishRepo->findByForcedUrl($relativeRequestUri);
            if ($pagePublish == null) {
                return;
            }
            $page = $pagePublish->getPage();

            $this->fakeRouter(
                $request,
                $pagePublish->getLanguage(),
                $page->getId(),
                $pagePublish->getUrlTitle()
            );
            return;
        }

        if (($viewMode == Context::VIEW_MODE_EDIT) || ($viewMode == Context::VIEW_MODE_PREVIEW) ) {
            $pageRepo = $this->getDoctrine()->getEntityManager()->getRepository('KitpagesCmsBundle:Page');
            $page = $pageRepo->findByForcedUrl($relativeRequestUri);
            if ($page == null) {
                return;
            }
            $this->fakeRouter(
                $request,
                $page->getLanguage(),
                $page->getId(),
                $page->getUrlTitle()
            );
            return;
        }

    }
    
    protected function fakeRouter($request, $language, $pageId, $urlTitle)
    {
        $url = $this->getRouter()->generate(
            'kitpages_cms_page_view_lang',
            array(
                'id' => $pageId,
                'lang' => $language,
                'urlTitle' => $urlTitle
            )
        );
        $baseUrl = trim($request->getBaseUrl(), '/');
        $pathInfo = str_replace($baseUrl, '', $url);
        $pathInfo = '/'.trim($pathInfo, '/');

        $this->getLogger()->debug("PageListener, pathInfo=$pathInfo");

        // add attributes based on the path info (routing)
        try {
            $parameters = $this->router->match($pathInfo);

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], print_r($parameters, true)));
            }

            $request->attributes->add($parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $pathInfo);

            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $pathInfo, strtoupper(implode(', ', $e->getAllowedMethods())));

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }

}

?>
