<?php

namespace App\Http\Service;

use App\Models\Advertisement;
use App\Models\AppVersion;
use App\Models\CommonProblem;
use App\Models\FeedBack;
use App\Models\LoginPopup;
use App\Models\PlatformParameter;
use App\Models\ProtocolManage;
use App\Models\ReponseData;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\Log;

class PlatformConfigService{

    protected $imageTypes = [
        1=>'车辆图片',
        2=>'遥控船图片',
        3=>'挖机图片',
        4=>'铲车图片',
        5=>'娃娃机图片',
    ];
    public function commonProblemList($request)
    {
        $data = [
            'name'=> $request['name'] ?? null,
        ];
        $query = CommonProblem::select('*');
        if($data['name']){
            $query->where('name',$data['name']);
        }
        $list = $query->get();


        return ReponseData::reponseFormatList(200,'获取成功',$list);
    }

    public function commonProblemCreate($request)
    {
        $data = [
            'name'=> $request['name'] ?? null,
            'detail' => $request['detail'] ?? null,
            'sort' => $request['sort'] ?? 1,
            'status' => $request['status'] ?? null,
        ];

        if(!$data['name']){
            return ReponseData::reponseFormat(2000,'问题标题必须填');
        }
        if(!$data['detail']){
            return ReponseData::reponseFormat(2000,'详情必须填');
        }

        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'状态必须填');

        }

        CommonProblem::create($data);


        return  ReponseData::reponseFormat(200,'新增成功');
    }

    public function commonProblemUpdate($request)
    {
        $id = $request['id'] ?? null;

        $data = [
            'name'=> $request['name'] ?? null,
            'detail' => $request['detail'] ?? null,
            'sort' => $request['sort'] ?? 1,
            'status' => $request['status'] ?? null,
        ];

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');

        }
        if(!$data['name']){
            return ReponseData::reponseFormat(2000,'问题标题必须填');
        }
        if(!$data['detail']){
            return ReponseData::reponseFormat(2000,'详情必须填');
        }

        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'状态必须填');

        }

        $list = CommonProblem::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据哦');
        }
        $list->update($data);

        return  ReponseData::reponseFormat(200,'更新成功');
    }

    public function commonProblemDelete($request)
    {
        $ids = $request['id'] ?? null;
        if(!$ids) {
            return ReponseData::reponseFormat(2000, 'id必传!');
        }
        CommonProblem::select('*')->whereIn('id', $ids)->delete();
//        if(!$list){
//            return ReponseData::reponseFormat(2001,'未找到该数据哦!');
//        }
        return ReponseData::reponseFormat(200,'删除成功');
    }


    public function protocolManageList($request)
    {
        $data = [
            'name'=> $request['name'] ?? null,
        ];
        $query = ProtocolManage::select('*');
        if($data['name']){
            $query->where('name',$data['name']);
        }
        $list = $query->get();


        return ReponseData::reponseFormatList(200,'获取成功',$list);
    }


    public function protocolManageUpdate($request)
    {
        $id = $request['id'] ?? null;

        $data = [
            'content' => $request['content'] ?? null,
        ];

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }

        if(!$data['content']){
            return ReponseData::reponseFormat(2000,'详情必须填');
        }


        $list = ProtocolManage::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据哦');
        }
        $list->update($data);

        return  ReponseData::reponseFormat(200,'更新成功');
    }


    public function feedBackList($request)
    {
        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'user_name'         => $request['user_name'] ?? null,
            'type'            => $request['type'] ?? null,


        ];
        $query = FeedBack::select('*');
        if(isset($query_params['user_name'])){
            $query->where('user_name',$query_params['user_name']);
        }

        if(isset($query_params['type'])){
            $query->where('type',$query_params['type']);
        }

        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        foreach ($rows as $value){
            $value['time'] = date('Y-m-d H:i:s', $value['time']);
            $value['content'] =$value['Content'];
            unset($value['Content']);
        }

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function feedBackUpdate($request)
    {
        $id = $request['id'] ?? null;

        $data = [
            'remark' => $request['remark'] ?? null,
            'type' => $request['type'] ?? null,
        ];

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }

        if(!$data['remark']){
            return ReponseData::reponseFormat(2000,'备注必须填');
        }
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'处理状态必须填');
        }

        $list = FeedBack::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2000,'未找到该数据哦');
        }
        $list->update($data);

        return  ReponseData::reponseFormat(200,'更新成功');
    }

    public function advertisingList($request)
    {

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,

        ];
        $query = Advertisement::select('*');
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        Log::info('查询数据：'.json_encode($rows));

        return ReponseData::reponsePaginationFormat($rows);
    }

    public function advertisingCreate($request)
    {
        $data = [
            'title' => $request['title'] ?? '',
            'image' => $request['image'] ?? '',
            'status' => $request['status'] ?? null,
        ];
        if(!$data['title']){
            return ReponseData::reponseFormat(2000,'title必填');
        }
        Log::info('新增数据'.json_encode($data));
        Advertisement::create($data);
        return ReponseData::reponseFormat(200,'新增成功');
    }

    public function advertisingUpdate($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'image' => $request['image'] ?? '',
            'status' => $request['status'] ?? null,
        ];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }

        $list = Advertisement::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function advertisingDelete($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        $list = Advertisement::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->delete();
        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function versionList($request)
    {

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'type'      => $request['type'] ?? null,
            'version_mark' => $request['version_mark'] ?? null,

        ];
        $query = AppVersion::select('*');
        if(isset($query_params['version_mark'])){
            $query->where('version_mark',$query_params['version_mark']);
        }
        if(isset($query_params['type'])){
            $query->where('type',$query_params['type']);
        }
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function versionCreate($request)
    {
        $data = [
            'version_mark' => $request['version_mark'] ?? null,
            'version_coding' => $request['version_coding'] ?? '',
            'type'=> $request['type'] ?? null,
            'update_content' => $request['update_content'] ?? '',
            'is_change_special' => $request['is_change_special'] ?? null,
            'forced_updating' => $request['forced_updating'] ?? null,
            'status' => $request['status'] ?? null,
            'app_url' => $request['app_url'] ?? '',
        ];

        if(!$data['version_mark']){
            return ReponseData::reponseFormat(2000,'版本号必填');
        }

        if(!$data['version_coding']){
            return ReponseData::reponseFormat(2000,'版本编码必填');
        }
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'app类型必填');
        }
        if($data['is_change_special'] === null){
            return ReponseData::reponseFormat(2000,'是否更改专区必填');
        }
        if($data['forced_updating'] === null){
            return ReponseData::reponseFormat(2000,'是否强制更新必填');
        }
        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'状态必填');
        }
