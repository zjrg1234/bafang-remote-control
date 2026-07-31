<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;

class ReponseData
{
    public static function reponseData($data)
    {
        $data = array(
            'code' => 200,
            'msg' => 'success',
            'data' => $data,
        );
        $data['traceId'] = Config::get('requestId');
        return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public static function reponseFormat($code = 200, $message = '')
    {
        $data = array(
            'code' => $code,
            'msg' => $message,
            'data' => null,
        );

        $data['traceId'] = Config::get('requestId');

        return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }


    public static function reponseFormatList($code = 200, $message = '', $data, $specialData = '')
    {
        if ($specialData) {
            if (is_array($data)) {
                $data = array_merge($data, $specialData);
            } elseif (is_object($data)) {
                $data = array_merge($data->toArray(), $specialData);
            }
        }

        $return = array(
            'code' => $code,
            'msg' => $message,
            'data' => $data
        );


        $return['traceId'] = Config::get('requestId');

        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public static function reponsePageFormat($pagination, $specialData = '')
    {
        if (count($pagination) == 0) {
            $data = ['page' => 1,
                'size' => 10,
                'total' => 0,
                'isLast' => 1,
                "content" => []
            ];
        } else {
            $data = ['page' => $pagination->currentPage(),
                'size' => $pagination->perPage(),
                'total' => $pagination->total(),
                'isLast' => $pagination->lastPage(),
                "content" => $pagination->items()
            ];
        }
        if ($specialData) {
            $data = array_merge($data, $specialData);
        }
        $return = array(
            'code' => 200,
            "msg" => "",
            'data' => $data
        );
        if (config('app.debug')) {
            $return['traceId'] = Config::get('requestId');
        }
        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public static function reponsePaginationFormat($pagination, $specialData = '')
    {
        if (count($pagination) == 0) {
            $data = ['page' => 1,
                'size' => 10,
                'total' => 0,
                'isLast' => 1,
                "content" => []
            ];
        } else {
//            foreach ($pagination->items() as &$item) {
//                if (time() - strtotime($item->last_online_time) < 600 && $item->online=='1') {
//                    $item->online = 1;
//                }else{
//                    $item->online = 0;
//                }
//            }
            $data = ['page' => $pagination->currentPage(),
                'size' => $pagination->perPage(),
                'total' => $pagination->total(),
                'isLast' => $pagination->lastPage(),
                "content" => $pagination->items()
            ];
        }
        if ($specialData) {
            $data = array_merge($data, $specialData);
        }
        $return = array(
            'code' => 200,
            "msg" => "成功",
            'data' => $data

        );

        if (config('app.debug')) {
            $return['traceId'] = Config::get('requestId');
        }
        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public static function reponsePaginationFormatForGoodsIndex($pagination, $userPoints)
    {
        $data = ['page' => $pagination->currentPage(),
            'size' => $pagination->perPage(),
            'total' => $pagination->total(),
            'isLast' => $pagination->lastPage(),
            "user_points" => $userPoints,
            "sever_time" => time(),
            "content" => $pagination->items()
        ];
        $return = array(
            'code' => 200,
            "msg" => "",
            'data' => $data

        );
        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public static function reponsePaginationFormatForBbsIndex($pagination, $game)
    {
        $data = ['page' => $pagination->currentPage(),
            'size' => $pagination->perPage(),
            'total' => $pagination->total(),
            'isLast' => $pagination->lastPage(),
            "game" => $game,
            "content" => $pagination->items()
        ];
        $return = array(
            'code' => 200,
            "msg" => "",
            'data' => $data

        );
        return response()->json($return)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
