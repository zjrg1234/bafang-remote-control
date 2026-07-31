<?php

namespace App\Http\Service;

use App\Models\Notice;
use App\Models\ReponseData;

class NoticeService
{


    public function list($request)
    {
        $data = [
            'page' =>   $request['page'] ?? 1,
            'size' =>   $request['size'] ?? 10,
        ];

        $query = Notice::query();

        $rows = $query->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);


        return ReponseData::reponsePaginationFormat($rows);
    }

    public function create($request)
    {
        $data = [
            'content' => $request['content'] ?? null,
            'status' => $request['status'] ?? null,
        ];

        if(!$data['content'])
        {
            return ReponseData::reponseFormat(2000,'内容必填');
        }

        if($data['status'] === null)
        {
            return ReponseData::reponseFormat(2000,'状态必填');
        }

        Notice::create($data);

        return ReponseData::reponseFormat(200,'新增成功');
    }

    public function update($request)
    {
        $id = $request['id'] ?? null;

        if(!$id)
        {
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $list = Notice::where('id', $id)->first();
        $data = [
            'content' => $request['content'] ?? $list['content'],
            'status' => $request['status'] ?? $list['status'],
        ];
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        $list->update($data);

        return ReponseData::reponseFormat(200,'更新成功');
    }



    public function delete($request)
    {
        $id = $request['id'] ?? null;

        if(!$id)
        {
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $list = Notice::where('id', $id)->first();

        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据');
        }

        $list->delete();

        return ReponseData::reponseFormat(200,'删除成功');
    }


    //前端用
    public function notice($request)
    {
//        $data = [
//            'uid' => $request['uid'] ?? null,
//        ];
//
//        if(!$data['uid'])
//        {
//            return ReponseData::reponseFormat(2000,'用户id必传');
//        }

        $list = Notice::where('status',1)->first();


        return ReponseData::reponseFormatList(200,'获取成功',$list);
    }

}
