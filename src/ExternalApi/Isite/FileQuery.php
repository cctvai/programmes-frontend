<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite;

class FileQuery implements QueryInterface
{
    /** @var (int|string)[] */
    private $parameters = [];

    public function setFileId(string $fileId): self
    {
        $this->parameters['id'] = $fileId;
        return $this;
    }

    public function setDepth(int $depth): self
    {
        $this->parameters['depth'] = $depth;
        return $this;
    }

    public function getPath(): string
    {
        return '/content/file/?' . http_build_query($this->parameters);
    }

    public function setProjectId(string $projectId): self
    {
        $this->parameters['project'] = $projectId;
        return $this;
    }

    public function setPreview(bool $preview): self
    {
        $this->parameters['preview'] = $preview ? 'true' : 'false';
        return $this;
    }

    public function setAllowNonLive(bool $allow): self
    {
        $this->parameters['allowNonLive'] = $allow ? 'true' : 'false';
        return $this;
    }
}
