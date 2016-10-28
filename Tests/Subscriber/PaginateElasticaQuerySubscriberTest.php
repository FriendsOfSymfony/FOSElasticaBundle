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
            'sortDirectionParameterName' => 'az'
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
            'sortFieldWhitelist' => ['createdAt', 'updatedAt']
        ];

        $subscriber->items($event);
    }
}
