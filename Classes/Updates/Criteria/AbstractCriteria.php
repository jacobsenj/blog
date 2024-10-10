<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\AgencyPack\Blog\Updates\Criteria;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

abstract class AbstractCriteria
{
    public function __construct(protected QueryBuilder $queryBuilder, protected string $field)
    {
    }

    public function getField(): string
    {
        return $this->field;
    }
}
