<?php

namespace App\Services;

use App\Models\AccountModel;
use App\Models\ProductAdditionModel;
use App\Models\ProductModel;
use App\Util\Constants;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ProductService
{
    protected $productModel;
    protected $productAdditionModel;
    protected $accountModel;
    public function __construct(ProductModel $productModel,AccountModel $accountModel,
                                ProductAdditionModel $productAdditionModel) {
        $this->productModel = $productModel;
        $this->accountModel = $accountModel;
        $this->productAdditionModel = $productAdditionModel;
    }

    public function AddProductSrv($params) {
        $data = $this->productModel->take(1)->orderByDesc('id')->get()->first();
        $insertProductParam = [];
        if (!empty($data)) {
            $insertProductParam['product_id'] = Constants::UUIDPre + $data->id + 1;
        }
        if (!empty($params['name'])) {
            $insertProductParam['name'] =  $params['name'];
        }
        if (!empty($params['product_type'])) {
            $insertProductParam['product_type'] =  $params['product_type'];
        }
        if (!empty($params['issue_time'])) {
            $insertProductParam['issue_time'] =  $params['issue_time'];
        }
        if (!empty($params['denomination'])) {
            $insertProductParam['denomination'] =  $params['denomination'];
        }
        if (!empty($params['product_version'])) {
            $insertProductParam['product_version'] =  $params['product_version'];
        }
        if (!empty($params['weight'])) {
            $insertProductParam['weight'] =  $params['weight'];
        }
        if (!empty($params['length'])) {
            $insertProductParam['length'] =  $params['length'];
        }
        if (!empty($params['width'])) {
            $insertProductParam['width'] =  $params['width'];
        }
        if (!empty($params['score'])) {
            $insertProductParam['score'] =  $params['score'];
        }
        if (!empty($params['identify_result'])) {
            $insertProductParam['identify_result'] =  $params['identify_result'];
        }
        if (!empty($params['desc'])) {
            $insertProductParam['desc'] =  $params['desc'];
        }
        if (empty($insertProductParam)) {
            return false;
        }
        $insertAdditionParam = [];
        if (!empty($params['pic'])) {
            foreach ($params['pic'] as $v) {
                $insertAdditionParam[] = [
                    'product_id' => $insertProductParam['product_id'],
                    'product_img'=> $v
                ];
            }
        }
        DB::beginTransaction();
        $isSuccess = $this->productModel->insert($insertProductParam);
        if (!$isSuccess) {
            DB::rollBack();
        }
        if (!empty($insertAdditionParam)) {
            $isSuccess = $this->productAdditionModel->insert($insertAdditionParam);
            if (!$isSuccess) {
                DB::rollBack();
            }
        }
        DB::commit();
        return true;
    }

    public function DelProductSrv ($params) {
        return $this->productModel->where('id',$params['id'])->update(['is_deleted'=>Constants::IsDeletedYes]);
    }

    public function EditProductSrv ($params) {
        return $this->productModel->where('id',$params['id'])->update($params);
    }

    public function GetProductListSrv ($search) {
        $list = $this->productModel->select()->where('is_deleted',Constants::IsDeletedNo);
        if (!empty($search['id'])) {
            $list = $list->where('id',$search['id']);
        }
        $total = $list->count();
        return ['total'=>$total,'list'=>$list->take($search['count'])->skip($search['offset'])->get()->toArray()];
    }

    public function LoginSrv($params) {
        $data = [];
        $pwd =  md5($params['password'].Constants::Salt);
        $user = $this->accountModel->select()->where('name',$params['username'])
            ->where('password',$pwd)->get()->first();
        if (empty($user)){
            return $data;
        }
        //生成token
        $token =md5(Constants::Salt.time());
        $data =[
            'token' => $token,
            'username'=>$user->name,
        ];
        //更新token
        $this->accountModel->where('id',$user->id)->update(['token'=>$token]);
        return $data;
    }

}
