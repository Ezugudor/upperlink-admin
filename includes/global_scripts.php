        <script src="scripts/jquery/jquery.min.js"></script>
	    <script src="css/bootstrap/js/bootstrap.min.js"></script>
	    <script src="scripts/jquery-slimscroll/jquery.slimscroll.min.js"></script>
		<script src="scripts/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
		<script src="scripts/chartist/js/chartist.min.js"></script>
		<script src="scripts/klorofil-common.js"></script>
		<script src="scripts/handlebars/dist/handlebars.min.js"></script>
		
		<script src="scripts/tokenize2/tokenize2.js"></script>
		
		<script src="scripts/rTabs/rTabs.js"></script>
		<script src="scripts/dropzone/dropzone.js"></script>
		<script src="scripts/bpopup/bpopup.min.js"></script>
		<script src="scripts/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
		<script src="scripts/form-to-json/form-to-json.js"></script>
		<script src="scripts/bootstrap-notify/bootstrap-notify.min.js"></script>
		<!-- <script src="scripts/vticker/vticker.min.js"></script> -->
		<script type="text/javascript">

	    //////////////////////////////////////////////////////////////////
		///////////////////    Logout Handler     ////////////////////////
		//////////////////////////////////////////////////////////////////

$(document).ready(function(){
	       // $('[data-toggle=tooltip]').tooltip();
	       $("[data-toggle='tooltip']").tooltip();

		(function($) { 



			$('#logout-btn,.logout-btn').on('click',function(e){
				e.preventDefault();
    			$.ajax({
			          url:'cpu/logout.php',
			          type:'POST',
			          dataType:'json',
			 
			          success:function(data, status){
			          	if(data.success==1){
							$.notify({ message:'You have successfully signed out...',icon:'fa fa-check-circle' },{ 
			          	    	       type:'success',delay:2000,onClose:function(){ location.reload(); }})
			          		
			          	}else{ //exist
			          		console.log('cant logout')
			          	}
			          	
			          },
			          error:function(xhr, status, error){alert(xhr.responseText + error)}
			   		 });
    		
  				});
		
	})(jQuery);




}); /// document.ready()

		</script>