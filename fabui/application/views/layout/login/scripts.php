<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script src="<?php echo base_url(); ?>/assets/js/<?php echo ENVIRONMENT ?>/app.config.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/bootstrap/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/notification/SmartNotification.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/smartwidgets/jarvis.widget.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/plugin/fastclick/fastclick.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/plugin/jquery-validate/jquery.validate.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/app.min.js"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
	<script src="<?php echo base_url(); ?>/assets/js/demo.min.js"></script>
<?php endif; ?>
<?php echo $jsScripts; ?>
<?php echo $jsInLine; ?>
	