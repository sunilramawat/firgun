<?php

namespace App\Http\Controllers\admin;

use Route;
use Auth;
use Validator;
use App\User;
use App\Models\TradeManage;
use App\Models\Categories;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Utility\CustomVerfication;
use App\Http\Controllers\Utility\SendEmails;
use App\Http\Controllers\Repository\CrudRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Charts;


class CategoryController extends Controller{
		
	public function add(Request $request){
		return view('admin.Category.add');
	}

	public function save(Request $request){

		$data = $request->all();
		//dd($data);
		$CrudRepository = new CrudRepository();
		$model     =  "App\Models\Categories";	
		$moduleType =  27; // TradeList
		$uni_count = $CrudRepository->view($model,$moduleType,$data);
		if($uni_count == 0){
			$rules = array('c_name' => 'required|unique_with:c_type');
			

	   		$validate = Validator::make($data,$rules);
			//echo '<pre>';	print_r($validate); exit;
			if (@$data['image'] != "") {
				$extension_photo = $data['image']->getClientOriginalExtension();
				if(strtolower($extension_photo) == 'jpg' || strtolower($extension_photo) == 'png' || strtolower($extension_photo) == 'jpeg' ) {
					$FileLogo_photo = time() .'123'.'.' .$data['image']->getClientOriginalExtension();
					$destinationPath_photo = 'public/images';
					$data['image']->move($destinationPath_photo, $FileLogo_photo);
					$documentFile_photo = $destinationPath_photo . '/' . $FileLogo_photo;
					$request->c_image = $FileLogo_photo;
				}
			}	
			
	 		//echo '<pre>'; print_r($supp_photo ); exit;
		   
	 		/*echo '<pre>'; print_r($validate); exit;
	 		
	 		if($validate->fails()){ 		
	 			return redirect()->back()->withInput()->withErrors($validate);  
	 		}else{
	 		*/	//echo 'dasda'; exit;
	 			$crudRepository = new CrudRepository();
				//$modelName 		= "App\Models\TradeManage";	
			
				$c_name = $request->c_name ?? null;
				$c_image = $request->c_image ?? null;
				
				$form_data 	= array(
								'c_name' => $c_name,
								'c_image' => $c_image,
								);
				//print_r($form_data); exit;
				//echo rand(); exit;
				$model     =  "App\Models\Categories";	
				$Users 		=  $crudRepository->addsave($model,$form_data);
				
				$Message 	= "Added successfully";
				return redirect('admin/category/view')->with('success',$Message);
		}else{
			return $uni_count;
		}
			//return redirect('admin/category/view')->with('success',$Message);
 		
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
		$model 		= "App\Models\Categories";	
		$moduleType =  29; 
		$Trades_manage = $CrudRepository->view($model,$moduleType);
		$current_page = $request->page?$request->page:1;
		$total_count = $Trades_manage->total_count;
		if($total_count<10){
			$row_count = $total_count;	
		}else{
			$row_count = 10;
		}
		//$Trades_manage = $Trades_manage::paginate(5);

		/*echo '<pre>';
		print_r($Trades_manage);die;*/
		return view('admin.Category.view',compact('Trades_manage','current_page','total_count','row_count'));

	} 
    
   
    public function changestatus(Request $request){


    	$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Categories"; 
		$id 		= $request->id;
		$form_data 	= array('block_status' => $request->status);
		$Changestatus = $CrudRepository->changestatus($model,$id,$form_data);
    		
	} 





   public function edit(Request $request){

		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Categories";
		$id 		= $request->id;
		$Cms 		= $CrudRepository->editcategory($model,$id);
		//echo '<pre>'; print_r($Cms); exit;	
		//return view('Admin.Users.edit',compact('Users'));
		//return view('admin/users/view',compact('Users'));
		return view('admin/Category/edit',compact('Cms'));
	
	}

	public function editsave(Request $request){
		$data = $request->all();
		//dd($data); exit;
		//echo $request->id; exit;
		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Categories";
		 $id 		= $request->id;
		$user 		= $model::where('c_id',$id)->first();	
		//echo '<pre>'; print_r($user); exit;
		$data['c_id'] = $id;

		$moduleType =  28; // TradeList
		$uni_count = $CrudRepository->view($model,$moduleType,$data);
			//echo 'ss='.$uni_count; exit;
		if( $uni_count == 0){
			if (@$data['image'] != "") {
				$extension_photo = $data['image']->getClientOriginalExtension();
				if(strtolower($extension_photo) == 'jpg' || strtolower($extension_photo) == 'png' || strtolower($extension_photo) == 'jpeg' ) {
					$FileLogo_photo = time() .'123'.'.' .$data['image']->getClientOriginalExtension();
					$destinationPath_photo = 'public/images';
					$data['image']->move($destinationPath_photo, $FileLogo_photo);
					$documentFile_photo = $destinationPath_photo . '/' . $FileLogo_photo;
					$request->c_image = $FileLogo_photo;
				}
			}else{
				$request->c_image = $user->c_image;
			}	
			$c_name = $request->c_name ?? null;
			$c_image = $request->c_image ?? null;
			$form_data 	= array(
							'c_id'	=>$id,
							'c_name' => $c_name,
							'c_image' => $c_image,
							);
			//print_r($form_data); exit;
			//echo rand(); exit;
				
			$Users 		= $CrudRepository->editcategorysave($model,$id,$form_data);
			$Message 	= "update successfully";
			return redirect('admin/category/view')->with('success',$Message);
			//return 'sucess';
		}else{
			return $uni_count;
		}
	
	}

	public function delete(Request $request){
		//dd($request); exit;
		$CrudRepository = new CrudRepository();
		
		/*$model 		= "App\Models\Categories";	
		$moduleType =  9; 
		$id 		= $request->id;
		$Product_manage = $CrudRepository->view($model,$moduleType,$id);
		echo '<pre>'; print_r($Product_manage); exit;
		$total_count = $Product_manage->total_count;
		if($total_count == 0 ){*/
			$model     =  "App\Models\Categories";	
			$id 		= $request->id;
			$Users 		= $CrudRepository->categorydelete($model,$id); 
			$Message 	= "Delete successfully";
			return redirect('admin/category/view')->with('error',$Message);
		/*}else{
			$Message 	= "Sorry, this trade cannot be deleted. It is associated with multiple products.";
			//return redirect('admin/trade/view')->with('success',$Message);*/
			//return 0;

		//}*/

	}


	public function chipdetail(Request $request){

		$CrudRepository = new CrudRepository();
		$model 		= "App\Models\Categories";
		$id 		= $request->id;
		$Chips 		= $CrudRepository->getdetail($model,$id);

		return view('Admin.Chips.chip_detail',compact('Chips'));
	}



}
