<?php
namespace Kitpages\CmsBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Kitpages\CmsBundle\Entity\UserPreference;
use Kitpages\CmsBundle\Entity\Page;

class UserPreferenceManager
{
    ////
    // dependency injection
    ////
    protected $doctrine = null;


    public function __construct(
        Registry $doctrine
    )
    {
        $this->doctrine = $doctrine;
    }

     ////
    // actions
    ////
    public function getPreference($userName)
    {
        $em = $this->doctrine->getEntityManager();
        $userPreference = $em->getRepository('KitpagesCmsBundle:UserPreference')->findOneByUserName($userName);
        if (!($userPreference instanceof UserPreference)) {
            $userPreference = $this->setUserPreference($userName);
        }
        return $userPreference;
    }

    public function setUserPreference($userName) {
        $em = $this->doctrine->getEntityManager();
        $userPreference = new UserPreference();
        $userPreference->setUserName($userName);
        $em->persist($userPreference);
        $em->flush();
        return $userPreference;
    }

    public function setPreferenceDataTreeState($userName, $pageId, $action, $target){
        $em = $this->doctrine->getEntityManager();
        $userPreference = $em->getRepository('KitpagesCmsBundle:UserPreference')->findOneByUserName($userName);



        if ($userPreference instanceof UserPreference) {
            $dataTree = $userPreference->getDataTree();
            if ($target == 'tree' && $pageId == null) {
                if ($action == 'expand') {
                    $pageList = $em->getRepository('KitpagesCmsBundle:Page')->findAll();
                    foreach($pageList as $page) {
                        $dataTree['stateTree'][$page->getId()] = true;
                    }
                } else {
                    $dataTree['stateTree'] = array();
                }
            } elseif ($target == 'page' && $pageId != null) {
                if ($action == 'expand') {
                    $dataTree['stateTree'][$pageId] = true;
                } else {
                    $dataTree['stateTree'][$pageId] = false;
                }
            }
            $userPreference->setDataTree($dataTree);
            $em->persist($userPreference);
            $em->flush();
        }
    }

    public function setPreferenceDataTreeScroll($userName, $scroll){
            $em = $this->doctrine->getEntityManager();
            $userPreference = $em->getRepository('KitpagesCmsBundle:UserPreference')->findOneByUserName($userName);
            if ($userPreference instanceof UserPreference) {
                $dataTree = $userPreference->getDataTree();
                $dataTree['scrollTree'] = $scroll;
                $userPreference->setDataTree($dataTree);
                $em->persist($userPreference);
                $em->flush();
            }
        }
    ////
    // event listener
    ////




}
