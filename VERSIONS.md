VERSIONS
========

future v1.4.0 (master)
=====================
NEW features

DEBUG

MIGRATION

current v1.3.0 (Feb 20, 2012)
=============================

NEW features
* [NEW] add page creation buttons in the navigation
* [NEW] tree is expandable and collapsable
* [NEW] remember Tree state (which node are opened ? scroll level of the page) for each user
* page link to the first child (for nav entries that goes to the 1st page of the category)
* inherited values in the tree => you can set a page value for a specific branch of the site tree
* remove dependecy with KitpagesRedirectionBundle
* new CSS classes in the navigation code to ease CSS designer work
*

DEBUG
* minor fix in navigation display
* fix in adding technical page
* correction of the filter of RTE values
* change ROLE_ADMIN to ROLE_CMS_ADMIN
* fix bug when URL title was null

MIGRATION
* you should add something like that in your security.yml :
    role_hierarchy:
        ROLE_ADMIN: [ ROLE_USER, ROLE_CMS_ADMIN]

* you need to run : ./app/console doctrine:schema:update --force
and click on "publish all pages and navigation" in the tree page


v1.2.0 (oct 21, 2011)
=====================
NEW features
* action render for 404 page for easing navigation for admins
* canonical URL for zones and block
* title for zones
* block renderer : display anchors for anchor links inside a zone
* pageId displayed in advanced mode in the tree (helps to change parentId if you want to move a page)
* add a filter on RTE editors to remove formats included by word for example
* add a breadcrumb system

DEBUG
* add forceUrl and linkUrl in navPublish and use a published version of forceUrl
* add an error message on a duplication of slug
* add an error message on a duplication of the forceUrl
* modify constant afterBlockPublish in KitpagesCmsEvents

HOW TO MIGRATE
--------------
you need to run : ./app/console doctrine:schema:update --force
and click on "publish all pages and navigation" in the tree page

v1.1.0
------
NEW features
* add page parameters inheritance
* add modal progress bar on publish all to prevent multiple publish all by the user
* template and form reorganisation to ease code understanding
* remove dependency with KitpagesRedirectBundle
* a configuration "renderer_twig_main" to change the layout.html.twig which adds cms toolbar
* added some technical values in pagePublish
* added indicators on needed publication in the tree view and in edition page
* Breadcrumb management
* added a big "Publish all pages and navigation" button

REFACTORING
* directory reorganization for templates

CORRECTED
* remove unused date picker (and we miss informations on license)
* correction on the navigation when the current page was outsite the displayed navigation
* corrections on the flags displayed when the publication is needed (navigation and page)
* pageData was not transmitted to the twig layout
* update cms css for less intrusive stylesheets on the real site design

v1.0.0
------
* in config.yml, name of configuration changed

In page.layout_list.default
    twig -> renderer_twig
    class_data -> data_form_class
    twig_data -> data_form_twig

Then in page.layout_list.default.zone_list.zone_name_sample
    render->renderer
    new parameter required : authorized_block_template_list: ["standard", "news"]


Sep 8, 2011 : v1.0.0-BETA1
---------------------------
first tag


