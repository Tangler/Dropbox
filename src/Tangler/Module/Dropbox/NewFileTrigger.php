<?php

namespace Tangler\Module\Dropbox;

use Tangler\Core\Interfaces\TriggerInterface;
use Tangler\Core\AbstractTrigger;
use Dropbox;


class NewFileTrigger extends AbstractTrigger implements TriggerInterface
{
    private $client;

    public function init()
    {
        $this->setKey('new_file');
        $this->setLabel('New Dropbox file trigger');
        $this->setDescription('This thing monitors a Dropbox directory for new files');

        $this->parameter->defineParameter('accesstoken', 'string', 'API access token');
        $this->parameter->defineParameter('clientid', 'string', 'Client identifier');
        $this->parameter->defineParameter('path', 'string', 'Absolute path in Dropbox to monitor');

        $this->output->defineParameter('filename', 'string', 'Name of the new file');
        $this->output->defineParameter('createstamp', 'stamp', 'Create stamp of the file');
        $this->output->defineParameter('content', 'string', 'Contents of the new file');

    }



    public function poll($channel)
    {

        $accesstoken = $this->parameter->getValue('accesstoken');;
        $clientid = $this->parameter->getValue('clientid');
        if ($clientid=='') {
            $clientid = 'Tangler';
        }
        $path = $this->parameter->getValue('path');;
        $locale = null;
        $host = null;
        $this->client = new Dropbox\Client($accesstoken, $clientid, $locale, $host);



        echo "POLLING FOR NEW DROPBOX FILES in [" . $path . "]\n";
        $metadata = $this->client->getMetadataWithChildren($path);

        foreach($metadata['contents'] as $entry) {
            if (!$entry['is_dir']) {
                $key = $path . ':' . $entry['rev']; // todo prefix account!
                if (!$this->isProcessed($key)) {
                    $localfilename = '/tmp/' . $entry['rev'];
                    $filename = $entry['path'];
                    $stream = fopen($localfilename, "wb");
                    $metadata = $this->client->getFile($filename, $stream);

                    if ($metadata === null) {
                        echo "File not found on Dropbox.\n";
                    } else {
                        $content = file_get_contents($localfilename);
                        $this->output->setValue('filename', basename($filename));
                        $this->output->setValue('content', $content);
                        foreach($channel->getActions() as $action) {
                            $action->Run($this->output);
                        }
                    }
                    $this->setProcessed($key);
                }
            }
        }
    }
}
