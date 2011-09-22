Design, template and block creation
===================================

this page is a work in progress

Block template creation
=======================
Let's say we will create a block template named "news"

Files to create or update :
* change config
** app/config/config.yml : add a "news" block template
* Form for editing data from news
** App/SiteBundle/Form/Block/News.php
** App/SiteBundle/Resources/views/Block/form/news.html.twig
* Create 2 renderers
** App/SiteBundle/Resources/views/Block/renderer/news/default.html.twig
** App/SiteBundle/Resources/views/Block/renderer/news/short.html.twig

global page layout creation
===========================
The Kitpages CMS uses the same page layout system as any other Symfony2 project.

If you want to change the layout of your website, you just have to change for
example your app/Resource/view/base.html.twig

New page layout
===============
If you want to create a new page layout named "home":

you have to create or update these files :

* in app/config/config.yml
** add a new layout in kitpages_cms.page.layout_list
* Form for editing data from home
** App/SiteBundle/Form/Page/Home.php
** App/SiteBundle/Resources/views/Page/form/home.html.twig
* renderer (a very standard symfony layout)
** App/SiteBundle/Resources/views/Page/template/home.html.twig
