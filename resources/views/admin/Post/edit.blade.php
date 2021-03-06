@extends('admin.mainlayout')
@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Manage Deals</h1>
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
          <div class="box-main-title">Edit Deals</div>
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
              {!!Form::open(['url'=>'admin/post/edit-save','name' => 'orderForm' , 'enctype' => 'multipart/form-data', 'method'=>'post' ,'onsubmit'=>"return validateForm()"]) !!} 

       		   {!!Form::hidden('id',$Partner->id) !!} 
             <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Deal Url </lable>
                </div>
                <div class="col-md-5 col-12">
                 <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                 {!!Form::hidden('image',null,['id'=>'image','class' => 'form-control','placeholder' => 'Image','required' => 'required']) !!}


                  {!!Form::text('url',$Partner->url,['id'=>'url','class' => 'form-control','placeholder' => 'Enter Website Url']) !!}
                </div>
                <button class="btn btn-primary" type="button" id="geturlData">Fetch Data</button>
              </div>
            </div>
             <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-12">
                  <!-- {!!Form::label('name','Trade Name') !!} -->
                  <lable class="control-label">Image:</lable>
                </div>
                <div class="col-md-9 col-12">
                  <div class="manage-supplierdetail-profile"> 


                      @if($Partner->imgUrl !=  '' )         

                         
                          @if($Partner->is_url == 1 )
                            <img id="preview" src="{{URL('/public/images/'.$Partner->imgUrl)}}" />
                          @else
                            <img id="preview" src="{{ $Partner->imgUrl }}" />
                            
                            
                          @endif  
                     
                      @endif

                     

                    </div>
                      <label class="manage-supplierdetail-browse">
                      <input type="file" name="image" onchange="previewImage(this,this.value)" accept="image/*"/>
                      
                      <input type="hidden" name="image_url" id="image_url"
                              value="{{ $Partner->imgUrl }}"> 
                        
                        <button class="btn btn-primary">Browse</button>
                      </label>
                       <span class="admin-error-msg" id="err_music_image" style="display: none;"></span>
                       <br>
                      <span id="redpatner" class="patnerimg">You can upload up to *500kb</span>
                </div>
                <div id="loder" style="display: none;"><img  src="{{URL('public/admin/dist/img/loder.gif')}}" alt="" style="width: 25px;"></div>
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
                        <option value="{{$categorykey}}" {{$categorykey == $Partner->category_id ? 'selected' : ''}}>{{$categoryval}}</option>
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
                  {!!Form::text('title',$Partner->title,['id'=>'title','class' => 'form-control','placeholder' => 'Enter Title','required' => 'required']) !!}

                   {!!Form::hidden('is_url',$Partner->is_url,['id'=>'is_url','class' => 'form-control','placeholder' => 'Enter Title','required' => 'required']) !!}
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
                  {!!Form::textarea('description',$Partner->description,['id'=>'description','class' => 'form-control','placeholder' => 'Enter Description','required' => 'required','cols'=>"50",'rows'=>"5",]) !!}
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Price </lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('price',$Partner->price,['id'=>'price','class' => 'form-control','placeholder' => 'Price','required' => 'required']) !!}
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  
                  <lable class="control-label">Discount Price </lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('discount_price',$Partner->discount_price,['id'=>'discount_price','class' => 'form-control','placeholder' => 'Discount Price','required' => 'required']) !!}
                </div>
              </div>
            </div>
           
          <!--   <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Opening:</lable>
                </div>
                <div class="col-md-9 col-12">
                  {!!Form::text('opening',date('m-d-Y',strtotime($Partner->opening)),['id'=>'opening','class' => 'form-control','required' => 'required']) !!}
                <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                <span class="red-text" id="error-from_date"></span>
                </div>
              </div>
            </div> -->
            <!-- <div class="form-group">
              <div class="row">
                <div class="col-md-3 col-8">
                  <lable class="control-label">Closing:</lable>
                </div>
                <div class="col-md-9 col-12">
                   {!!Form::text('closing',date('m-d-Y',strtotime($Partner->closing)),['id'=>'closing','class' => 'form-control','required' => 'required']) !!}
                  <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                  <span class="red-text" id="error-to_date"></span>
                </div>
              </div>
            </div>
 -->
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

                     {!!Form::text('closing',null,['id'=>'closing','class' => 'form-control datetimepicker-input','data-target' => '#closing','required' => 'required',]) !!}
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
          <div class="form-group" id="subcatdiv">
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" rel="stylesheet" />
<script type="text/javascript">
  $(document).ready(function () {

    var opening = '<?php echo $Partner->opening; ?>';
    var closing = '<?php echo $Partner->closing; ?>';

    $('#opening').datetimepicker({
        date: new Date(opening)
    });

    $('#closing').datetimepicker({
        date: new Date(closing)
    });

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
            //alert(data.description);
            if(data.title != ''){
              $("#is_url").val(0);
              var preview = document.getElementById('preview');
               $("#title").val(data.title);
               $("#description").val(data.description);
               $("#price").val(data.price);
               $("#preview").val(data.image);
               $("#loder").attr("style", "display:none");
               preview.setAttribute('src', data.image);
                $('#image_url').val(data.image);
               /*$("#image").val(data.image);*/
            }
           
           }
       })
    });

    $('#opening').datetimepicker({
      minView: 2,
      minDate: new Date,
     onClose: function( selectedDate ) {  
        $( "#closing" ).datetimepicker( "option", "minDate", selectedDate );  
      }  
    }); 
    
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
  
   var event_type_id = "<?php echo $Partner->event_type; ?>" ;
   var is_premium_id = "<?php echo $Partner->is_premium; ?>" ;
   if(event_type_id == 4){
   	$('#catdiv').show(); 
  	$('#subcatdiv').show();
   }else{
   	$('#catdiv').hide(); 
  	$('#subcatdiv').hide(); 
   }

	if(is_premium_id  == 1){
   		 $('#promodetaildiv').show(); 
      $('#promocodediv').show();
   }else{
   	$('#promodetaildiv').hide(); 
  	$('#promocodediv').hide(); 
   }  
  
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
  $(function () {
  
      //Timepicker
    $('#opening').datetimepicker({
      //format: 'LT'
    })
    
      //Timepicker
    $('#closing').datetimepicker({
     // format: 'LT'
    })

  });

  function previewImage(input,fileName) {
    $("#is_url").val(0);
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
      //alert(1);
      $("#is_url").val(1);
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.setAttribute('src', 'placeholder.png');
    }
  }    
   



  </script> 

  <script type="text/javascript">
    ////
    function validateForm() {
      console.log(document.forms["orderForm"]);
      var opening = document.forms["orderForm"]["opening"].value;
      var closing = document.forms["orderForm"]["closing"].value;
      var newVal = $("#price").val();
      var regexp = /^-?(\d{1,12})(\.\d{1,12})?$/;

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

