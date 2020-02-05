<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Image\ImageInterface;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}


class MockImageInterface implements ImageInterface
{
    public function get_forum_image($id){}

    public function create_image($request){}

    public function update_image($request){}

    public function remove_image($id){}
}
