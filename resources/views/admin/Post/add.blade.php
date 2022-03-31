@extends('admin.mainlayout')
@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Manage Deals </h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
    <!-- /.content-header -->

    <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="box-main">
        <div class="box-main-top">
          <div class="box-main-title">Deals</div>
          <div class="box-main-top-right">
             <a href="{{URL('admin/post/view')}}" <button type="button" class="btn btn-primary">Back</button></a>
          </div>
        </div>
          @if ($errors->any())
            <div class="alert alert-danger">
               <ul>
                  @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                  @endforeach
               </ul>
               @if ($errors->has('email'))
               @endif
            </div>
          @endif
        <div class="box-main-content mb-3">
          <div class="row">

            <div class="col-md-12 col-xl-6">
              {!!Form::open(['url'=>'admin/post/save','name' => 'orderForm' , 'enctype' => 'multipart/form-data', 'method'=>'post' ,'onsubmit'=>"return validateForm()"]) !!}  
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      
                      <lable class="control-label">Deal Url </lable>
                    </div>
                    <div class="col-md-5 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                     {!!Form::hidden('image',null,['id'=>'image','class' => 'form-control','placeholder' => 'Image','required' => 'required']) !!}


                      {!!Form::text('url',null,['id'=>'url','class' => 'form-control','placeholder' => 'Enter Website Url']) !!}
                    </div>
                    <button class="btn btn-primary" type="button" id="geturlData">Fetch Data</button>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      <lable class="control-label">Photo </lable>
                    </div>
                   
                    <div class="col-md-9 col-12">
                      <div class="row align-items-end">
                      </div>
                    
                    <div class="manage-supplierdetail-profile"><img id="preview" src="" /></div>

                     <label class="manage-supplierdetail-browse">
                          <input type="file" name="image" onchange="previewImage(this,this.value)" accept="image/*"/>
                          

                            <input type="hidden" name="image_url" id="image_url">

                            <button class="btn btn-primary">Browse</button>
                          </label>
                           <span class="admin-error-msg" id="err_music_image" style="display: none;"></span>
                           <br>
                          <span id="redpatner" class="patnerimg">You can upload up to *500kb</span>
                    </div>
                    <div id="loder" style="display: none;"><img  src="{{URL('public/admin/dist/img/loder.gif')}}" alt="" style="width: 25px;"></div>
                  {!!Form::hidden('is_url',0,['id'=>'is_url','class' => 'form-control','placeholder' => 'Enter Title','required' => 'required']) !!}
                </div>
            </div>
            <div class="form-group"  >
              <div class="row">
                <div class="col-md-3 col-8">
                  <!-- {!!Form::label('name','Trade Name') !!} -->
                  <lable class="control-label">Category</lable>
                </div>
                <div class="col-md-9 col-12">
                   <select class="form-control" id="category" name="category">
                        <option  value="">Select Category</option>
                        @foreach ($category as $categorykey => $categoryval)
                        <option value="{{$categorykey}}">{{$categoryval}}</option>
                        @endforeach
                </select>
                </div>
              </div>
            </div>
           
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Title </lable>
                </div>
                <div class="col-md-9 col-12">
                 <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                  {!!Form::text('title',null,['id'=>'title','class' => 'form-control','placeholder' => 'Enter Title','required' => 'required']) !!}
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <!-- {!!Form::label('name','Trade Name') !!} -->
                  <lable class="control-label">Description</lable>
                </div>
                <div class="col-md-9 col-12">
                 <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                  {!!Form::textarea('description',null,['id'=>'description','class' => 'form-control','placeholder' => 'Enter Description','required' => 'required','cols'=>"50",'rows'=>"5",]) !!}
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Price </lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('price',null,['id'=>'price','class' => 'form-control','placeholder' => 'Price','required' => 'required' ,'onblur'=>"javascript:return validatePrice(this)"]) !!}
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Discount Price </lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('discount_price',null,['id'=>'discount_price','class' => 'form-control','placeholder' => 'Discount Price','required' => 'required']) !!}
                </div>
              </div>
            </div>
           <!--  <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">offer </lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('offer',null,['id'=>'offer','class' => 'form-control','placeholder' => 'Offer','required' => 'required']) !!}
                </div>
              </div>
            </div> -->
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Opening:</lable>
                </div>
                <div class="col-md-9 col-12">
                   <div class="input-group date" id="time" data-target-input="nearest">

                       {!!Form::text('opening',null,['id'=>'opening','class' => 'form-control datetimepicker-input','data-target' => '#opening','required' => 'required']) !!}
                      <div class="input-group-append" data-target="#opening" data-toggle="datetimepicker">
                          <div class="input-group-text"><i class="far fa-clock"></i></div>
                           <span class="admin-error-msg" id="err_music_time" style="display: none;"></span>
                      </div>
                    </div>
                 <!--  {!!Form::text('opening',null,['id'=>'opening','class' => 'form-control','data-target' => '#opening','required' => 'required']) !!}
                <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                <span class="red-text" id="error-from_date"></span> -->
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Closing:</lable>
                </div>
                <div class="col-md-9 col-12">

                  <div class="input-group date" id="time" data-target-input="nearest">

                     {!!Form::text('closing',null,['id'=>'closing','class' => 'form-control datetimepicker-input','data-target' => '#closing','required' => 'required']) !!}
                    <div class="input-group-append" data-target="#closing" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                         <span class="admin-error-msg" id="err_music_time" style="display: none;"></span>
                    </div>
                  </div>
                  <!--  {!!Form::text('closing',null,['id'=>'closing','class' => 'form-control','data-target' => '#closing','required' => 'required']) !!}
                  <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                  <span class="red-text" id="error-to_date"></span> -->
                </div>
              </div>
            </div>
           
            
                
          
                <?php /*<div class="form-group" id="subcatdiv">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Subcategory</lable>
                    </div>
                    <div class="col-md-9 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                        <select class="browser-default custom-select" name="subcategory" id="subcategory">
                        </select>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <lable class="control-label">Premium:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                      <label class="switch">
                           <input id="toggle" value="1" name="toggle" type="checkbox" >
                        <span class="switchslider round"></span>
                      </label>
                       <input id="is_premium"  name="is_premium" type="hidden">
                     
                    </div>
                  </div>
                </div>
                <div class="form-group" id="promocodediv">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Promo code</lable>
                    </div>
                    <div class="col-md-9 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                      {!!Form::text('promo_code',null,['class' => 'form-control','placeholder' => 'Enter Promo Code']) !!}
                    </div>
                  </div>
                </div>
                <div class="form-group"  id="promodetaildiv">
                  <div class="row">
                    <div class="col-md-3 col-8">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Promo Detail</lable>
                    </div>
                    <div class="col-md-9 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                      {!!Form::text('promo_detail',null,['class' => 'form-control','placeholder' => 'Enter Promo Detail']) !!}
                    </div>
                  </div>
                </div>*/?>
                <!-- <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <lable class="control-label">Status:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                      <label class="switch">
                       
                           <input id="toggle1" value="1" name="toggle1" type="checkbox" >
                        <span class="switchslider round"></span>
                      </label>
                       <input id="status"  name="status" type="hidden">
                     
                    </div>
                  </div>
                </div> -->
               

                <!--  <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <lable class="control-label">Discount:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                      <label class="switch">
                       
                           <input id="toggle2" value="1" name="toggle2" type="checkbox" >
                        <span class="switchslider round"></span>
                      </label>
                       <input id="is_discount"  name="is_discount" type="hidden">
                     
                    </div>
                  </div>
                </div> -->
               <!--  <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <lable class="control-label">Is Recommend:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                      <label class="switch">
                       
                           <input id="toggle3" value="1" name="toggle3" type="checkbox" >
                        <span class="switchslider round"></span>
                      </label>
                       <input id="is_recommend"  name="is_recommend" type="hidden">
                     
                    </div>
                  </div>
                </div>
                 -->
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      
                    </div>
                    <div class="col-md-8 col-12">
                      <!--  <a href="">{!! Form::button('Cancle',array('class'=>'btn btn-deflaut')) !!} </a> -->
                       {!! Form::submit('Submit',array('class'=>'btn btn-primary')) !!}
                     <!--  <button type="button" class="btn btn-primary">Submit</button> -->
                      
                    </div>
                  </div>
                </div>
              {!!Form::close()!!}
            </div>
          </div>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css"/>
