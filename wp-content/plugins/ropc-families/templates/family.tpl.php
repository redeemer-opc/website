<div class="family-main-container" data-record-id="<?php echo $fam_id ?>">
	<div class="picture-backdrop <?php echo $picture_id ? '' : 'no-picture' ?>">
		<div class="picture-backdrop-blurry-img"
			style="background-image: url('<?php echo $pic_src_full ?>')">
		</div>
		<div class="container">
			<div class="actual-picture" data-picture-caption="<?php echo $picture_caption ?>"
				data-picture-url="<?php echo $pic_src_full ?>"
				style="background-image: url('<?php echo $pic_src_thumb ?>')">
				<span class="fa fa-expand expand-picture"></span>
			</div>
			<div class="family-name">
				<h2><?php echo $name_fl ?>
				<?php if ( $can_edit ): ?>
					<span class="delete-family delete-btn fa fa-trash"></span>
				<?php endif ?>
				</h2>
				<?php if ( $can_edit ): ?>
				<form data-role="family-upload" method="post" action="#" enctype="multipart/form-data" >
					<label class="family-image button">
						<input type="file" class="hidden" name="family_image">
						<span class="label-text"><?php echo $picture_id ? "Change" : "Add" ?> picture</span>
					</label>
					<input type="hidden" name="action" value="update_ropc_picture">
					<input type="hidden" name="family_id" value="<?php echo $fam_id ?>">
				</form>
				<?php endif ?>
			</div>
		</div>
	</div>
	<div class="info-columns-container container">
		<div class="info-column">
			<?php echo $husband ?>
			<?php echo $wife ?>
		</div>
		<?php if ( $can_edit || $children ): ?>
		<div class="info-column with-title">
			<div class="column-title">
				Children
			</div>
			<?php foreach ( $children as $child ): ?>
				<?php echo $child ?>
			<?php endforeach ?>
			<?php if ( $can_edit ): ?>
			<button class="button add-child">Add child</button>
			<div class="hidden child-template">
				<?php echo $child_template ?>
			</div>
			<?php endif ?>
		</div>
		<?php endif ?>
		<?php if ( $show_contact_info ): ?>
		<div class="info-column with-title">
			<div class="column-title">
				Additional Info
			</div>
			<?php if ( $can_edit || $address1 ) : ?>
				<div data-placeholder="Address line 1" data-edit-field="ropc_family.address1">
					<?php echo $address1 ?>
				</div>
			<?php endif ?>
			<?php if ( $can_edit || $address2 ) : ?>
				<div data-placeholder="Address line 2" data-edit-field="ropc_family.address2">
					<?php echo $address2 ?>
				</div>
			<?php endif ?>
			<?php if ( $can_edit || $city ) : ?>
				<span data-placeholder="City" data-edit-field="ropc_family.city"><?php echo $city ?></span>,
			<?php endif ?>
			<?php if ( $can_edit || $state ) : ?>
				<span data-placeholder="State" data-edit-field="ropc_family.state"><?php echo $state ?></span>,
			<?php endif ?>
			<?php if ( $can_edit || $zip ) : ?>
				<span data-placeholder="Zip" data-edit-field="ropc_family.zip">
					<?php echo $zip ?>
				</span>
				<br>
			<?php endif ?>
			<?php if ( $can_edit || $home_phone ) : ?>
				<div class="family-phone">Home phone:
					<span data-placeholder="Home phone" data-edit-field="ropc_family.home_phone">
						<?php echo $home_phone ?>
					</span>
				</div>
			<?php endif ?>
			<?php if ( $can_edit || $anniversary ): ?>
				<div class="family-anniversary">Anniversary:
					<span data-placeholder="Anniversary" data-edit-field="ropc_family.anniversary">
						<?php echo $anniversary ?>
					</span>
				</div>
			<?php endif ?>
		</div>
		<?php endif ?>
	</div>
</div>