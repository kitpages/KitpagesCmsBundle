KitpagesCmsBundle Installation
==============================

There is two methods to install the Kitpages Cms Bundle :
* the easy one is to use the the KitpagesCmsEdition. This is a symfony distribution with all the bundles and configurations included to have a ready to run CMS
* the flexible (and complicated) one is to use the KitpagesCms as a bundle in an existing symfony2 project.

This page describe the second way.


Download all the source code
----------------------------

::

    wget http://symfony.com/download?v=Symfony_Standard_2.0.1.tgz
    tar zxvf Symfony_Standard_2.0.1.tgz
    cd Symfony


Edit deps file
--------------

::

    [KitpagesSimpleCacheBundle]
        git=http://github.com/kitpages/KitpagesSimpleCacheBundle.git
        target=Kitpages/SimpleCacheBundle

    [KitpagesUtilBundle]
        git=http://github.com/kitpages/KitpagesUtilBundle.git
        target=Kitpages/UtilBundle

    [KitpagesCmsBundle]
        git=http://github.com/kitpages/KitpagesCmsBundle.git
        target=Kitpages/CmsBundle

    [KitpagesFileBundle]
        git=http://github.com/kitpages/KitpagesFileBundle.git
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

run the vendors re-install
--------------------------

::

    ./bin/vendors install --reinstall


Change configuration
--------------------

In the app/AppKernel.php add
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    new Kitpages\CmsBundle\KitpagesCmsBundle(),
    new Kitpages\FileBundle\KitpagesFileBundle(),
    new Kitpages\SimpleCacheBundle\KitpagesSimpleCacheBundle(),
    new Kitpages\UtilBundle\KitpagesUtilBundle(),
    new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
    new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),


In the app/autoload.php add the line
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    'Kitpages'         => __DIR__.'/../vendor',
    'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/bundles/DataFixtures/DataFixturesBundle/lib',
    'Stof'             => __DIR__.'/../vendor/bundles',
    'Gedmo'            => __DIR__.'/../vendor/gedmo-doctrine-extensions/lib',


Edit app/config/parameters.ini, put your confs and add a new conf
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    base_url          = http://www.mywebsite.fr


Edit the app/config/config.yml
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

in twig section add the following

::

    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
        globals:
            cms:
                type: service
                id: kitpages.cms.model.cmsManager

add the following sections

::

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
                        class: '\Kitpages\CmsBundle\Form\Block\StandardForm'
                        name: "Standard"
                        twig: "KitpagesCmsBundle:Block:form/standard.html.twig"
            renderer:
                standard:
                    default:
                        type: "twig"
                        twig: "KitpagesCmsBundle:Block:renderer/standard/default.html.twig"
        page:
            layout_list:
                default:
                    renderer_twig: "KitpagesCmsBundle:Page:template/default.html.twig"
                    data_form_class: '\Kitpages\CmsBundle\Form\Page\DefaultForm'
                    data_form_twig: "KitpagesCmsBundle:Page:form/default.html.twig"
                    zone_list:
                        column:
                            renderer: "default"
                            authorized_block_template_list : ["standard"]
                        main:
                            renderer: "default"
                            authorized_block_template_list : ["standard"]
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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    ./app/console doctrine:database:create
    ./app/console doctrine:schema:update --force
    ./app/console doctrine:fixtures:load

Routing
-------

::

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

Modify base template
--------------------
Add at the end of the header

::

    {% block kitpages_cms_javascripts %}{% endblock %}

Add at the very beginning of the body :

::

    {% block kitpages_cms_toolbar %}{% endblock %}


Modify security.yml
-------------------

in the file app/conf/security.yml, you need to configure the firewall for every URL.
You can for example change the line

::

    pattern:    ^/demo/secured/

by

::

    pattern:    ^(/demo/secured/|/)

Test the result
===============

* try the URL /cms/arbo
* click on the "edit" button in the top toolbar
* click on one of the home page to edit the page
