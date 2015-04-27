<script data-require="angular.js@1.2.x" src="<?php echo getAssetsPath(); ?>js/angular/angular.js" data-semver="1.2.15"></script>
<script>
    
(function(){    
var app = angular.module('ams',[]) ;

app.controller('MainCtrl',function($scope,$http,$compile,$parse,$window)
{
  /*angular validation variables*/
  $scope.batch_name         = '';
  $scope.batch_description  = '';
  $scope.campaign_id        = <?php echo $iCampaignId; ?>;
  $scope.schedule_date      = '';
  $scope.schedule_time      = '';
  $scope.image_preview_html = '';
  $scope.template_cuttoff_period = 0; 
  $scope.htmlPackagesDropdown = ''; 
  
  
  
  /*angular validation variables*/
  $scope.error_batch_name     = false;
  $scope.error_batch_desc     = false;
  
  $scope.error_campaign_id    = false;
  $scope.error_template_id    = false;
  $scope.error_product_id     = false;
  $scope.error_schedule_date  = false;
  $scope.error_whitelabel  = false;
  $scope.error_package  = false;
  
  
  
  $scope.error_upload_content = [];
  
  $scope.previous_button    = false;
  $scope.next_button        = true;
  $scope.finish_button       = false;
  
  //$scope.element_value      = '';
  $scope.element_array      =  [];
  
  $scope.active_tab = 1;
  $scope.batch_id   = 0;
  $scope.aProducts  = [];
  $scope.aProducts  = <?php echo empty($aProducts)? '[]' : $aProducts ; ?>;
  $scope.selected_template_id = 0;
  $scope.selected_product_id = 0;
  $scope.selected_whitelabel_id = 0 ;
  $scope.selected_package_id = 0 ;
  
  
  /*data recieved from ajax for productTempaltes is saved in this var*/
  $scope.htmlProductTemplate = false;
  
  /*data received from ajax for Upload Content*/
  $scope.htmlUploadContent   = false;
  
  /*data received from ajax for Sumamry tab*/
  $scope.htmlSummaryContent   = false;
  
  
  $scope.setActiveTab = function(selected_tab) 
    {   
      $scope.active_tab = selected_tab;
    }
   
   $scope.previous_tab = function ()
    {
        
       $scope.active_tab = $scope.active_tab - 1; 
       //hiding showing previous next  and finish buttons
       $scope.previous_button   = ($scope.active_tab == 1) ? false : true ; 
       $scope.next_button   = ($scope.active_tab == 4) ? false : true ; 
       $scope.finish_button = ($scope.active_tab == 4) ? true : false ;
       
       
    }

     
    $scope.addClass = function(someValue)
    {
        if($scope.active_tab==someValue)   
            return "active";
        else                               
            return "";
    }
  
    $scope.getProductTemplate = function(productId)
    {
        //hiding the vaidation message 
        $scope.error_product_id = false;
        
        //reinitializing tempalte id before making ajax call to populate templates
        $scope.selected_product_id  = productId;
        $scope.selected_template_id = 0;
        
        var request = 
        {
           method: 'POST',
           url:    '<?php echo $sGetTempaltesUrl; ?>',
           data:   {call_from:'createCampaign',product_id:''+productId,method:'getTemplatesByProductId'}
        };
                       
        //ajax call for populationg tempaltes
        ajax_call(request,'populateTempaltes');
    }
    
      
    
    $scope.loading = function(bool)
    {
        if(bool == true )
        {
            $('.main-div').addClass('show-loading');
        }
        else if(bool == false)
        {
            $('.main-div').removeClass('show-loading');
        }
    }
    
    
    $scope.setElementDetails = function(element_value,element_name,element_id,position_id,fold_id,index) 
    {
     //string appended because without it all indexs less then the position passed are created     
     
     $scope.obj = {};
     $scope.index = parseInt(index);
          
     $scope.obj.element_data                = element_value;
     //obj.element_name                     = element_name;
     $scope.obj.template_element_id         = element_id;
     $scope.obj.template_fold_id            = fold_id;
     $scope.obj.element_position            = position_id;
     $scope.obj.template_id                 = $scope.selected_template_id;
     $scope.obj.campaign_batch_id           = $scope.batch_id;
      
     //$scope.element_array.splice(index,1, obj); 
     $scope.element_array[$scope.index]= $scope.obj ;
     //console.log($scope.element_array);
     
    
     
      
    };
    
    
    // Watching if selected_template_id variable is changed
    $scope.$watch('selected_template_id', function() 
    {
       //empty element_array if template_id is changed 
	$scope.element_array = [];
        var models = document.getElementsByClassName("custom-elements");
                
        //setting all ng-model value to null on custon-elements when tempalte_id is changed
         angular.forEach(models, function(value,key) 
            {   
                   //getting model names to nullify their values
                   var val_ng_model = value.attributes['ng-model'].value;       //console.log(val_ng_model);  
                  
                  //setting null values to a model obtained in val_ng_model var
                  //$parse(val_ng_model).assign($scope, null);                    //console.log($parse(val_ng_model));
                  $scope[val_ng_model] = '';
                  
                  //removing previous validations 
                  $scope['error_'+val_ng_model] = false ;
                    
                  
            });
        
    });
    
  
    //called when the next button is clicked
    $scope.submit_form = function(selected_tab) 
    {
     
          /*step 1     
        * Called when Create tab is selected
        * */

         //client side validation

         
         if(selected_tab==1)
         {       
                $scope.error_batch_name = false; 
                $scope.error_batch_desc = false;
                $scope.error_campaign_id  = false;


                 if(angular.equals($scope.batch_name,'') || angular.isUndefined($scope.batch_name) )
                         {
                             $scope.error_batch_name = true;
                         }

                 //cleint side validation for batch name
                 if(angular.equals($scope.batch_description,'') || angular.isUndefined($scope.batch_description))
                         {
                             $scope.error_batch_desc = true;
                         }
                 
                  if($scope.campaign_id == 0)
                         {
                             $scope.error_campaign_id = true;
                         }  

                  //return false if any of the validation fails   
                  if($scope.error_batch_desc || $scope.error_batch_name  || $scope.error_campaign_id )
                         {
                          return false;
                         }

             
             
             
             //making ajax call only if validations are passed
             var request = 
             {
                 method: 'POST',
                 url:    '<?php echo $sFormAction; ?>',
                 data:   {method:'createBatch',campaign_id:''+$scope.campaign_id,name:$scope.batch_name,description:$scope.batch_description,batch_id:$scope.batch_id}
              };
              
              
             //ajax call for creating batch 
             ajax_call(request,selected_tab);
         }

         /*step 2     
        * Called when Template selection tab is selected
        * */  
         else if (selected_tab==2)
         {
            //validate if product is not selected
            if($scope.selected_product_id == 0)
                {
                    $scope.error_product_id = true; 
                }
                
            //only make the call if template is selected
            else if($scope.selected_template_id > 0)
                {
                    //hidding both template and product validation messages before making the ajax call
                    $scope.error_template_id    = false;
                    $scope.error_product_id     = false;
                    
                    var request = {
                      method: 'POST',
                      url:    '<?php echo $sFormAction; ?>',
                      data:   {method:'setBatchTemplate',template_id:$scope.selected_template_id,product_id:$scope.selected_product_id,                                      predefined_campaign_batch_id:$scope.batch_id}
                                  };
                    
                    
                    //ajax call for updating teplate_id and product_id in campaign_batches table
                    ajax_call(request,selected_tab);
                }
                
             else
             
                {
                    $scope.error_template_id = true;
                }
             

         }   
         
        
         /*step 3     
        * Called when Scheduling a batch
        * */  
         else if (selected_tab==3)
         {
                $scope.error_schedule_date = false; 
                
                 if(angular.equals($scope.schedule_date,'') || angular.isUndefined($scope.schedule_date) )
                         {
                             $scope.error_schedule_date = true;
                             return false;
                         }

                  
             var request = 
             {
                 method: 'POST',
                 url:    '<?php echo $sFormAction; ?>',
                 data:   {method:'ScheduleBatch',campaign_batch_id:$scope.batch_id,schedule_date:$scope.schedule_date,schedule_time:$scope.schedule_time}
              };
              
              
             //ajax call for creating batch 
             ajax_call(request,selected_tab);
            //console.log(selected_tab);   

         } 
         
         
         
          /*step 4     
        * Called when Scheduling a batch
        * */  
         else if (selected_tab==4)
         {
                 $scope.error_whitelabel = false;
                 $scope.error_package = false;
                
                 if(angular.equals($scope.selected_whitelabel_id,0))
                         {
                             $scope.error_whitelabel = true;
                             return false;
                         }
                if(angular.equals($scope.selected_package_id,0))
                         {
                             $scope.error_package = true;
                             return false;
                         }         
                         

                  
             var request = 
             {
                 method: 'POST',
                 url:    '<?php echo site_url('ajax/predefined_campaign') ?>',
                 data:   {  method:'savePreDefinedCampaign',
                            whitelabel_id:$scope.selected_whitelabel_id,
                            package_id:$scope.selected_package_id,
                            predefined_campaign_batch_id:$scope.batch_id,
                            predefined_campaign_id:$scope.campaign_id,
                            product_id:$scope.selected_product_id,
                            template_id:$scope.selected_template_id
                          }
              };
              
              
             //ajax call for creating batch 
             ajax_call(request,selected_tab);
            //console.log(selected_tab);   

         } 
    };

/*@params:request and tab no
* @desc:  request is the json object to be posted*/        
function ajax_call(request,selected_tab)
    {
     //show loading 
     $scope.loading(true);   
     
       $http(request).
       success(function(data, status, headers, config) {
          if(data)
              {
                    //hide loading 
                    $scope.loading(false);
             
                        if(selected_tab == 1)
                            {
                             if(angular.equals(data.status,true))
                                { 
                                    //autoselecting the first product in create mode {will be shown selected only for 1st time}
                                    if(angular.equals($scope.batch_id,0))
                                        {    
                                        $scope.selected_product_id = $scope.aProducts[0].product_id;
                                        $scope.getProductTemplate($scope.selected_product_id);
                                        }
                                    
                                    $scope.active_tab  = (isNaN(data.tab)) ? $scope.active_tab : data.tab;
                                    $scope.batch_id    = data.batch_id;

                                    $scope.error_batch_name   = false;
                                    $scope.error_batch_desc   = false;
                                    $scope.error_batch_lists  = false;
                                    $scope.error_campaign_id  = false;

                                    $scope.previous_button    = true;
                                    
                                    
                                                                        
                                }
                             else
                                 {
                                  //showing error messages if received
                                    if(angular.isArray(data.message))
                                        {
                                                   angular.forEach(data.message, function(item) 
                                                    {       

                                                           if(angular.equals(item,'<?php echo ERROR_CAMPAIGN_ID_REQUIRED; ?>'))
                                                                {
                                                                    $scope.error_campaign_id = true;
                                                                }

                                                            //for batch name validation
                                                           if(angular.equals(item,'<?php echo ERROR_NAME_REQUIRED; ?>'))
                                                                {
                                                                    $scope.error_batch_name = true;

                                                                }

                                                            //batch description validation
                                                            if(angular.equals(item,'<?php echo ERROR_DESC_REQUIRED; ?>'))
                                                                {
                                                                    $scope.error_batch_desc = true;
                                                                }
                                                            //batch lists validation
                                                            if(angular.equals(item,'<?php echo ERROR_CAMPAIGN_LIST_REQUIRED; ?>'))
                                                                {
                                                                    $scope.error_batch_lists = true;
                                                                }

                                                        //console.log(item);


                                                    }); 
                                         }   
                                 }
                            }
                  else if(selected_tab == 2)
                            {
                              //schedule batch step for admin
                                if(angular.equals(data.status,true))
                                    {
                                        $scope.active_tab  = (isNaN(data.tab)) ? $scope.active_tab : data.tab;
                                        $scope.template_cuttoff_period = data.cuttOffPeriod;
                                        //disabling next button on last tab
                                        $scope.next_button   = ($scope.active_tab == 4) ? false : true ; 
                                        //showing finish button 
                                        $scope.finish_button = ($scope.active_tab == 4) ? true : false ; 
                                        

                                    }
                                else{
                                        console.log(data.message);
                                    }      
                            
                            }
                  else if(selected_tab == 3)
                            {
                                
                                if(angular.equals(data.status,true))
                                    {
                                        $scope.active_tab  = (isNaN(data.tab)) ? $scope.active_tab : 4;
                                        //$window.location.href = '<?php echo site_url('predefined_campaigns/view'); ?>';
                                        
                                    }
                                //display error message    
                                else
                                    {
                                        $scope.error_schedule_date = true;
                                        console.log(data.message);
                                    }    
                                //compling is necessary for binding ajax data's Angular attributes to current scope
                                
                            
                            }
                   else if(selected_tab == 4)
                            {
                                
                                if(angular.equals(data.status,true))
                                    {
                                       // $scope.active_tab  = (isNaN(data.tab)) ? $scope.active_tab : 4;
                                        $window.location.href = '<?php echo site_url('predefined_campaigns/view'); ?>';
                                        
                                    }
                                //display error message    
                                else
                                    {
                                
                                        console.log(data.message);
                                    }    
                                //compling is necessary for binding ajax data's Angular attributes to current scope
                                
                            
                            }         
                  else if(selected_tab == 'populateTempaltes')
                            {
                                //compling is necessary for binding ajax data's Angular attributes to current scope
                                $scope.htmlProductTemplate = $compile(data)($scope);
                            
                            }
                  else if(selected_tab == 'whiteLabelPackages')
                            {
                            
                              $scope.htmlPackagesDropdown =  $compile(data)($scope);
                              
                            }         
                        
              }

       }).
       error(function(data, status, headers, config) {
                    //hide loading 
                    $scope.loading(false);
                    console.log('error making request for step '+selected_tab);    

       }); 
         
    }
    
  
  $scope.getWhiteLabelPackages = function() 
  {
      $scope.selected_package_id = 0;
     $scope.values = $scope.selected_whitelabel_id.split('~~~');
     $scope.selected_promocode = $scope.values[0];
     $scope.selected_whitelabel_id = $scope.values[1];
     
     //console.log($scope.values); return;
     
      var request = 
             {
                 method: 'POST',
                 url:    '<?php echo site_url('ajax/package') ?>',
                 data:   {method:'getWhiteLabelPackages',iPromotionCode:$scope.selected_promocode}
              };
      ajax_call(request,'whiteLabelPackages');
  }
    
    

});

//defining productTemplate directive
app.directive("productTemplate",function($compile){
    return {
    
        link: function (scope, iElement, iAttrs) {
			
			scope.$watch('htmlProductTemplate', function() {
						
                                                iElement.html('');
						iElement.append(scope.htmlProductTemplate);
			});
			
           

        }
    
    }
});	



//defining whitelabel directive directive
app.directive("whitelabelDropdown",function($compile){
    return {
    
        link: function (scope, iElement, iAttrs) {
			
			scope.$watch('htmlPackagesDropdown', function() {
						
                                                iElement.html('');
						iElement.append(scope.htmlPackagesDropdown);
			});
			
           

        }
    
    }
});




 })();
 
 

 
 
