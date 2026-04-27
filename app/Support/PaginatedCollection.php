<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaginatedCollection
{

    protected int $page;
    protected int $limit;
    protected int $total;
    protected int $totalPages;
    protected string $currPageLink;
    protected ?string $prevPageLink;
    protected ?string $nextPageLink;
    protected Collection $data;

    /**
     * Create a new class instance.
     */
    public function __construct(
    )
    {
        //
    }

    public static function fromPaginator(LengthAwarePaginator $paginator, ?callable $transform=null): self
    {
        $data = $paginator->getCollection();
        if ($transform) {
            $data = $data->map($transform);
        }

        return (new self)
            ->setPage($paginator->currentPage())
            ->setLimit($paginator->perPage())
            ->setTotal($paginator->total())
            ->setTotalPages($paginator->lastPage())
            ->setCurrPageLink($paginator->url($paginator->currentPage()))
            ->setPrevPageLink($paginator->previousPageUrl())
            ->setNextPageLink($paginator->nextPageUrl())
            ->setData($data);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function setTotalPages(int $totalPages): self
    {
        $this->totalPages = $totalPages;
        return $this;
    }

    /**
     * @param callable|null $transform
     * @return Collection
     */
    public function getData(?callable $transform=null): Collection
    {
        $data = $this->data;
        if ($transform) {
            $data = $data->map($transform);
        }

        return $data;
    }

    public function setData(Collection $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getCurrPageLink(): string
    {
        return $this->currPageLink;
    }

    public function setCurrPageLink(string $currPageLink): self
    {
        $this->currPageLink = $currPageLink;
        return $this;
    }

    public function getPrevPageLink(): ?string
    {
        return $this->prevPageLink;
    }

    public function setPrevPageLink(?string $prevPageLink): self
    {
        $this->prevPageLink = $prevPageLink;
        return $this;
    }

    public function getNextPageLink(): ?string
    {
        return $this->nextPageLink;
    }

    public function setNextPageLink(?string $nextPageLink): self
    {
        $this->nextPageLink = $nextPageLink;
        return $this;
    }

    public function getLinks(): array
    {
        return [
            'self'  => $this->getCurrPageLink(),
            'prev'  => $this->getPrevPageLink(),
            'next'  => $this->getNextPageLink(),
        ];
    }
}
