<?php

namespace App\Support\SeriesTasks;

readonly class SeriesLibraryCollection
{
    public function __construct(
        public string $type,
        public ?int $id,
        public string $title,
        public ?string $description = null,
        public bool $selectable = true,
        public ?string $blockedReason = null,
        public ?int $parentId = null,
        public int $directResourceCount = 0,
        public int $treeResourceCount = 0,
        public int $childFolderCount = 0,
    ) {}
}
