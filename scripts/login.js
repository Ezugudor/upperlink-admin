 /////////////////////////////////////////////////////////////////////////////////////
		///////////////////     NEW POST SUBMISSION FORM HANDLER     ////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////

		(function($) { 

			$('form').on('submit',function(e){
				e.preventDefault();

			var allFields = JSON.parse(formToJson(this)) ;
    	    var error = [];
    
			     if(typeof allFields.uname =='undefined'){
                    error.push(allFields.uname);
			     	$.notify({ message:'Username field is required' },{ type:'info'})
			     	return false;

			     } if(typeof allFields.pwd =='undefined'){
			     	error.push(allFields.pwd);
			     	$.notify({ message:'Password field is required' },{ type:'info'})
			     	return false;
			     } 
    	if(error.length==0){
    			$.ajax({
			          url:roott+'cpu/login.php',
			          data:allFields,
			          type:'POST',
			          dataType:'json',
			          success:function(data, status){
			          	if(data.success==1){
			          	    $.notify({ title:'<span>Success</span>:',message:'Redirecting...',icon:'fa fa-check-circle' },{ 
			          	    	       type:'success',delay:2000,onClose:function(){ location.reload(); 
			          	    	       }})

							
			          		
			          	}else if(data.success==0){ //exist
				    			
				    			$.notify({ title:'<span>Error</span>:',message:'Invalid Login detals',icon:'fa fa-exclamation' },{ type:'danger',delay:300})
			          	}
			          	
			          },
			          error:function(xhr, status, error){alert(xhr.responseText + error)}
			   		 });
    		}else{
				    // getError(error);
    		}
           

  });
		
	})(jQuery);