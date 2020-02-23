<?php

namespace App\Services;

use App\Http\Requests\ForumRequest;
use App\Http\Requests\ForumUpdateRequest;
use App\Models\Forum\ForumInterface;
use App\SharedServices\ImageSharedService;
use Illuminate\Support\Facades\DB;

class ForumService
{
    private $forum;
    private $image;

    /**
     * ForumService constructor.
     * @param ForumInterface $forum_interface
     * @param ImageSharedService $image_shared_service
     */
    public function __construct(ForumInterface $forum_interface, ImageSharedService $image_shared_service)
    {
        $this->forum = $forum_interface;
        $this->image = $image_shared_service;
    }

    /**
     * 指定したIDのフォーラムを取得
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->forum->get($id);
    }

    /**
     * フォーラムの一覧を取得
     * @return mixed
     */
    public function get_forum_list()
    {
        return $this->forum->get_forum_list();
    }

    /**
     * フォーラムの作成、画像の保存
     * @param ForumRequest $request
     * @throws \Exception
     */
    public function create_forum(ForumRequest $request)
    {
        DB::beginTransaction();
        try {
            $forum = $this->forum->create_forum($request);
            if ($request->image) {
                $request->merge(['forum_id' => $forum->id]);
                $image_info = $this->image->rekognition_forum_image($request);
                if ($image_info['level']) {
                    $request->merge($image_info);
                    $this->forum->create_image($request);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * フォーラムの更新、画像の保存
     * @param ForumUpdateRequest $request
     * @throws \Exception
     */
    public function update_forum(ForumUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $forum = $this->forum->update_forum($request);
            $request->merge(['forum_id' => $forum->id]);
            if ($request->image) {
                $image_info = $this->image->rekognition_forum_image($request);
                if ($image_info['level'] !== 0) {
                    $request->merge($image_info);
                    $this->forum->update_image($request);
                }
            }else{
                $this->forum->delete_image($forum->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * フォーラムの削除
     * @param $id
     * @throws \Exception
     */
    public function remove($id)
    {
        DB::beginTransaction();
        try {
            $this->forum->remove($id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
