Design, template and block creation

Introduction
============


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



