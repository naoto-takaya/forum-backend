<?php

namespace App\Services;

use App\Models\Forum\ForumInterface;
use App\Http\Requests\ForumRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForumService
{
    private $forum;

    public function __construct(ForumInterface $forum_interface)
    {
        $this->forum = $forum_interface;
    }

    public function get($id)
    {
        $forum = $this->forum->get($id);
        return $forum;
    }

    public function get_list()
    {
        $forum = $this->forum->get_list();
        return $forum;
    }

    public function create(ForumRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath  = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }
            $this->forum->create($request);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Image::image_delete($filepath);
            throw $e;
        }
    }

    public function update(ForumRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath  = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }
            $this->forum->update($request);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Image::image_delete($filepath);
            throw $e;
        }
    }

    public function remove($id)
    {
        try {
            $forum = $this->forum->remove($id);
            return $forum;
        } catch (\Exception  $e) {
            throw $e;
        }
    }
}
