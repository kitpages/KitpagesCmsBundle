KitpagesCmsBundle Installation
==============================

Download all the source code
----------------------------

.. code-block

    wget http://symfony.com/download?v=Symfony_Standard_2.0.0.tgz
    tar zxvf Symfony_Standard_2.0.0.tgz
    cd Symfony
    

Editer les deps et ajouter les lignes suivantes :
.. code-block

    [KitpagesSimpleCacheBundle]
        git=http://github.com/kitpages/KitpagesSimpleCacheBundle.git
        target=Kitpages/SimpleCacheBundle

    [KitpagesRedirectBundle]
        git=http://github.com/kitpages/KitpagesRedirectBundle.git
        target=Kitpages/RedirectBundle

    [KitpagesUtilBundle]
        git=git@git.kitpages.fr:util-bundle.git
        target=Kitpages/UtilBundle

    [KitpagesCmsBundle]
        git=git@git.kitpages.fr:cmsbundle.git
        target=Kitpages/CmsBundle

    [KitpagesFileBundle]
        git=git@git.kitpages.fr:file-bundle.git
        target=Kitpages/FileBundle

Lancer l'update
.. code-block

    ./bin/vendors update


Change configuration
--------------------
In the app/appKernel.php add
.. code-block

    new Kitpages\CmsBundle\KitpagesCmsBundle(),
    new Kitpages\FileBundle\KitpagesFileBundle(),
    new Kitpages\SimpleCacheBundle\KitpagesSimpleCacheBundle(),
    new Kitpages\UtilBundle\KitpagesUtilBundle(),
    new Kitpages\RedirectBundle\KitpagesRedirectBundle(),
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
    new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),


In the app/autoload.php add the line
.. code-block

    'Kitpages'         => __DIR__.'/../vendor',
    'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/bundles/DataFixtures/DataFixturesBundle/lib',
    'Stof'             => __DIR__.'/../vendor/bundles',
    'Gedmo'            => __DIR__.'/../vendor/gedmo-doctrine-extensions/lib',


Edit app/config/parameters.ini, put your confs and add a new conf
.. code-block

    base_url          = http://www.kitpages.fr


Create database if needed and update db
.. code-block

    ./app/console doctrine:database:create
    ./app/console doctrine:schema:update --force

Routing
-------
.. code-block

    KitpagesRedirectBundle:
        resource: "@KitpagesRedirectBundle/Resources/config/routing.yml"
        prefix:   /cms/redirect

    kitpages_cms:
        resource: "@KitpagesCmsBundle/Resources/config/routing.yml"
        prefix: "cms"

    kitpages_file:
        resource: "@KitpagesFileBundle/Resources/config/routing.yml"
        prefix: "file"

    kitpages_cms_page_view_lang:
        pattern:  /{lang}/cms/{id}/{urlTitle}
        defaults: { _controller: KitpagesCmsBundle:Page:view, lang: fr }
        requirements:
            id: \d+
            lang:  en|fr


Routes
------
.. code-block:: yaml

    kitpages_cms:
        resource: "@KitpagesCmsBundle/Resources/config/routing.yml"
        prefix: "cms"

