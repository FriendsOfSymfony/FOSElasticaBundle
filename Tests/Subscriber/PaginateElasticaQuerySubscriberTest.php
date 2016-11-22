<?php

namespace FOS\ElasticaBundle\Tests\Subscriber;

use Elastica\Query;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOS\ElasticaBundle\Subscriber\PaginateElasticaQuerySubscriber;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\HttpFoundation\Request;

class PaginateElasticaQuerySubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function getAdapterMock()
    {
        return $this->getMockBuilder(RawPaginatorAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getResultSetMock()
    {
        return $this->getMockBuilder(PartialResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testShouldDoNothingIfSortParamIsEmpty()
    {
        $request = new Request();

        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest($request);

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
                'ignore_unmapped' => true
            ]
        ];
        $tests[] = [$expected, new Request()];

        $expected = [
            'name' => [
                'order' => 'desc',
                'ignore_unmapped' => true
            ]
        ];
        $tests[] = [$expected, new Request(['ord' => 'name', 'az' => 'desc'])];

        $expected = [
            'updatedAt' => [
                'order' => 'asc',
                'ignore_unmapped' => true
            ]
        ];
        $tests[] = [$expected, new Request(['ord' => 'updatedAt', 'az' => 'invalid'])];

        return $tests;
    }

    /**
     * @dataProvider sortCases
     */
    public function testShouldSort(array $expected, Request $request)
    {
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest($request);

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

        $this->assertEquals($expected, $query->getParam('sort'));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testShouldThrowIfFieldIsNotWhitelisted()
    {
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest(new Request(['ord' => 'owner']));

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
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest(new Request(['ord' => 'owner.name']));

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
        $this->assertEquals([
            'owner.name' => [
                'order' => 'asc',
                'ignore_unmapped' => true,
                'nested_path' => 'owner',
            ]
        ], $query->getParam('sort'));
    }

    public function testShouldInvokeCallableNestedPath()
    {
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest(new Request(['ord' => 'owner.name']));

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
                $this->assertEquals('owner.name', $sortField);
                return 'owner';
            },
        ];

        $subscriber->items($event);
        $this->assertEquals([
            'owner.name' => [
                'order' => 'asc',
                'ignore_unmapped' => true,
                'nested_path' => 'owner',
            ]
        ], $query->getParam('sort'));
    }

    public function testShouldAddNestedFilter()
    {
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest(new Request(['ord' => 'owner.name']));

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
                    'ignore_unmapped' => true,
                    'nested_path' => 'owner',
                    'nested_filter' => [
                        'term' => [
                            'enabled' => ['value' => true]
                        ]
                    ]
                ]
            ],
            'query' => [
                'match_all' => new \stdClass()
            ]
        ], $query->toArray());
    }

    public function testShouldInvokeNestedFilterCallable()
    {
        $subscriber = new PaginateElasticaQuerySubscriber();
        $subscriber->setRequest(new Request(['ord' => 'owner.name']));

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
                $this->assertEquals('owner.name', $sortField);
                return new Query\Term(['enabled' => ['value' => true]]);
            },
        ];

        $subscriber->items($event);
        $this->assertEquals([
            'sort' => [
                'owner.name' => [
                    'order' => 'asc',
                    'ignore_unmapped' => true,
                    'nested_path' => 'owner',
                    'nested_filter' => [
                        'term' => [
                            'enabled' => ['value' => true]
                        ]
                    ]
                ]
            ],
            'query' => [
                'match_all' => new \stdClass()
            ]
        ], $query->toArray());
    }
}
