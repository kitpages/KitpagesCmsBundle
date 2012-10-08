MIGRATION
=============================
* upgrade fileBundle in version 2.0

DEPS
=====================

```
[KitpagesCmsBundle]
    git=http://github.com/kitpages/KitpagesCmsBundle.git
    target=Kitpages/CmsBundle
    version=origin/v2.0

[KitpagesFileBundle]
    git=http://github.com/kitpages/KitpagesFileBundle.git
    target=Kitpages/FileBundle
    version=origin/master

; KitFileBundle
[Imagine]
    git=http://github.com/avalanche123/Imagine.git
    target=imagine

[KitpagesFileSystemBundle]
    git=http://github.com/kitpages/KitpagesFileSystemBundle.git
    target=Kitpages/FileSystemBundle

[aws-sdk]
    git=http://github.com/amazonwebservices/aws-sdk-for-php
    target=aws-sdk
    version=1.5.4
```

app/autoload.php
=====================

```
$loader->registerNamespaces(array(
    // ...
    'Imagine'          => __DIR__.'/../vendor/imagine/lib',
));

// AWS SDK needs a special autoloader
require_once __DIR__.'/../vendor/aws-sdk/sdk.class.php';
```


app/AppKernel.php
=====================

```
$bundles = array(
...
    new Kitpages\FileSystemBundle\KitpagesFileSystemBundle(),
);
```

Configuration
=====================

```
kitpages_file:
    tmp_dir: %kernel.root_dir%/data/tmp
    type_list:
        image:
            resize:
                form: 'kit_file.image.resize.form'
                form_twig: 'KitpagesFileBundle:ActionOnFile:Image/Resize/form.html.twig'
                handler_form: 'kit_file.image.resize.form.handler'
                library: 'imagine.gd'

kitpages_file_system:
    file_system_list:
        kitpagesFile:
            local:
                directory_public: %kernel.root_dir%/../web
                directory_private: %kernel.root_dir%
                base_url: %base_url%
    OR
kitpages_file_system:
    file_system_list:
        kitpagesFile:
            amazon_s3:
                bucket_name: %kitpagesFile_amazons3_bucketname%
                key: %kitpagesFile_amazons3_key%
                secret_key: %kitpagesFile_amazons3_secretkey%
```

VENDORS UPDATE
=====================

```
php bin/vendors update

php app/console doctrine:schema:update --force
```

launch command
=====================

```
php app/console kitCms:updateForFileBundle
php app/console kitFile:updateDatabase
```

update BLOCK and PAGE
=====================

```
replace
    {% render 'KitpagesFileBundle:Upload:widget' with {'fieldId': 'form_data_root_media_mainImage'} %}
    By
    {% render 'KitpagesCmsBundle:Block:uploadWidget' with {'blockId':id, 'fieldId': 'form_data_root_media_mainImage', parameterList:{'multi': false, 'publishParent': false} } %}

replace
    data.root.media_mainImage
    By
    data.media.mainImage.0.default

replace
    data.root.url_media_mainImage
    By
    data.media.mainImage.0.default.url
```