</script>


<!--javacript custom-->
<?php //echo $custom_js;  ?>


<div class="m-t-20" id="create-batch" ng-app="ams">

    <h2 class="heading-sty-2">Add a <?php echo BATCH; ?></h2>
    
    <div  class="row" ng-controller="MainCtrl">
        <div class="col-md-12 main-div">
            <div class="" id="">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-tabs-linetriangle nav-tabs-separator nav-stack-sm c-nav-tabs">
                    <li ng-class="addClass('1')" >
                        <a><span>CREATE</span></a>
                    </li>
                    <li  ng-class="addClass('2')" >
                        <a ><span>TEMPLATE SELECTION</span></a>
                    </li>
                    <li  ng-class="addClass('3')">
                        <a ><span>SCHEDULE</span></a>
                    </li>
                    <li  ng-class="addClass('4')">
                        <a ><span>SELECT WHITELABEL</span></a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
<!-- tab 1 Create Batch start-->
                    <div id="tab1" ng-class="addClass('1')" class="tab-pane padding-20  m-p-l-r-0 slide-left">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-default" ng-class="{ 'has-error':error_batch_name }">
                                    <label for="batch-name" class="batch-name"><?php echo BATCH; ?> Name:</label>
                                    <input ng-model="batch_name" type="text" placeholder="Name" class="form-control" name="batch_name" required="" id="batch_name" required>
                                </div>
                                <label ng-show="error_batch_name"  class="error r-25">Batch Name is Required!</label>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div ng-class="{ 'has-error':error_batch_desc }" class="form-group form-group-default">
                                  <label for="batch-description" class="batch-description">Description:</label>
                                  <textarea ng-model="batch_description" placeholder="Add some description..." class="form-control m-t-4" name="batch_description" required="" id="batch-description"></textarea>
                                </div>
                                <label ng-show="error_batch_desc"  class="error r-25">Batch Description is Required !</label>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        
                    </div>