//        if($data['type'] == 2){
//            if(!$data['app_url']){
//                return ReponseData::reponseFormat(2000,'安卓包链接必填');
//            }
//        }else{
            $data['app_url'] = '';
//        }

        AppVersion::create($data);
        return ReponseData::reponseFormat(200,'新增成功');
    }

    public function versionUpdate($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'version_mark' => $request['version_mark'] ?? null,
            'version_coding' => $request['version_coding'] ?? '',
            'type'=> $request['type'] ?? null,
            'update_content' => $request['update_content'] ?? '',
            'is_change_special' => $request['is_change_special'] ?? null,
            'forced_updating' => $request['forced_updating'] ?? null,
            'status' => $request['status'] ?? null,
            'app_url' => $request['app_url'] ?? '',
        ];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        if(!$data['version_mark']){
            return ReponseData::reponseFormat(2000,'版本号必填');
        }

        if(!$data['version_coding']){
            return ReponseData::reponseFormat(2000,'版本编码必填');
        }
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'app类型必填');
        }
        if($data['is_change_special'] === null){
            return ReponseData::reponseFormat(2000,'是否更改专区必填');
        }
        if($data['forced_updating'] === null){
            return ReponseData::reponseFormat(2000,'是否强制更新必填');
        }
        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'状态必填');
        }
//        if($data['type'] == 2){
//            if(!$data['app_url']){
//                return ReponseData::reponseFormat(2000,'安卓包链接必填');
//            }
//        }else{
            $data['app_url'] = '';
