<?php

namespace JiraCloud\Attachment;

use JiraCloud\Issue\Attachment;

/**
 * Class AttachmentService.
 */
class AttachmentService extends \JiraCloud\JiraClient
{
    private $uri = '/attachment/';

    /**
     * Returns the meta-data for an attachment, including the URI of the actual attached file.
     *
     * @param string|int $id attachment Id
     * @outDir string downloads the content and store into outDir
     * @overwrite boolean determines whether to overwrite the file if it already exists.
     * @mode int outDir creation mode.
     * @recursive boolean Allows the creation of nested directories specified in the pathname.
     *
     *@throws \JsonMapper_Exception
     * @throws \JiraCloud\JiraException
     *
     * @return \JiraCloud\ADF\Attachment
     */
    public function get($id, $outDir = null, $overwrite = false, $mode = 0777, $recursive = true)
    {
        $ret = $this->exec($this->uri.$id, null);

        $this->log->info("Result=\n".$ret);

        $attachment = $this->json_mapper->map(
            json_decode($ret),
            new Attachment()
        );

        if ($outDir == null) {
            return $attachment;
        }

        // download contents
        if (!file_exists($outDir)) {
            mkdir($outDir, $mode, $recursive);
        }

        // extract filename
        $file = substr(strrchr($attachment->content, '/'), 1);

        if (file_exists($outDir.DIRECTORY_SEPARATOR.$file) && $overwrite == false) {
            return $attachment;
        }

        $this->download($attachment->content, $outDir, $file);

        return $attachment;
    }

    /**
     * Remove an attachment from an issue.
     *
     * @param string|int $id attachment id
     *
     * @throws \JiraCloud\JiraException
     *
     * @return string
     */
    public function remove($id)
    {
        $ret = $this->exec($this->uri.$id, null, 'DELETE');

        $this->log->info("Result=\n".$ret);

        return $ret;
    }
}