<script type="text/javascript"> 
  $(function() {   
    $('#opening').datetimepicker({
      minView: 2,
      minDate: new Date,
     onClose: function( selectedDate ) {  
        $( "#closing" ).datetimepicker( "option", "minDate", selectedDate );  
      }  
    });
     /* $( "#opening" ).datepicker({   
        minView: 2,
        minDate: new Date,
        dateFormat: 'dd-mm-yy',
        maxDate: 160,
      //changeMonth: true,
      //changeYear: true,
      //changeDay: true,
      //showButtonPanel: true,   
     // numberOfMonths: 1,  
      onClose: function( selectedDate ) {  
        $( "#closing" ).datepicker( "option", "minDate", selectedDate );  
      }  
    });  */
    $( "#closing" ).datetimepicker({
     
        minView: 2,
        minDate: new Date,
        
      //  changeMonth: true,
      //  numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#opening" ).datetimepicker( "option", "maxDate", selectedDate );
      }
    });  
  }); 
  function validatePrice($argument) {
    var newVal = $("#price").val();
    var regexp = /^-?(\d{1,6})(\.\d{1,2})?$/;

    if (regexp.test(newVal)) {
       //alert("valid Price");

    }else{
           alert("Invalid price");
    }
  }
  
  $('#catdiv').hide(); 
  $('#subcatdiv').hide();
  $('#event_type').on('change',function(e) {
   var event_id = e.target.value;
   //alert(event_id);
   if(event_id == 4){
     $('#catdiv').show(); 
     $('#subcatdiv').show(); 
   }else{
    $('#catdiv').hide(); 
    $('#subcatdiv').hide();
   }
 });
  $('input[name=toggle]').change(function(){
    var mode1 = $(this).prop('checked') == true ? true:  false; 
    $("#is_premium").val(mode1);
    if(mode1 != false){
      $('#promodetaildiv').show(); 
      $('#promocodediv').show(); 
    }else{
      $('#promodetaildiv').hide(); 
      $('#promocodediv').hide();
    }
    //promodetaildiv
  });
  $('input[name=toggle1]').change(function(){
    var mode2 = $(this).prop('checked') == true ? true:  false; 
     $("#status").val(mode2);
  
  });
  $('input[name=toggle2]').change(function(){
    var mode3 = $(this).prop('checked') == true ? true:  false; 
     $("#is_discount").val(mode3);
  
  });
   $('input[name=toggle3]').change(function(){
    var mode4 = $(this).prop('checked') == true ? true:  false; 
     $("#is_recommend").val(mode4);
  
  });
 
  /*function previewImage(input) {
    var preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.setAttribute('src', 'placeholder.png');
    }
  }*/

  function previewImage(input,fileName) {
    var preview = document.getElementById('preview');
    var allowed_extensions = new Array("jpeg","jpg",'png','bmp');
    var file_extension = fileName.split('.').pop().toLowerCase(); 

    if(allowed_extensions.indexOf(file_extension) > -1){
      $("#err_music_image").css("display", "none");
      $(':input[type="submit"]').prop('disabled', false);
    }else{
      $("#err_music_image").html("*Please select image.");
      $("#err_music_image").css("display", "block");
      $(':input[type="submit"]').prop('disabled', true);
    }
    if (input.files && input.files[0]) {
      $("#is_url").val(1);
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
        $('#image_url').val(e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.setAttribute('src', 'placeholder.png');
    }
  }     
  </script> 

  <script type="text/javascript">
    $(document).ready(function () {
        $( "#geturlData" ).click(function() {
         //var urldata = "https://www.codeat21.com/";
         urldata = '';
         urldata = document.getElementById('url').value
         //alert(urldata);
         //var urldata = e.target.value;
         $.ajax({
              url:"{{URL('admin/post/getUrlData')}}",
              type:"POST",
              data: {
                 urldata: urldata
              },
              beforeSend: function(){
                $("#loder").attr("style", "display:block");
              },
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
              success:function (data) {
              console.log(data);
              if(data.title != ''){
                var preview = document.getElementById('preview');
                $("#title").val(data.title);
                $("#description").val(data.description);
                $("#price").val(data.price);
                $("#loder").attr("style", "display:none");
                $("#preview").val(data.image);
                  preview.setAttribute('src', data.image);
                  
                  $('#image_url').val(data.image);

                }
               
              }
           })
        });

        $('#category').on('change',function(e) {
         var cat_id = e.target.value;

         $.ajax({
               url:"{{URL('admin/partner/subcat')}}",
               type:"POST",
               data: {
                   cat_id: cat_id
                },
               headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
               success:function (data) {
                console.log(data);
                $('#subcategory').empty();

               $.each(data.subcategories,function(index,subcategory){
                $('#subcategory').append('<option value="'+subcategory.sc_id+'">'+subcategory.sc_name+'</option>');
                })
               }
           })
        });
    });


    ////
    function validateForm() {
      
      console.log(document.forms["orderForm"]);
      var opening = document.forms["orderForm"]["opening"].value;
      var closing = document.forms["orderForm"]["closing"].value;
      var image =  $("input[name='image']").val();
      var image_url =  $("input[name='image_url']").val();

      //alert(e.target.result);
        if(image == '' && image_url == ''){
         // alert(image_url);
          alert("Please select product image");
          $("#err_music_image").html("*Please select image");
          $("#err_music_image").css("display", "block");
          return false;
        }else{
          $("#err_music_image").css("display", "none");
        }
        if($('#category').val()){
         
        } else {
          alert("Please Select Category");
          return false;
          // do something else
        }

       var newVal = $("#price").val();
       var regexp = /^-?(\d{1,6})(\.\d{1,2})?$/;

        if (regexp.test(newVal)) {
           //alert("valid Price");  
        }else{
               alert("Invalid price");
                return false;
        }

       var newVal1 = $("#discount_price").val();
       if (regexp.test(newVal1)) {
          
          if(parseInt(newVal1) > parseInt(newVal)){
            alert("Discount should be less than price amount");
            return false;  
          }
        }else{
               alert("Invalid discount");
                return false;
        }

        
    

    if (opening == "" ) {
      document.getElementById('error-from_date').innerHTML = "Please Enter From Date";
      return false;
    }
  } 
  </script>

        
@stop

