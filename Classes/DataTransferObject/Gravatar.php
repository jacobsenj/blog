<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\AgencyPack\Blog\DataTransferObject;

use Psr\Http\Message\UriInterface;

class Gravatar implements AvatarResource
{
    public function __construct(private readonly UriInterface $uri, private readonly string $contentType, private readonly string $content)
    {
    }

    #[\Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->contentType;
    }

    #[\Override]
    public function getContent(): string
    {
        return $this->content;
    }
}