<!-- tab 1 Create Batch end-->
                    
<!-- tab 2 Template selection start-->
                    <div id="tab2" ng-class="addClass('2')" class="tab-pane padding-20  m-p-l-r-0 slide-left">
                        <div class="row row-same-height">
                            <div class="col-md-12">
                                    
                                <!-- Template -->
                                <div class="panel panel-transparent">
                                    
                                  <label ng-show="error_product_id"  class="center alert alert-danger m-b-20"><button data-dismiss="alert" class="close"></button> Please Select a Product !</label>
                                  <label ng-show="error_template_id"  class="alert alert-danger m-b-20"><button data-dismiss="alert" class="close"></button> Please Select a Template !</label>

                                  <ul class="nav nav-tabs nav-tabs-simple nav-tabs-left bg-white" id="tab-3">
                                    <li ng-repeat="(key,product) in aProducts" ng-class="{active: key+1 == 1 }" >
                                      <a ng-click='getProductTemplate(product.product_id)' data-toggle="tab" href="#template-tab-{{key+1}}">
                                        {{product.title}}
                                      </a>
                                    </li>
                                  </ul>
                                    <!-- product template directive start-->
                                    <div product-template class="tab-content bg-white"> </div>
                                    <!-- product template directive end -->
                                </div>
                                <!-- / Template -->
                                

                            </div>
                        </div>
                    </div>
