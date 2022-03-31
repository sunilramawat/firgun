<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Service\ApiService;
use App\Http\Controllers\Service\SpecialitiesService;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Msg;
use App\Http\Controllers\Repository\UserRepository;
use App\Http\Controllers\Repository\CrudRepository;
use App\User;
use App\Models\Partners;
use App\Models\ChipData;
use App\Models\Categories;
use App\Models\Page;
use App\Models\SubCategories;
use App\Models\Post;
use Twilio\Rest\Client;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use App\Http\Controllers\Utility\SendEmails;
use PolygonIO\Rest\Rest;
use PolygonIO\Rest\Stocks;
use DateTime;
use DB;
use PolygonIO\PolygonIO;
use PolygonIO\Websockets;
use Validator;
use Route;


//use Illuminate\Routing\Controller as BaseController;

class ApiController extends Controller
{
    // Corn check add to show according adte 
    public function cronJobForaddList(Request $request){
        echo  date ( 'Y-m-d H:i:s' ).'<br>';
        echo $today = date ( 'Y-m-d' );
        $checkopen = Post::whereDate('opening','>=', $today)
            ->where('status',0)
            //->whereDate('closing','<=', $today)
            ->get();
        if(!empty($checkopen)){

            foreach ($checkopen as $checkopenkey => $checkopenvalue) {

                $updatestatu = Post::where('id', $checkopenvalue->id)
                ->update(['status' => 1]); 
                echo '<br>'.$checkopenvalue->id;
            }
        }    
    }

