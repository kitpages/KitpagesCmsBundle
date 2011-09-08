VERSIONS
========

Sep 8, 2011 : v1.0.0-BETA1
---------------------------
first tag

v1.0.0-BETA2
------------
* in config.yml, name of configuration changed

In page.layout_list.default
    twig -> renderer_twig
    class_data -> data_form_class
    twig_data -> data_form_twig

Then in page.layout_list.default.zone_list.zone_name_sample
    render->renderer
    new parameter required : authorized_block_template_list: ["standard", "news"]

