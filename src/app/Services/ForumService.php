<?php

namespace App\Services;

use App\Http\Requests\ForumRequest;
use App\Models\Forum\ForumInterface;
use Illuminate\Support\Facades\DB;

class ForumService
{
    private $forum;

    /**
     * ForumService constructor.
     * @param ForumInterface $forum_interface
     */
    public function __construct(ForumInterface $forum_interface)
    {
        $this->forum = $forum_interface;
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
            if ($request->session()->get('image_name')) {
                $this->forum->create_image($forum->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * フォーラムの更新、画像の保存
     * @param ForumRequest $request
     * @throws \Exception
     */
    public function update_forum(ForumRequest $request)
    {
        DB::beginTransaction();
        try {
            $forum = $this->forum->update_forum($request);
            if ($request->session()->get('image_name')) {
                $this->forum->update_image($forum->id);
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
