VERSIONS
========

v1.1.0
------
NEW features
* add page parameters inheritance
* add modal progress bar on publish all to prevent multiple publish all by the user
* template and form reorganisation to ease code understanding
* remove dependency with KitpagesRedirectBundle
* a configuration "renderer_twig_main" to change the layout.html.twig which adds cms toolbar
* added some technical values in pagePublish

CORRECTED
* remove unused date picker (and we miss informations on license)

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


