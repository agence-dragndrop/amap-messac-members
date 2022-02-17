<?php

namespace App\Utility;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AttachmentFile
{
    public const FILE_NAME = 'tarifs-orange-bio.pdf';
    private ParameterBagInterface $parameterBag;

    /**
     * AttachmentFile constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }


    public function getFileName(): string
    {
        return self::FILE_NAME;
    }

    public function getFilePath(): string
    {
        return $this->parameterBag->get('attachment_dir');
    }

    public function getFullPath(): string
    {
        return $this->parameterBag->get('attachment_dir') . "/" . self::FILE_NAME;
    }

    public function getPublicPath(): string
    {
        return $this->parameterBag->get('attachment_public_dir') . "/" . self::FILE_NAME;
    }

    public function fileExist(): bool
    {
        return file_exists($this->getFullPath());
    }

    public function unlink(): bool
    {
        return unlink($this->getFullPath());
    }
}
