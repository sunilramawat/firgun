<?php

namespace App\Http\Controllers\Repository;
use App\User;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\Partner;
use App\Models\Like;
use App\Models\Follow;
use App\Models\CommentLike;
use App\Models\Favourite;
use App\Models\Notification;
use App\Models\PendingMatches;
use App\Models\Categories;
use App\Models\Discount;
use App\Models\SubCategories;
use App\Models\Gender;
use App\Models\Faq;
use App\Models\Answer;
use App\Models\UserAnswer;
use App\Models\ReportList;
use App\Models\Religion;
use App\Models\Report;
use App\Models\PartnerType;
use App\Models\Region;
use App\Models\Subscription;
use App\Models\Transaction;
use Twilio\Rest\Client;
use App\Http\Controllers\Utility\CustomVerfication;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\SendEmails;
use Carbon\Carbon;	
use Auth;
use DB;

Class UserRepository extends User{

	
	public function check_Guest_user($data){
		if(isset($data['guest_id'])){
			$user_list = User::Where('guest_id',@$data['guest_id'])->first();
			//print_r($user_list); exit;
		}

		return $user_list;				
	}
	public function check_user($data){
		if(isset($data['facebook_id'])){
			$user_list = User::Where('facebook_id',@$data['facebook_id'])->first();
			//print_r($user_list); exit;
		}elseif(isset($data['google_id'])){
			$user_list = User::Where('google_id',@$data['google_id'])->first();
		}elseif(isset($data['apple_id'])){
			$user_list = User::Where('apple_id',@$data['apple_id'])->first();
		}elseif(isset($data['email'])){
			$user_list = User::Where('email',@$data['email'])
				->where('user_status','!=',0)->first();
		}else{
			$user_list = User::Where('phone',@$data['phone'])
				->where('user_status','!=',0)->first();
		}

		return $user_list;				
	}

	public function check_unactive_user($data){
		if(isset($data['email'])){
			$user_list = User::where('email',@$data['email'])->first();
		}else{
			$user_list = User::where('phone',@$data['phone'])->first();
		}
		//echo '<pre>'; print_r($user_list); die;
		return $user_list;				
	}

	public function register($data){

		$CustomVerfication = new CustomVerfication();
		$SendEmail = new SendEmails();
		$code = $CustomVerfication->generateRandomNumber(4);
		$rescod  = "";
		
		if(!isset($data['id'])){
			$create_user = new User();
			$create_user->username = @$data['username']?@$data['username']:'';
			$create_user->photo = @$data['photo'];
			$create_user->phone = @$data['phone'];
			$create_user->country_code = @$data['country_code'];
			$create_user->user_type = @$data['user_type']?$data['user_type']:1;
			$create_user->first_name = @$data['first_name'];
			$create_user->last_name = @$data['last_name'];
			$create_user->added_date = date ( 'Y-m-d H:i:s' );
			$create_user->user_status = '0';
			$create_user->is_approved = '0';
			$create_user->user_status = '0';
			$create_user->activation_code = $code;
			$create_user->password = hash::make($code);
			$create_user->is_email_verified = '0';
			$create_user->is_phone_verified = '0';
	        $create_user->last_login= date ( 'Y-m-d H:i:s' );
	        $create_user->token_id = mt_rand(); 
			$create_user->created_at = date ( 'Y-m-d H:i:s' );
			$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		
		}else{
			$create_user = User::find($data['id']);
			$create_user->username = @$data['username']?$data['username']:$create_user['username'];
			$create_user->photo = @$data['photo']?$data['photo']:$create_user['photo'];
			$create_user->phone = @$data['phone']?$data['phone']:$create_user['phone'];
			$create_user->country_code = @$data['country_code']?$data['country_code']:$create_user['country_code'];
			$create_user->user_type = @$data['user_type']?$data['user_type']:$create_user['user_type'];
			$create_user->first_name = @$data['first_name']?$data['first_name']:$create_user['first_name'];
			$create_user->last_name = @$data['last_name']?$data['last_name']:$create_user['last_name'];
			$create_user->added_date = $create_user['added_date'];
			$create_user->user_status = $create_user['user_status'];
			$create_user->is_approved =  $create_user['is_approved'];
			$create_user->activation_code = $code;
			$create_user->password = hash::make($code);
			$create_user->is_email_verified = $create_user['is_email_verified'];
			$create_user->is_phone_verified = $create_user['is_phone_verified'];
	        $create_user->last_login= date ( 'Y-m-d H:i:s' );
	        $create_user->token_id = mt_rand(); 
			$create_user->created_at =  $create_user['created_at'];
			$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		}
		//$create_user->email 	= @$data['email'] ? $data['email']: '';
		//$create_user->password 	= hash::make(@$data['password']) ? hash::make(@$data['password']): '';
		
		$create_user->save();
		$userid = $create_user->id; 
		$message = "Your Firgun verification Code is ". $code;
		
		if(isset($data['phone'])){
			$phone = $data['country_code'].''.$data['phone'];
            $verify_type = 1;
            $create_user->activation_code = $code;
            $user = User::find($userid);
            /*$sidname = getenv("CHAT_ENV").$userid; 
            if(empty($user['sid'])){
            	$chat_sid_create = $CustomVerfication->chat_user($sidname);
            	$user = User::find($userid);
				$user->sid = $chat_sid_create ;
				$user->save();
            }*/
            
			$verify = $CustomVerfication->phoneVerification($message,$phone);
            //$verify = $CustomVerfication->phoneVerification($message,"+917340337597");

		}else{
            $verify_type = 2;
        }

        $data['forgot_type'] = 1;

        if(@$data['email'] != ''){

            $email = $create_user->email;
            $name = @$create_user->name;
            $code =  $code;

            //$url =  url("activation/".$code);
			//$newpassword = $url;

            $SendEmail->sendUserRegisterEmail($email,$name,$code,$data['forgot_type'],$userid);
        	
        }

		return $create_user;
	}


	public function social_register($data){
		if(@$data['facebook_id']){
			$code = @$data['facebook_id'];
		}elseif(@$data['google_id']){
			$code = @$data['google_id'];
		}elseif(@$data['apple_id']){
			$code = @$data['apple_id'];
		}



		$CustomVerfication = new CustomVerfication();
		$SendEmail = new SendEmails();
		$rescod  = "";
		
		if(!isset($data['id'])){
			$create_user = new User();
			$follower_count  = 0;
        	$following_count  = 0;
		}else{
			$create_user = User::find($data['id']);
			$follower_count  = $this->follower_count($data['id']);
        	$following_count  = $this->following_count($data['id']);
		}
		//$create_user->email 	= @$data['email'] ? $data['email']: '';
		//$create_user->password 	= hash::make(@$data['password']) ? hash::make(@$data['password']): '';


		$create_user->username = @$data['username'];
		$create_user->bio = @$data['bio'];
		$create_user->website = @$data['website'];
		$create_user->fb_link = @$data['fb_link'];
		$create_user->linkedin_link = @$data['linkedin_link'];
		$create_user->twitter_link = @$data['twitter_link'];
		$create_user->Instagram = @$data['Instagram'];
		$create_user->rank = @$data['rank']?$data['rank']:0;
		$create_user->followers = @$follower_count;
		$create_user->followings = @$following_count;
		$create_user->posts = @$data['posts']?$data['posts']:0;
		$create_user->user_type =  @$data['user_type']?$data['user_type']:1;
		
		$create_user->facebook_id = @$data['facebook_id'];
		$create_user->google_id = @$data['google_id'];
		$create_user->apple_id = @$data['apple_id'];
		$create_user->first_name = @$data['first_name'];
		$create_user->last_name = @$data['last_name'];
		$create_user->phone = @$data['phone'];
		$create_user->added_date = date ( 'Y-m-d H:i:s' );
		$create_user->user_status = 1;
		$create_user->is_approved = '0';
		$create_user->activation_code = $code;
		$create_user->password = hash::make($code);
		$create_user->is_email_verified = '0';
		$create_user->is_phone_verified = '0';
        $create_user->last_login= date ( 'Y-m-d H:i:s' );
        $create_user->token_id = mt_rand(); 
		$create_user->created_at = date ( 'Y-m-d H:i:s' );
		$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		
		$create_user->save();
		$userid = $create_user->id; 
		
		
        return $userid;
	}

	public function getuser($data){
		if(!empty($data['code'])){
			
			if(isset($data['email'])){
				$query = User::where('activation_code','=',$data['code'])
					->where('email',@$data['email'])
					->first();
			}else{
				$find = 0;
				$query = User::where('activation_code','=',$data['code'])
					->where('phone',@$data['phone'])
					->where('user_status','!=',2)
					->first();
				//echo '<pre>'; print_r($query); exit;
				if(!empty($query)){
					$find = 1;	
				}else{
					$query = User::where('activation_code','=',$data['code'])
					->where('phone_tmp',@$data['phone'])
					->first();
					$find = 2; // to blank phone_tmp and  update in phone
				}

			}	
			if(!empty($query)){
				$user = User::find($query->id);
				//$user->password = Hash::make($data['password']);
		        //$user->activation_code = '';
		        //$user->user_status = 1;
				if(isset($data['email'])){
		            $user->is_email_verified = 1;
		        }else{
		           // $user->is_phone_verified = 1;
		        }
		        if($find == 1){
		        	$user->is_phone_verified  = 1;
		        	$user->user_status  = 1;
		        	
		        }
		        if($find == 2){
		        	$user->phone = $data['phone'];
		        	$user->phone_tmp = '';
		        }

	        	$user->save();



	        	$userData['code'] = 205;
	        	//$userData['email'] = $user->email; 
	        	//$userData['password'] = $user->password; 
	        	$userData['id'] = $user->id; 
	        	$userData['phone'] = $user->phone; 
	        	//$userData['access_token'] = $data['token']; 
		        
			}else{

				$userData['code'] = 422;	

	        }

		}else{

			$userData['code'] = 422;	

		}

		return $userData;
	}

	public function login($data){
		if(!empty($data['phone']))
		{
			$query = User::where('phone',$data['phone'])->first();
		}elseif (!empty($data['email'])) {
		
			$query = User::where('email',$data['email'])->first();			
		
		}else{
		
			$query = User::where('phone',$data['phone'])->where('email',$data['email'])->first();
		
		}
		

		return $query;
	}

	public function  clear_user_token($data){

		$clear_token = User::where('device_id',$data)->first();
		$clear_token->device_id = "";
		$clear_token->save();  
	}

	public function get_user_detail($data)
	{
		$token_id =  mt_rand();
		$query = User::find($data['id']);
		$query->token_id    = $token_id;
        $query->last_login  = date ( 'Y-m-d H:i:s' );
    	$query->device_id   = $data['device_id'];
        $query->device_type = $data['device_type'];
        if(@$data['first_name'] != ''){
        	$query->first_name  = @$data['first_name'];
    	}
    	if(@$data['last_name'] != ''){
       	 $query->last_name 	= @$data['last_name'];
    	}
    	if(@$data['photo'] != ''){
      		$query->photo 		= @$data['photo'];
      	}
      
        $query->save();

    	
        //$follower_count  = $this->follower_count($data['id']);
        //$following_count  = $this->following_count($data['id']);
        $userdata['username'] = @$query['username']?$query['username']:'';
		$userdata['user_type'] =  @$query['user_type']?$query['user_type']:1;

    	$userdata['id'] 		 = $query['id'];
       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $query['device_id']?$query['device_id']:'';
        $userdata['is_notify'] = $query['is_notify']? intval($query['is_notify']):0;
        $userdata['device_type'] = $query['device_type']? intval($query['device_type']):'';
        $userdata['bio'] = $query['bio']?$query['bio']:'';
        $userdata['is_subscribe'] = $query['is_subscribe']?intval($query['is_subscribe']):0;
        $userdata['first_name']  = $query['first_name']?$query['first_name']:'';
        $userdata['last_name'] 	 = $query['last_name']?$query['last_name']:'';
        $userdata['device_token']= $query['device_token']?$query['device_token']:'';
        $userdata['access_token']= $data['token'];
        $userdata['user_status'] = $query['user_status']?$query['user_status']:'';
        $userdata['is_active_profile']= $query['is_active_profile']?$query['is_active_profile']:0;
        $userdata['photo']=  $query['photo']? URL('/public/images/'.$query['photo']):'';
        $userdata['phone']= $query['phone']?$query['phone']:'';
        $userdata['email']= $query['email']?$query['email']:'';
        $userdata['country_code']= $query['country_code']?$query['country_code']:'';
      	$userdata['category_id']= $query['category_id']?$query['category_id']:'';
	        


		return $userdata;
	}

	public function forgot_password($data,$user){

		$data['forgot_type'] = 1;
		$SendEmail = new SendEmails();
		$getuser = User::find($user->id);
		$PhoneVerification = new CustomVerfication();
		$rescod = "";
		if($data['forgot_type'] == 1){

			if(@$data['phone'] != ''){
		        $pass = 1234;  //mt_rand (1000, 9999) ;
                $getuser->forgot_password_code = $pass;
                $getuser->activation_code  = $pass;

            }else{

                $pass = mt_rand (1000, 9999) ;
                $getuser->forgot_password_code = $pass;
            }

            $getuser->forgot_password_date = date ( 'Y-m-d H:i:s' );
            unset($getuser->password);

            //print_r($getuser);die;
            $getuser->save();


            if(@$data['email'] != ''){
                $email = $getuser->email;
                $name = $getuser->name;
                $newpassword =  $pass;
                $SendEmail->sendUserEmailforgot($email,$name,$newpassword,$data['forgot_type']);
            	$rescod = 601;
            	
            }

            $lastId = $getuser->id;
            $country_code = '';
			$code =  $pass ;

			$message = "Your Pump Tracker verification code is ". $code;

			if(@$data ['phone'] != ''){
                //$verify = $PhoneVerification->phoneVerification($message,$data['phone']);
                $rescod = 601;
            }
		}

		return $rescod;
	}

	public function getdoctor(){

		$getdoctor 	=	User::select('id','name')->where('user_type',1)
						->where('user_status',1)->where('is_approved',1)->get();
		return $getdoctor; 
	}

	public function getuserById($data){
		//print_r($data); exit;
		$user 	=	User::find($data);
		
		//$follower_count  = $this->follower_count($user->id);
        //$following_count  = $this->following_count($user->id);
		$userdata['id'] = $user->id;
        $userdata['username'] = @$user['username']?$user['username']:'';
        $userdata['phone'] = @$user['phone']?$user['phone']:'';
        $userdata['country_code'] = @$user['country_code']?$user['country_code']:'';
        $userdata['photo'] = @$user['photo']? URL('/public/images/'.$user['photo']):'';
        $userdata['email'] = @$user['email']?$user['email']:'';
		$userdata['user_type'] =  @$user['user_type']?$user['user_type']:1;
		$userdata['is_notify'] =  @$user['is_notify']?intval($user['is_notify']):0;
		$userdata['bio'] = $user['bio']?$user['bio']:'';
        $userdata['is_subscribe'] = $user['is_subscribe']?intval($user['is_subscribe']):0;
       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $user['device_id']?$user['device_id']:'';
        $userdata['device_type'] = $user['device_type']?intval($user['device_type']):'';
        $userdata['first_name']  = $user['first_name']?$user['first_name']:'';
        $userdata['last_name'] 	 = $user['last_name']?$user['last_name']:'';
        $userdata['device_token']= $user['device_token']?$user['device_token']:'';
        $userdata['reset_key']= $user['reset_key']?$user['reset_key']:'';
        //$userdata['access_token']= $user['token'];
        $userdata['user_status'] = $user['user_status']?$user['user_status']:'';
        $userdata['is_active_profile']= $user['is_active_profile']?$user['is_active_profile']:0;
         $userdata['category_id']= $user['category_id']?$user['category_id']:'';

       	
		return $userdata;

	}
	public function getotheruserById($data){
		//$follower_count  = $this->follower_count($user->id);
        //$following_count  = $this->following_count($user->id);
		$userdata['id'] = $user->id;
        $userdata['username'] = @$user['username']?$user['username']:'';
        $userdata['phone'] = @$user['phone']?$user['phone']:'';
        $userdata['country_code'] = @$user['country_code']?$user['country_code']:'';
        $userdata['photo'] = @$user['photo']? URL('/public/images/'.$user['photo']):'';
		$userdata['user_type'] =  @$user['user_type']?$user['user_type']:1;
		$userdata['is_notify'] =  @$user['is_notify']?intval($user['is_notify']):0;
		$userdata['bio'] = $user['bio']?$user['bio']:'';
        $userdata['is_subscribe'] = $user['is_subscribe']?intval($user['is_subscribe']):0;
       	
       	$userdata['last_login']  = date ( 'Y-m-d H:i:s' );
        $userdata['device_id'] 	 = $user['device_id']?$user['device_id']:'';
        $userdata['device_type'] = $user['device_type']? intval($user['device_type']):'';
        $userdata['first_name']  = $user['first_name']?$user['first_name']:'';
        $userdata['last_name'] 	 = $user['last_name']?$user['last_name']:'';
        $userdata['device_token']= $user['device_token']?$user['device_token']:'';
        //$userdata['access_token']= $user['token'];
        $userdata['user_status'] = $user['user_status']?$user['user_status']:'';
        $userdata['is_active_profile']= $user['is_active_profile']?$user['is_active_profile']:0;
        $userdata['category_id']= $user['category_id']?$user['category_id']:'';

		//print_r($data);die;
		//	print_r($user);die;
       	// $userData['user_type'] = $user->user_type;
        //$userData['phone'] = $user->phone ? $user->phone : '';
        //$userData['address'] = @$user->address ? $user->address : '';
        //$userData['zip'] = @$user->zip ? $user->zip :'';
       //	$userData['forgot_password_code'] = $user->forgot_password_code ? $user->forgot_password_code : '';
        /*if($user->user_type == 2){
        
        }*/
        
        //$userData['photo'] = @$user->photo ? URL('/public/images/'.@$user->photo) : URL('/public/images/profile.png');
        //$userData['license_photo'] = $user->license_photo ? URL('/public/images/'.@$user->license_photo):'';
        
		return $userData;
	}

	public function getupdateprofile($data){
		
		$user 	=	User::find($data['Id']);
		$query  = 0;
		/*if($user->is_email_verified != 1){

			$user->email 	= 	@$data['email'] ? $data['email']:$user->email;
		} 	

		if($user->is_phone_verified != 1){

        	$user->phone 	= 	@$data['phone'] ? $data['phone']:$user->phone;
		}*/	
		/*if(isset($data['d_o_b'])){
			$dob = Carbon::createFromFormat('d/m/Y', $data['d_o_b']);
			//print_r($dob); exit;
			$age = 0;
			if(!empty($dob)){
				$age = Carbon::parse($dob)->diff(Carbon::now())->y;
			}
		}*/
		/*if(isset($data['email'])){

			$query = User::where('email',@$data['email'])->where('id','!=',@$data['Id'])->count();

		}else if(isset($data['phone'])){

			$query = User::where('phone',@$data['phone'])->where('id','!=',@$data['Id'])->count();

		}

		$code = 1234;//$CustomVerfication->generateRandomNumber(4);*/
		$is_verify  = 0;
		if($query == 0){
			
			$user->first_name 	= 	@$data['first_name'] ? $data['first_name'] : $user->first_name;
			$user->last_name 	= 	@$data['last_name'] ? $data['last_name'] : $user->last_name;
			$user->username 	= 	@$data['username'] ? $data['username'] : $user->username;
			//$user->user_type 	= 	@$data['user_type'] ? $data['user_type'] : $user->user_type;
			//$user->photo 	= 	@$data['photo'] ? $data['photo'] : $user->photo;
			$user->bio 	= 	@$data['bio'] ? $data['bio'] : $user->bio;
			$user->email 	= 	@$data['email'] ? $data['email'] : $user->email;

			$user->category_id 	= 	@$data['category_id'] ? @$data['category_id'] : $user->category_id;
			$user->is_active_profile 	= 	1;
			

			/*if($user->is_email_verified == 0){
				$SendEmail = new SendEmails();
				$user->email 	= 	@$data['email'] ? $data['email'] : $user->email;
				$is_verify  = 1;
			}
			if($user->phone == @$data['phone']){

			}else{
				$user->phone_tmp 	= 	@$data['phone'] ? $data['phone'] : $user->phone;
				$message = "Your Hopple verification Code is ". $code;
		
				if(isset($data['phone'])){
					$code = 1234;//$CustomVerfication->generateRandomNumber(4);
					$phone = $data['phone'];
		            $verify_type = 1;
		            $user->activation_code = $code;
					//$verify = $CustomVerfication->phoneVerification($message,$data['phone']);
		            //$verify = $CustomVerfication->phoneVerification($message,"+917340337597");

				}else{
		            $verify_type = 2;
		        }
			}
			if($is_verify  == 1){

	            $email = @$data['email'];
	            $name = $user->first_name;
	            $code =  $code;
	            $user->activation_code = $code;
	            //$url =  url("activation/".$code);
				//$newpassword = $url;

	            $SendEmail->sendUserRegisterEmail($email,$name,$code,0,$data['Id']);
			}*/

		/*	if(@$data['occupation'] == null){
				$user->occupation 			= 	@$data['occupation']?$data['occupation']:'';
			}else{
				$user->occupation 			= 	@$data['occupation']?$data['occupation']:$user->occupation ;

			}
		*/	//dd($data); 
		
			//$user->email 		=	@$data['email'] 	? $data['email'] : $user->email;
	        /*$user->lat 		=	@$data['lat'] ? $data['lat'] : $user->lat;
	        $user->lng 		=	@$data['lng'] ? $data['lng'] : $user->lng;*/
	        //$user->zip 		= 	@$data['zip'] ? $data['zip'] : $user->zip; 
	        
	        if (@$data['photo'] != "") {
				$extension_photo = $data['photo']->getClientOriginalExtension();
				if(strtolower($extension_photo) == 'jpg' || strtolower($extension_photo) == 'png' || strtolower($extension_photo) == 'jpeg' ) {
					$FileLogo_photo = time() .'123'.'.' .$data['photo']->getClientOriginalExtension();
					$destinationPath_photo = 'public/images';
					$data['photo']->move($destinationPath_photo, $FileLogo_photo);
					$documentFile_photo = $destinationPath_photo . '/' . $FileLogo_photo;
					$user->photo = $FileLogo_photo;
				}
			}	
			//print_r($user); exit;
			$user->save();

			$userData['code'] = 200;
			$userData['id'] = $user->id;
			//$follower_count  = $this->follower_count($user->id);
        	//$following_count  = $this->following_count($user->id);
	        //$userData['user_type'] = $user->user_type ? $user->user_type : '';
	        $userData['email'] = $user->email ? $user->email : '';
	        $userData['phone'] = $user->phone ? $user->phone : '';
	        $userData['country_code'] = $user->country_code ? $user->country_code : '';
	        $userData['photo'] = @$user->photo? URL('/public/images/'.$user->photo):'';
	        $userData['is_notify'] = $user->is_notify ? intval($user->is_notify) : 0;
	        $userData['device_id'] = $user->device_id ? $user->device_id :'';
	        $userData['device_type'] = $user->device_type ? intval($user->device_type) : '';
	        $userData['first_name'] = $user->first_name ? $user->first_name : '';
	        $userData['last_name'] = $user->last_name ? $user->last_name : '';
	        $userData['username'] = $user->username ? $user->username : '';
			$userData['user_type'] =  @$user->user_type ? intval($user->user_type) : 1 ;

	        $userData['bio'] = $user->bio?$user->bio:'';
       		 $userData['is_subscribe'] = $user->is_subscribe?intval($user->is_subscribe):0;
       	
		 	$userData['is_active_profile'] 			= 	 $user->is_active_profile?$user->is_active_profile : 1 ;
			//$userData['is_email_verified'] 			= 	 $user->is_email_verified   ? $user->is_email_verified   : 0;

       		$userData['last_login']  = date ( 'Y-m-d H:i:s' );
		    $userData['device_token']= $user->device_token ? $user->device_token : '';
	        //$userdata['access_token']= $user['token'];
	        $userData['user_status'] = $user->user_status ? $user->user_status : '';
	        $userData['category_id'] = $user->category_id ? $user->category_id : '';
	        
		   	}else{

	   		$userData['code'] = 410;
	   	}
	   /*	$sid = getenv("TWILIO_ACCOUNT_SID");
		$token = getenv("TWILIO_AUTH_TOKEN");
		$twilio = new Client($sid, $token);
	   	if(!empty($user->sid)){
		   	$user = $twilio->conversations->v1->users($user_chat_id)
	          ->update([
	                       "friendlyName" => $userData['first_name'],
	                   ]
	          );
     	}*/
		return $userData;
	}
	public function profilesetting($data){
		
		$user 	=	User::find($data['Id']);
		$query  = 0;
		$is_verify  = 0;
		if($query == 0){

			if($data['is_notify'] == 0){
				$user->is_notify 	= 	$data['is_notify'] ;
			}else if($data['is_notify'] == 1){
				$user->is_notify 	= 	$data['is_notify'] ;
			}else{

				$user->is_notify 	= 	 $user->is_notify;
			}
			
			//print_r($data); exit;
			$user->save();

			$userData['code'] = 200;
			$userData['id'] = $user->id;
			//$follower_count  = $this->follower_count($user->id);
        	//$following_count  = $this->following_count($user->id);
	        //$userData['user_type'] = $user->user_type ? $user->user_type : '';
	        $userData['email'] = $user->email ? $user->email : '';
	        $userData['phone'] = $user->phone ? $user->phone : '';
	        $userData['is_notify'] = $user->is_notify ? intval($user->is_notify) : 0;
	        $userData['country_code'] = $user->country_code ? $user->country_code : '';
	        $userData['photo'] = @$user->photo? URL('/public/images/'.$user->photo):'';

	        $userData['device_id'] = $user->device_id ? $user->device_id :'';
	        $userData['device_type'] = $user->device_type ? intval($user->device_type) : '';
	        $userData['first_name'] = $user->first_name ? $user->first_name : '';
	        $userData['last_name'] = $user->last_name ? $user->last_name : '';
	        $userData['username'] = $user->username ? $user->username : '';
			$userData['user_type'] =  @$user->user_type ? intval($user->user_type) : 1 ;

	         
		 	$userData['is_active_profile'] 			= 	 $user->is_active_profile?$user->is_active_profile : 1 ;
			//$userData['is_email_verified'] 			= 	 $user->is_email_verified   ? $user->is_email_verified   : 0;

       		$userData['last_login']  = date ( 'Y-m-d H:i:s' );
		    $userData['device_token']= $user->device_token ? $user->device_token : '';
	        //$userdata['access_token']= $user['token'];
	        $userData['user_status'] = $user->user_status ? $user->user_status : '';
	        
		   	}else{

	   		$userData['code'] = 410;
	   	}
	   /*	$sid = getenv("TWILIO_ACCOUNT_SID");
		$token = getenv("TWILIO_AUTH_TOKEN");
		$twilio = new Client($sid, $token);
	   	if(!empty($user->sid)){
		   	$user = $twilio->conversations->v1->users($user_chat_id)
	          ->update([
	                       "friendlyName" => $userData['first_name'],
	                   ]
	          );
     	}*/
		return $userData;
	}

	public function pref_profile($data){
		
		$user 	=	User::find($data['Id']);
		$query  = 0;
		/*if($user->is_email_verified != 1){

			$user->email 	= 	@$data['email'] ? $data['email']:$user->email;
		} 	

		if($user->is_phone_verified != 1){

        	$user->phone 	= 	@$data['phone'] ? $data['phone']:$user->phone;
		}*/	


		if(isset($data['email'])){

			$query = User::where('email',@$data['email'])->where('id','!=',@$data['Id'])->count();

		}else if(isset($data['phone'])){

			$query = User::where('phone',@$data['phone'])->where('id','!=',@$data['Id'])->count();

		}

		$code = 1234;//$CustomVerfication->generateRandomNumber(4);
		
		if($query == 0){
			
			$user->first_name 	= 	@$data['first_name'] ? $data['first_name'] : $user->first_name;
			$user->last_name 	= 	@$data['last_name'] ? $data['last_name'] : $user->last_name;
			$user->email 	= 	@$data['email'] ? $data['email'] : $user->email;
			
			
			$user->pref_gender	= 	@$data['pref_gender'] ? $data['pref_gender'] : $user->pref_gender;
			$user->pref_agegroup	= 	@$data['pref_agegroup'] ? $data['pref_agegroup'] : $user->pref_agegroup;
			$user->pref_race 	= 	@$data['pref_race'] ? $data['pref_race'] : $user->pref_race;
			$user->pref_religion	= 	@$data['pref_religion'] ? $data['pref_religion'] : $user->pref_religion;
			$user->pref_willing_to_dutch 	= 	@$data['pref_willing_to_dutch'] ? $data['pref_willing_to_dutch'] : $user->pref_willing_to_dutch;
			$user->pref_non_smoker 			= 	@$data['pref_non_smoker'] ? $data['pref_non_smoker'] : $user->non_smoker;
			$user->pref_min 			= 	@$data['pref_min'] ? $data['pref_min'] : $user->pref_min;
			$user->pref_max 			= 	@$data['pref_max'] ? $data['pref_max'] : $user->pref_max;
			$user->is_setpreferences 			= 	@$data['is_setpreferences'] ? $data['is_setpreferences'] : $user->is_setpreferences;
			
			$user->save();

			$userData['code'] = 200;
			$userData['id'] = $user->id;
	        $userData['pref_gender']	= 	$user->pref_gender ? $user->pref_gender : 0;
			$userData['pref_agegroup']	= 	$user->pref_agegroup ? $user->pref_agegroup : 0;
			$userData['pref_min']	= 	$user->pref_min ? $user->pref_min : 0;
			$userData['pref_max']	= 	$user->pref_max ? $user->pref_max : 0;
			$userData['pref_race'] 	= 	$user->pref_race ? $user->pref_race : 0;
			$userData['pref_religion']	= 	$user->pref_religion ? $user->pref_religion : 0;
			$userData['pref_willing_to_dutch'] = $user->pref_willing_to_dutch ? $user->pref_willing_to_dutch : 0;
			$userData['pref_non_smoker'] = $user->pref_non_smoker ? $user->pref_non_smoker : 0;
			$userData['is_setpreferences'] 			= 	 $user->is_setpreferences;
			$userData['is_active_profile'] 			= 	 $user->is_active_profile;
	   		
	   	}else{

	   		$userData['code'] = 410;
	   	}

		return $userData;
	}


	public function visibilty_profile($data){
		
		$user 	=	User::find($data['Id']);
		$query  = 0;
		/*if($user->is_email_verified != 1){

			$user->email 	= 	@$data['email'] ? $data['email']:$user->email;
		} 	

		if($user->is_phone_verified != 1){

        	$user->phone 	= 	@$data['phone'] ? $data['phone']:$user->phone;
		}*/	


		if(isset($data['email'])){

			$query = User::where('email',@$data['email'])->where('id','!=',@$data['Id'])->count();

		}else if(isset($data['phone'])){

			$query = User::where('phone',@$data['phone'])->where('id','!=',@$data['Id'])->count();

		}

		
		if($query == 0){
			
			$user->occupation_status	= 	@$data['occupation_status'] ? $data['occupation_status'] : $user->occupation_status;
			$user->religion_status	= 	@$data['religion_status'] ? $data['religion_status'] : $user->religion_status;
			$user->height_status 	= 	@$data['height_status'] ? $data['height_status'] : $user->height_status;
			$user->pref_willing_to_dutch_status 	= 	@$data['pref_willing_to_dutch_status'] ? $data['pref_willing_to_dutch_status'] : $user->pref_willing_to_dutch_status;
			$user->pref_non_smoker_status 			= 	@$data['pref_non_smoker_status'] ? $data['pref_non_smoker_status'] : $user->pref_non_smoker_status;
			
			$user->save();

			$userData['code'] = 200;
	   	
	   	}else{

	   		$userData['code'] = 410;
	   	}

		return $userData;
	}


	public function create_post($data){
		//print_r($data); exit;
		if($data['description'] !=  ''){
			if(@$data['post_id']){
				$post = Post::where('id','=',@$data['post_id'])
					->where('u_id','=',$data['userid'])
					->first();
			}else{
				$post = new Post();
			}
			
			$post->u_id = @$data['userid'] ? $data['userid']: '';
			$post->post_type = @$data['post_type'] ? $data['post_type']: 0;
			if(@$data['post_type'] == 3){
				$post->poll_one = @$data['poll_one'] ? $data['poll_one']: '';
				$post->poll_two = @$data['poll_two'] ? $data['poll_two']: '';
				$post->poll_three = @$data['poll_three'] ? $data['poll_three']: '';
				$post->poll_four = @$data['poll_four'] ? $data['poll_four']: '';
			}
			if(@$data['post_type'] == 2){
				$post->stock_name  = @$data['stock_name'] ? $data['stock_name']: '';
				$post->stock_target_price  = @$data['stock_target_price'] ? $data['stock_target_price']: '';
				$post->time_left   = @$data['time_left'] ? $data['time_left']: '';
				$post->term   = @$data['term'] ? $data['term']: '';
				$post->trend   = @$data['trend'] ? $data['trend']: '';
				$post->recommendation   = @$data['recommendation'] ? $data['recommendation']: '';

			}
			$post->posted_time = date ( 'Y-m-d H:i:s' );
			$post->description = @$data['description'] ? $data['description']: '';
			$post->created_at =  date ( 'Y-m-d H:i:s' );
			$post->updated_at =  date ( 'Y-m-d H:i:s' );
			if(@$data['imgUrl'] !=  ''){
				$post->imgUrl = @$data['imgUrl'];
			}
			$post->save();
			$lastid = $post->id;
			$partner_array['code'] = 200;
			$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', $lastid)
					->leftjoin('users','posts.u_id','users.id')
					->first();
			//echo '<pre>';print_r($list); exit;

			
			$is_my_favourite = DB::table('favourities')
	            ->where('post_id','=',$list['id'])
	            ->where('f_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_favourite == 1){

	            $partner_array['post_data']['is_favorited']  =  true;
	        }else{
	            $partner_array['post_data']['is_favorited']  =  false;

	        }


	        $is_my_like = DB::table('likes')
	                        ->where('post_id','=',$list['id'])
	            ->where('l_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_like == 1){

	            $partner_array['post_data']['is_liked']  =  true;
	        }else{
	            $partner_array['post_data']['is_liked']  =  false;

	        }
	        $partner_array['post_data']['is_reposted']  =  false;
			$partner_array['id']            =   @$list['id'] ? $list['id'] : '';
	        $partner_array['userid']      =   @$list['userid'] ? $list['userid'] : '';
	        $partner_array['picUrl']      =   @$list['picUrl'] ? $list['picUrl'] : '';
	        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
	        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
	        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
	        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
	       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
	        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
	        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
	        
	        $partner_array['post_data']['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] : '';
	        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
	        $partner_array['post_data']['like_count']  =   @$list['like_count'] ? $list['like_count'] : 0;
	        $partner_array['post_data']['favourite_count']  =   @$list['favourite_count'] ? $list['favourite_count'] : 0;
	        $partner_array['post_data']['comment_count']  =   @$list['comment_count'] ? $list['comment_count'] : 0;

	        $partner_array['post_data']['share_count']  =   @$list['share_count'] ? $list['share_count'] : 0;
	        $partner_array['post_data']['retweet_count']  =   @$list['retweet_count'] ? $list['retweet_count'] : 0;
	        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;
	        $partner_array['post_data']['stock_name']  =   @$list['stock_name'] ? $list['stock_name'] : '';
	        $partner_array['post_data']['stock_target_price']  =   @$list['stock_target_price'] ? $list['stock_target_price'] : '';
	        $partner_array['post_data']['time_left']  =   @$list['time_left'] ? $list['time_left'] : '';
	        $partner_array['post_data']['term']  =   @$list['term'] ? $list['term'] : '';
	        $partner_array['post_data']['result']  =   @$list['result'] ? $list['result'] : '';
	        $partner_array['post_data']['trend']   =  @$list['trend'] ? $list['trend'] : 0;
	        $partner_array['post_data']['recommendation']   =  @$list['recommendation'] ? $list['recommendation'] : 0;

	        $partner_array['post_data']['total_votes']  =   @$list['total_votes'] ? $list['total_votes'] : 0;
	        
	        if(!empty($list['poll_one'])){
	            $partner_array['post_data']['options'][0]['id']  =   1;
	            $partner_array['post_data']['options'][0]['title']  =   @$list['poll_one'] ? $list['poll_one'] : '';
	            $partner_array['post_data']['options'][0]['percentage']  =   0;
	            $partner_array['post_data']['options'][0]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_two'])){
	            $partner_array['post_data']['options'][1]['id']  =   2;
	            $partner_array['post_data']['options'][1]['title']  =   @$list['poll_two'] ? $list['poll_two'] : '';
	            $partner_array['post_data']['options'][1]['percentage']  =  0;
	            $partner_array['post_data']['options'][1]['is_voted']  =   0;
	        }
	        if(!empty($list['poll_three'])){
	            $partner_array['post_data']['options'][2]['id']  =   3;
	            $partner_array['post_data']['options'][2]['title']  =   @$list['poll_three'] ? $list['poll_three'] : '';
	            $partner_array['post_data']['options'][2]['percentage']  =  0;
	            $partner_array['post_data']['options'][2]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_four'])){
	            $partner_array['post_data']['options'][3]['id']  =   4;
	            $partner_array['post_data']['options'][3]['title']  =   @$list['poll_four'] ? $list['poll_four'] : '';
	            $partner_array['post_data']['options'][3]['percentage']  =  0;
	            $partner_array['post_data']['options'][3]['is_voted']  =  0;
	            
	        }
			/*$userData['code'] = 200;
			$userData['p_id'] = @$lastid;
			$userData['imgUrl'] = @$post->imgUrl;
			$userData['post_type'] = @$post->post_type;
			if(@$data['post_type'] == 3){
				$userData['poll_one'] = @$post->poll_one;
				$userData['poll_two'] = @$post->poll_two;
				if(@$post->poll_three != ''){
					$userData['poll_three'] = @$post->poll_three;
				}
				if(@@$post->poll_four != ''){
					$userData['poll_four'] = @$post->poll_four;
				}
			}
			$userData['created_at'] = @$post->created_at;
			$userData['updated_at'] = @$post->updated_at;
			$userData['u_id'] = @$post->u_id;*/
	

		}else{

			$partner_array['code'] = 633;

		}

		return $partner_array;
	}


	public function delete_post($data){
		$deleteanswer =  Post::where('id',$data['post_id'])
		->where('u_id',$data['userid'])
		->delete();	

		$deleteanswerq =  Post::where('repost_id',$data['post_id'])
		->delete();	
		return 1;
	}

	// Post Related Fundtion
	// Total vote count on post
	public function total_vote_count($postid){
		$total_vote_count = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->count();
        return $total_vote_count;
	}

	// Total vote count on post
	public function vote_count($postid){
		$vote_count_one = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',1)
		    ->count();

		$vote_count_two = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',2)
		    ->count();    

		$vote_count_three = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',3)
		    ->count();    

		$vote_count_four = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_option','=',4)
		    ->count();
		
		$total_vote_count = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->count();
		$userId = Auth::user()->id;
		$check_is_vote = DB::table('votes')
		    ->where('v_post_id','=',@$postid)
		    ->where('v_user_id','=',@$userId)
		    ->first();    
		//echo '<pre>'; print_r($check_is_vote->v_option); exit;   
		$vote_poll['one'] =  $vote_count_one;  
		$vote_poll['two'] =  $vote_count_two;  
		$vote_poll['three'] = $vote_count_three;  
		$vote_poll['four'] =  $vote_count_four;
		$vote_poll['total_vote_count'] =  $total_vote_count;
		$vote_poll['is_voted_one'] = 0 ;
		$vote_poll['is_voted_two'] = 0 ;
		$vote_poll['is_voted_three'] = 0 ;
		$vote_poll['is_voted_four'] = 0 ;
		if(!empty($check_is_vote)){	
			if($check_is_vote->v_option == 1){
				$vote_poll['is_voted_one'] =  1; 
			}elseif($check_is_vote->v_option == 2){
				$vote_poll['is_voted_two'] =  1 ;
			}elseif($check_is_vote->v_option == 3){
				$vote_poll['is_voted_three'] =  1 ;
			}elseif($check_is_vote->v_option == 4){
				$vote_poll['is_voted_four'] =  1 ;
			}else{
				$vote_poll['is_voted_one'] = 0 ;
				$vote_poll['is_voted_two'] = 0 ;
				$vote_poll['is_voted_three'] = 0 ;
				$vote_poll['is_voted_four'] = 0 ;
			}
		}

		if($vote_count_one != 0){
			$vote_poll['one_per'] =  $vote_count_one/$total_vote_count*100;  
		}else{
			$vote_poll['one_per'] =  0;  
		}


		if($vote_count_two != 0){
			$vote_poll['two_per'] =  $vote_count_two/$total_vote_count*100;   
		}else{
			$vote_poll['two_per'] =  0;  
		}

		if($vote_count_three != 0){
			$vote_poll['three_per'] =  $vote_count_three/$total_vote_count*100;   
		}else{
			$vote_poll['three_per'] =  0;  
		}

		if($vote_count_four != 0){
			$vote_poll['four_per'] =  $vote_count_four/$total_vote_count*100;   
		}else{
			$vote_poll['four_per'] =  0;  
		}
		//print_r($vote_poll); exit;
	
        return $vote_poll;
	}

	// Total follower count on user
	public function follower_count($userid){
		$follower_count = DB::table('follows')
		    ->where('user_id','=',@$userid)
		    ->count();
        return $follower_count;
	}

	// Total following count on user
	public function following_count($userid){
		$following_count = DB::table('follows')
		    ->where('follow_by','=',@$userid)
		    ->count();
        return $following_count;
	}

	// Total like count on post
	public function like_count($postid){
		$like_count = DB::table('likes')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $like_count;
	}
	
	// Total favourite count on post
	public function favourite_count($postid){
		$favourite_count = DB::table('favourities')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $favourite_count;
	}
	// Total Comment count on post
	public function comment_count($postid){
		$comment_count = DB::table('comments')
		    ->where('post_id','=',@$postid)
		    ->count();
        return $comment_count;
	}
	// Retweet Cont on post
	public function repost_count($postid){
		$repost_count = DB::table('posts')
            ->where('repost_id','=',@$postid)
            ->count();    
        return $repost_count;
	}
	// Get Own like on post
	public function my_like_count($postid,$user_id){
		$is_my_like = DB::table('likes')
            ->where('post_id','=',$postid)
            ->where('l_user_id','=',$user_id)
            ->count();
        if($is_my_like == 1){

            $mylike  =  true;
        }else{
            $mylike   =  false;

        }
        return $mylike;
	}
	
	// Get Own favourite on post
	public function is_my_favourite($postid,$user_id){
		$is_my_favourite = DB::table('favourities')
                ->where('post_id','=',@$postid)
                ->where('f_user_id','=',$user_id)
                ->count();
            if($is_my_favourite == 1){

                $is_my_favourite  =  true;
            }else{
                $is_my_favourite  =  false;

            }
            return $is_my_favourite;
    }

    // Total Comment/Reply like count
	public function comment_like_count($commentid){
		$comment_like_count = DB::table('comment_likes')
		    ->where('c_id','=',@$commentid)
		    ->count();
        return $comment_like_count;
	}

	// Get own like  on Comment/Reply
	public function my_comment_like_count($commentid,$user_id){
		$my_comment_like_count = DB::table('comment_likes')
            ->where('c_id','=',$commentid)
            ->where('l_user_id','=',$user_id)
            ->count();
        if($my_comment_like_count == 1){

            $mycommentlike  =  true;
        }else{
            $mycommentlike   =  false;

        }
        return $mycommentlike;
	}

    // Get post detail Model
    public function post_response($postid,$result=null){
    	$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', $postid)
					->leftjoin('users','posts.u_id','users.id')
					->first();
		//echo '<pre>';print_r($list); exit;
		    
        
        $like_count  = $this->like_count($postid);
        $favourite_count  = $this->favourite_count($postid);
        //$comment_count  = $this->comment_count($postid);
        //$repost_count  = $this->repost_count($postid);  
        $is_my_like = $this->my_like_count($postid,Auth::user()->id);      
        $is_my_favourite = $this->is_my_favourite($postid,Auth::user()->id);      

		$partner_array['result']            =   $result;
		
		

        $partner_array['post_data']['is_favorited']  =  $is_my_favourite;
        $partner_array['post_data']['is_liked']  =  $is_my_like;
        $partner_array['post_data']['id']            =   @$list['id'] ? $list['id'] : '';
        $partner_array['post_data']['title']            =   @$list['title'] ? $list['title'] : '';
        $partner_array['post_data']['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] : '';
        $partner_array['post_data']['desc']  =   @$list['description'] ? $list['description'] : '';
        $partner_array['post_data']['price']  =   @$list['price'] ? $list['price'] : "0";
        $partner_array['post_data']['discount_price']  =   @$list['discount_price'] ? $list['discount_price'] : "0";
        $partner_array['post_data']['offer']  =   @$list['offer'] ? $list['offer'] : "";
        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : "";
        $partner_array['post_data']['like_count']  =  $like_count;
        $partner_array['post_data']['favourite_count']  =   $favourite_count;
        

        return $partner_array;
    } 





	public function comment_post($data){
		//print_r($data); exit;
		if($data['description'] !=  ''){
			
			$post = Post::where('id','=',$data['post_id'])
				->where('u_id','=',$data['userid'])
				->first();
			
			$comment = new Comment();
			$comment->u_id = @$data['userid'] ? $data['userid']: '';
			$comment->post_id = @$data['post_id'] ? $data['post_id']: '';
			
			if(@$data['c_id']){
				$comment->parent_id = @$data['c_id'] ? $data['c_id']: '';
			}
			
			$comment->description = @$data['description'] ? $data['description']: '';
			$comment->created_at =  date ( 'Y-m-d H:i:s' );
			$comment->updated_at =  date ( 'Y-m-d H:i:s' );
			$comment->save();
			$lastid = $comment->c_id;
			$comment_count= Comment::where('post_id', $data['post_id'])->count();
			$postData 	=	Post::where('id', $data['post_id'])->first();
			$postData->comment_count 	= 	$comment_count ? $comment_count : 0;
			//print_r($postData); exit;
			$postData->save();
			$userData['code'] = 200;
			//$userData['c_id'] = @$lastid;

			$commentvalue = Comment::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','comments.*')
			->where('comments.c_id', $lastid)
			->leftjoin('users','comments.u_id','users.id')
			->first();
			if(!empty($comment)){
				$partner_array['post_data']['comments'] =array();
				
				$userData['id']= $commentvalue['c_id']?$commentvalue['c_id']:0;

				$comment_like_count  = $this->comment_like_count($commentvalue['c_id']);
      
				$userData['userid']=   @$commentvalue['userid'] ? $commentvalue['userid'] : '';
		        $userData['picUrl']  =   @$commentvalue['picUrl'] ? $commentvalue['picUrl'] : '';
		        $userData['user_name']  =   @$commentvalue['username'] ? $commentvalue['username'] : '';
		        $userData['first_name']  =   @$commentvalue['first_name'] ? $commentvalue['first_name'] : '';
		        $userData['last_name']  =   @$commentvalue['last_name'] ? $commentvalue['last_name'] : '';
		        $userData['description']  =   @$commentvalue['description'] ? $commentvalue['description'] : '';
		        $userData['posted_time']  =   @$commentvalue['created_at'] ? $commentvalue['created_at'] : '';
		        $userData['like_count']  =   $comment_like_count;
		       
		        $myowncommenton = $this->my_comment_like_count($commentvalue['c_id'],Auth::user()->id);
		        $userData['is_liked']  =  $myowncommenton;

				
			}

		
	
		}else{

			$userData['code'] = 633;

		}

		return $userData;
	}

	public function report($arg,$userId){
		$checkreport = Report::where('user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(empty($checkreport)){
			$report = new Report();
			$report->user_id = $userId;
			$report->post_id = $arg['post_id'];
			//$report->reported_user = intval($arg['reported_user']);
			$report->report_type = $arg['report_type'];
			$report->report_desc = @$arg['report_desc'];
			//echo '<pre>'; print_r($report); exit;
			$report->save();
			return 1;
		}else{
			return 0;
		}		
	}

	public function repost($data){
		//print_r($data); exit;
		$post_old = Post::where('id','=',@$data['post_id'])
					->first();
		if($post_old['description'] !=  ''){
			
			
			$post = new Post();
			
			
			$post->u_id = @$post_old['u_id'] ? $post_old['u_id']: 0;
			$post->repost_u_id = @$data['userid'] ? $data['userid']: '';
			$post->repost_id = @$post_old['id'] ? $post_old['id']: 0;
			$post->post_type = @$post_old['post_type'] ? $post_old['post_type']: 0;
			$post->post_type = @$post_old['post_type'] ? $post_old['post_type']: 0;
			
			$post->posted_time = date ( 'Y-m-d H:i:s' );
			$post->description = @$data['description'] ? $data['description']: '';
			$post->created_at =  date ( 'Y-m-d H:i:s' );
			$post->updated_at =  date ( 'Y-m-d H:i:s' );
			
			$post->save();
			$lastid = $post->id;

			$partner_array['code'] = 200;
			$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('posts.id', @$data['post_id'])
					->leftjoin('users','posts.u_id','users.id')
					->first();
			//echo '<pre>';print_r($list); exit;

			
			$is_my_favourite = DB::table('favourities')
	            ->where('post_id','=',$list['id'])
	            ->where('f_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_favourite == 1){

	            $partner_array['post_data']['is_favorited']  =  true;
	        }else{
	            $partner_array['post_data']['is_favorited']  =  false;

	        }


	        $is_my_like = DB::table('likes')
	                        ->where('post_id','=',$list['id'])
	            ->where('l_user_id','=',Auth::user()->id)
	            ->count();
	        if($is_my_like == 1){

	            $partner_array['post_data']['is_liked']  =  true;
	        }else{
	            $partner_array['post_data']['is_liked']  =  false;

	        }
	        $partner_array['post_data']['is_reposted']  =  false;
			$partner_array['id']            =   @$list['id'] ? $list['id'] : '';
	        $partner_array['userid']      =   @$list['userid'] ? $list['userid'] : '';
	        $partner_array['picUrl']      =   @$list['picUrl'] ? $list['picUrl'] : '';
	        $partner_array['user_name']  =   @$list['username'] ? $list['username'] : '';
	        $partner_array['first_name']  =   @$list['first_name'] ? $list['first_name'] : '';
	        $partner_array['last_name']  =   @$list['last_name'] ? $list['last_name'] : '';
	        $partner_array['is_verified']  =   @$list['is_verified'] ? $list['is_verified'] : '';
	       // $partner_array['tags']  =   @$list['tags'] ? $list['tags'] : '';
	        $partner_array['user_type']  =   @$list['user_type'] ? $list['user_type'] : '';
	        $partner_array['post_type']  =   @$list['post_type'] ? $list['post_type'] : '';
	        
	        $partner_array['post_data']['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] : '';
	        $partner_array['post_data']['description']  =   @$list['description'] ? $list['description'] : 0;
	        $partner_array['post_data']['like_count']  =   @$list['like_count'] ? $list['like_count'] : 0;
	        $partner_array['post_data']['favourite_count']  =   @$list['favourite_count'] ? $list['favourite_count'] : 0;
	        $partner_array['post_data']['comment_count']  =   @$list['comment_count'] ? $list['comment_count'] : 0;

	        $partner_array['post_data']['share_count']  =   @$list['share_count'] ? $list['share_count'] : 0;
	        $partner_array['post_data']['retweet_count']  =   @$list['retweet_count'] ? $list['retweet_count'] : 0;
	        $partner_array['post_data']['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;
	        $partner_array['post_data']['stock_name']  =   @$list['stock_name'] ? $list['stock_name'] : '';
	        $partner_array['post_data']['stock_target_price']  =   @$list['stock_target_price'] ? $list['stock_target_price'] : '';
	        $partner_array['post_data']['time_left']  =   @$list['time_left'] ? $list['time_left'] : '';
	        $partner_array['post_data']['term']  =   @$list['term'] ? $list['term'] : '';
	        $partner_array['post_data']['result']  =   @$list['result'] ? $list['result'] : '';
	        $partner_array['post_data']['trend']   =  @$list['trend'] ? $list['trend'] : 0;
	        $partner_array['post_data']['recommendation']   =  @$list['recommendation'] ? $list['recommendation'] : 0;

	        $partner_array['post_data']['total_votes']  =   @$list['total_votes'] ? $list['total_votes'] : 0;
	        
	        if(!empty($list['poll_one'])){
	            $partner_array['post_data']['options'][0]['id']  =   1;
	            $partner_array['post_data']['options'][0]['title']  =   @$list['poll_one'] ? $list['poll_one'] : '';
	            $partner_array['post_data']['options'][0]['percentage']  =   0;
	            $partner_array['post_data']['options'][0]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_two'])){
	            $partner_array['post_data']['options'][1]['id']  =   2;
	            $partner_array['post_data']['options'][1]['title']  =   @$list['poll_two'] ? $list['poll_two'] : '';
	            $partner_array['post_data']['options'][1]['percentage']  =  0;
	            $partner_array['post_data']['options'][1]['is_voted']  =   0;
	        }
	        if(!empty($list['poll_three'])){
	            $partner_array['post_data']['options'][2]['id']  =   3;
	            $partner_array['post_data']['options'][2]['title']  =   @$list['poll_three'] ? $list['poll_three'] : '';
	            $partner_array['post_data']['options'][2]['percentage']  =  0;
	            $partner_array['post_data']['options'][2]['is_voted']  =  0;
	        }
	        if(!empty($list['poll_four'])){
	            $partner_array['post_data']['options'][3]['id']  =   4;
	            $partner_array['post_data']['options'][3]['title']  =   @$list['poll_four'] ? $list['poll_four'] : '';
	            $partner_array['post_data']['options'][3]['percentage']  =  0;
	            $partner_array['post_data']['options'][3]['is_voted']  =  0;
	            
	        }
			/*$userData['code'] = 200;
			$userData['p_id'] = @$lastid;
			$userData['imgUrl'] = @$post_old->imgUrl;
			$userData['post_type'] = @$post_old->post_type;
			if(@$post_old['post_type'] == 3){
				$userData['poll_one'] = @$post_old->poll_one;
				$userData['poll_two'] = @$post_old->poll_two;
				if(@$post_old->poll_three != ''){
					$userData['poll_three'] = @$post_old->poll_three;
				}
				if(@@$post_old->poll_four != ''){
					$userData['poll_four'] = @$post_old->poll_four;
				}
			}
			$userData['created_at'] = @$post->created_at;
			$userData['updated_at'] = @$post->updated_at;
			$userData['u_id'] = @$post->u_id;*/
	

		}

		return $partner_array;
	}


	public function view_gallery($data){

		$getphotolist =  Photo::where('p_u_id',$data['p_u_id'])->get();	
		
		$PhotoData = array();
		$PhotoArr = array();
		foreach($getphotolist as $list){

			$PhotoData['p_id'] 		=  @$list->p_id ? $list->p_id : '';
			$PhotoData['p_u_id'] 	=  @$list->p_u_id ? $list->p_u_id : '';
			$PhotoData['p_photo'] 	=  @$list->p_photo? URL('/public/images/'.$list->p_photo): '';
			$PhotoData['is_default'] 	=  @$list->is_default ? $list->is_default : '';
			array_push($PhotoArr,$PhotoData);
			
		}



		return $PhotoArr;
	}

	public function delete_gallery($data){

		$getphotolist =  Photo::where('p_id',$data['p_id'])->delete();	
		return 1;
	}

	public function delete_match($data){
		$getmatch = PendingMatches::where('id','=',$data['id'])
					->first();
		if(!empty($getmatch)){
			$getothermatch = PendingMatches::where('reciver_id','=',$getmatch['sender_id'])
					->where('sender_id','=',$getmatch['reciver_id'])
					->first();
			
			//$deleteMymatch =  PendingMatches::where('id',$getmatch['id'])->delete();
			PendingMatches::where('id', $getmatch['id'])
	       		->update([
	           'is_deleted' => 1
        	]);	
	       	PendingMatches::where('id', $getothermatch['id'])
	       		->update([
	           'is_deleted' => 1
        	]);		
			//$deleteOthermatch =  PendingMatches::where('id',$getothermatch['id'])->delete();
			return 1;
		}else{
			return 0;
		}
	}

	public function get_user_list($data){

		$getpatient = User::where('current_physican_id','=',$data['Id'])
						->where('user_type','=',2)->where('user_status','=',1)->get();

		$patient = array();
		$Patient_list = array();

		foreach($getpatient as $list){


			$patient['id'] 				=  	@$list->id ? $list->id : '';
			$patient['name'] 			=  	@$list->name ? $list->name : '';
			/*$patient['email'] 			=  	@$list->email ? $list->email : '';
			$patient['country_code'] 	= 	@$list->country_code ? $list->country_code : '';
			$patient['phone'] 			= 	@$list->phone ? $list->phone : '';
			$patient['photo'] 			=  	@$list->photo ? $list->photo : '';
			$patient['address'] 		=  	@$list->address ? $list->address : '';
			$patient['zip'] 			=  	@$list->zip ? $list->zip : '';
			$patient['gender'] 			=  	@$list->gender ? $list->gender : '';
			$patient['phone'] 			=  	@$list->phone ? $list->phone : '';*/
			
			array_push($Patient_list,$patient);
			
		}

		return $Patient_list;
	}

	public function update_forgot_code($userId,$code){
		
		$user = User::find($userId);
		$user->reset_key = $code;
		$user->save();
		return $user;
	}

	public function update_activation($userId){
		
		$user = User::find($userId);
		$user->activation_code = "";
		$user->user_status = 1;
		$user->is_email_verified = 1;

		$user->save();
		$sender_name = $user['first_name'];
		$message =  $sender_name." your email account has been activated.";
		$data['userid'] = $userId;
		$data['name'] = $user['first_name'];
		$data['message'] = $message;
		$data['n_type'] = 1;
		$notify = array ();
		$notify['receiver_id'] = $userId;
		$notify['relData'] = $data;
		$notify['message'] = $message;
		//print_r($notify); exit;
		$test =  $this->sendPushNotification($notify); 
		return $user;
	}

	public function update_password($data){
		//print_r($data); exit;	
		//$user = User::where('reset_key', $data['code'])->where('email', $data['email'])->first();
		$user = User::where('id', $data['id'])->first();
		if($user){
			$forgot_password = 0;
			if($user->password != ''){
				$forgot_password = 1;
			}
			//if($user->reset_key == $data['code']){

				$user->password = hash::make($data['password']);
				$user->user_status = 1;
				$user->activation_code  = '';
				$user->is_phone_verified = 1;

				$user->save();

				$user->is_forgot = $forgot_password; 
			//}
		}
		
		return $user;
	}

	public function category_list($data){

		$category = Categories::where('c_status',0)->paginate(100,['*'],'page_no');
	
		$category_array = array();
		$category_list = array();

		foreach($category as $list){
			$category_array['c_id'] 			=  	@$list->c_id ? $list->c_id : '';
			$category_array['c_name'] 	=  	@$list->c_name ? $list->c_name : '';
			
			array_push($category_list,$category_array);
		}

		//echo '<pre>'; print_r($chip); exit;
		
		return $category;
	}




	public function discount_list($data){

		$discount = Discount::where('d_status',1)->paginate(100,['*'],'page_no');
	
		$discount_array = array();
		$discount_list = array();

		foreach($discount as $list){
			$discount_array['d_id'] 			=  	@$list->d_id ? $list->d_id : '';
			$discount_array['d_name'] 	=  	@$list->d_name ? $list->d_name : '';
			
			array_push($discount_list,$discount_array);
		}

		//echo '<pre>'; print_r($chip); exit;
		
		return $discount;
	}
	public function gender_list($data){

		$gender = Gender::where('status',1)->paginate(100,['*'],'page_no');

		$gender_array = array();
		$gender_list = array();

		foreach($gender as $list){
			$gender_array['id'] 			=  	@$list->id ? $list->id : '';
			$gender_array['gender'] 	=  	@$list->gender ? $list->gender : '';
			
			array_push($gender_list,$gender_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $gender;
	}

	public function race_list($data){

		$race = Race::where('status',1)->paginate(100,['*'],'page_no');

		$race_array = array();
		$race_list = array();

		foreach($race as $list){
			$race_array['id'] 			=  	@$list->id ? $list->id : '';
			$race_array['race'] 	=  	@$list->race ? $list->race : '';
			
			array_push($race_list,$race_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $race;
	}

	public function religion_list($data){

		$religion = Religion::where('status',1)->paginate(100,['*'],'page_no');

		$religion_array = array();
		$religion_list = array();

		foreach($religion as $list){
			$religion_array['id'] 			=  	@$list->id ? $list->id : '';
			$religion_array['religion'] 	=  	@$list->religion ? $list->religion : '';
			
			array_push($religion_list,$religion_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $religion;
	}

	public function report_list($data){

		$report = ReportList::paginate(100,['*'],'page_no');

		$report_array = array();
		$report_list = array();

		foreach($report as $list){
			$report_array['id'] 			=  	@$list->id ? $list->id : '';
			$report_array['gender'] 	=  	@$list->gender ? $list->report : '';
			
			array_push($report_list,$report_array);
		}
		
		//echo '<pre>'; print_r($chip); exit;
		
		return $report;
	}

	public function partner_type($data){

		$partner_type = PartnerType::paginate(100,['*'],'page_no');

		
		//echo '<pre>'; print_r($chip); exit;
		
		return $partner_type;
	}

	public function region($data){

		$region = Region::paginate(100,['*'],'page_no');

		
		//echo '<pre>'; print_r($chip); exit;
		
		return $region;
	}


	public function subcategory_list($data){
		$subcategory = SubCategories::where('sc_c_id',$data)->paginate(100,['*'],'page_no');
		$subcategory_array = array();
		$subcategory_list = array();

		foreach($subcategory as $list){
			$subcategory_array['sc_id'] 	=  	@$list->sc_id ? $list->sc_id : '';
			$subcategory_array['sc_name'] 	=  	@$list->sc_name ? $list->sc_name : '';
			
			array_push($subcategory_list,$subcategory_array);
		}
		//echo '<pre>'; print_r($chip); exit;
		
		return $subcategory;
	}

	public function mark_default($arg){
		$photo = Photo::where('p_id', $arg['p_id'])->first();
		if(!empty($photo)){
			/*$photo->p_id = $arg['p_id'];
			$photo->is_default = $arg['is_default'];
			//echo '<pre>'; print_r($photo); exit;
			$photo->save();*/
			Photo::where('p_u_id', $photo['p_u_id'])
	       		->update([
	           'is_default' => 0
        	]);
			Photo::where('p_id', $arg['p_id'])
	       		->update([
	           'is_default' => $arg['is_default']
        	]);
			$userData = Photo::where('p_id', $arg['p_id'])->first();
				$userData['code'] = 200;
			$userData['p_id'] = @$userData->p_id;
			$userData['p_photo'] = @$userData->p_photo? URL('/public/images/'.$userData->p_photo):'';
			$userData['p_u_id'] = @$userData->p_u_id;
			$userData['is_default'] = @$userData->is_default;
		}else{
			$userData['code'] = 431;
			//print_r($userData); exit;
		}
		return $userData;
	}

	


	public function like($arg,$userId){
		$checklike = Like::where('l_user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(empty($checklike)){
			$like = new Like();
			$like->l_user_id = $userId;
			$like->post_id = $arg['post_id'];
			//echo '<pre>'; print_r($like); exit;
			$like->save();
			$result= 1;
		}else{
			$deletelike =  Like::where('l_id',$checklike['l_id'])->delete();	
			$result = 0;
		}		
		
		$like_count= Like::where('post_id', $arg['post_id'])->count();
		
		$postData 	=	Post::where('id', $arg['post_id'])->first();
		$postData->like_count 	= 	$like_count ? $like_count : 0;
		//print_r($postData); exit;
		$postData->save();
		
		$partner_array = $this->post_response($arg['post_id'],$result);
		return $partner_array;
	}

	public function follow($arg,$userId){
		$checkfollow = Follow::where('follow_by', $userId)->where('user_id', $arg['user_id'])->first();
		if(empty($checkfollow)){
			$follow = new Follow();
			$follow->follow_by = $userId;
			$follow->user_id = $arg['user_id'];
			//echo '<pre>'; print_r($like); exit;
			$follow->save();
			$result= 1;
			
		}else{
			$deletefollow =  Follow::where('id',$checkfollow['id'])->delete();	
			$result = 0;
		}		
		$getuser =array();
		$id = $arg['user_id'];

		$getuser  =   $this->getuserById($id);
		$getuser['result'] = $result;
		//$partner_array = $this->post_response($arg['post_id'],$result);
		return $getuser;
	}


	public function comment_like($arg,$userId){
		$checklike = CommentLike::where('l_user_id', $userId)->where('c_id', $arg['c_id'])->first();
		if(empty($checklike)){
			$like = new CommentLike();
			$like->l_user_id = $userId;
			$like->c_id = $arg['c_id'];
			//echo '<pre>'; print_r($like); exit;
			$like->save();
			$result= 1;
		}else{
			$deletelike =  CommentLike::where('c_id',$checklike['c_id'])->delete();	
			$result = 0;
		}		
		
		
		$postData 	=	Comment::where('c_id', $arg['c_id'])->first();
		$partner_array = $this->post_detail($postData['post_id']);
		$partner_array['result'] = $result;
		//echo '<pre>'; print_r($partner_array); exit;
		return $partner_array;
	}

	public function favourite($arg,$userId){
		//echo '<pre>'; print_r(Auth::user()->is_subscribe); exit;
		$doFav  = 1;
		if(Auth::user()->is_subscribe == 0){
			$checkfavcount = favourite::where('f_user_id', $userId)->count();
			$doFav = 0;
			if($checkfavcount < 6){
				$doFav = 1;
			}
		}
		$checklike = favourite::where('f_user_id', $userId)->where('post_id', $arg['post_id'])->first();
		if(!empty($checklike)){
			$doFav = 1;
		}
		if($doFav == 1){	
			
			if(empty($checklike)){
				$favourite = new favourite();
				$favourite->f_user_id = $userId;
				$favourite->post_id = $arg['post_id'];
				//echo '<pre>'; print_r($like); exit;
				$favourite->save();
				$result = 1;
			}else{
				$deletelike =  favourite::where('f_id',$checklike['f_id'])->delete();	
				$result = 0;
			}		
			
		
		}else{
			$result = 2;
		}
		$partner_array = $this->post_response($arg['post_id'],$result);
		return $partner_array;
	}

	public function vote($arg,$userId){
		$checklike = Vote::where('v_user_id', $userId)->where('v_post_id', $arg['v_post_id'])->first();
		if(empty($checklike)){
			$vote = new Vote();
			$vote->v_user_id = $userId;
			$vote->v_post_id = $arg['v_post_id'];
			$vote->v_option = $arg['v_option'];
			//echo '<pre>'; print_r($like); exit;
			$vote->save();
			$result= 1;
		}else{
			$deletelike =  Vote::where('v_user_id', $userId)->where('v_post_id', $arg['v_post_id'])->delete();	
			$result = 0;
		}		
		
		$partner_array = $this->post_response($arg['v_post_id'],$result);
		

		//echo '<pre>'; print_r($partner_array); exit;
		return $partner_array;
	}

	function comma_separated_to_array($string, $separator = ',')
	{
	  //Explode on comma
	  $vals = explode($separator, $string);

	  //Trim whitespace
	  foreach($vals as $key => $val) {
	    $vals[$key] = trim($val);
	  }
	  //Return empty array if no items found
	  //http://php.net/manual/en/function.explode.php#114273
	  return array_diff($vals, array(""));
	}
	public function adds_list($data){
		$model 		= "App\Models\Post";	  
		$post_type = @$data['post_type'];
		$offer = @$data['offer'];
		$category_id = @$data['category_id'];
		$discount_id = @$data['discount_id'];
		$title = @$data['title'];
		$query = $model::query();
		//20,40,50,60
			if(isset($offer)){
				//echo $selected_date ; exit;
				$query =$query->where('offer','=',@$offer);
			}
			

			if(isset($post_type)){
				//echo $selected_date ; exit;
				$query =$query->where('post_type','=',@$post_type);
			}

			if(isset($category_id)){
				@$category_id = $this->comma_separated_to_array($category_id, $separator = ',');
				//echo '<pre>'; print_r($category_id); exit;
				//echo $selected_date ; exit;
				$query =$query->whereIn('posts.category_id' ,@$category_id);
				//$query =$query->whereIn('category_id', Array);
			}
			if(isset($title)){
				//echo $selected_date ; exit;
				
				$query =$query->where('title','LIKE','%'.$title.'%');
			}

			if(isset($discount_id)){ ////20,40,50,60
				//echo $discount_id; exit;
				if($discount_id == 1){
					$query =$query->whereBetween('offer', [20, 100]);
				}elseif($discount_id == 2){

					$query =$query->whereBetween('offer', [40, 100]);
				}elseif($discount_id == 3){

					$query =$query->whereBetween('offer', [50, 100]);
				}elseif($discount_id == 4){
					$query =$query->whereBetween('offer', [60, 100]);

				}else{

				}
			}

				
			$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
					->where('status',1)
					->leftjoin('users','posts.u_id','users.id')
					->orderBy('posts.id', 'DESC')
					->paginate(40,['*'],'page_no');

			$query->total_count = $model::where('status',1)
					->count();
			$partner = $query;
			//echo '<pre>'; print_r($query); exit;
		/*$partner = Partner::where('status','=',1)->paginate(10,['*'],'page_no');
		$partner_array = array();
		$Partner_list = array();*/

		/*foreach($partner as $list){
			$partner_array['id'] 			=  	@$list->id ? $list->id : '';
			$partner_array['name'] 	=  	@$list->name ? $list->name : '';
			$partner_array['desc'] 	=  	@$list->desc ? $list->desc : '';
			$partner_array['photo'] 		=  	@$list->photo ? $list->photo : '';
			$partner_array['status'] 		=  	@$list->status ? $list->status : '';
			
			array_push($Partner_list,$partner_array);
		}*/
		//echo '<pre>'; print_r($partner); exit;
		
		return $partner;
	}


	public function favourite_list($data){
		$model 		= "App\Models\Favourite";	
		$post_type = @$data['post_type'];
		$query = $model::query();
			

			if(isset($partner_type)){
				//echo $selected_date ; exit;
				$query =$query->where('post_type','=',@$post_type);
			}
			$userId = Auth::user()->id;
				
			
            $query = $query->select('favourities.*','posts.*')
            ->where('favourities.f_user_id',$userId)
			->leftjoin('posts','favourities.post_id','posts.id')
			->orderBy('posts.id', 'DESC')
			->paginate(40,['*'],'page_no');




			$query->total_count = $model::where('favourities.f_user_id',$userId)
					->count();



			$partner = $query;
		
		return $partner;
	}


	public function post_detail($data){
			
		$list = Post::select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*')
			->where('posts.id', $data)
			->leftjoin('users','posts.u_id','users.id')
			->first();
		/*if(!empty($list)){

		}*/
			
			$partner_array['id']   =   @$list['id'] ? $list['id'] : '';
			
			$postid =  $data;
	            

	        $like_count  = $this->like_count($postid);
	        $favourite_count  = $this->favourite_count($postid);
	        //$comment_count  = $UserRepostitory->comment_count($postid);
	        //$repost_count  = $UserRepostitory->repost_count($postid);  
	        $is_my_like = $this->my_like_count($postid,Auth::user()->id);      
	        $is_my_favourite = $this->is_my_favourite($postid,Auth::user()->id);      
	        /*if($list['post_type'] == 3){
	            $total_vote_count = $UserRepostitory->total_vote_count($postid); 
	            $vote_count_per = $UserRepostitory->vote_count($postid) ; 
	           // print_r($vote_count_per); exit;
	        }else{
	            $total_vote_count = 0; 
	            $vote_count_per = 0 ; 

	        }*/
	        
	        $partner_array['id']            =   @$list['id'] ? $list['id'] : '';
	        if($list['is_url'] == 1){
            	$partner_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';
            }else{
                $partner_array['imgUrl']  =   @$list['imgUrl'] ? $list['imgUrl'] :'';

            }
	        //$partner_array['imgUrl']  =   @$list['imgUrl'] ? URL('/public/images/'.@$list['imgUrl']) :'';
	        $partner_array['title']  =   @$list['title'] ? $list['title'] : '';
	        $partner_array['website_url']  =   @$list['url'] ? $list['url'] : '';
	        $partner_array['desc']  =   @$list['description'] ? $list['description'] : '';
	        $partner_array['price']  =   @$list['price'] ? $list['price'] : 0;
	        $partner_array['discount_price']  =   @$list['discount_price'] ? $list['discount_price'] : '';
	        $partner_array['offer']  =   @$list['offer'] ? intval($list['offer']).'% off' : '';
	        $partner_array['is_favorited']  =  $is_my_favourite;

	        
	        $partner_array['posted_time']  =   @$list['posted_time'] ? $list['posted_time'] : 0;


	        
		//print_r($comment); exit;
		return $partner_array;
	}

	public function watch_list($data){
		$model 		= "App\Models\Favourite";	
		$post_type = @$data['post_type'];
		$query = $model::query();
			

			
		if(isset($post_type)){
			//echo $selected_date ; exit;
			$query =$query->where('post_type','=',@$post_type);
		}

		
		/*$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','favourities.*')
			->where('users.user_status',1)
			->leftjoin('users','favourities.f_user_id','users.id')
			->orderBy('favourities.f_id', 'DESC')
			->paginate(10,['*'],'page_no');*/
		$userId= Auth::user()->id;
		$query = $query->select('users.id as userid','users.first_name as first_name','users.last_name as last_name','users.username as username','users.photo as picUrl','users.user_status as is_verified','users.user_type as user_type','posts.*', 'favourities.f_id as fav_id','favourities.post_id as fav_post_id','favourities.f_user_id as fav_user_id')
				->where('favourities.f_user_id',$userId)
				->leftjoin('posts','posts.id','favourities.post_id')
				->leftjoin('users','users.id','posts.u_id')
				->orderBy('posts.id', 'DESC')
				->paginate(10,['*'],'page_no');


					

		$query->total_count = $model::where('f_user_id',$userId)
				->count();
		$partner = $query;
				
		
		//echo '<pre>'; print_r($partner); exit;
		
		return $partner;
	}


	public function notificationList($data){
		$model 		= "App\Models\Notification";	
		$post_type = @$data['post_type'];
		$query = $model::query();
		if(isset($post_type)){
			//echo $selected_date ; exit;
			$query =$query->where('post_type','=',@$post_type);
		}
		$userId= Auth::user()->id;
		$query = $query->select('posts.*','notifications.*')
				->where('notifications.n_status','!=',2)
				->where('notifications.n_u_id',$userId)
				->leftjoin('posts','notifications.n_data','posts.id')
				->orderBy('notifications.n_id', 'DESC')
				->paginate(40,['*'],'page_no');

		$query->total_count = $model::where('notifications.n_status','!=',2)
				->count();
		$notification = $query;
		return $notification;
	}


	public function check_username($data,$userId){
		$checkEmail = User::where('username', $data['username'])->first();
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail); exit;
		$userData =array();
		$userData['is_username_available'] = 0;	
		if(!isset($checkEmail['id'])){
			$userData['is_username_available'] = 0;
		}else{
			
	   		$userData['is_username_available'] = 1;
	   	}

		return $userData;
	}


	public function update_device($data,$userId){
		$checkEmail = User::where('id', $userId)
	       		->update([
	           'device_token' => @$data['device_token'] ,'device_type' => @$data['device_type'],
	           'device_id' => @$data['device_id']
        ]);	
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['device_token'] = $data['device_token'];
		
		return $userData;
	}

	public function chat_user_sid_update($sid,$userId){
		$checkEmail = User::where('id', $userId)
	       		->update([
	           'sid' => @$sid 
        ]);	
		////////////
		//print_r($userId); exit;
		//print_r($checkEmail['id']); exit;
		$userData =array();
		$userData['code'] = 200;
		$userData['sid'] = $sid;
		
		return $userData;
	}


		public function sendPushNotification($notify) {

		$data                       = $notify['relData'];
		$receiver_id                = trim($notify['receiver_id']); 
		$message                    = trim($notify['message']);
	    // $badge                      = trim(@$_POST['badge']);
		if (strlen($message) > 189) {
			$message = substr($message, 0, 185);
			$message = $message . '...';
		}else{
			$message = $message;
		}
		//echo $receiver_id; exit;
		$check_user 	=	User::find($receiver_id);
		
		$badge = 1;
		/*$notificationTable = TableRegistry::get('Notifications');
		$badge = $notificationTable
					->find()
					->where(['n_u_id'=> $receiver_id])
					->where(['n_type != 5'])
					->where(['n_status' => 0])
					->count();
		//print_r($badge);
		if($badge == 0){
		}else{
			$badge = $badge+1;
		}*/
		//prd($data);
		//echo '<pre>';print_r($check_user); exit;

		if (empty($receiver_id)) {
			exit;
		}
		if ($check_user['device_type'] == 0) { //ios
			$check_user['device_id'] = trim($check_user['device_id']);
			if($check_user['device_id'] != ''){
				if(!empty($message)){
					//$this->iphone_push($check_user['device_token'], $message,  $data, $badge);
					//echo 'yesy';
					//print_r($data); exit;
					$this->sendApns_P8($check_user['device_token'], $message,  $data, 0);
					//$this->ios_fcm_push($check_user['fcm_token'], $message,  $data, $badge);
				}
			}
			//$this->android_push($check_user['device_id'], $message,  $data, $badge=0);
		}else{ //android
			//dd($check_user);
			if($check_user['device_id'] != ''){
				if(!empty($message)){
					//echo '<br>'.$check_user['device_id'].'<br>';
					$this->android_fcm_push($check_user['device_token'], $message,  $data, $badge);
				}
			}
		}
	   
		//return;
	}

	
	// iphone FCM 
	public function android_fcm_push($id, $message, $relData, $badge){
		
		$url = "https://fcm.googleapis.com/fcm/send";
		$token =  $id; 
		//Client key
		//prd($relData['notification_title']);
		$serverKey = 'AAAAVnOvXP8:APA91bG8xUxwKwmzGQCk5tq8cGfGpoUa1HKptr3v-jJFalvNNC7gOGueQExZJKT_FYMycUUsBzDqFOpOKhzlg1E4Go2f1rhfk6cFm5z3riyvvTpneWxraUjQKZrahoXgbmFb6XtlckIU';
		$title = "Firgun";
 		if(isset($relData['notification_title'])){
			$title = $relData['notification_title'];
		}
		
		$body = $message;
		$msg['data']= array(
		'message' => $message,
		'relData' => $relData,
		'badge' => (int)$badge,
		);

		
		$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => $badge);
		//$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','data'=>$msg);
		$arrayToSend = array('to' => $token, 'priority'=>'high','data'=>$msg );
		$json = json_encode($arrayToSend);
		//print_r($json);exit;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='. $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,

		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		//print_r($response); exit;
		//Close request
		if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);	
	}
	
	public function android_fcm_push_firgun($id, $message, $relData, $badge){
		
		$url = "https://fcm.googleapis.com/fcm/send";
		$token =  $id; 
		//Client key
		//prd($relData['notification_title']);
		$serverKey = 'AAAAVnOvXP8:APA91bG8xUxwKwmzGQCk5tq8cGfGpoUa1HKptr3v-jJFalvNNC7gOGueQExZJKT_FYMycUUsBzDqFOpOKhzlg1E4Go2f1rhfk6cFm5z3riyvvTpneWxraUjQKZrahoXgbmFb6XtlckIU';
		$title = "Firgun";
 		if(isset($relData['notification_title'])){
			$title = $relData['notification_title'];
		}
		
		$body = $message;
		$msg['data']= array(
		'message' => $message,
		'relData' => $relData,
		'badge' => (int)$badge,
		);
		$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => $badge);
		//$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','data'=>$msg);
		$arrayToSend = array('to' => $token, 'priority'=>'high','data'=>$msg );
		$json = json_encode($arrayToSend);
		//print_r($json);exit;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: key='. $serverKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,

		"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		//Send the request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		//print_r($response); exit;
		//Close request
		if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);	
	}
	
	


	public function sendApns_P8($deviceIds,$message,$optionalData,$badge){
        //echo '<pre>'; print_r($deviceIds); exit;
        //$pem_path = app_path().'/AuthKey_RR5BW56AWA.p8';
        $keyfile = app_path().'/AuthKey_L5QUR97285.p8';  # <- Your AuthKey file
        $keyid = 'L5QUR97285';                            # <- Your Key ID
        $teamid = '7F6CSJJ54W';                           # <- Your Team ID (see Developer Portal)
        $bundleid = 'com.firgun.app';               # <- Your Bundle ID
        $url = 'https://api.push.apple.com'; # <- production url, or use 
        //$url = 'https://api.sandbox.push.apple.com'; # <- development url, or use 

 
        //print_r($optionalData) exit;
        $pload = isset($optionalData) ? $optionalData : [];
        
        $payload = array();
        $n_type = $optionalData['n_type'];
        $payload['aps'] = array('noti_type' => $n_type,'alert' => $message, 'badge' => intval(0), 'sound' => 'default','pload'=>$pload, 'n_type' => $n_type  );
        $payload = json_encode($payload);

 		//print_r($payload); exit;

        $key = openssl_pkey_get_private('file://'.$keyfile);

 

        $header = ['alg'=>'ES256','kid'=>$keyid];
        $claims = ['iss'=>$teamid,'iat'=>time()];

 

        // $header_encoded = base64($header);
        // $claims_encoded = base64($claims);
        $header_encoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $claims_encoded = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');

 

        $signature = '';
        openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
        $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

 

        // only needed for PHP prior to 5.5.24
        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }

 

        if(is_array($deviceIds)){
            foreach ($deviceIds as $k => $v) {
                $http2ch = curl_init();
                curl_setopt_array($http2ch, array(
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                    CURLOPT_URL => "$url/3/device/$v",
                    CURLOPT_PORT => 443,
                    CURLOPT_HTTPHEADER => array(
                        "apns-topic: {$bundleid}",
                        "authorization: bearer $jwt"
                    ),
                    CURLOPT_POST => TRUE,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HEADER => 1
                ));

                $result = curl_exec($http2ch);
                //print_r($deviceIds);
                if ($result === FALSE) {
                    echo "Error for given device : ".$v;
                    //$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
                    //throw new Exception("Curl failed: ".curl_error($http2ch));
                }
            }
        }else{
            $http2ch = curl_init();
            curl_setopt_array($http2ch, array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_URL => "$url/3/device/$deviceIds",
                CURLOPT_PORT => 443,
                CURLOPT_HTTPHEADER => array(
                    "apns-topic: {$bundleid}",
                    "authorization: bearer $jwt"
                ),
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HEADER => 1
            ));

 

            $result = curl_exec($http2ch);
            //echo '<pre>'; print_r($result);
            if ($result === FALSE) {
                echo "Error for one device : ".$deviceIds;
                //$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
                //throw new Exception("Curl failed: ".curl_error($http2ch));
            }            
        }        
        return true;            
    }

	//subscriptionsList => It is used for get Subscription plan List
	public function subscriptionsList(){
        $query = Subscription::where('country', 'US')->get();
        if(!empty($query)){
        	//$query =  $query->toArray();
        	$query->code = 200;
        }else{
        	$query->code = 400;
        }
        return $query;
    }

    //pendingSubscriptionPlan =>  It is used for save the purchased plan which is pending
 	public function pendingSubscriptionPlan($arg,$userId)
    { 
    	$data = $arg;
		$u_id =  $userId;
		$itunesReceipt = $data['itunes_receipt'];

        $receiptData = '{"receipt-data":"'.$itunesReceipt.'","password":"0af7f7025dc14b55975efea28578c206"}';

        //$endpoint =  'https://sandbox.itunes.apple.com/verifyReceipt';
        $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
		$query = Transaction::where('user_id','=',$u_id )
        ->leftjoin('subscriptions','transactions.subscription_id','subscriptions.id')
        ->where('payment_status','=',1)
        ->where('expired_at', '>', NOW())
        ->orderBy('expired_at','DESC')
        ->first();
       	//print_r($query); exit;
        
        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $receiptData);

        $errno = curl_errno($ch);

        //print_r($errno); exit;

        if($errno==0){

            $response = curl_exec($ch);

            $receiptInfo = json_decode($response,true);
        	//echo '<pre>'; print_r($receiptInfo); exit;

            if(!empty($receiptInfo)){

                if(isset($receiptInfo['status']) && $receiptInfo['status']==0){

                    $latestReceiptInfo = $receiptInfo['latest_receipt_info'];

                    $latestTransactioninfo = $latestReceiptInfo[count($latestReceiptInfo)-1];

                    //echo'<pre>';print_r($latestTransactioninfo);

                   /* $SubscriptionModel = TableRegistry::get('Subscriptions'); //use Cake\ORM\TableRegistry;

                    $subscriptionData = $SubscriptionModel

                    ->find()

                    ->select(['id','price'])

                    ->where(['itunes_product_id'=>$latestTransactioninfo['product_id']])

                    ->first();  */ 
                    $find_other_user = Transaction::where('user_id','!=',$u_id )
			        ->where('itune_original_transaction_id','=',$latestTransactioninfo['original_transaction_id'])
			        ->first();

	                //print_r($find_other_user); exit;
                    
                    if(empty($find_other_user)){
	                    $transactionData = new Transaction();
						$transactionData->user_id = $u_id;
						$transactionData->subscription_id = 1;
						$transactionData->total_amount 	=  $data['amount'];
						$transactionData->currency 	=  $data['currency'];
						$transactionData->payment_status 	=  1;
						$transactionData->itune_original_transaction_id = $latestTransactioninfo['original_transaction_id'];
						$transactionData->itunes_receipt = $itunesReceipt;
						$transactionData->orderId = $latestTransactioninfo['transaction_id'];
						$transactionData->packageName = $latestTransactioninfo['product_id'];
						$transactionData->productId = $latestTransactioninfo['product_id'];
						$transactionData->purchaseTime =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['purchase_date']));
						$transactionData->purchaseState =  1;
						$transactionData->created_at =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['purchase_date']));
						$transactionData->expired_at =  date('Y-m-d H:i:s',strtotime($latestTransactioninfo['expires_date']));
						$transactionData->device_type = 0;
						$transactionData->purchaseToken = 'Iphone';
						if ($result = $transactionData->save()){
	                        $transaction_last_id = $transactionData->id;
	                      	$user = User::where('id', $u_id)
						       		->update([
						           'itunes_autorenewal' => 1 ,'is_subscribe' => 1,'active_subscription' => 1,
						           'last_transaction_id' => $transaction_last_id
					        ]);	
	                     
	                       	$is_success = 221;
						    //print_r($query); exit;


	                    }else{
	                        $is_success = 423;

	                    }
	                }else{
	                	$is_success = 424;
	                }

                }else{
                	$user = User::where('id', $u_id)
					       		->update([
					           'itunes_autorenewal' => 0 
				        ]);	

                     $is_success = 424;
                }

            }

        }

        return $is_success;
    } 

	//subscriptions -> It is used for get Subscription Type
	public function subscriptions()
	{ 

        if ($this->request->is('get')){

            $data = $this->request->query;

            $uid = $this->userid;

            $SubscriptionsModel = TableRegistry::get('Subscriptions'); //use Cake\ORM\TableRegistry;

            $querySubscriptions = $SubscriptionsModel

            ->find();

            $TransactionsModel = TableRegistry::get('Transactions'); //use Cake\ORM\TableRegistry;

            $query = $TransactionsModel

            ->find()

            ->contain(['Subscriptions','Users'])

            ->where(['user_id'=>$uid])

            ->where(['payment_status'=>'1'])

            ->where(['NOW()<`expired_at`'])

            ->order(['expired_at'=>'DESC'])

             ->first();

            $timestamp = strtotime(date('Y-m-d H:i:s'));

            if(!empty($query)){

                $query =  $query->toArray();

                if(!empty($query['user']['id'])){

                    $addded_date = strtotime($query['user']['added_date']);

                }else{

                    $addded_date ='';

                }

                if($query['device_type']== 0){

                     $this->set([

                    'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>$query['subscription']['name'],'plan_id'=>$query['subscription_id'],'added_date'=>$addded_date,'itune_original_transaction_id'=>$query['itune_original_transaction_id'],'itunes_receipt'=>json_decode($query['itunes_receipt'])),

                    'code' => 209,

                    'msg'=> responseMsg(209),

                    '_serialize' => ['code','data','msg']

                 ]);



                }else{     

                    $this->set([

                        'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>$query['subscription']['name'],'plan_id'=>$query['subscription_id'],'added_date'=>$addded_date,'itune_original_transaction_id'=>$query['itune_original_transaction_id'],),

                        'code' => 209,

                        'msg'=> responseMsg(209),

                        '_serialize' => ['code','data','msg']

                     ]);

                }

            }else{

                $querySubscriptions =  $querySubscriptions->toArray();

                  $this->set([

                    'data' => array('Subscriptions'=>$querySubscriptions,'timestamp' =>$timestamp,'plan_name'=>'','plan_id'=>0,'added_date'=>'','itune_original_transaction_id'=>''),

                    'code' => 209,

                    'msg'=> responseMsg(209),

                    '_serialize' => ['code','data','msg']

                 ]);

            }

        }
    }

	//newSubscriptionPlan => It is used for Add new Subscription Plan (not need)
	public function newSubscriptionPlan()
    { 

        if ($this->request->is('post')){



            $data = $this->request->data;

            //pr($data);

            $u_id = $this->userid;

            $this->loadModel('Transactions');

            $Transactions = TableRegistry::get('Transactions'); 

            $transaction = $this->Transactions->newEntity();

            $transaction = $this->Transactions->patchEntity($transaction, $data);

            $transaction ['user_id'] = $this->userid;

            $created_at = $data['created_at']/1000;

            $transaction ['created_at'] =  date('Y-m-d H:i:s', $created_at);

            $expired_at = $data['expired_at']/1000;

            $transaction ['expired_at'] =date('Y-m-d H:i:s', $expired_at);

            

            //prd($transaction);

            if ($this->Transactions->save($transaction)){

                $this->loadModel('Users');

                $UserModel = TableRegistry::get('Users'); //use Cake\ORM\TableRegistry;

                $user = $UserModel->get($u_id);

                $user->itunes_autorenewal = 0;

                $user->active_subscription = $data['subscription_id'];

                $user->last_transaction_id = $transaction_last_id;

                $UserModel->save($user);

                $this->set([

                    'msg'=> responseMsg(210),

                    'code'  => 200,

                    '_serialize' => ['code','msg']

                ]);

                

            }else{

                 $this->set([

                    'msg'=> responseMsg(418),

                    'code'  => 418,

                    '_serialize' => ['code','msg']

                ]);

            }

        }
    }


	//actionCheckTransactionId => This function is used to check original trasaction id of itunes.
	public function actionCheckTransactionId()
    {   

        $Transactions = TableRegistry::get('Transactions'); 

        if ($this->request->is('post')){

            $data = $this->request->data;

            $userId = $this->userid;

            $itune_original_transaction_id = $data['itune_original_transaction_id'];

            $subscription = $Transactions

            ->find()

            ->where(['itune_original_transaction_id'=> $itune_original_transaction_id])

            ->where(['NOW()>`expired_at`'])

            ->first();  

            if(empty($subscription)){

                 $this->set([

                    'msg'=> responseMsg(210),

                    'data' => '',

                    'code'  => 200,

                    '_serialize' => ['code','msg','data']

                 ]);

            }else{

                $this->set([

                    'msg'=> responseMsg(436),

                    'data' => '',

                    'code'  => 436,

                    '_serialize' => ['code','msg','data']

                 ]);

            }

        }
    }  

	

	///androidSubscreption
	public function androidSubscreption($arg,$userId) {

        $request = $this->request;
        $postData = $arg;
		$u_id =  $userId;
        

       
        $requestStatus = 1;

        if( !isset($postData['orderId']) ) { $requestStatus = 0; }

        if( !isset($postData['productId']) ) { $requestStatus = 0; }

        if( !isset($postData['packageName']) ) { $requestStatus = 0; }

        if( !isset($postData['autoRenewing']) ) { $requestStatus = 0; }

        if( !isset($postData['purchaseToken']) ) { $requestStatus = 0; }

        if( !isset($postData['purchaseTime']) ) { $requestStatus = 0; }

        



        if($requestStatus==1) { 



            $user_id = $this->userid;

            /*$subTable = TableRegistry::get('Subscreption'); 

            $subData = $subTable->find()

                        ->where(['user_id'=>$user_id, 'status'=>1])

                        ->first();*/



            /*if(!empty($subData)) {



                $Result['code'] = '217';

                $Result['message'] = $this->ErrorMessages($Result['code']);

                echo json_encode($Result); exit;



            } else {*/



                require_once app_path().'/GoogleClientApi/Google_Client.php';

                require_once app_path().'/GoogleClientApi/auth/Google_AssertionCredentials.php';



			  $CLIENT_ID = '500178777931-57oe6pro6q5oeq8v6vh184qedbba2meo.apps.googleusercontent.com';
			  //$CLIENT_ID = '500178777931-57oe6pro6q5oeq8v6vh184qedbba2meo.apps.googleusercontent.com';

			                //'110053402852490647256';

			  $SERVICE_ACCOUNT_NAME = 'firgun@pc-api-5637810868213956066-772.iam.gserviceaccount.com';
			            $KEY_FILE = app_path().'/pc-api-5637810868213956066-772-8f2f5101f579.p12';

			            $KEY_PW   = 'notasecret';



            $key = file_get_contents($KEY_FILE);

            $client = new \Google_Client();

            $client->setApplicationName("firgun");



                $cred = new \Google_AssertionCredentials(

                            $SERVICE_ACCOUNT_NAME,

                            array('https://www.googleapis.com/auth/androidpublisher'),

                            $key);  



                $client->setAssertionCredentials($cred);

                $client->setClientId($CLIENT_ID);

               

                if ($client->getAuth()->isAccessTokenExpired()) {

                    try {

                        $client->getAuth()->refreshTokenWithAssertion($cred);

                    } catch (Exception $e) {

                    }

                }

                $token = json_decode($client->getAccessToken());
                //print_r($token); exit;
                    

                $expireTime = "";

                $amount = 0;

                if( isset($token->access_token) && !empty($token->access_token) ) {

                    $appid = $postData['packageName'];

                    $productID = $postData['productId'];

                    $purchaseToken = $postData['purchaseToken'];



                    $ch = curl_init();

                    $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/";

                    $VALIDATE_URL .= $appid."/purchases/subscriptions/".$productID."/tokens/".$purchaseToken;

                    $res = $token->access_token;
                    //print_r($res); exit;



                    $ch = curl_init();

                    curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$res);

                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

                    $result = curl_exec($ch);

                    $result = json_decode($result, true);

                    //print_r($result); exit;

                    

                    if(isset($result["startTimeMillis"])) {

                        $startTime = date('Y-m-d H:i:s', $result["startTimeMillis"]/1000. - date("Z"));

                        //$amount = $result["priceAmountMicros"]/1000000;

                    }

                    if(isset($result["expiryTimeMillis"])) {

                        $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));

                        $amount = $result["priceAmountMicros"]/1000000;

                    }

                }

                if(!empty($result)){
	                $date = new \DateTime();

	                $date->setTimestamp($postData['purchaseTime']/1000);

	                $dateStart = $date->format('Y-m-d H:i:s');

	                $transactionData = new Transaction();
					$transactionData->user_id = $u_id;
					$transactionData->subscription_id = 1;
					$transactionData->total_amount 	= $postData['amount'];
					$transactionData->currency 	= $postData['currency'];
					$transactionData->payment_status 	=  1;
					$transactionData->itune_original_transaction_id = $postData['orderId'];
					$transactionData->itunes_receipt = $result["orderId"];
					$transactionData->orderId = $result["orderId"];
					$transactionData->packageName = $postData['packageName'];
					$transactionData->productId = $productID;
					$transactionData->purchaseState =  1;//@$postData['purchaseState'];
					$transactionData->created_at =  $dateStart;
					$transactionData->expired_at =  $expireTime;
					$transactionData->device_type = 2;
					$transactionData->purchaseToken = $postData['purchaseToken'];
					if ($result = $transactionData->save()){
	                    $transaction_last_id = $transactionData->id;
	                  	$user = User::where('id', $u_id)
					       		->update([
					           'itunes_autorenewal' => 1 ,'is_subscribe' => 1,'active_subscription' => 1,
					           'last_transaction_id' => $transaction_last_id
				        ]);	
	                 
	                   	$is_success = 221;
					    //print_r($query); exit;


	                }else{
	                    $is_success = 423;

	                }
	            }else{
	            	$is_success = 429;
	            }

        } else {

             $is_success = 424;

        }
        return $is_success;
        
    }

    /*public function cronJobForaddList(){
    	//$model 		= "App\Models\Post";	


    	$getpatient = Post::where('current_physican_id','=',$data['Id'])
						->where('user_type','=',2)->where('user_status','=',1)->get();
    }*/

	//cronJobForSubscreption 
	public function cronJobForSubscreption() { //use for  cron
   

        $Result['code'] = '200';

        $request = $this->request;

        $requestStatus = 1;

        if($requestStatus==1) { 

             $currentDate = date('Y-m-d H:i:s');

            //$transactionsTable = TableRegistry::get('Transactions');

           /* $subData = $transactionsTable->find()

                        ->where(['expired_at < '=>$currentDate])

                        ->ToArray();*/
            $subData = Transaction::where('expired_at', '<', $currentDate)
	        ->get();
	        echo $currentDate;
	        //echo '<pre>'; print_r($subData); 
            if(!empty($subData) && count($subData)) {

                //---- get auth token ---------------

                require_once app_path().'/GoogleClientApi/Google_Client.php';

                require_once app_path().'/GoogleClientApi/auth/Google_AssertionCredentials.php';

                $CLIENT_ID = '100377813809460893738';

                    //'110053402852490647256';

                $SERVICE_ACCOUNT_NAME = 'h-subscriptions@h.h.gserviceaccount.com';
                $KEY_FILE = app_path().'/GoogleClientApi/h-39e53e5c539b.p12';

                $KEY_PW   = 'notasecret';



                $key = file_get_contents($KEY_FILE);

                $client = new \Google_Client();

                $client->setApplicationName("hopple");


                $cred = new \Google_AssertionCredentials(

                            $SERVICE_ACCOUNT_NAME,

                            array('https://www.googleapis.com/auth/androidpublisher'),

                            $key);  



                $client->setAssertionCredentials($cred);

                $client->setClientId($CLIENT_ID);

                

                if ($client->getAuth()->isAccessTokenExpired()) {

                    try {

                        $client->getAuth()->refreshTokenWithAssertion($cred);

                    } catch (Exception $e) {

                    }

                }

                $token = json_decode($client->getAccessToken());





                //---- cron job work  ---------------------



                foreach ($subData as $key => $val) {

                    if( $val->device_type==2 ) {  // android
	                	

                        $expireTime = "";

                        $amount = 0;

                        if( isset($token->access_token) && !empty($token->access_token) ) {

                            $appid = $val->packageName;

                            $productID = $val->productId;

                            $purchaseToken = $val->purchaseToken;



                            $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/";

                            $VALIDATE_URL .= $appid."/purchases/subscriptions/".$productID."/tokens/".$purchaseToken;

                            $res = $token->access_token;



                            $ch = curl_init();

                            curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$res);

                            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

                            $result = curl_exec($ch);

                            $result = json_decode($result, true);

	                        if(isset($result["expiryTimeMillis"])) {
	                        	echo '<pre>'; print_r($result);

                                $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));
                                echo  $expireTime;
                                $amount = $result["priceAmountMicros"]/1000000;

                            	echo 'SUNIL'.$val->user_id; 

                                if($expireTime > date('Y-m-d H:i:s')) {
                                	echo 'Renew Test Sunil';
                                   /* Transaction::where('id',  $val->user_id)
							       		->update([
							           'expired_at' => $expireTime,
							           'payment_status' => 1
						        	]);	*/

                                    User::where('id',  $val->user_id)
                                    	->where('is_subscribe',0)
							       		->update([
							           'is_subscribe' => 1
						        	]);	

                                 

                                } else {

                                    echo 'Expire Test Sunil Aadroid';
                                    /*Transaction::where('id',  $val->user_id)
							       		->update([
							           'payment_status' => 2
						        	]);	*/
        

                                            
							       	User::where('id',  $val->user_id)
                                    	->where('is_subscribe',1)
							       		->update([
							           'is_subscribe' => 0
						        	]);	


                                    
                                } 



                            }

                        }

                    } else if( $val->device_type==1 ) {   // iphone

                        $itunesReceipt = $val->purchase_token;  

                        //$password = "58c72878cd56401a9c71927679fd9ee5";        

                        $password = "51197df0c08744ca903b0dcc0f0a259a";        

                        $receiptData = '{"receipt-data":"'.$itunesReceipt.'","password":"'. $password .'"}';

                        //$endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';

                         $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';    



                        $ch = curl_init($endpoint);

                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        curl_setopt($ch, CURLOPT_POST, true);

                        curl_setopt($ch, CURLOPT_POSTFIELDS, $receiptData);

                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                        $response = curl_exec($ch);

                        $errno = curl_errno($ch);



                        if($errno==0) {



                            $receiptInfo = json_decode($response,true);

                            

                            if( isset($receiptInfo['latest_receipt_info']) && !empty($receiptInfo['latest_receipt_info']) ) {



                                $lastData = end($receiptInfo['latest_receipt_info']);

                                

                                $expireTime = date('Y-m-d H:i:s',strtotime($lastData['expires_date']));



                                if($expireTime > date('Y-m-d H:i:s')) {
                                	echo '<pre>'; print_r($receiptInfo);
                                    echo 'SUNIL'.$val->user_id;

                                    $query = $transactionsTable->query();

                                    $result = $query->update()

                                            ->set(['expired_at' => $expireTime , 'status' => 1])

                                            ->where(['id' => $val->user_id])

                                            ->execute();

                                       User::where('id',  $val->user_id)
                                    	->where('is_subscribe',0)
							       		->update([
							           'is_subscribe' => 1,
							           'active_subscription' => 1
						        	]);	     

                                   /* $salonQuery = $userTable->query();

                                    $salonQuery->update()

                                                    ->set(['active_subscription' => 1])

                                                    ->where(['id' => $val->user_id, 'active_subscription' => 0])

                                                    ->execute();*/

                                } else {

                                    $query = $transactionsTable->query();

                                    /*$result = $query->update()

                                            ->set(['payment_status' => 2])

                                            ->where(['id' => $val->id])

                                            ->execute();*/

                                    User::where('id',  $val->user_id)
                                    	->where('is_subscribe',1)
							       		->update([
							           'is_subscribe' => 0,
							           'active_subscription' => 0
						        	]);	

                                      echo 'Expire Test Sunil IOS';
                                    $salonQuery = $userTable->query();


                                } 
                            }
                        }       
                    }
                }

            } 
        }   

        exit;   
    }

	public function checkchip($data){
		$checkunique = Chip::where('unique_id', $data['unique_id'])->where('u_id', $data['id'])->first();
		if(!empty($checkunique)){
			$rescod = 1;
		
    	}else{

        	$rescod = 0;

    	}
		return $rescod;
	}


	public function chip($data){

		$chip = new Chip();
		$chip->chip_name = $data['chip_name'];
		$chip->unique_id = $data['unique_id'];
		$chip->u_id 	=  $data['id'];
		$chip->save();
		
		return $chip;
	}

	public function chip_data_list($data,$arg){
		//$checkunique = ChipData::where('unique_id', $data['unique_id'])->where('u_id', $arg['id'])->first();
		$chip = ChipData::where('unique_id',$data['unique_id'])->paginate(10,['*'],'page_no');
		//print_r($chip); exit;
		//$chip = ChipData::where('unique_id',$data['unique_id'])->paginate(20,['*'],'page_no');
		$chip_array = array();
		$Chip_list = array();

		foreach($chip as $list){
			$chip_array['c_id'] 			=  	@$list->c_id ? $list->c_id : '';
			$chip_array['u_id'] 			=  	@$list->u_id ? $list->u_id : '';
			$chip_array['unique_id'] 		=  	@$list->unique_id ? $list->unique_id : '';
			$chip_array['data_date_time'] 	=  	@$list->data_date_time ? $list->data_date_time : '';
			$chip_array['cycle_count'] 		=  	@$list->cycle_count ? $list->cycle_count : '';
			$chip_array['status'] 			=  	@$list->status ? $list->status : '';
			
			array_push($Chip_list,$chip_array);
		}
		//echo '<pre>'; print_r($chip); exit;
		
		return $chip;
	}

	public function getquestion($data){
		$user 	=	User::find($data);
		//$photo = Photo::where('p_u_id', $user->id)->where('is_default', 1)->first();
		$getquestionlist =  Faq::where('f_status',1)->get();	
		//print_r($getquestionlist); exit;
		$QuestionData = array();
		$QuestionArr = array();
		foreach($getquestionlist as $list){
			$QuestionData['id'] 		=  @$list->id ? $list->id : '';
			$QuestionData['question'] 	=  @$list->question ? $list->question : '';
			$QuestionData['admin_answer_id'] 	=  @$list->admin_answer_id ? $list->admin_answer_id : 0;
			$is_answer = Answer::where('u_id', $user->id)->where('q_id', $list->id)->first();
			//print_r($is_answer); exit;
			if(!empty($is_answer['id'])){
				$QuestionData['ans_id'] 	=   $is_answer['id'];
				$QuestionData['admin_answer_id'] 	= $is_answer['admin_answer_id'];
				$QuestionData['answer'] 	=   $is_answer['answer'];
			}else{
				$QuestionData['answer'] 	=   '';
			}
			$question_option =  Answer::where('q_id', $list->id)->where('u_id', 1)->get();
			//print_r($question_option); exit;
			if(!empty($question_option)){
				$QuestionData['option'] = array();
				foreach($question_option as $key =>$question_optionlist){
					$QuestionData['option'][$key]['ans_id'] 		=  @$question_optionlist->id ? $question_optionlist->id : '';
					$QuestionData['option'][$key]['admin_answer_id'] 		=  @$question_optionlist->admin_answer_id ? $question_optionlist->admin_answer_id : '';
					$QuestionData['option'][$key]['answer'] 		=  @$question_optionlist->answer ? $question_optionlist->answer : '';
					//$QuestionData['answer'][$key]['answer'] 	=  @$answer_option->answer ? $answer_option->answer : '';
				}
			}
			array_push($QuestionArr,$QuestionData);
			
		}
       	
		return $QuestionArr;
	}


	public function answer($arg,$userId){
		$checkreport = Answer::where('u_id', $userId)->where('q_id', $arg['q_id'])->first();
		if(!empty($checkreport)){
			$deleteanswer =  Answer::where('id',$checkreport['id'])->delete();	
		}
		//print_r($checkreport['id']); exit;	
		$answer = new Answer();
		$answer->u_id = $userId;
		$answer->answer = $arg['answer'];
		$answer->admin_answer_id = $arg['admin_answer_id'];
		$answer->q_id = intval($arg['q_id']);
		$answer->status = 1;
		$answer->save();
		return 1;
	}




	public function answer_delete($data){
		$deleteanswer =  Answer::where('id',$data['id'])->delete();	
		return 1;
	}
	
	public function notification_match_detail($arg,$user_id){
		$modal     =  "App\Models\PendingMatches";
		$query = $modal::query();

		$user =$query->select('customer.*','pending_matches.*')
				->leftjoin('users as customer','pending_matches.reciver_id','customer.id')
				->where('pending_matches.sender_id','=',@$user_id)
				->where('pending_matches.chat_channel','=',$arg)
				->orderBy('pending_matches.id', 'DESC')->first();
			////////////

			$userData =array();	
			//$userData['myMatch'] = array();
				$userData['isFromSubCategory'] = 0;
			if(isset($user['id'])){
				if($user['is_pending'] == 1){
					$userData['isFromSubCategory'] = 1;
				}
				//	print_r($user); exit;
				$userData['id'] = $user['id'];
				$category = Categories::where('c_id',$user['cat_id'])->first();
				$subcategory = SubCategories::where('sc_id',$user['sub_cat_id'])->first();
				//print_r($subcategory); exit; 
				$photo = Photo::where('p_u_id',  $user['reciver_id'])->where('is_default', 1)->first();
				$userData['c_id'] = @$category['c_id']?$category['c_id']:0;
				$userData['c_name'] = @$category['c_name']?$category['c_name']:'';
				$userData['sc_c_id'] = @$subcategory['sc_id']?$subcategory['sc_id']:0;
				$userData['sc_name'] = @$subcategory['sc_name']?$subcategory['sc_name']:'';
				if(isset($user['phone'])){
					$userData['p_photo'] = @$photo->p_photo? URL('/public/images/'.$photo->p_photo):'';
					$userData['p_id'] = @$photo->p_id? $photo->p_id:0;
			        $userData['first_name'] = $user['first_name'] ? $user['first_name'] : '';
			        $userData['age'] 	= 	$user['age'].' Years';
					$userData['race'] 	= 	$user['race'] ? $user['race'] : 0;

					$userData['occupation_status'] = 	$user['occupation_status'] ? $user['occupation_status'] : 1;
					$userData['occupation'] = 	$user['occupation'] ? $user['occupation'] : '';
					$userData['descr'] = 	$user['description'] ? $user['description'] : '';
					$userData['is_pending'] = 0;
					$userData['sender_id'] = 	$user['sender_id'] ? $user['sender_id'] :'';
					$userData['reciver_id'] = 	$user['reciver_id'] ? $user['reciver_id'] :'';
					$userData['chat_channel'] = 	$user['chat_channel'] ? $user['chat_channel'] :'';
				}else{
					$userData['is_pending'] = 1;

				}
				
			}
			return $userData;
	}

	public function logout($data){

		$rescod = "";
		//print_r($data); exit;
		if ($data) {
        
			$user =  User::findorfail($data);
			$user->device_id = "";
			$user->device_token = "";
			$user->device_type = 2;
			$user->save();

			$user = Auth::user()->token();
        	//$user->revoke();
        	$rescod = 642;

    	}else{

        	$rescod = 461;

    	}
		return $rescod;
	}

	public function deleteAccount($data){
		


		$deletepost =  Post::where('u_id',$data['userid'])
		->delete();	


		


		$deletefav =  Favourite::where('f_user_id',$data['userid'])
		->delete();	



		$deletelike =  Like::where('l_user_id',$data['userid'])
		->delete();	

		$deletephoto =  Photo::where('p_u_id',$data['userid'])
		->delete();	


		$deletereport =  Report::where('user_id',$data['userid'])
		->delete();	

		$deletereported =  Report::where('reported_user',$data['userid'])
		->delete();




		$deleteuser =  User::where('id',$data['userid'])
		->delete();
	
		return 1;
	}

	public function userNotify($arg,$userId){
		//print_r($userId); exit;
		$model     =  "App\Models\Post";	
		$sender_name = 'Admin';
		$getpostList =  Post::select('id','category_id','title')->where('is_notify',0)->get()->toArray();
		
		foreach($getpostList as $getpostListkey => $getpostListval) {
			//echo '<pre>'; print_r($getpostListval['id']); exit;
			//$query =$query->whereIn('posts.category_id' ,@$category_id);
			$userList =  User::select('id','username','device_id','device_type','device_token')
				//->whereIn('category_id' ,$getpostListval['category_id'])
				//->whereRaw(FIND_IN_SET($getpostListval['category_id'], category_id))
				->whereRaw('FIND_IN_SET("'.$getpostListval['category_id'].'",category_id)')
				->where('is_notify',1)
				->where('user_status',1)
				->where('id','!=',1)
				->get()
				->toArray();
			foreach($userList as $userListkey => $userListval) {
				//echo '<pre>'; print_r($userListval); exit;
				$receiver_name = $userListval['username'];
				$fcm_token = $userListval['device_token'];

				//$user = User::find($userId);

				//$receiver_detail = User::find($arg['receiver_id']);
				$message =  $getpostListval['title']." is available at a deal price.";
				$data['userid'] = 1;
				$data['name'] = 'Admin';
				$data['message'] = $message;
				$data['a_id'] = $getpostListval['id'];
				$data['n_type'] = 1;
				$notify = array ();
				$notify['a_id'] = $getpostListval['id'];
				$notify['receiver_id'] = $userListval['id'];
				$notify['relData'] = $data;
				$notify['message'] = $message;
				//print_r($notify); exit;
				$test =  $this->sendPushNotification($notify); 
				$n_type = 1;
				$this->notification_save($userListval['id'],$getpostListval['id'],$message,$sender_name,$n_type,$receiver_name,$fcm_token);
				

			}
			Post::where('id', $getpostListval['id'])
		       		->update([
		           'is_notify' => 1
        		]);	
		}
			
		
		//return 1;
	}
	// Save Notification
	public function notification_save($receiver_id,$notify,$message,$sender_name,$n_type,$receiver_name,$device_token){
		$notification = new Notification();
		$notification->n_u_id = @$receiver_id;
		$notification->n_sender_id = 1;
		$notification->n_type = $n_type;
		$notification->n_data = json_encode($notify);
		$notification->n_message = $message;
		$notification->n_name = $sender_name;
		$notification->n_receiver_name = $receiver_name;
		$notification->n_fcm_token = $device_token;
		$notification->n_status  = 0;
		$notification->n_added_date  =  date ( 'Y-m-d H:i:s' );
		$notification->n_update_date  =  date ( 'Y-m-d H:i:s' );
		$notification->save();
	}

	public function notification_master($sender,$userArr,$message,$n_type,$ref_id,$push_type){
		$user = User::find($sender); //notification sender
		if($userArr != Auth::user()->id){
			$receiver_detail = User::find($userArr);// Notification Recceiver
			$receiver_name = @$receiver_detail['first_name'];
			$device_token = @$receiver_detail['device_token'];
			// Notification Payload
			$data['userid'] = $sender;
			$data['name'] = $user['first_name'];
			$data['message'] = $message;
			$data['n_type'] = $n_type;
			if($n_type == 29){
				$data['ref_id'] = $ref_id['g_id'];
				$data['room_id'] = $ref_id['room_id'];
				$data['notification_title'] = $ref_id['notification_title'];
				$message;
			}else{
				$data['notification_title'] = 'Social Trade';
				$message = $user['first_name'].' '.$message;
				$data['ref_id'] = $ref_id;
			}

			$notify = array ();
			$notify['receiver_id'] = $userArr;
			$notify['relData'] = $data;
			$notify['message'] = $message;
			//print_r($notify); exit;
			$test =  $this->sendPushNotification($notify); 

			if($n_type != 29){
				$this->notification_save($userArr,$notify,$message,$user['first_name'],$n_type,$receiver_name,$device_token);
			}
		}
	}


	public function guestLogin($data){
		if(@$data['guest_id']){
			$code =  $data['guest_id'];
		}



		$CustomVerfication = new CustomVerfication();
		$SendEmail = new SendEmails();
		$rescod  = "";
		
		if(!isset($data['id'])){
			$create_user = new User();
			$follower_count  = 0;
        	$following_count  = 0;
		}else{
			$create_user = User::find($data['id']);
			$follower_count  = 0;
        	$following_count  = 0;
		}
		//$create_user->email 	= @$data['email'] ? $data['email']: '';
		//$create_user->password 	= hash::make(@$data['password']) ? hash::make(@$data['password']): '';


		$create_user->first_name = 'Guest';
		$create_user->last_name = 'Guest';
		$create_user->guest_id  = $data['guest_id'];
		$create_user->phone = '';
		$create_user->added_date = date ( 'Y-m-d H:i:s' );
		$create_user->user_type = 2 ;
		$create_user->user_status = '1';
		$create_user->is_approved = '1';
		$create_user->user_status = '1';
		$create_user->activation_code = $code;
		$create_user->is_email_verified = '0';
		$create_user->is_phone_verified = '0';
        $create_user->last_login= date ( 'Y-m-d H:i:s' );
        $create_user->token_id = mt_rand(); 
		$create_user->created_at = date ( 'Y-m-d H:i:s' );
		$create_user->updated_at = date ( 'Y-m-d H:i:s' );
		$create_user->save();
		//print_r($create_user); exit;
		$userid = $create_user->id;  
		
		
        return $userid;
	}

} 

