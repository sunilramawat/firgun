@extends('admin.mainlayout')
@section('content')

  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Categories </h1>
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
          <div class="box-main-title">Add Category</div>
          <div class="box-main-top-right">
            <a href="{{URL('admin/category/view')}}"><button type="button" class="btn btn-primary">Back</button></a>
          </div>
        </div>
        <div class="box-main-content mb-3">
          <div class="row">
            <div class="col-md-12 col-xl-6">
              {!!Form::open(['url'=>'admin/category/save','id'=>"category_form", 'enctype' => 'multipart/form-data', 'method'=>'post']) !!}   
           
                 <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Name:</lable>
                    </div>
                    <div class="col-md-6 col-12">
                     <!--  <input type="text" name="" class="form-control" placeholder="Enter Trade Name"> -->
                      {!!Form::text('c_name',null,['class' => 'form-control','placeholder' => 'Name','required' => 'required']) !!}
                      <span class="admin-error-msg" id="err_c_name" style="display: none;"></span>
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      <!-- {!!Form::label('name','Trade Name') !!} -->
                      <lable class="control-label">Image:</lable>
                    </div>
                    <div class="col-md-9 col-12">
                     <div class="manage-supplierdetail-profile"><img id="preview" src="" /></div>
                          <label class="manage-supplierdetail-browse">
                          <input type="file" name="image" onchange="previewImage(this,this.value)" accept="image/*"/>
                          
                            
                            <button class="btn btn-primary">Browse</button>
                          </label>
                           <span class="admin-error-msg" id="err_music_image" style="display: none;"></span>
                           <br>
                          
                    </div>
                  </div>
                </div>
               <br>
                
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-3 col-12">
                      
                    </div>

                    <div class="col-md-8 col-12">
                      <!--  <a href="">{!! Form::button('Cancle',array('class'=>'btn btn-deflaut')) !!} </a> -->
                      {!! Form::submit('Submit',array('class'=>'btn btn-primary  mr-2  btn-submit')) !!} 
                      <a href="{{URL('admin/category/add')}}">{{ Form::button('Cancel',array('class'=>'btn btn-default')) }}</a>
                      
                      
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
    <!-- /.content -->
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script type="text/javascript"> 

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
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.setAttribute('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.setAttribute('src', 'placeholder.png');
    }
  }     
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
  }
*/


</script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".btn-submit").click(function(e){
            e.preventDefault();
            //alert('sunil');

            var _token = $("input[name='_token']").val();
            var c_type = $('#c_type').val();
            var c_name = $.trim($("input[name='c_name']").val());
            // var email = $("input[name='email']").val();
            //var locations = $("textarea[name='locations']").val();
            /////////////////////////////////////////////////////////////////////
            //var image = document.forms["orderForm"]["image"].value;
            var image =$("input[name='c_image']").val();
            console.log(image);
            if(image == ''){
              //alert('Please select image.');
              $("#err_music_image").html("*Please select image");
              $("#err_music_image").css("display", "block");
              return false;
            }else{
                $("#err_music_image").css("display", "none");
            }
            if(c_name == ''){
              //alert('fill c_name');
              $("#err_c_name").html("*Please enter Category name");
              $("#err_c_name").css("display", "block");
              return false;
            }else{
              $("#err_c_name").css("display", "none");
            }
            $.ajax({
                url: "{{URL('admin/category/save')}}",
                type:'POST',
                //data: {_token:_token, c_image:image, c_name:c_name},
                success: function($("#category_form").serialize()) {
                  console.log(data);
                  if(data == 1){
                     //window.location.href = "{{URL('admin/category/view')}}";
                  }else{
                     $("#err_c_name").html("*Please enter different category name");
                     $("#err_c_name").css("display", "block");
                  }
                    /*if($.isEmptyObject(data.error)){
                      window.location.href = "{{URL('admin/category/view')}}";

                        //alert(data.success);
                    }else{
                        printErrorMsg(data.error);
                    }*/
                }
            });


        }); 


        function printErrorMsg (msg) {
            $(".err_c_name").find("ul").html('');
            $(".err_c_name").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }
    });


</script> 
@stop


