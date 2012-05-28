Principles
==========

Before configuring and designing, you need ton understand basic concept of the Kitpages CMS.

Slogan
------

"If you can browse your website, you can manage it"

Exemple d'utilisation
---------------------

Let's see how it works.

[principles of the Kitpages cms in video -on kitpages.fr website-](http://www.kitpages.fr/fr/cms/134/principles-of-the-kitpages-cms) (3min)

(If someone better than me in english could add a better audio speech on this video, it would be great ! contact me on twitter (@plv).)

Contexts
--------
After the authentification with a ROLE_CMS_ADMIN role, you have 3 contexts

* production : in this context you see the same website than the internet user
* edition : in this context, buttons are appearing on the page. You can modify content of the blocks or page properties.
Your modifications will only be visible by the internet users after the publication of the page.
* preview : you can see the modifications done in edition mode, even before page publication.

Blocks, Zones, Pages
--------------------

* blocks can be standalone or in a zone
* zones can be standalone or in a page
* zone contain blocks
* pages contain zones and/or blocks

Layout
------

* Layouts are standard symfony2 twig files
* pages in the CMS are linked to a given layout
* pages templates and twig layout mapping is done in the config.yml

Configuration in config.yml
---------------------------

The configuration in config.yml is a bit complexe. It's aim is to define :

* block templates : block templates are a Form class and a twig that define the list of fields that you need in the
edition form of a block.
* block renderer : for a given block template (list of fields), you can have one or more renderers. With renderers,
you can display a given block with different presentations (ie html code) depending on where you are in the website.
* page : you define the different possible layouts. For each layout you have :
** a twig layout
** a list of zones that will be displayed by the twig layout
** for each zone, you define the block templates allowed in this zone and the renderer to use
** a form class and the associated twig used to define fields for the page properties (each page can have a property like meta title,...)


Navigation
----------

* The Kitpages CMS can manage the website navigation for you
* In this case all the website can be view in a tree (left button of the toolbar)
* in the tree you have 3 types of nodes
** standard pages : a page content with zones and/or blocks inside
** a link : just a link to another URL
** a technical node : it is just a way to have a kind of "bookmark" in your tree that you can access with a unique name

