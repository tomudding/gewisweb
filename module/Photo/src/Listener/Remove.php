<?php

namespace Photo\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Photo\Model\{
    Album as AlbumModel,
    Photo as PhotoModel,
};
use Photo\Service\{
    Album as AlbumService,
    Photo as PhotoService,
};

/**
 * Doctrine event listener class for Album and Photo entities.
 * Do not instantiate this class manually.
 */
class Remove
{
    public function __construct(
        private readonly PhotoService $photoService,
        private readonly AlbumService $albumService,
    ) {
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof AlbumModel) {
            $this->albumRemoved($entity);
        } elseif ($entity instanceof PhotoModel) {
            $this->photoRemoved($entity);
        }
    }

    /**
     * @param PhotoModel $photo
     */
    protected function photoRemoved(PhotoModel $photo): void
    {
        $this->photoService->deletePhotoFiles($photo);
    }

    /**
     * @param AlbumModel $album
     */
    protected function albumRemoved(AlbumModel $album): void
    {
        $this->albumService->deleteAlbumCover($album);
    }
}