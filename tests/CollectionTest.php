<?php namespace Test\Znck\Attach;

use Znck\Attach\Attachment;

class CollectionTest extends TestCase
{
    public function test_setCollectionAccessor()
    {
        $collection = (new Attachment())->getCollection();
        $collection->setCollectionAccessor('foo');
        $this->assertEquals('foo', $collection->getCollectionAccessor());
    }
}
