<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;

trait MediaActions
{
    use InteractsWithMedia;

    /**
     * Retrieve all medias by model ids and collections.
     * Returns array $ret[$media->model_id][$media->collection_name][]
     *
     * @param mixed $modelIds
     * @param mixed $collections
     * @param bool $withFileInfo
     * @return array
     */
    public function getMediaByModelIds($modelIds = [], $collections = [], bool $withFileInfo = false): array
    {
        if (empty($modelIds)) {
            $modelIds = $this->id;
        }
        return Media::getMediaByModelIds($modelIds, $collections, self::class, $withFileInfo);
    }

    /**
     * Store the media by key from request to collection
     *
     * @param string $param
     * @param string $collection
     * @param Request $request
     * @return array
     */
    public function storeMedia(string $param, string $collection, Request $request) : array
    {
        $response = [];
        $file = $request->file($param);
        if ($file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    if ($f->isValid()) {
                        $response[] = $this->addMedia($f)->toMediaCollection($collection);
                    }
                }
            } elseif ($file->isValid()) {
                $response[] = $this->addMedia($file)->toMediaCollection($collection);
            }
        }

        return $response;
    }

    /**
     * Create response array from media collection
     *
     * @param $medias
     * @param bool $withFileInfo
     * @return array
     */
    public function responseArrFromMedias($medias, bool $withFileInfo = false): array
    {
        $response = [];

        if (is_array($medias)){
            foreach ($medias as $media) {
                $response[] = Media::responseFromOneMedia($media, $withFileInfo);
            }
        } else {
            $response[] = Media::responseFromOneMedia($medias, $withFileInfo);
        }

        return $response;
    }

    public function deleteMediaById(int $mediaId)
    {
        try {
            $this->deleteMedia($mediaId);
        } catch (\Exception $e) {
            return 'Cant delete the media. Exception message: ' . $e->getMessage();
        }

        return true;
    }
}
