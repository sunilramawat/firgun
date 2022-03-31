<?php

namespace App\Http\Controllers\admin;

use Route;
use Auth;
use Validator;
use App\User;
use App\Models\Transaction;
use App\Models\Categories;
use App\Models\SubCategories;
use App\Models\Gender;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\CustomVerfication;
use App\Http\Controllers\Utility\SendEmails;
use App\Http\Controllers\Repository\CrudRepository;
use Illuminate\Pagination\Paginator;
use Charts;
use DB;
use Storage;

use DOMDocument;

class TransactionController extends Controller{
		
	public function add(Request $request){
		$CrudRepository = new CrudRepository();
		
		/*$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);
		*/
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);

		//echo '<pre>'; print_r($category); exit;
		/*$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		$model 		= "App\Models\PartnerType";	
		$moduleType =  16; 
		$eventType = $CrudRepository->view($model,$moduleType);*/


		/*$model3		= "App\Models\Region";	
		$moduleType =  18; 
		$region = $CrudRepository->view($model3,$moduleType);*/
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		return view('admin.Post.add',compact('category'));
		//return view('admin.Post.add');
	}
	public function subCat(Request $request)
    {
        $parent_id = $request->cat_id; 
        $subcategories =DB::table('sub_categories')->select('sc_id','sc_name')
        	->where('sc_c_id',$parent_id)
        	->get();                    
       // print_r($subcategories); exit;
        return response()->json([
            'subcategories' => $subcategories
        ]);
        
    }
	public function save(Request $request){

		$data = $request->all();

		$request->validate([

            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

    
        $imageName = time().'.'.$request->image->extension();  

     
       
		//echo '<pre>';	print_r($path); exit;
		$rules = array(
                    'title'         =>  'required',
                    'description' => "required",
                    'price' => "required",
                    'opening' => 'nullable',
            		'closing' => 'nullable',
                    /*'photo'      =>  'required',*/
                    /*'location'      =>  'required',
                    'suitable'      =>  'required|in:1,2,3',
                    'event_type'      =>  'required|in:1,2,3,4',*/
                );

        $validate = Validator::make($data,$rules);
		$filename = time();
		if (@$data['image'] != "") {
			//echo 'das'; exit;
			$extension = $data['image']->getClientOriginalExtension();
			if(strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'jpeg' ) {
				
				$FileLogo = $filename . '.' .$data['image']->getClientOriginalExtension();
				$destinationPath = 'public/images';
				$data['image']->move($destinationPath, $FileLogo);
				$documentFile = $destinationPath . '/' . $FileLogo;
				$upload = $FileLogo;
			}
		}		
		
		
 		//echo '<pre>'; print_r($upload); exit;
	   
 		//echo '<pre>'; print_r($validate); exit;
 		
 		if($validate->fails()){ 
 			return redirect()->back()->withInput()->withErrors($validate);  
 		}else{
 			//echo 'dasda'; exit;
 			$crudRepository = new CrudRepository();
			//$modelName 		= "App\Models\TradeManage";	
			
			if($request->status == true){
				$request->status = 1;
			}else{
				$request->status = 0;
			}

			/*if($request->is_discount == true){
				$request->is_discount = 1;
			}else{
				$request->is_discount = 0;
			}

			if($request->is_recommend == true){
				$request->is_recommend = 1;
			}else{
				$request->is_recommend = 0;
			}*/
			//$trade_logo = $supp_photo?@$supp_photo: null;
	
   			$form_data 	= array(
				'title' => $request->title ?? null,
				'imgUrl' => $upload ?? null,
				'u_id' => 1,
				'description' => $request->description ?? null,
				'price' => $request->price ?? null,
				'discount_price' => $request->discount_price ?? null,
				'offer' => $request->offer ?? null,
				'opening' => date('Y-m-d H:i:s',strtotime($request->opening)) ?? null,
				'closing' => date('Y-m-d H:i:s',strtotime($request->closing)) ?? null,
				'category_id' => $request->category ?? null,
				'status' => $request->status ?? null,
				'created_at' => date('Y-m-d H:i:s'),
				'modify_at' => date('Y-m-d H:i:s'),
				);
			//echo '<pre>'; print_r($form_data); exit;
			//echo rand(); exit;
			$model     =  "App\Models\Post";	
			$Users 		=  $crudRepository->addsave($model,$form_data);
			$Message 	= "Added successfully";
			return redirect('admin/post/view')->with('success',$Message);
 		}
		   /* $Check = $companyservices->companysave($data);

		    if($Check->error_code == 200){
		    	$Message = "successfully added";
				return redirect('admin/Trade/view')->with('success',$Message);
		    }else{
		    	$Message = "something went wrong";
				return redirect()->back()->with('error', $Message);
		    }*/
		//}    
	} 

	public function view(Request $request){
		
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Transaction";	
		$moduleType =  20; 
		$Partner = $CrudRepository->view($model,$moduleType);
		$current_page = $request->page?$request->page:1;
		$total_count = $Partner->total_count;
		if($total_count<10){
			$row_count = $total_count;	
		}else{
			$row_count = 10;
		}
		
			/*echo '<pre>';
			print_r($Partner);die;*/
		return view('admin.Transaction.view',compact('Partner'));

	} 
    
   
    public function changestatus(Request $request){
    	//print_r($request->status); exit;

    	$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Collection"; 
		$id 		= $request->id;
		$form_data 	= array('status' => $request->status);
		//print_r($form_data); exit;
		$Changestatus = $CrudRepository->changestatus($model,$id,$form_data);
    		

	} 


	public function edit(Request $request){
		
		$CrudRepository = new CrudRepository();
		
		/*$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);*/
		
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);
		//echo '<pre>'; print_r($category); exit;
		/*$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		$model 		= "App\Models\PartnerType";	
		$moduleType =  16; 
		$eventType = $CrudRepository->view($model,$moduleType);


		$model3		= "App\Models\Region";	
		$moduleType =  18; 
		$region = $CrudRepository->view($model3,$moduleType);*/
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		
		$model 		= "App\Models\Post";
		$id 		= $request->id;
		$Partner 		= $CrudRepository->edit($model,$id);
		//echo '<pre>'; print_r($Partner); exit;
		return view('admin.Post.edit',compact('Partner','category'));
	
	}


	public function editsave(Request $request){
		//dd($request); exit;
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Post";
		$id 		= $request->id;
		$user 		= $model::findorfail($id);
		$data = $request->all();
		//echo '<pre>';	dd($request); exit;
		$rules = array(
                    'title'         =>  'required',
                    'description' => "required",
                    'price' => "required",
                    'opening' => 'nullable',
            		'closing' => 'nullable',
                );

        $validate = Validator::make($data,$rules);
		$filename = time();
		if (@$data['image'] != "") {
			//echo 'das'; exit;
			$extension = $data['image']->getClientOriginalExtension();
			if(strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'jpeg' ) {
				
				$FileLogo = $filename . '.' .$data['image']->getClientOriginalExtension();
				$destinationPath = 'public/images';
				$data['image']->move($destinationPath, $FileLogo);
				$documentFile = $destinationPath . '/' . $FileLogo;
				$upload = $FileLogo;
			}
		}else{
			$upload =  $user->imgUrl;
		}		

		
 		//echo '<pre>'; print_r($upload); exit;
	   
 		//echo '<pre>'; print_r($validate); exit;
 		
 		if($validate->fails()){ 
 			return redirect()->back()->withInput()->withErrors($validate);  
 		}else{
 			//echo 'dasda'; exit;
 			$crudRepository = new CrudRepository();
			//$modelName 		= "App\Models\TradeManage";	
			/*if($request->is_premium == "true" ){
				$request->is_premium = 1;
			}else if($request->is_premium == "false"){
				$request->is_premium = 0;
			}else{
				if($request->is_premium == null){
					//echo '1121';
					$request->is_premium = $user->is_premium;
				}else{
					$request->is_premium = 0;
				}
			}*/
			if($request->status == "true"){
				$request->status = 1;
			}else if($request->status == "false"){
				$request->status = 0;
			}else{
				if($request->status == null){
					$request->status = $user->status;
				}else{
					$request->status = 0;
				}

			}

			/*if($request->is_discount == "true"){
				$request->is_discount = 1;
			}else if($request->is_discount == "false"){
				$request->is_discount = 0;
			}else{
				if($request->is_discount == null){
					$request->is_discount = $user->is_discount;
				}else{
					$request->is_discount = 0;
				}

			}

			if($request->is_recommend == "true"){
				$request->is_recommend = 1;
			}else if($request->is_recommend == "false"){
				$request->is_recommend = 0;
			}else{
				if($request->is_recommend == null){
					$request->is_recommend = $user->is_recommend;
				}else{
					$request->is_recommend = 0;
				}

			}
			*/
		
			//$trade_logo = $supp_photo?@$supp_photo: null;
	
   			$form_data 	= array(
							'id' => $request->id ?? $user->id,
							'title' => $request->title ?? null,
							'imgUrl' => $upload ?? $request->imgUrl,
							'u_id' => 1,
							'description' => $request->description ?? null,
							'price' => $request->price ?? null,
							'discount_price' => $request->discount_price ?? null,
							'offer' => $request->offer ?? null,
							'opening' => date('Y-m-d H:i:s',strtotime($request->opening)) ?? null,
							'closing' => date('Y-m-d H:i:s',strtotime($request->closing)) ?? null,
							'category_id' => $request->category ?? null,
							'status' => $request->status ?? null,
							'status' => $request->status ?? $user->status ,
								);
			//echo '<pre>'; print_r($form_data); exit;
			//echo rand(); exit;
			$model     =  "App\Models\Post";	
			$Users 		= $CrudRepository->editsave($model,$id,$form_data);
			//$Users 		=  $crudRepository->addsave($model,$form_data);
			$Message 	= "Updated successfully";
			return redirect('admin/post/view')->with('success',$Message);
 		}
	
	}

	public function delete(Request $request){

		$CrudRepository = new CrudRepository();
			$model     =  "App\Models\Post";	
			$id 		= $request->id;
			$Users 		= $CrudRepository->harddelete($model,$id);
			$Message 	= "Delete successfully";
			return redirect('admin/post/view')->with('error',$Message);
		
	}


	public function detail(Request $request){

	
		$CrudRepository = new CrudRepository();
		
		$model 		= "App\Models\Gender";	
		$moduleType =  3; 
		$gender = $CrudRepository->view($model,$moduleType);
		
		$model1 		= "App\Models\Categories";	
		$moduleType1 =  4; 
		$category = $CrudRepository->view($model1,$moduleType1);
		//echo '<pre>'; print_r($category); exit;
		$model 		= "App\Models\SubCategories";	
		$moduleType =  8; 
		$subcategories = $CrudRepository->view($model,$moduleType);
		
		
		$model 		= "App\Models\PartnerType";	
		$moduleType =  16; 
		$eventType = $CrudRepository->view($model,$moduleType);


		$model3		= "App\Models\Region";	
		$moduleType =  18; 
		$region = $CrudRepository->view($model3,$moduleType);
		//dd($eventType); exit;
		//$gender = Gender::where('status',1);
		//echo '<pre>'; print_r($region); exit;
		
		$model 		= "App\Models\Partner";
		$id 		= $request->id;
		$Partner 		= $CrudRepository->edit($model,$id);
		//echo '<pre>'; print_r($Partner); exit;
		return view('admin.Partner.detail',compact('Partner','category','subcategories','eventType','region'));
	
	}

	public function getUrlData(Request $request){
		//echo '<pre>'; print_r($request['urldata']); exit;
		// Web page URL 
		$url = $request['urldata']; 
		 
		// Extract HTML using curl 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
		 
		$data = curl_exec($ch); 
		curl_close($ch); 
		 
		// Load HTML to DOM object 
		$dom = new DOMDocument(); 
		@$dom->loadHTML($data); 
		//echo '<pre>'; print_r($dom); exit;
		// Parse DOM to get Title data 
		$nodes = $dom->getElementsByTagName('title'); 
		$title = $nodes->item(0)->nodeValue; 
		 
		// Parse DOM to get meta data 
		$metas = $dom->getElementsByTagName('meta'); 
		 
		$description = ''; 
		$keywords = ''; 
		$site_name = ''; 
		$image = ''; 
		$price = ''; 

		for($i=0; $i<$metas->length; $i++){ 
		    $meta = $metas->item($i); 
		    if($meta->getAttribute('name') == 'description'){ 
		        $description = $meta->getAttribute('content'); 
		    } 
		    if($meta->getAttribute('name') == 'keywords'){ 
		        $keywords = $meta->getAttribute('content'); 
		    }
			if($meta->getAttribute('property') == 'og:site_name'){
				$site_name = $meta->getAttribute('content');
			}
			if($meta->getAttribute('property') == 'og:image'){
				$image = $meta->getAttribute('content');
			}
			if($meta->getAttribute('property') == 'og:price'){
				$price = $meta->getAttribute('content');
			}

		} 
		$data = array();
		$data['title'] = $title;
		$data['description'] = $description;
		$data['image'] = $image;
		$data['price'] = $price;
		/*
		echo "Title: $title". '<br/><br/>'; 
		echo "Description: $description". '<br/><br/>'; 
		echo "Keywords: $keywords". '<br/><br/>';
		echo "site_name: $site_name". '<br/><br/>';
		echo "image: $image";
		*/ 
		 return $data;
		
	}



}
