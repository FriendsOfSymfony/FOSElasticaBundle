<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Subscriber;

use Elastica\Query;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOS\ElasticaBundle\Subscriber\PaginateElasticaQuerySubscriber;
use Knp\Component\Pager\Event\ItemsEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateElasticaQuerySubscriberTest extends TestCase
{
    public function testShouldDoNothingIfSortParamIsEmpty()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request()));

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->never())
            ->method('getQuery');
        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;

        $subscriber->items($event);
    }

    public function sortCases()
    {
        $tests = [];

        $expected = [
            'createdAt' => [
                'order' => 'asc',
            ],
        ];
        $tests[] = [$expected, new Request()];

        $expected = [
            'name' => [
                'order' => 'desc',
            ],
        ];
        $tests[] = [$expected, new Request(['ord' => 'name', 'az' => 'desc'])];

        $expected = [
            'updatedAt' => [
                'order' => 'asc',
            ],
        ];
        $tests[] = [$expected, new Request(['ord' => 'updatedAt', 'az' => 'invalid'])];

        return $tests;
    }

    /**
     * @dataProvider sortCases
     */
    public function testShouldSort(array $expected, Request $request)
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack($request));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
        ];

        $subscriber->items($event);

        $this->assertSame($expected, $query->getParam('sort'));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testShouldThrowIfFieldIsNotWhitelisted()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request(['ord' => 'owner'])));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
            'sortFieldWhitelist' => ['createdAt', 'updatedAt'],
        ];

        $subscriber->items($event);
    }

    public function testShouldAddNestedPath()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request(['ord' => 'owner.name'])));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
            'sortNestedPath' => 'owner',
        ];

        $subscriber->items($event);
        $this->assertSame([
            'owner.name' => [
                'order' => 'asc',
                'nested_path' => 'owner',
            ],
        ], $query->getParam('sort'));
    }

    public function testShouldInvokeCallableNestedPath()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request(['ord' => 'owner.name'])));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
            'sortNestedPath' => function ($sortField) {
                $this->assertSame('owner.name', $sortField);

                return 'owner';
            },
        ];

        $subscriber->items($event);
        $this->assertSame([
            'owner.name' => [
                'order' => 'asc',
                'nested_path' => 'owner',
            ],
        ], $query->getParam('sort'));
    }

    public function testShouldAddNestedFilter()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request(['ord' => 'owner.name'])));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
            'sortNestedPath' => 'owner',
            'sortNestedFilter' => new Query\Term(['enabled' => ['value' => true]]),
        ];

        $subscriber->items($event);
        $this->assertEquals([
            'sort' => [
                'owner.name' => [
                    'order' => 'asc',
                    'nested_path' => 'owner',
                    'nested_filter' => [
                        'term' => [
                            'enabled' => ['value' => true],
                        ],
                    ],
                ],
            ],
            'query' => [
                'match_all' => new \stdClass(),
            ],
        ], $query->toArray());
    }

    public function testShouldInvokeNestedFilterCallable()
    {
        $subscriber = new PaginateElasticaQuerySubscriber($this->getRequestStack(new Request(['ord' => 'owner.name'])));

        $query = new Query();
        $adapter = $this->getAdapterMock();
        $adapter->method('getQuery')
            ->willReturn($query);

        $adapter->method('getResults')
            ->willReturn($this->getResultSetMock());

        $event = new ItemsEvent(0, 10);
        $event->target = $adapter;
        $event->options = [
            'defaultSortFieldName' => 'createdAt',
            'sortFieldParameterName' => 'ord',
            'sortDirectionParameterName' => 'az',
            'sortNestedPath' => 'owner',
            'sortNestedFilter' => function ($sortField) {
                $this->assertSame('owner.name', $sortField);

                return new Query\Term(['enabled' => ['value' => true]]);
            },
        ];

        $subscriber->items($event);
        $this->assertEquals([
            'sort' => [
                'owner.name' => [
                    'order' => 'asc',
                    'nested_path' => 'owner',
                    'nested_filter' => [
                        'term' => [
                            'enabled' => ['value' => true],
                        ],
                    ],
                ],
            ],
            'query' => [
                'match_all' => new \stdClass(),
            ],
        ], $query->toArray());
    }

    protected function getAdapterMock()
    {
        return $this->createMock(RawPaginatorAdapter::class);
    }

    protected function getResultSetMock()
    {
        return $this->createMock(PartialResultsInterface::class);
    }

    private function getRequestStack(Request $request = null)
    {
        $stack = new RequestStack();

        if ($request) {
            $stack->push($request);
        }

        return $stack;
    }
}
