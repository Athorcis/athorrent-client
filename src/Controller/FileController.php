<?php

namespace Athorrent\Controller;

use Athorrent\Filesystem\FilesystemInterface;
use Athorrent\Filesystem\TorrentFilesystem;
use Silex\Application;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/files", name="files")
 */
class FileController extends AbstractFileController
{
    /**
     * @param Application $app
     * @return TorrentFilesystem
     */
    protected function getFilesystem(Application $app): FilesystemInterface
    {
        return $app['user.fs'];
    }
}
