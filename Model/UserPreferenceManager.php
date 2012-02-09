<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\UserPreference;

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

    public function setPreferenceDataTree($userName, $pageId, $statePage){
        $em = $this->doctrine->getEntityManager();
        $userPreference = $em->getRepository('KitpagesCmsBundle:UserPreference')->findOneByUserName($userName);
        if ($userPreference instanceof UserPreference) {
            $dataTree = $userPreference->getDataTree();
            if ($statePage == 'collapsed') {
                $dataTree['stateTree'][$pageId] = true;
            } else {
                $dataTree['stateTree'][$pageId] = false;
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