<!-- tab 2 Template selection End-->
                    



<!-- tab 3 schedule start-->
                    <div  ng-class="addClass('3')" id="tab4" class="tab-pane p-t-20 p-b-20  m-p-l-r-0 slide-left">

                  
                  <div class="row">
                    <div class="col-md-5">
                    	<div class="form-group" ng-class="{ 'has-error':error_schedule_date }">
		                    <label class="schedule_date" for="schedule_date">Schedule Date</label>
		                    <div class="controls">
		                    	<div class="input-group date datepicker-future-date-only">
			                      <input readonly ng-model="schedule_date" type="text" id="schedule_date" required="" name="schedule" class="form-control">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			                    </div>
                          <label ng-show="error_schedule_date"  class="error">Sorry ! At least {{template_cuttoff_period}} Days(required by printers).</label>
		                    </div>
		                  </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <p class="m-t-10"><strong>Note</strong> Cutoff period will be <strong class="c-blue">{{template_cuttoff_period}} days</strong></p>
                    </div>
                  </div>
                  

                </div>
<!-- tab 3 schedule end-->

<!-- tab 4 select whitelabel start-->
                    <div id="tab1" ng-class="addClass('4')" class="tab-pane padding-20  m-p-l-r-0 slide-left">
                        <div class="row">
                            <label for="batch-description" class="batch-description">Select a  WhiteLabel</label>
                            <div class="col-md-2">
                                <div class="form-group" > 
                                          
                                <select id="whitelabel" ng-change="getWhiteLabelPackages(this)" ng-model="selected_whitelabel_id"  required name="whitelabel" class="select--no-search full-width" data-init-plugin="select2">
                                <option  value='0'>Select</option>
                                
                                <?php foreach ($aSolutions as $whitelabel): ?>
				<option   value='<?php echo $whitelabel->promotion_code.'~~~'.$whitelabel->whitelabel_id; ?>'><?php echo $whitelabel->title; ?></option>
                                <?php endforeach; ?>
                                
                                </select> <br>
                                <label ng-show="error_whitelabel"  class="error r-25">Please Select a White label!</label>  
                                
                                <!-- whitelabel drop down -->
                                <div whitelabel-dropdown > </div>
                                <!-- whitelabel drop down -->
                                
                                </div>
                           </div>
                        </div>
                        
                    </div>

<!-- tab 4 select whitelabel end-->


                    
                    <div class="padding-20 bg-white m-p-l-r-0">
                        <ul class="pager wizard">
                            <li ng-show="next_button" class="next">
                                <button ng-click="submit_form(active_tab)" type="button" class="btn btn-complete btn-cons btn-animated fa pull-right m-m-b-10">
                                    <span>Next</span>
                                </button>
                            </li>
                            <li ng-show="finish_button" class="next finish" >
                                <button onclick="location.href='<?php echo site_url('predefined_campaigns/view'); ?>'" type="button" class="btn btn-primary btn-cons from-left pull-right m-m-b-10">
                                    <span>Finish</span>
                                </button>
                            </li>
                            <li ng-show="previous_button" class="previous" ng-click="previous_tab()">
                                <button type="button" class="btn btn-default btn-cons pull-right m-m-b-10">
                                    <span>Previous</span>
                                </button>
                            </li>
                            <li class="previous first finish_button hidden" >
                                <button type="button" class="btn btn-default btn-cons btn-animated from-left fa fa-cog pull-right m-m-b-10">
                                    <span>First</span>
                                </button>
                            </li>
                            
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>