@extends('admin.mainlayout')
@section('content')

@php 
//echo '<pre>';  print_r($Partner); exit;
@endphp
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-10">
          <h1 class="m-0 text-dark">Manage Subscription </h1>
        </div><!-- /.col -->
        <div class="col-sm-2">
         <!--  <a href="{{URL('admin/post/add')}}">{{ Form::submit('Add New',array('class'=>'btn btn-primary')) }}</a> -->
        </div>
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
    <!-- /.content-header -->
  
    <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="box-main">
         <div class="box-main-top">
          <div class="box-main-title">Subscription List</div>
          <div class="box-main-top-right"> 
             <div class="box-serch-field">
              <input type="text" class="box-serch-input" name="" id ="search" placeholder="Search">
              <i class="fa fa-search" aria-hidden="true"></i>
            </div> 
           <!--  <button class="btn btn-primary " data-toggle="modal" data-target="#exampleModal"><i class="fa fa-filter" aria-hidden="true"></i></button> -->
            <!-- <a href="{{URL('admin/trade/add')}}">{{ Form::submit('Add New',array('class'=>'btn btn-primary')) }}</a> -->
            <!-- <button class="btn btn-primary ">Pending Request (25)</button> -->
              <!--  <button class="btn btn-primary " data-toggle="modal" data-target="#exampleModal"><i class="fa fa-filter" aria-hidden="true"></i></button> -->
          </div>
        </div> 
        <div  id="maintable" class="maintable" >
          <div class="box-main-table" id="maintable" >
            <div class="table-responsive">
              <table class="table table-bordered admin-table dataTable"  id="example2" >
                <thead>
                  <tr>
                    <th>S.No</th>
                    <th>Name</th> 
                    <th>Phone </th> 
                    <th>Amount</th>
                    <th>OrderId</th>
                    <!-- <th>Address</th> -->
                   <!--  <th>Action</th> -->
                  </tr>
                </thead>
                <tbody>
                  @foreach($Partner as $no => $chip)
                  <tr>
                    <td>{{$no+1}}</td>
                    <td>{{$chip->username}}</td>
                    
                    <td>{{$chip->phone}} </td>
                    <td>{{$chip->total_amount}} </td>
                    <td>{{$chip->orderId}} </td>
                    
                  </tr>
                  @endforeach()
                </tbody>
              </table>
            </div>
          </div>
        
        </div>
        
      </div>
    </div><!-- /.container-fluid -->
  </section>
    <!-- /.content -->

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <img src="{{URL('public/admin/dist/img/cancel-close.svg')}}" alt="">
          </button>
        </div>
        <div class="modal-body">
           {!!Form::open(['url'=>'admin/partner/view',  'name' => 'orderForm' ,'enctype' => 'multipart/form-data', 'method'=>'get','onsubmit'=>"return validateForm()"]) !!} 
            <div class="form-group">
              <div class="row">
                <div class="col-md-4 col-12">
                  <lable class="control-label">From:</lable>
                </div>
                <div class="col-md-8 col-12">
                  <input type="text" name="from_date" class="form-control" id="from_date" placeholder="MM-DD-YYYY" value="{{Request::get('from_date')}}" readonly="readonly">
                <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                <span class="red-text" id="error-from_date"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-4 col-12">
                  <lable class="control-label">To:</lable>
                </div>
                <div class="col-md-8 col-12">
                   <input type="text" name="to_date" class="form-control" id="to_date" placeholder="MM-DD-YYYY" value="{{Request::get('to_date')}}" readonly="readonly">
                  <i class="fa fa-calendar input-icon" aria-hidden="true"></i>
                  <span class="red-text" id="error-to_date"></span>
                </div>
              </div>
            </div>
            <!-- <div class="form-group">
              <div class="row">
                <div class="col-md-4 col-12">
                  <lable class="control-label">Suppliers:</lable>
                </div>
                <div class="col-md-8 col-12">
                  <select class="form-control">
                    <option>All</option>
                  </select>
                </div>
              </div>
            </div> -->
            <div class="form-group">
              <div class="row">
                <div class="col-md-4 col-12">
                  
                </div>
                <div class="col-md-8 col-12">
                  <button type="submit" class="btn btn-primary">Apply</button>
                   <a href="{{URL('admin/user/view')}}"> <button type="button" class="btn btn-primary">Reset</button></a>
                </div>
              </div>
            </div>
          {!!Form::close()!!}    
          
        </div>
        
      </div>
    </div>
  </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css"/>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script type="text/javascript">

function ChangeStatus(Id, Status)
{ 
  $("#LoadingProgress").fadeIn('fast');
    $.ajax({
      url: "{{ URL('admin/post/ChangeStatus') }}/"+Id+"/"+Status,
      type: "GET",
      contentType: false,
      cache: false,
      processData:false,
    success: function( data, textStatus, jqXHR ) {
      window.location.reload();
      $("#LoadingProgress").fadeOut('fast');
    },
    error: function( jqXHR, textStatus, errorThrown ) {

    }
  });
}

function DeleteTrade(Id, Status)
{ 
  $("#LoadingProgress").fadeIn('fast');
    if (confirm('Are you sure you want to delete this ADV?')) {
      $.ajax({
        url: "{{ URL('admin/post/delete') }}/"+Id,
        type: "GET",
        contentType: false,
        cache: false,
        processData:false,
      success: function( data, textStatus, jqXHR ) {
        if(data == 0){
          alert('Sorry, this ADV cannot be deleted.');
        }else{
          window.location.reload();
         $("#LoadingProgress").fadeOut('fast');
        }
        //alert('das');
         window.location.reload();
         $("#LoadingProgress").fadeOut('fast');
      },
      error: function( jqXHR, textStatus, errorThrown ) {
        //alert('dassa');
      }
    });
  }
}
//.datepicker("setDate",'now')
  $(function() {   
      $( "#from_date" ).datepicker({   
      defaultDate: "+1w",  
      maxDate: 0,
      //changeMonth: true,
      //changeYear: true,
      //changeDay: true,
      //showButtonPanel: true,   
     // numberOfMonths: 1,  
      onClose: function( selectedDate ) {  
        $( "#to_date" ).datepicker( "option", "minDate", selectedDate );  
      }  
    });  
    $( "#to_date" ).datepicker({
      defaultDate: "+1w",
      maxDate: 0,
      //  changeMonth: true,
      //  numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#from_date" ).datepicker( "option", "maxDate", selectedDate );
      }
    });  
  }); 

function validateForm() {
    var from_date = document.forms["orderForm"]["from_date"].value;
    var to_date = document.forms["orderForm"]["to_date"].value;
    if (from_date != "" || to_date != "") {
      if (from_date == ""){
        document.getElementById('error-from_date').innerHTML = " Please Enter From Date"
        //alert("Please Select From Date");
        return false;
      }
      if(to_date == ""){
         $('#to_date').val(from_date);
        /*document.getElementById('error-to_date').innerHTML = " Please Enter To Date"
        return false;*/
      }
      
    }
   /* if (from_date == "" && to_date == "") {
      document.getElementById('error-from_date').innerHTML = "Please Enter From Date";
      document.getElementById('error-to_date').innerHTML = "Please Enter To Date";
      return false;
    }*/
  } 
/*$(document).ready(function () {
  $('#search').on('keyup',function(){
    $value=$(this).val();
      $.ajax({
        url: "{{ URL('admin/user/search') }}",
        type : 'get',
        data:{'search':$value},
        success:function(data){
          $('#maintable').html(data);
        }
    });
  })

  //your code here
});*/
  
</script>
@stop