    public function cronJobForaddClose(Request $request){
        //$today = date ( 'Y-m-d H:i:s' );
        echo $today = date ( 'Y-m-d' );
        $checkclose = Post::where('status',1)
            ->whereDate('closing','<', $today)
            ->get();
        //echo '<pre>'; print_r($checkclose); exit;    
        if(!empty($checkclose)){

            foreach ($checkclose as $checkclosekey => $checkclosevalue) {

                $updatestatu = Post::where('id', $checkclosevalue->id)
                ->update(['e_status' => 2]); 
                echo '<br>'.$checkclosevalue->id;
            }
        }    
    }
    // cron 
    public function userNotify(Request $request){
       
        if($request->method() == 'GET'){
            $data = $request;
            $ApiService = new ApiService();
            $Check = $ApiService->userNotify($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //print_r($Check); exit;
            if($Check->error_code == 219){
         
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg 
                ];
            }else if($Check->error_code == 302){
                

                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg
                ];
            
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }
    
    public function register(Request $request){
            
        $data = $request->all();
        
           
        if($request->method() == 'POST'){

            
            if(isset($data['phone'])){// Register With Phone Number 
                $rules = array(  'phone'=>'required|digits:10');
                
                
                $validate = Validator::make($data,$rules);

                if($validate->fails() ){
                    
                    $validate_error = $validate->errors()->all();

                    $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

                }else{
                    $ApiService = new ApiService();
                    $query = User::where('phone',@$data['phone'])
                        ->first();
                    if(@$query->user_status == 2){
                         $response = [
                            'code' => 422,
                            'msg'=>  'Your account is deactivated by admin'
                        ];

                    }else{
                        $Check = $ApiService->checkemail_phone($data);  
                        $error_msg = new Msg();
                        $msg =  $error_msg->responseMsg($Check->error_code);
                    

                        if($Check->error_code == 203 ){
                           
                            $response = [
                                'code' => 200,
                                'msg'=>  $msg
                            ];
                        }else{
                            $response = [
                                'code' => $Check->error_code,
                                'msg'=>  $msg
                            ];
                        }
                    }

                }
            }
           
            
            return $response;
        }   
    }
    
    /*****************************************************************************
    * API                   => verify Phone and email                            *
    * Description           => It is used  verify                                *
    * Required Parameters   => code,password,confirm_password                    *
    * Created by            => Sunil                                             *
    *****************************************************************************/
    public function verifyUser(Request $request){

        $data = $request->all();

        if($request->method() == 'POST'){

            $ApiService = new ApiService();
            $Check = $ApiService->verifyUser($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 205 ){
                $ApiService = new ApiService();
                $Check = $ApiService->login($data);
                // /echo '<pre>'; print_r($Check); exit;
                $response = [
                    'code' => 200,
                    'msg'=>  $msg,
                    'data' => $Check->data
                ];
            }else{  //where('activation_code','=',$data['code'])
                $query = User::where('phone',@$data['phone'])
                    ->first();
                if(@$query->user_status == 2){
                     $response = [
                        'code' => $Check->error_code,
                        'msg'=>  'Your account is deactivated by admin'
                    ];

                }else if(@$query->activation_code != $data['code']){
                     $response = [
                        'code' => $Check->error_code,
                        'msg'=>  'Invalid Code'
                    ];

                }else{    
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }

            return $response;
        }   

    }


    /*****************************************************************************
    * API                   => Social Login                                      *
    * Description           => It is used  verify                                *
    * Required Parameters   => facebook_id,google_id,apple_id                    *
    * Created by            => Sunil                                             *
    *****************************************************************************/    
    public function socialLogin(Request $request){
            
        $data = $request->all();
           
        if($request->method() == 'POST'){

            
            if(isset($data['facebook_id'])){// Register With facebook
                $rules = array(  'facebook_id'=>'required');
            }
            if(isset($data['google_id'])){// Register With google 
                $rules = array(  'google_id'=>'required');
            }
            if(isset($data['apple_id'])){// Register With Apple 
                $rules = array(  'apple_id'=>'required');
            }

                $validate = Validator::make($data,$rules);

                if($validate->fails() ){
                    
                    $validate_error = $validate->errors()->all();

                    $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

                }else{
                    $ApiService = new ApiService();
                    $Check = $ApiService->socialLogin($data); 
                    //print_r($Check); exit; 
                    $error_msg = new Msg();
                    $msg =  $error_msg->responseMsg($Check->error_code);
                

                    if($Check->error_code == 200 ){
                        $response = [
                            'code' => 200,
                            'msg'=>  $msg,
                            'data' => $Check->data
                        ];
                    }else{
                        $response = [
                            'code' => $Check->error_code,
                            'msg'=>  $msg
                        ];
                    }

                }
            
           
            
            return $response;
        }   
    }
    
    /*****************************************************************************
      API                   => set Password                                      *
    * Description           => It is to set the ssword                           *
    * Required Parameters   =>                                                   *
    * Created by            => Sunil                                             *
    ******************************************************************************/
    public function resetPassword(Request $request){
       
        $data = $request->all();
        if($request->method() == 'POST'){

            $rules = array(
                    'id'         =>  'required',
                    'password'      =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){

                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->resetPassword($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 638 || $Check->error_code == 645){

                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            
            return $response;
        }   
    }


   

    public function changePassword(Request $request){
        
        $userId= Auth::user()->id; 
        if($request->method() == 'POST'){

            $data = $request->all();
            $rules = array(
                'old_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
            } else {
                try {
                    if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {

                        $arr = array("code" => 400, "msg" => "Check your old password.", "data" => array());
                    } else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
                        $arr = array("code" => 400, "msg" => "Your new password cannot be the same as your current password.", "data" => array());
                    } else {
                        User::where('id', $userId)->update(['password' => Hash::make($data['new_password'])]);
                        $arr = array("code" => 200, "msg" => "Password updated successfully.", "data" => array());
                    }
                } catch (\Exception $ex) {
                    if (isset($ex->errorInfo[2])) {
                        $msg = $ex->errorInfo[2];
                    } else {
                        $msg = $ex->getMessage();
                    }
                    $arr = array("code" => 404, "msg" => $msg, "data" => array());
                }
            }
            return \Response::json($arr);
        }
    }



    /************************************************************************************
    * API                   => Login                                                    *
    * Description           => It is used to login new user                             *
    * Required Parameters   => email,password,device_id,device_type                     *
    * Created by            => Sunil                                                    *
    *************************************************************************************/

    public function login(Request $request){
        $data = $request->all();

        if($request->method() == 'POST'){

            $rules = array(
                    'password'      =>  'required | min:8',
                    'device_id'     =>  'required',
                    'device_type'   =>  'required');

            $validate = Validator::make($data,$rules);

            if($validate->fails()){
                $validate_error = $validate->errors()->all();
                $response = ['code' => 403, 'msg'=>  $validate_error[0]]; 

            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->login($data);
                
                    //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 200){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        'data' => $Check->data
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
            return $response;
        }   
    }


    public function registeremail(Request $request){
            
    	$data = $request->all();
    	   
       
    	if($request->method() == 'POST'){

            $rules = array('email' =>'required|email|max:255|unique:users','password'=>'required | min:8');
            

            $validate = Validator::make($data,$rules);

            if($validate->fails() ){
                
                $validate_error = $validate->errors()->all();

                $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

            }else{
                
                $ApiService = new ApiService();
                $Check = $ApiService->checkemail_phone($data);  
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            

                if($Check->error_code == 203 ){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }

            }
    		
    		return $response;
    	}	
    }


    /***********************************************************************************
    * API                   => ''                                                      *
    * Description           => It is used  verify  the email..                         *
    * Required Parameters   => ''                                                      *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function activation(Request $request){
            //print_r($request->all());die;
            $id = $request->id;
            $code = $request->code;   

            $UserRepostitory   = new UserRepository();
            $getuser = $UserRepostitory->getuserById($id);
            //echo '<pre>'; print_r($getuser); exit;
            if($getuser['id'] == 1){
                $getCode = $getuser['forgot_password_code'];
            }else{
                $getCode = $getuser['activation_code'];
            }
            $endTime = strtotime("+5 minutes",strtotime($getCode));
            $newTime = date('H:i:s',$endTime);
            if($getCode == $request->code){
                $user = $UserRepostitory->update_activation($id);
                if($getuser['id'] == 1){
                    return view('admin/users/reset');
                }else{
                    return view('activations');

                } 
            }else{
                
                return view('activationsfail');
            }   
        }


    /******************************************************************************
    * API                   => ''                                                 *
    * Description           => It is used  verify  the email..                    *
    * Required Parameters   => ''                                                 *
    * Created by            => Sunil                                              *
    *******************************************************************************/

    public function terms(Request $request){
           $result = DB::table('pages')->where('p_status','=',1)->where('id','=',1)->first();
           print_r($result->p_description);
    }   

    public function privacypolicy(Request $request){
           $result = DB::table('pages')->where('p_status','=',1)->where('id','=',2)->first();
           print_r($result->p_description);

    }   

    
    public function aboutus(Request $request){
            $result = DB::table('pages')->where('p_status','=',1)->where('id','=',3)->first();
           print_r($result->p_description);
    }    
      
  

    /*************************************************************************************
    * API                   => Forgot Password                                           *
    * Description           => It is used send forgot password mail..                    *
    * Required Parameters   => email                                                     *
    * Created by            => Sunil                                                     *
    **************************************************************************************/

    public function forgotPassword(Request $request){
        $data = $request->all();
        if($request->method() == 'POST'){
        
            $ApiService = new ApiService();
            $Check = $ApiService->forgotPassword($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 601){
                $response = [
                    'code' => 200,
                    'msg'=>  $msg
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }



    /**********************************************************************************
      API                   => Category list                                          *
    * Description           => It is to get Chip list                                 *
    * Required Parameters   => Access Token                                           *
    * Created by            => Sunil                                                  *
    **********************************************************************************/

    public function category_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->category_list();
            $Check_discount = $ApiService->discount_list();
            

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            $discount = $Check_discount->data;  
             
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray(),
                    'discount' =>  $discount->toArray(), 
                   
                ];
                //$CategoryArr['category'] = $responseOld['data']['data'];
                
                $category_list = array();
                foreach($responseOld['data']['data'] as $key => $list){

                    $category_array['c_id']             =   @$list['c_id'] ? $list['c_id'] : '';
                    $category_array['c_name']   =   @$list['c_name'] ? $list['c_name'] : '';
                    $category_array['c_background']    =  URL('/public/images/'.$key.'.png')  ;
                    $category_array['c_status']    =  @$list['c_status'];
                    $category_array['c_image']  =   @$list['c_image'] ?  URL('/public/images/'.@$list['c_image']): '';
                    
                    array_push($category_list,$category_array);
                }
                $CategoryArr['category'] =$category_list;
                $CategoryArr['discount'] = $responseOld['discount']['data'];
                //echo '<pre>'; print_r($responseOld['gender']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $CategoryArr,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }

    /**********************************************************************************
      API                   => Get and update Profile                                 *
    * Description           => It is user for Profile                                 *
    * Required Parameters   =>                                                        *
    * Created by            => Sunil                                                  *
    ***********************************************************************************/
    public function profile(Request $request){
        
        $userId= Auth::user()->id;
        //$userId = $request['userid'];
        $Is_method  = 0; 
        
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $userId;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }
        }      
        return $response;
    }


    /**********************************************************************************
      API                   => Get and setting Profile                                *
    * Description           => It is user for setting                                 *
    * Required Parameters   =>                                                        *
    * Created by            => Sunil                                                  *
    ***********************************************************************************/
    public function setting(Request $request){
        
        $userId= Auth::user()->id;
        //$userId = $request['userid'];
        $Is_method  = 0; 
        
        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->setting($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }
        }      
        return $response;
    }

    /************************************************************************************
    * API                   => Update Device                                            *
    * Description           => It is user for email                                     *
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function update_device(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'POST'){
            $data = $request;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->update_device($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }


    

    /***********************************************************************************
    * API                   => favourite_list                                          *
    * Description           => It is to get favourite_list                             *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function favourite_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->favourite_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //$Partner_list['tranding'] = array();
                // $trandingList = $this->tranding();
                //$Partner_list['tranding'] = $trandingList;

                $Partner_list['post'] = array();

                foreach($responseOld['data']['data'] as $list){
                    $partner_array = array();
                    $repost  = array();
                    $postid = @$list['repost_id'] ? $list['repost_id'] : $list['id'];

                    
                    $like_count  = $UserRepostitory->like_count($postid);
                    $favourite_count  = $UserRepostitory->favourite_count($postid);
                    //$comment_count  = $UserRepostitory->comment_count($postid);
                    //$repost_count  = $UserRepostitory->repost_count($postid);  
                    $is_my_like = $UserRepostitory->my_like_count($postid,Auth::user()->id);      
                    $is_my_favourite = $UserRepostitory->is_my_favourite($postid,Auth::user()->id);      
                    /*if($list['post_type'] == 3){
                        $total_vote_count = $UserRepostitory->total_vote_count($postid); 
                        $vote_count_per = $UserRepostitory->vote_count($postid) ; 
                       // print_r($vote_count_per); exit;
                    }else{
                        $total_vote_count = 0; 
                        $vote_count_per = 0 ; 

                    }*/
                    
                    $partner_array['id']            =   @$list['id'] ? $list['id'] : '';
                    /*$partner_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';*/
                    if($list['is_url'] == 1){
                        $partner_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';
                    }else{
                        $partner_array['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] :'';

                    }

                    $partner_array['title']  =   @$list['title'] ? $list['title'] : '';
                    $partner_array['website_url']  =   @$list['url'] ? $list['url'] : '';
                    $partner_array['desc']  =   @$list['description'] ? $list['description'] : '';
                    $partner_array['price']  =   @$list['price'] ? $list['price'] : 0;
                    $partner_array['discount_price']  =   @$list['discount_price'] ? $list['discount_price'] : '';
                    $partner_array['offer']  =   @$list['offer'] ? intval($list['offer']).'% off' : '';
                    $partner_array['is_favorited']  =  $is_my_favourite;

                    
                    $partner_array['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

                    array_push($Partner_list['post'],$partner_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Partner_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    public function contact(Request $request){
        if($request->method() == 'POST'){
            $data = $request->all();
            //print_r($data); exit;
            $email = @$data['email'];
            //$phone = @$data['phone'];
            $subject = 'User Query';    //@$data['subject'];
            $msg = @$data['messsage'];
            $name = @$data['name'];
            $to = 'socialtrade@mailinator.com';
            $SendEmail = new SendEmails();
           // $SendEmail->sendContact($to,$email,$subject,$name,$msg);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg(648);
            $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
                return $response;
        }
    }


    /***********************************************************************************
    * API                   => Create Report                                           *
    * Description           => It is used for creating the report                      * 
    * Required Parameters   =>                                                         *
    * Created by            => Sunil                                                   *
    ************************************************************************************/
    
    public function report(Request $request){
        if($request->method() == 'POST'){
            $data = $request->all();
            $rules = array('post_id' => 'required');
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->report($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($msg); exit;
                if($Check->error_code == 222){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        //'data'  =>  $Check->data  
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    

            return $response;
        }   
    }
    /***************************************************************************************
      API                   => Get and update Profile                                     *
    * Description           => It is user for Profile                                     *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function user_detail(Request $request){
        
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            //$data = $request->id;
            $data = $request['userid'];
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->user_detail($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }




    /***************************************************************************************
      API                   => Sub Category list                                           *
    * Description           => It is to get Chip list                                      *
    * Required Parameters   => Access Token                                                *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/

    public function subcategory_list(Request $request){
       
        if($request->method() == 'GET'){
            $data = $request->all();
            $ApiService = new ApiService();
            $Check = $ApiService->subcategory_list($data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 641){
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $responseOld['data']['data'],
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from'],
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to'],
                    'total' => $responseOld['data']['total']
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /***************************************************************************************
      API                   => pendingSubscriptionPlan IOS                                 *
    * Required Parameters   => Access Token                                                *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/

    public function pendingSubscriptionPlan(Request $request){
         if($request->method() == 'POST'){
            $data = $request->all();
            $userId = Auth::user()->id;
            $datanew =  json_encode ($data ,true );
            // /*echo "<pre/>";
            // print_r($datanew);
            // exit;*/http://18.218.99.33/
            $path = '/var/www/html/public/images';
            $fileName = $path.'/'.date('Ymd').'subscription.txt';
           
            $file = fopen($fileName,'a+');
            fwrite($file,"\n ------------------------\n ");
            fwrite($file, 'time='.date('Y-m-d H:i:s'));
            fwrite($file,"\n ------------------------\n ");
            // //////////////
            //$file = fopen($fileName,'a');
            //$controller =  Route::getCurrentRoute()->getActionName()?Route::getCurrentRoute()->getActionName():'';
            
             //fwrite($file,"\n Called from api :- ".$controller );
             fwrite($file,"\n ". print_r($datanew, true));
            // fwrite($file,"\n -----------Response----------\n");
            // fwrite($file,"\n ". print_r($response, true));
            // fwrite($file,"\n -----------error msg---------\n");
            // fwrite($file,"\n ". print_r($errormsg, true));
            fwrite($file,"\n -----------userid-------------\n");
            fwrite($file,"\n ". print_r($userId, true));
            
            // //fwrite($file,"\n re :- ".  $ResponseData['error']);
            // //fwrite($file,"\n ". print_r(json_encode( $_POST['data']['User'] ), true));
            // if(!empty($_FILES))
            // {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            // }
            ///////

            $ApiService = new ApiService();
            $Check = $ApiService->pendingSubscriptionPlan($data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //$data = $Check->data;   
            if($Check->error_code == 221){
                /*$responseOld = [
                    'data'  => $data->toArray()    
                ];*/
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

    /***************************************************************************************
      API                   => cronJobForSubscreption                                     *
    * Required Parameters   => Access Token                                                *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/

    public function cronJobForSubscreption(Request $request){
         if($request->method() == 'GET'){
            $data = $request->all();
            //$userId = Auth::user()->id;
            $ApiService = new ApiService();
            $Check = $ApiService->cronJobForSubscreption();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            $data = $Check->data;   
            if($Check->error_code == 221){
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

     /***************************************************************************************
      API                   => pendingSubscriptionPlan IOS                                 *
    * Required Parameters   => Access Token                                                *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/

    public function androidSubscreption(Request $request){
         if($request->method() == 'POST'){
            $data = $request->all();
            $userId = Auth::user()->id;
            $datanew =  json_encode ($data ,true );
            // /*echo "<pre/>";
            // print_r($datanew);
            // exit;*/http://18.218.99.33/
            $path = '/var/www/html/public/images';
            $fileName = $path.'/'.date('Ymd').'androidsubscription.txt';
           
            $file = fopen($fileName,'a+');
            fwrite($file,"\n ------------------------\n ");
            fwrite($file, 'time='.date('Y-m-d H:i:s'));
            fwrite($file,"\n ------------------------\n ");
            // //////////////
            //$file = fopen($fileName,'a');
            //$controller =  Route::getCurrentRoute()->getActionName()?Route::getCurrentRoute()->getActionName():'';
            
             //fwrite($file,"\n Called from api :- ".$controller );
             fwrite($file,"\n ". print_r($datanew, true));
            // fwrite($file,"\n -----------Response----------\n");
            // fwrite($file,"\n ". print_r($response, true));
            // fwrite($file,"\n -----------error msg---------\n");
            // fwrite($file,"\n ". print_r($errormsg, true));
            fwrite($file,"\n -----------userid-------------\n");
            fwrite($file,"\n ". print_r($userId, true));
            
            // //fwrite($file,"\n re :- ".  $ResponseData['error']);
            // //fwrite($file,"\n ". print_r(json_encode( $_POST['data']['User'] ), true));
            // if(!empty($_FILES))
            // {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            // }
            ///////
            $ApiService = new ApiService();
            $Check = $ApiService->androidSubscreption($data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            //$data = $Check->data;   
            if($Check->error_code == 221){
                /*$responseOld = [
                    'data'  => $data->toArray()    
                ];*/
                //echo '<pre>'; print_r($responseOld['data']); exit;
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }  
    }

    /***********************************************************************************
      API                   => Set Match Preferences                                   *
    * Description           => It is user for Profile                                  *
    * Required Parameters   =>                                                         *
    * Created by            => Sunil                                                   *
    ***********************************************************************************/
    public function setpreferences(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $userId;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->pref_profile($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->pref_profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      



        
        return $response;
    }






    /********************************************************************************
      API                   => Set Match Preferences                                *
    * Description           => It is user for Profile                               *
    * Required Parameters   =>                                                      *
    * Created by            => Sunil                                                *    ********************************************************************************/
    public function visibility(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        
        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->visibilty_profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    //'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      



        
        return $response;
    }



    /***********************************************************************************
    * API                   => Home Page Adds list                                     *
    * Description           => It is to get Adds list                                  *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function adds_list(Request $request){
       
        if($request->method() == 'GET'){
                $data = $request->all();
                $datanew =  json_encode ($data ,true );
                // /*echo "<pre/>";
                // print_r($datanew);
                // exit;*/http://18.218.99.33/
                 $path = '/var/www/html/public/images';
                 $fileName = $path.'/'.date('Ymd').'notregister.txt';
                // // prd($fileName);

                  $file = fopen($fileName,'a+');
                 fwrite($file,"\n ------------------------\n ");
                 fwrite($file, 'time='.date('Y-m-d H:i:s'));
                 fwrite($file,"\n ------------------------\n ");
                // //////////////
                 //$file = fopen($fileName,'a');
                //$controller =  Route::getCurrentRoute()->getActionName()?Route::getCurrentRoute()->getActionName():'';
                
                 //fwrite($file,"\n Called from api :- ".$controller );
                 fwrite($file,"\n ". print_r($datanew, true));
                // fwrite($file,"\n -----------Response----------\n");
                // fwrite($file,"\n ". print_r($response, true));
                // fwrite($file,"\n -----------error msg---------\n");
                // fwrite($file,"\n ". print_r($errormsg, true));
                // fwrite($file,"\n -----------userid-------------\n");
                // fwrite($file,"\n ". print_r($userId, true));
                
                // //fwrite($file,"\n re :- ".  $ResponseData['error']);
                // //fwrite($file,"\n ". print_r(json_encode( $_POST['data']['User'] ), true));
                // if(!empty($_FILES))
                // {
                
                    fwrite($file,"\n ".print_r($_FILES, true));
                    fclose($file);
                
                // }
                ///////

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->adds_list($request);
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 647){
                //print_r($Check); exit;
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                //$Partner_list['tranding'] = array();
                // $trandingList = $this->tranding();
                //$Partner_list['tranding'] = $trandingList;

                $Partner_list['post'] = array();
                $doFav = 0;
            if(Auth::user()->is_subscribe == 0){
                $checkpaiduser = Post::where('status', 1)->count();
                //echo $checkpaiduser; exit;
                if($checkpaiduser > 2){
                    $page = $request->page_no; 
                    $doFav = 1;
                }
            }
            $needSubscribe = 0;
            if($doFav == 1 && $page != 1){
                 $needSubscribe = 1; 
            } 
                if($needSubscribe == 0){
                    foreach($responseOld['data']['data'] as $list){
                        
                        $partner_array = array();
                        $repost  = array();
                        $postid =  $list['id'];

                        
                        $like_count  = $UserRepostitory->like_count($postid);
                        $favourite_count  = $UserRepostitory->favourite_count($postid);
                        //$comment_count  = $UserRepostitory->comment_count($postid);
                        //$repost_count  = $UserRepostitory->repost_count($postid);  
                        $is_my_like = $UserRepostitory->my_like_count($postid,Auth::user()->id);      
                        /*if($list['post_type'] == 3){
                            $total_vote_count = $UserRepostitory->total_vote_count($postid); 
                            $vote_count_per = $UserRepostitory->vote_count($postid) ; 
                           // print_r($vote_count_per); exit;
                        }else{
                            $total_vote_count = 0; 
                            $vote_count_per = 0 ; 

                        }*/
                        
                        
                        $partner_array['id']            =   @$list['id'] ? $list['id'] : '';

                        $is_my_favourite = $UserRepostitory->is_my_favourite($postid,Auth::user()->id);      
                        if($list['is_url'] == 1){
                        $partner_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';
                        }else{
                            $partner_array['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] :'';

                        }
                        $partner_array['website_url']  =   @$list['url'] ? $list['url'] : '';
                        $partner_array['title']  =   @$list['title'] ? $list['title'] : '';
                        $partner_array['desc']  =   @$list['description'] ? $list['description'] : '';
                        $partner_array['price']  =   @$list['price'] ? $list['price'] : 0;
                        $partner_array['discount_price']  =   @$list['discount_price'] ? $list['discount_price'] : '';
                        $partner_array['offer']  =   @$list['offer'] ? intval($list['offer']).'% Off ' : '';
                        $partner_array['is_favorited']  =  $is_my_favourite;

                        
                        $partner_array['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;

                        array_push($Partner_list['post'],$partner_array);
                    }
                    //echo '<pre>'; print_r($responseOld['data']); exit;
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Partner_list,
                        'current_page' => $responseOld['data']['current_page'],
                        'first_page_url' => $responseOld['data']['first_page_url'],
                        'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                        'last_page' => $responseOld['data']['last_page'],
                        'last_page_url' => $responseOld['data']['last_page_url'],
                        'per_page' => $responseOld['data']['per_page'],
                        'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                        'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                    ];
                }else{
                    $msg =  $error_msg->responseMsg(650);
                    $response = [
                        'code' => 650,
                        'msg'=>  $msg
                    ];
                }

            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    


    /**********************************************************************************
      API                   => Get  partner detail                                    *
    * Description           => It is user for partner detail                          *
    * Required Parameters   =>                                                        *
    * Created by            => Sunil                                                  *
    ***********************************************************************************/
    public function deal_detail(Request $request){
        
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $request['id'];
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->post_detail($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 213    ){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }

    /***************************************************************************************
      API                   => Get and update Profile                                     *
    * Description           => It is user for Profile                                     *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function profile1(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
           

            //$data = $request->id;
            $data = $userId;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        if($request->method() == 'POST'){

            $data = $request->all();
            $Is_method = 0;
            $ApiService = new ApiService();
            $Check = $ApiService->profile($Is_method,$data);
            
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 217){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }      

        return $response;
    }






    /***************************************************************************************
      API                   => Upload Gallery                                              *
    * Description           => It is user for for CRED gallery api                                      *
    * Required Parameters   =>                                                             *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/
    
    public function gallery(Request $request){
        $Is_method = 0;
        if($request->method() == 'GET'){
        
            $Is_method = 1;

            $rules = array('p_u_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }
        }    


        if($request->method() == 'POST'){


            $Is_method = 2;
            $rules = array('p_photo' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){      

                $validate_error = $validate->errors()->all();
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 218){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }  


        
        if($request->method() == 'DELETE'){


            $Is_method = 3;
            $rules = array('p_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{

                $ApiService = new ApiService();
                $Check = $ApiService->gallery($Is_method,$data);
                
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 214){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }  

        return $response;
    
    }


    /***************************************************************************************
    * API                   => make default                                                *
    * Description           => It is used for creating the report                          *        
    * Required Parameters   =>                                                             *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/
    
    public function make_default(Request $request){
         if($request->method() == 'POST'){
            $rules = array('p_id' => 'required','is_default' =>'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){      

                $validate_error = $validate->errors()->all();
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->mark_default($data);
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 646){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data 
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
            }    
        }   

        return $response;        
        
    }



    

    /************************************************************************************
    * API                   => Create Like post                                         *
    * Description           => It is used for liked the post                            * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function like(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->like($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 219){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }


    /************************************************************************************
    * API                   => Create favourite post                                    *
    * Description           => It is used for favourite post                            * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function favourite(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('post_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->favourite($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->data); exit;
                if($Check->error_code == 220){
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  649,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }    
    }



     /************************************************************************************
    * API                   => Create follow/unfollow                                   *
    * Description           => It is used for follow/unfollow  the post                 * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    ************************************************************************************/
    public function follow(Request $request){
       
        if($request->method() == 'POST'){
            $data = $request;
            
            $rules = array('user_id' => 'required');
            $data = $request->all();
            $validate = Validator::make($data,$rules);

            if($validate->fails()){    
                $validate_error  = $validate->errors()->all();  
                $response = ['code'=>403, 'msg'=> $validate_error[0]];        
            }else{
                $ApiService = new ApiService();
                $Check = $ApiService->follow($data);

                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
                //print_r($Check->error_code); exit;
                if($Check->error_code == 219){
                    $Check->data['is_follow'] = 1;
                    unset($Check->error_code);
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg,
                        'data'  =>  $Check->data
                    ];
                }else{
                    $Check->data['is_follow'] = 0;
                    $response = [
                       // 'code' => $Check->error_code,
                        'code'  =>  200,
                        'msg'=>  $msg,
                        'data'  =>  $Check->data
                    ];
                }
            }

            return $response;
        }   
    }

 
   








     /***********************************************************************************
    * API                   => notificationList                                        *
    * Description           => It is to get notificationList                           *
    * Required Parameters   => Access Token                                            *
    * Created by            => Sunil                                                   *
    ************************************************************************************/

    public function notificationList(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $UserRepostitory = new UserRepository();
            $Check = $ApiService->notificationList($request);
            $error_msg = new Msg();

            $msg =  $error_msg->responseMsg($Check->error_code);
            if($Check->error_code == 277){
                $data = $Check->data;   
                $responseOld = [
                    'data'  => $data->toArray()    
                ];
                    //print_r($Check->data); exit;           
                //print_r($Check); exit;
                $notification_list['notification'] = array();
                foreach($responseOld['data']['data']  as $list){
                    $notification_array = array();
                    //echo '<pre>';print_r($list); exit;
                    $notification_array['id'] =  @$list['n_id'] ? $list['n_id'] : '';
                    $notification_array['userid']        =   @$list['n_u_id'] ? $list['n_u_id'] : '';
                    $notification_array['a_id']  =   @$list['n_data'] ? $list['n_data'] : '';
                    if($list['is_url'] == 1){
                        $notification_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';
                    }else{
                        $notification_array['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] :'';

                    }
                   

                    //$notification_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';

                    $notification_array['n_type']  =   @$list['n_type'] ? $list['n_type'] : '';
                    $notification_array['message']  =   @$list['n_message'] ? $list['n_message'] : '';
                    $notification_array['status']  =   @$list['n_status'] ? $list['n_status'] : '';
                    $notification_array['added_date']  =   @$list['n_added_date'] ? $list['n_added_date'] : '';
                    //$notification_list[] =$notification_array;
                    array_push($notification_list['notification'],$notification_array);
                }
                //echo '<pre>'; print_r($responseOld['data']); exit;
                 $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $notification_list,
                    'current_page' => $responseOld['data']['current_page'],
                    'first_page_url' => $responseOld['data']['first_page_url'],
                    'from' => $responseOld['data']['from']?$responseOld['data']['from']:0,
                    'last_page' => $responseOld['data']['last_page'],
                    'last_page_url' => $responseOld['data']['last_page_url'],
                    'per_page' => $responseOld['data']['per_page'],
                    'to' => $responseOld['data']['to']?$responseOld['data']['to']:0,
                    'total' => $responseOld['data']['total']?$responseOld['data']['total']:0
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    /***********************************************************************************
    * API                   => Patient List                                              *
    * Description           => It is used for getting patient list                        *        
    * Required Parameters   =>                                                             *
    * Created by            => Sunil                                                       *
    ***************************************************************************************/
    
    public function patient_list(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->patient_list();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 635){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    
    /************************************************************************************
    * API                   => subscriptionsList                                        *
    * Description           => It is used for subscriptionsList                         * 
    * Required Parameters   =>                                                          *
    * Created by            => Sunil                                                    *
    *************************************************************************************/
    
    public function subscriptionsList(Request $request){
        //send push notification
        $sender_name = 'sunil';
        $message =  $sender_name." find as match.";
        $datass['userid'] = 66;
        $datass['name'] = 'sunil';
        $datass['n_type'] = 2;
        $datass['noti_type'] = "2";
        $datass['message'] = $message;
        $notify = array ();
        $notify['receiver_id'] = 83;
        $notify['relData'] = $datass;
        $notify['message'] = $message;

        $UserRepostitory = new UserRepository();
        $test =  $UserRepostitory->sendPushNotification($notify);  exit;
        if($request->method() == 'GET'){
            //$data = $request;
            $ApiService = new ApiService();
            $Check = $ApiService->subscriptionsList();
            //print_r($Check); exit;
            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 220){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }





    

    

    /*********************************************************************
      API                   => check_username                            *
    * Description           => It is user for username                   *
    * Required Parameters   =>                                           *
    * Created by            => Sunil                                     *
    **********************************************************************/
    public function check_username(Request $request){
        
        $userId= Auth::user()->id;
        $Is_method  = 0; 
      
        if($request->method() == 'GET'){
            $data = $request;
            $Is_method = 1;
            $ApiService = new ApiService();
            $Check = $ApiService->check_username($Is_method,$data,$userId);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 207){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }

        return $response;
    }

    

    /***************************************************************************************
      API                   => chat_user for test                                          *
    * Description           => It is user for chat_user                                  *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function chat_user(Request $request){
        $userId = Auth::user()->id;
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);
        //print_r($twilio); exit;
        $user = $twilio->conversations->v1->users
                                          ->create($userId);

        //print_r($user); exit;
        $sid = $user->sid;
        $ApiService = new ApiService();
        $Check = $ApiService->chat_user_sid_update($sid,$userId);
    }


    /***************************************************************************************
      API                   => Chat_token                                                 *
    * Description           => It is user for test_twilio                                 *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/
    public function chat_token(Request $request){
        
        // Required for all Twilio access tokens
        // Required for Chat grant
        $data = $request; 
        //print_r($data['device_type']); exit; 
        $twilioAccountSid = getenv("TWILIO_ACCOUNT_SID");
        $twilioApiKey = getenv("TWILIO_APIKEY");
        $twilioApiSecret = getenv("TWILIO_APISECRET");
        $userId = Auth::user()->id;
        // Required for Chat grant
        $serviceSid = getenv("TWILIO_SERVICESID");//Default
        $chat_env = getenv("CHAT_ENV");//Default
        // choose a random username for the connecting user
        $identity = $chat_env.''.$userId ;//$data['sid'];

        // Create access token, which we will serialize and send to the client
        $token = new AccessToken(
            $twilioAccountSid,
            $twilioApiKey,
            $twilioApiSecret,
            3600,
            $identity
        );
        //print_r($token); exit;
        // Create Chat grant
        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid($serviceSid);
        if($data['device_type'] == 0){// APNS
            $chatGrant->setPushCredentialSid('CR6d5f79c62f75ff86e03453027a6662dd');
        }else{//FCM
            $chatGrant->setPushCredentialSid('CR159af2c172372ea4bf411d8e465104c5');
        }
       
        // Add grant to token
        $token->addGrant($chatGrant);

        // render token to string
        $user_token = $token->toJWT();

        
        $response = [
            'code' => 200,
            'msg'=>  'Token created succesfully',
            'token'=> $user_token
        ];

        return $response;
    }


    public function addchatuser(){
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

       /* $message = $twilio->conversations->v1->conversations("CHc1bafe6eab554f01ba755b350fb450e4")
                                     ->messages
                                     ->create([
                                                  "author" => "Dev3",
                                                  "body" => "Ahoy there!"
                                              ]
                                     );*/
          
        //if($data->EventType == 'onConversationAdded'){
            //fwrite($file,"\n ". print_r('sunil2', true));
            // fwrite($file,"\n ". print_r($data->EventType, true));
            // $receiver_id = getenv("CHAT_ENV").''.$data->Attributes; 
           //echo $receiver_id = getenv("CHAT_ENV").'3'; 
        $participant = $twilio->conversations->v1->conversations("CHc779b7e4ed3b44c29bad092083e68d61")
                 ->participants
                 ->create([
                            "identity" => "Dev41"
                          ]
                 );
                $datanew =  json_decode ($participant ,true );                          
        print($datanew);
            //print($participant->sid);
        //}

    }

    public function chat_post_event(Request $request){
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $data = $request->all();  
        //if(isset($data)){

            //$datanew =  json_encode ( $data ,true );
            //$fileName = date('Ymd').'chat_post_event.txt';
            // prd($fileName);
            //$file = fopen($fileName,'a');
            $file = fopen('chat_pre_event.txt','a+');
            
            fwrite($file,"\n ". print_r('sunil1', true));
            //fwrite($file,"\n ". print_r($datanew, true));
            fwrite($file,"\n ". print_r($data, true));
            if(!empty($_FILES))
            {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            }

            if($data['EventType'] == 'onConversationAdded'){
                $sid = getenv("TWILIO_ACCOUNT_SID");
                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio = new Client($sid, $token);
                fwrite($file,"\n ". print_r('sunil2', true));
                $ConversationSid = $data['ConversationSid'];
                $Attributes = $data['Attributes'];
                $receiver_id = getenv("CHAT_ENV").''.$data['Attributes']; 
                $participant = $twilio->conversations->v1->conversations($ConversationSid)
                     ->participants
                     ->create([
                                "identity" => $receiver_id
                              ]
                     );

                //print($participant->sid);
            }

           
            fwrite($file,"\n ". print_r('sunil6', true));
                
            /////////
        //}
    }

    public function chat_pre_event(Request $request){
        $data = $request->all();   
        //if(isset($data)){

            $datanew =  json_encode ( $data ,true );

            if($datanew['EventType'] == 'onConversationAdded'){


            }
            $file = fopen('chat_pre_event.txt','a+');
            
            fwrite($file,"\n ". print_r($datanew, true));
            fwrite($file,"\n ". print_r($datanew->EventType, true));
            fwrite($file,"\n ". print_r($datanew->Attributes, true));
            fwrite($file,"\n ". print_r('sunil', true));
            if(!empty($_FILES))
            {
            
                fwrite($file,"\n ".print_r($_FILES, true));
                fclose($file);
            
            }
            if($datanew->EventType == 'onMessageAdded'){
                 fwrite($file,"\n ". print_r($datanew->EventType, true));
                $sid = getenv("TWILIO_ACCOUNT_SID");
                $token = getenv("TWILIO_AUTH_TOKEN");
                $twilio = new Client($sid, $token);
                $receiver_id = getenv("CHAT_ENV").''.$datanew->Attributes; 
                $participant = $twilio->conversations->v1->conversations($datanew->ConversationSid)
                     ->participants
                     ->create([
                                "identity" => $receiver_id
                              ]
                     );

                //print($participant->sid);
            }
            /////////
        //}
    }

    public function chat_update_uername(Request $request){
        $data = $request->all();   
             //if(isset($data)){
       
        // Find your Account SID and Auth Token at twilio.com/console
        // and set the environment variables. See http://twil.io/secure
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

        $user = $twilio->conversations->v1->users("US6808d12f805c493b8572e02f81f03153")
          ->update([
                       "friendlyName" => "techno new name",
                   ]
          );

        //print($user->friendlyName);

       
                //print($participant->sid);
           
            /////////
        //}
    }


    public function check_pending(){
        $date = new DateTime;
        //echo $test = $date->format('Y-m-d H:i:s').'<br>';
        $date->modify('-1 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');

        $result = DB::table('pending_matches')->where('is_pending','=',1)->where('is_notify','=',0)->where('added_date','<',$formatted_date)->get();
        if(!empty($result)){
            foreach ($result as $resultkey => $resultvalue) {
                # code...
                DB::table('pending_matches')->where('id', $resultvalue->id)
                ->update([
                   'is_notify' => 1,
                   ]);
                $message =  "your are not found any match in last fifteen minutes.";
                $data['userid'] = $resultvalue->sender_id;
                $data['message'] = $message;
                $data['n_type'] = 3;
                $notify = array ();
                $notify['receiver_id'] = $resultvalue->sender_id;
                $notify['relData'] = $data;
                $notify['message'] = $message;
                echo print_r($notify);
                $UserRepostitory   = new UserRepository();
                $test =  $UserRepostitory->sendPushNotification($notify); 
                         echo '<pre>'; print_r($resultvalue->sender_id);
            }
        }
    }

    // Cron 30MIn
    public function update_previous(){
        $date = new DateTime;
        //echo $test = $date->format('Y-m-d H:i:s').'<br>';
        $date->modify('-1 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');

        $result = DB::table('pending_matches')->where('is_new','=',1)->where('is_pending','=',0)->where('added_date','<',$formatted_date)->get();

        if(!empty($result)){
            foreach ($result as $resultkey => $resultvalue) {
                //print_r($resultvalue->id); exit;
                # code...
                  DB::table('pending_matches')->where('id', $resultvalue->id)
                                ->update([
                               'is_pending' => 0,
                               'is_new' => 0,
                               ]);
                /*$message =  "your are not found any match in last fifteen minutes.";
                $data['userid'] = $resultvalue->sender_id;
                $data['message'] = $message;
                $data['n_type'] = 3;
                $notify = array ();
                $notify['receiver_id'] = $resultvalue->sender_id;
                $notify['relData'] = $data;
                $notify['message'] = $message;
                //print_r($notify); exit;
                $UserRepostitory   = new UserRepository();
                $test =  $UserRepostitory->sendPushNotification($notify); 
                         echo '<pre>'; print_r($resultvalue->sender_id);*/
            }
        }
    }
   



    /*********************************************************************************
      API                   => Get and notification_match_detail                     *
    * Description           => It is notification_match_detail                       *
    * Required Parameters   =>                                                       *
    * Created by            => Sunil                                                 *
    *********************************************************************************/
    public function notification_match_detail(Request $request){
        
        $Is_method  = 0; 
        if($request->method() == 'GET'){
            $req = $request->id;
            $Is_method = 1;
            $data = Auth::user()->id; 
            $ApiService = new ApiService();
            $Check = $ApiService->notification_match_detail($Is_method,$req,$data);

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 303){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg,
                    'data'  =>  $Check->data  
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

        }
        return $response;
    }
    /***************************************************************************************
      API                   => Logout                                                     *
    * Description           => It is user for Logout                                      *
    * Required Parameters   =>                                                            *
    * Created by            => Sunil                                                      *
    ***************************************************************************************/


    public function logout(Request $request){
       
        if($request->method() == 'GET'){

            $ApiService = new ApiService();
            $Check = $ApiService->logout();

            $error_msg = new Msg();
            $msg =  $error_msg->responseMsg($Check->error_code);
        
            if($Check->error_code == 642){
                $response = [
                    'code'  =>  200,
                    'msg'   =>  $msg
                ];
            }else{
                $response = [
                    'code' => $Check->error_code,
                    'msg'=>  $msg
                ];
            }

            return $response;
        }   
    }


    public function deleteAccount(Request $request)
    {
        if($request->method() == 'DELETE'){
               
                $ApiService = new ApiService();
                $Check = $ApiService->deleteAccount();
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 447){
                    $response = [
                        'code'  =>  200,
                        'msg'   =>  $msg
                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }
                return $response;
            
        }   
    }

    /*****************************************************************************
     API                   => Guest Login                                      
     Description           => It is used  verify                                
     Required Parameters   => guest_Id                    
     Created by            => Sunil                                             
    *****************************************************************************/    
    public function guestLogin(Request $request){
            
        $data = $request->all();
        $logdata = $data; 
        
        if($request->method() == 'POST'){

            
            $rules = array('guest_id'=>'required');
            

            $validate = Validator::make($data,$rules);

            if($validate->fails() ){
                
                $validate_error = $validate->errors()->all();

                $response = ['code' => 403, 'msg'=> $validate_error[0] ]; 

            }else{  
                $ApiService = new ApiService();
                $Check = $ApiService->guestLogin($data); 
                //print_r($Check); exit; 
                $error_msg = new Msg();
                $msg =  $error_msg->responseMsg($Check->error_code);
            
                if($Check->error_code == 200 ){
                    $response = [
                        'code' => 200,
                        'msg'=>  $msg,
                        'data' => $Check->data,
                        'is_remove_gestlogin' => 1                    ];
                }else{
                    $response = [
                        'code' => $Check->error_code,
                        'msg'=>  $msg
                    ];
                }

            }
           // $ApiService = new ApiService();
           // $ApiService->customLog($logdata,$response,@$msg,@$validate_error,@$userId);
            return $response;
        }   
    }
}
