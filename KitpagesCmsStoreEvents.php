<?php
namespace Kitpages\CmsBundle;

final class KitpagesCmsStoreEvents
{
    /**
     * The store.order event is thrown each time an order is created
     * in the system.
     *
     * The event listener receives an Acme\StoreBundle\Event\FilterOrderEvent
     * instance.
     *
     * @var string
     */
    const onBlockPublish = 'kitpages.cms.block.publish';
}