//        }


        $list = AppVersion::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function versionDelete($request)
    {
        $ids = $request['ids'] ?? null;
        if(!$ids){
            return ReponseData::reponseFormat(2000,'id必填');
        }

        AppVersion::whereIn('id',$ids)->delete();

        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function popupList($request)
    {

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,

        ];
        $query = LoginPopup::select('*');
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function popupCreate($request)
    {
        $data = [
            'content' => $request['content'] ?? '',
            'type' => $request['type'] ?? null,
        ];
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'是否生效必填');
        }

        LoginPopup::create($data);
        return ReponseData::reponseFormat(200,'新增成功');
    }

    public function popupUpdate($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'content' => $request['content'] ?? '',
            'type' => $request['type'] ?? null,
        ];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        if($data['type'] === null){
            return ReponseData::reponseFormat(2000,'是否生效必填');
        }
        $list = LoginPopup::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }

        $list->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function popupDelete($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        $list = LoginPopup::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->delete();
        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function parameterList($request)
    {

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,

        ];
        $query = PlatformParameter::select('*');
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function parameterUpdate($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'value' => $request['value'] ?? '',
        ];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }

        $list = PlatformParameter::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }

        $list->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function vehicleImageList($request)
    {

        $query_params = [
            'page'                 => $request['page'] ?? 1,
            'size'                 => $request['size'] ?? 10,
            'status'            =>  $request['status'] ?? null,
        ];
        $query = VehicleImage::select('*');
        if($query_params['status']){
            $query->where('status',$query_params['status']);
        }
        $rows = $query->orderBy("id", 'desc')->paginate($query_params['size'], ['*'], 'page', $query_params['page']);
        foreach($rows as $row){
            $row['type_name'] =  $this->imageTypes[$row['type']] ?? '';
        }
        return ReponseData::reponsePaginationFormat($rows);
    }

    public function vehicleImageCreate($request)
    {
        $data = [
            'image' => $request['image'] ?? null,
            'type' => $request['type'] ?? null,
            'status' => $request['status'] ?? null,
        ];
        if(!$data['type']){
            return ReponseData::reponseFormat(2000,'类型必填');
        }

        if(!$data['image']){
            return ReponseData::reponseFormat(2000,'图片必填');
        }
        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'是否启用必填');
        }
        $data['type_name'] = $this->imageTypes[$request['type']] ?? '';


        VehicleImage::create($data);
        return ReponseData::reponseFormat(200,'新增成功');
    }

    public function vehicleImageUpdate($request)
    {
        $id = $request['id'] ?? null;
        $data = [
            'image' => $request['image'] ?? null,
            'type' => $request['type'] ?? null,
            'status' => $request['status'] ?? null,
        ];
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        if($data['status'] === null){
            return ReponseData::reponseFormat(2000,'是否启用必填');
        }
        $list = VehicleImage::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }

        $list->update($data);
        return ReponseData::reponseFormat(200,'更新成功');
    }

    public function vehicleImageDelete($request)
    {
        $id = $request['id'] ?? null;

        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        $list = VehicleImage::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->delete();
        return ReponseData::reponseFormat(200,'删除成功');
    }

    public function vehicleImageChangeStatus($request)
    {
        $id = $request['id'] ?? null;
        $type = $request['status'] ?? null;
        if(!$id){
            return ReponseData::reponseFormat(2000,'id必填');
        }
        $list = VehicleImage::where('id',$id)->first();
        if(!$list){
            return ReponseData::reponseFormat(2002,'未找到该数据');
        }
        $list->update(['status' => $type]);
        return ReponseData::reponseFormat(200,'更改成功');
    }

    public function vehicleImageTypeList($request)
    {

        $data = $this->imageTypes;
        $resp = [

        ];
        foreach ($data as $key=>$value){
            $list = [
                'type'=>$key,
                'name'=>$value,
            ];
            $resp[] = $list;
        }

        return ReponseData::reponseFormatList(200,'成功',$resp);
    }
}
