<?php
namespace Kitpages\CmsBundle;

final class KitpagesCmsEvents
{
    ////
    // block events
    ////
    const afterBlockModify = 'kitpages.cms.block.after.modify';
    
    const onBlockPublish = 'kitpages.cms.block.on.publish';
    const afterBlockPublish = 'kitpages.cms.block.on.publish';
    
    const onBlockDelete = 'kitpages.cms.block.on.delete';
    const afterBlockDelete = 'kitpages.cms.block.after.delete';
    
    ////
    // zone events
    ////
    const onBlockMove = 'kitpages.cms.zone.on.block_move'; 
    const afterBlockMove = 'kitpages.cms.zone.after.block_move'; 
    
    const onZonePublish = 'kitpages.cms.zone.on.publish';
    const afterZonePublish = 'kitpages.cms.zone.after.publish';

    const onZoneUnpublish = 'kitpages.cms.zone.on.unpublish';
    const afterZoneUnpublish = 'kitpages.cms.zone.after.unpublish';   

    const onZoneDelete = 'kitpages.cms.page.on.delete';
    const afterZoneDelete = 'kitpages.cms.page.after.delete';
    
    
    ////
    // page events
    ////
    const afterPageModify = 'kitpages.cms.page.after.modify';
    
    const onPagePublish = 'kitpages.cms.page.on.publish';
    const afterPagePublish = 'kitpages.cms.page.after.publish';
    
    const onPageUnpublish = 'kitpages.cms.page.on.unpublish';
    const afterPageUnpublish = 'kitpages.cms.page.after.unpublish';  
    
    const onPageDelete = 'kitpages.cms.page.on.delete';
    const afterPageDelete = 'kitpages.cms.page.after.delete';
    
}