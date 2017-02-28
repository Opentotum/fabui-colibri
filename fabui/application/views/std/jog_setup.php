<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */
if( !isset($jog_image) ) $jog_image = "/assets/img/controllers/create/subtractive/1.png";
if( !isset($jog_message) ) $jog_message = _("Jog the endmill to the desired origin point, press <i class=\"fa fa-bullseye\" ></i> and when you are ready press \"Next\"");
if( !isset($fourth_axis) ) $fourth_axis = False;
 
?>

<hr class="simple">

<div class="row">
	
	<div class="col-sm-6 col-md-6" style="margin-top:42px">
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 hidden-xs">
					<div class="product-image medium text-center">
						<img class="img-responsive" src="/assets/img/controllers/create/additive/2.png" style="max-width: 50%; margin-top:10px;"/>
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
						<p class="font-md"><?php echo _("Close the cover"); ?></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 hidden-xs">
					<div class="product-image medium text-center">
						<img class="img-responsive" src="<?php echo $jog_image;?>" style="max-width: 100%; margin-top:10px;"/>
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
						<p class="font-md"><?php echo _($jog_message); ?></p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="note">Note: If you home the axis, the jog position will be saved for future use.</div>
	</div>
	
	<div class="col-sm-6 col-md-6">
		<div class="" style="margin: 15px">
			
			<div class="row" style="margin-right: 0px">
				<ul class="nav nav-tabs pull-right">
					<li data-attribute="jog" class="tab active"><a data-toggle="tab" href="#jog-tab"><i aria-hidden="true" class="fa fa-floppy-o save-indication" style="display:none" data-toggle="tooltip" title="<?php echo _("Position will be saved for future use");?>" data-container="body" data-html="true"></i> <?php echo _("Jog");?></a></li>
					<li data-attribute="touch" class="tab"><a data-toggle="tab" href="#touch-tab"> <?php echo _("Touch");?></a></li>
					<?php if($fourth_axis): ?>
					<li data-attribute="4th" class="tab"><a data-toggle="tab" href="#fourth-tab"> <?php echo _("4th axis");?></a></li>
					<?php endif;?>
				</ul>
			</div>
			
			<div class="text-center">
				<div class="tab-content padding-10 well">
					<div class="tab-pane fade in active" id="jog-tab">
						<!-- jog step and feedrate inputs -->
						<div class="smart-form">
							<fieldset style="background: none !important;">
								<div class="row">
									<section class="col col-4">
										<label class="label-mill text-center">XY <?php echo _("Step"); ?> (mm)</label>
										<label class="input">
											<input  type="number" id="xyStep" value="1" step="1" min="0" max="100">
										</label>
									</section>
									<section class="col col-4">
										<label class="label-mill text-center"><?php echo _("Feedrate"); ?></label>
										<label class="input">
											<input  type="number" id="xyzFeed" value="1000" step="50" min="0" max="10000">
										</label>
									</section>
									<section class="col col-4">
										<label class="label-mill text-center">Z <?php echo _("Step"); ?> (mm)</label>
										<label class="input"> 
											<input type="number" id="zStep" value="0.5" step="0.1" min="0" max="100">
										</label>
									</section>
								</div>
							</fieldset>
						</div>
						<!-- jog controls placeholder -->
						<div class="jog-controls-holder"></div>
					</div><!-- id="jog-tab" -->
				
					<div class="tab-pane fade in" id="touch-tab">
						<div class="touch-container">
							<img class="bed-image" src="/assets/img/std/hybrid_bed_v2_small.jpg" >
							
							<div class="button_container">
								<button class="btn btn-primary touch-home-xy" data-toggle="tooltip" title="<?php echo _("Before using the touch interface you need to home XY axis first.<br><br>Make sure that the head will not hit anything during homing.");?>" data-container="body" data-html="true">
									<?php echo _("Home XY"); ?>
								</button>
							</div>
						</div>
					</div><!-- id="touch-tab" -->
					
					<div class="tab-pane fade in" id="fourth-tab">
						<div class="smart-form">
							<fieldset>
								<section>
									<label class="label"><?php echo _("Feedrate"); ?></label>
									<label class="input">
										<input type="number" min="1" value="800" id="4thaxis-feedrate">
									</label>
								</section>
							</fieldset>
						</div>
						<div class="knobs-container text-center" id="mode-a">
							<input value="0" class="knob" data-displayPrevious="true" data-width="230" data-height="230" data-cursor="true" data-step="0.5" data-min="0" data-max="360" data-thickness=".3" data-fgColor="#A0CFEC" data-displayInput="true">
						</div>
					</div><!-- id="fourth-tab" -->
					
					
				</div>
			</div>
		</div>
		
	</div>
</div>

