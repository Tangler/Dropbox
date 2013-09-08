<?php

namespace Tangler\Module\Dropbox;

use Tangler\Core\AbstractModule;
use Tangler\Core\Interfaces\ModuleInterface;

class Module extends AbstractModule implements ModuleInterface
{
    public function Init()
    {
        $this->setKey('dropbox');
        $this->setLabel('Dropbox module');
        $this->setDescription('Work with Dropbox files and folders');
        $this->setImageUrl('http://www.noordinaryhomestead.com/wp-content/uploads/2013/01/dropbox-logo.jpeg');

        $this->setTriggers(array(
            new \Tangler\Module\Dropbox\NewFileTrigger()
        ));

        $this->setActions(array(    
        ));
    }
}
