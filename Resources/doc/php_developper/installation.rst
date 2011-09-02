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

    [DoctrineExtensions]
        git=http://github.com/l3pp4rd/DoctrineExtensions.git
        target=/gedmo-doctrine-extensions

    [DoctrineExtensionsBundle]
        git=http://github.com/stof/StofDoctrineExtensionsBundle.git
        target=/bundles/Stof/DoctrineExtensionsBundle

    [DoctrineFixturesBundle]
        git=http://github.com/symfony/DoctrineFixturesBundle.git
        target=/bundles/Symfony/Bundle/DoctrineFixturesBundle

    [DataFixturesBundle]
        git=http://github.com/doctrine/data-fixtures.git
        target=/bundles/DataFixtures/DataFixturesBundle

Lancer l'update
.. code-block

    ./bin/vendors install --reinstall


Change configuration
--------------------
In the app/AppKernel.php add
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


Edit the app/config/config.yml

in twig section add the following
.. code-block
    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
        globals:
            cms:
                type: service
                id: kitpages.cms.model.cmsManager

add the following sections
.. code-block
    stof_doctrine_extensions:
        default_locale: en_US
        orm:
            default:
                timestampable: true # not needed: listeners are enabled by default
                sortable: true
                sluggable: true
                tree: true

    kitpages_cms:
        block:
            template:
                template_list:
                    standard:
                        class: "\Kitpages\CmsBundle\Form\BlockTemplateEditStandardType"
                        name: "Standard"
                        twig: "KitpagesCmsBundle:Block:edit/standard.html.twig"
                    news:
                        class: "\Kitpages\CmsBundle\Form\BlockTemplateEditNewsType"
                        name: "News"
                        twig: "KitpagesCmsBundle:Block:edit/news.html.twig"
            renderer:
                standard:
                    default:
                        type: "twig"
                        twig: "KitpagesCmsBundle:Block:render/standard-default.html.twig"
                news:
                    default:
                        type: "twig"
                        twig: "KitpagesCmsBundle:Block:render/news-default.html.twig"
                    short:
                        type: "twig"
                        twig: "KitpagesCmsBundle:Block:render/news-short.html.twig"
        page:
            layout_list:
                default:
                    twig: "KitpagesCmsBundle:Page:_exampleLayout.html.twig"
                    class_data: "\Kitpages\CmsBundle\Form\PageLayoutEditDefault"
                    twig_data: "KitpagesCmsBundle:Page:page-layout-edit-default.html.twig"
                    zone_list:
                        column:
                            render: "default"
                        main:
                            render: "default"
                with_col_right:
                    twig: "AppSiteBundle:Page:with_col_right.html.twig"
                    class_data: "\Kitpages\CmsBundle\Form\PageLayoutEditWithColRight"
                    twig_data: "KitpagesCmsBundle:Page:page-layout-edit-with_col_right.html.twig"
                    zone_list:
                        column:
                            render: "default"
                        main:
                            render: "default"
            default_twig: "::base.html.twig"

    kitpages_file:
        data_dir: %kernel.root_dir%/data/bundle/kitpagesfile
        public_prefix: data/bundle/kitpagesfile
        base_url: %base_url%

    services:
        twig.extension.text:
            class: Twig_Extensions_Extension_Text
            tags:
                - { name: twig.extension }



Create database if needed and update db
.. code-block

    ./app/console doctrine:database:create
    ./app/console doctrine:schema:update --force
    ./app/console doctrine:fixtures:load

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


