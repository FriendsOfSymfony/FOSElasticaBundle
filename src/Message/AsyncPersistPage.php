<?php
declare(strict_types=1);

namespace FOS\ElasticaBundle\Message;

class AsyncPersistPage
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var array
     */
    private $options;

    public function __construct(int $page, array $options)
    {
        $this->page = $page;
        $this->options = $options;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
