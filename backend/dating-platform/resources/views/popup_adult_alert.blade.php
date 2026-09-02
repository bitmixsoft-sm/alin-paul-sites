<style>
	.modal-dialog {
	      max-width: 600px;
	      margin: 50px auto;
	  }

	.modal-body {
	  position:relative;
	  padding:0px;
	  text-align: center;
	}
	.close {
	  position:absolute;
	  right:-30px;
	  top:0;
	  z-index:999;
	  font-size:2rem;
	  font-weight: normal;
	  color:#fff;
	  opacity:1;
	}
   </style>
        <div class="modal fade" id="adultAlertModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-body">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>        
        			<div class="embed-responsive py-5">
        			  <h3>ADULT CONTENT - ACCESS RESTRICTED</h3>
        			  <p>Please confirm that you're over 18 or leave the website</p>
        			</div>
        	    </div>

        	    <div class="form-control  py-2 text-center">

        	    	<div class="row">
        	    		<div class="col-6">
        	    			<input type="button" name="adult" id="adult" value="I'M OVER 18"  onclick="addCookie()" class="btn btn-primary">
        	    		</div>
        	    		<div class="col-6">
        	    			<input type="button" name="exit" id="exit" value="EXIT" class="btn btn-danger">
        	    		</div>
        	    	</div>
        		</div>
        			    
            </div>
          </div>
        </div> 

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        if($.cookie('Adult') == undefined)	
		{
	 	 	$('#adultAlertModal').modal("show");
		}   
        $("#exit").click(function() {
            $('#adultAlertModal').modal("hide");
            window.location.href = "https://www.google.fr/";
        });
    });
    let addCookie=()=>{ $.cookie("Adult", "Over_18", { expires: 3000, path: '/' }); $('#adultAlertModal').modal("hide");}; 
    let removeCookie=()=>{ $.removeCookie("Adult","Over_18"); }; 
</script>



