<?php
/**
 * This file should be adapted to your needs in order to transform uploaded
 * files before import in the archive folder and the database of Omeka.
 *
 * @note This file must be adapted to your needs.
 *
 * @todo Create specific exception class.
 * @todo Use Imagick & GD for image watermark.
 */

function file_modify_default_parameters()
{
    return array_values(array(
        'imageLibrary' => 'ExternalImageMagick',
        // 'imageLibrary' => 'Imagick',
        // 'imageLibrary' => 'GD',

        // Image magick: limit parameters for some shared host.
        // Memory area can be set directly by arguments.
        // 'hostLimit' => ' ' . '-limit memory 50MB -limit map 100MB -limit area 25MB -limit disk 1GB -limit thread 2' . ' ',
        'hostLimit' => '',
    ));
}

/**
 * Run a general command on a file. Function should be executed here.
 *
 * You should check mime type when necessary.
 *
 * @note ImageMagick convert command is managed by the plugin for basic needs.
 *
 * @example This example adds a watermark to the image, with parameters.
 *
 * @return NULL if there is no error, else the error code.
 */
function file_modify_preprocess($file, $args)
{
    list($imageLibrary, $hostLimit) = file_modify_default_parameters();

    // Only process images.
    if (strstr($file->mime_type, '/', TRUE) != 'image') {
        return;
    }

    $filepath = $file->getPath('original');
    $filename = $file->original_filename;

    // Check to see if we have rotation embedded in file name
    $parts = explode('_~_', $filename);

    if (count($parts) > 1) {
      $rotation = escapeshellarg($parts[0]);
      $filepath = escapeshellarg($filepath);
      $file->original_filename = $parts[1];

      $command = "convert " . $hostLimit . " -rotate " . $rotation . " " . $filepath . " " . $filepath;

      unset($error);
      unset($output);
      exec($command, $output, $error);

      if ($error) {
        _log('[FileModify]: Error: ' . $error . PHP_EOL . 'Command:' . PHP_EOL . $command, Zend_Log::ERR);
        return $error;
      }
    }

    // Return error code if any.
    if ($error) {
        _log('[FileModify]: Unknown Error.', Zend_Log::ERR);
    }
    return $error;
}
