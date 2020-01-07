<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Forum\ForumInterface;
use App\Http\Requests\ForumRequest;

class ForumService
{
    private $forum;

    public function __construct(ForumInterface $forum_interface)
    {
        $this->forum = $forum_interface;
    }

    public function get($id)
    {
        try {
            $forum = $this->forum->get($id);
            return $forum;
        } catch (\Exception  $e) {
            throw $e;
        }
    }

    public function get_list()
    {
        try {
            $forum = $this->forum->get_list();
            return $forum;
        } catch (\Exception  $e) {
            throw $e;
        }
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

    public function delete($id)
    {
        try {
            $forum = $this->forum->delete($id);
            return $forum;
        } catch (\Exception  $e) {
            throw $e;
        }
    }
}
