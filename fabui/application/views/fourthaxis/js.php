<script type="text/javascript">


    $(function () {
        $(".do-engage").on('click', do_engage);
    });
    
    function do_engage()
    {
        openWait("<?php echo _("Engaging in process");?>");
        
        var now = jQuery.now();
        
        $.ajax({
            type: "POST",
            url : "fourthaxis/engage",
            url : "<?php echo site_url("fourthaxis/engage") ?>/"+ now,
            dataType: "json"
        }).done(function( data ) {
            
            closeWait();
            
            if(data.response == 'success')
            {
               
            }else{
                fabApp.showErrorAlert(data.message);
            }
            
             
        });
    }
    
</script>
