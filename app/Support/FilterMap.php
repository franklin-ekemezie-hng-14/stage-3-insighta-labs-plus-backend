<?php
declare(strict_types=1);

namespace App\Support;

use App\Enums\AgeGroup;
use App\Enums\Country;
use App\Enums\Gender;

class FilterMap
{

    public function __construct(
        private array $filters=[]
    )
    {

    }

    public static function make(): self
    {
        return self::fromFilters([]);
    }

    public static function fromFilters(array $filters): self
    {
        return new self($filters);
    }

    public function gender(Gender $gender): self
    {
        $this->filters['gender'] = $gender->value;

        return $this;
    }

    public function male(): self
    {
        return $this->gender(Gender::MALE);
    }

    public function female(): self
    {
        return $this->gender(Gender::FEMALE);
    }

    public function ageGroup(AgeGroup $ageGroup): self
    {
        $this->filters['age_group'] = $ageGroup->value;

        return $this;
    }

    public function child(): self
    {
        return $this->ageGroup(AgeGroup::CHILD);
    }

    public function teenager(): self
    {
        return $this->ageGroup(AgeGroup::TEENAGER);
    }

    public function adult(): self
    {
        return $this->ageGroup(AgeGroup::ADULT);
    }

    public function senior(): self
    {
        return $this->ageGroup(AgeGroup::SENIOR);
    }

    public function minAge(int $age): self
    {
        $this->filters['min_age'] = $age;

        return $this;
    }

    public function maxAge(int $age): self
    {
        $this->filters['max_age'] = $age;

        return $this;
    }

    public function young(): self
    {
        return $this->minAge(16)->maxAge(24);
    }

    public function old(): self
    {
        return $this->minAge(40);
    }

    public function country(Country $country): self
    {
        $this->filters['country_id'] = $country->id();

        return $this;
    }

    public function toArray(): array
    {
        return $this->filters;
    }

}
