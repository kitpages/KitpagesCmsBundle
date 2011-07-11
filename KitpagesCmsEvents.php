<?php
namespace Kitpages\CmsBundle;

final class KitpagesCmsEvents
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
    const onBlockModify = 'kitpages.cms.block.modify';
    const onBlockPublish = 'kitpages.cms.block.publish';
    const onBlockUnpublish = 'kitpages.cms.block.unpublish';
    const onBlockMove = 'kitpages.cms.zone.blockmove'; 
    const onBlockDelete = 'kitpages.cms.block.delete';      
    const onZonePublish = 'kitpages.cms.zone.publish';
    const onZoneUnpublish = 'kitpages.cms.zone.unpublish';
}