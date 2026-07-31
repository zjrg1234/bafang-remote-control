<?php

namespace App\Http\Service;


use App\Models\ActivityNotic;
use App\Models\ReponseData;

class ActivityService{


    public function List($request)
    {
        $data = [
            'activity_title' => $request['activity_title'] ?? null,
            'type' => $request['type'] ?? null,
            'status' => $request['status'] ?? null,
            'special_area' => $request['special_area'] ?? null,
            'is_index' => $request['is_index'] ?? null,
            'is_discover' => $request['is_discover'] ?? null,
            'page' => $request['page'] ?? 1,
            'size' => $request['size'] ?? 10,
        ];




        $list = ActivityNotic::select('*');
        if($data['activity_title']){
            $list->where('activity_title',$data['activity_title']);
        }

        if($data['type']){
            $list->where('type',$data['type']);
        }

        if($data['status']){
            $list->where('status',$data['status']);
        }

        if($data['special_area']){
            $list->where('special_area',$data['special_area']);
        }

        if($data['is_index']){
            $list->where('is_index',$data['is_index']);
        }
        if($data['is_discover']){
            $list->where('is_discover',$data['is_discover']);
        }
        $rows = $list->orderBy("id", 'desc')->paginate($data['size'], ['*'], 'page', $data['page']);

        return ReponseData::reponsePaginationFormat($rows);
    }
    public function Create($request)
    {
        $data = [
            'type' => $request['type'] ?? null,
            'activity_title' => $request['activity_title'] ?? null,
            'activity_image' => $request['activity_image'] ?? null,
            'is_index' => $request['is_index'] ?? null,
            'content' => $request['content'] ?? '',
            'is_discover' => $request['is_discover'] ?? null,
            'index_image' => $request['index_image'] ?? '',
            'discover_image' => $request['discover_image'] ?? '',
            'status' => $request['status'] ?? null,
            'sort' => $request['sort'] ?? null,
            'remark' => $request['remark'] ?? '',
        ];
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'公告所属类型必填');
        }
        if(!$data['activity_title']){
            return ReponseData::reponseFormat(2000,'公告标题必填');
        }
        if($data['is_index'] === null){
            return ReponseData::reponseFormat(2000,'是否首页显示必填');
        }
        if($data['is_discover'] === null){
            return ReponseData::reponseFormat(2000,'是否广告页显示必填');
        }

        if(!$data['activity_image']){
            return ReponseData::reponseFormat(2000,'活动图片必填');
        }

        if(!$data['status']){
            return ReponseData::reponseFormat(2000,'状态必填');
        }

        if(!$data['sort']){
            return ReponseData::reponseFormat(2000,'排序必填');
        }

        ActivityNotic::create($data);

        return ReponseData::reponseFormat(200,'成功');

    }

    public function Update($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'type' => $request['type'] ?? null,
            'activity_title' => $request['activity_title'] ?? null,
            'activity_image' => $request['activity_image'] ?? null,
            'is_index' => $request['is_index'] ?? null,
            'content' => $request['content'] ?? '',
            'is_discover' => $request['is_discover'] ?? null,
            'index_image' => $request['index_image'] ?? '',
            'discover_image' => $request['discover_image'] ?? '',
            'status' => $request['status'] ?? null,
            'sort' => $request['sort'] ?? null,
            'activity_type' => $request['activity_type'] ?? null,
            'remark' => $request['remark'] ?? '',
            'special_area'=> $request['special_area'] ?? 0,
        ];

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $list = ActivityNotic::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该条数据');
        }
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'公告所属类型必填');
        }
        if(!$data['activity_title']){
            return ReponseData::reponseFormat(2000,'公告标题必填');
        }
        if($data['is_index'] === null){
            return ReponseData::reponseFormat(2000,'是否首页显示必填');
        }
        if($data['is_discover'] === null){
            return ReponseData::reponseFormat(2000,'是否广告页显示必填');
        }

        if(!$data['activity_image']){
            return ReponseData::reponseFormat(2000,'活动图片必填');
        }

        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'状态必填');
        }

        if(!$data['sort']){
            return ReponseData::reponseFormat(2000,'排序必填');
        }
        if($data['activity_type'] === null){
            return ReponseData::reponseFormat(2000,'活动公告类型必填');
        }

        $list->update($data);

        return ReponseData::reponseFormat(200,'成功');

    }
    public function Delete($request)
    {
        $id = $request['id'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        $list = ActivityNotic::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该条数据');
        }
        $list->delete();

        return ReponseData::reponseFormat(200,'成功');
    }

    public function ChangeStatus($request)
    {
        $id = $request['id'] ?? null;
        $status = $request['status'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必传');
        }
        if($status === null){
            return ReponseData::reponseFormat(2000,'状态必传');
        }
        $list = ActivityNotic::where('id', $id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该条数据');
        }
        $list->update(['status' => $status]);

        return ReponseData::reponseFormat(200,'成功');
    }
}
