<?php

namespace FlexPress\Plugins\CMS\Generators;

use FlexPress\Components\Hooks\HookableTrait;

class PDFThumbnail
{

    use HookableTrait;

    /**
     *
     * @param $postId
     *
     * @author Tim Perry
     * @since 3.2
     * @type action
     *
     */
    public function addAttachment($postId)
    {
        $this->processAttachment($postId);
    }

    /**
     *
     * @param $postId
     *
     * @author Tim Perry
     * @since 3.2
     * @type action
     *
     */
    public function editAttachment($postId)
    {
        $this->processAttachment($postId);
    }

    /**
     *
     * Processes the attachment for the given post id
     *
     * @param $postId
     * @author Tim Perry
     *
     */
    protected function processAttachment($postId)
    {

        $mimeType = get_post_mime_type($postId);

        if ($mimeType != "application/pdf") {
            return;
        }

        $file = get_attached_file($postId);

        if ($outputPath = $this->createPdfThumb($file, $postId)) {
            $this->createAndAttachAttachment($outputPath, $postId);
        }

    }

    /**
     *
     * Creates a thumb for the given path
     *
     * @param $path
     * @param $postId
     * @return bool
     * @author Tim Perry
     *
     */
    protected function createPdfThumb($path, $postId)
    {

        if (has_post_thumbnail($postId)) {
            return false;
        }

        if (!class_exists("Imagick")) {
            return false;
        }

        $pathInfo = pathinfo($path);
        $outputPath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . ".png";

        if (file_exists($outputPath)) {
            return false;
        }

        $im = new \Imagick($path . '[0]');

        $im->setImageFormat('png');
        $im->stripImage();

        $im->writeImage($outputPath);

        return $outputPath;

    }

    /***
     *
     * Creates an attachment for the given path and attaches it to
     * the given postId as well as set it as its thumbnail
     *
     * @param $path
     * @param $postId
     * @author Tim Perry
     */
    protected function createAndAttachAttachment($path, $postId)
    {

        $wpFileType = wp_check_filetype(basename($path), null);
        $wpUploadDir = wp_upload_dir();

        $attachment = array(

            'guid' => $wpUploadDir['url'] . '/' . basename($path),
            'post_mime_type' => $wpFileType['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($path)),
            'post_content' => '',
            'post_status' => 'inherit'

        );

        $attachId = wp_insert_attachment($attachment, $path, $postId);

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attach_data = wp_generate_attachment_metadata($attachId, $path);
        wp_update_attachment_metadata($attachId, $attach_data);

        update_post_meta($postId, "_thumbnail_id", $attachId);

    }
}
