<div class="person" data-family-role="<?php echo $family_role ?>" data-record-id="<?php echo $mem_id ?>">
	<?php if ( $can_edit && !$mem_id ): ?>
		<?php if ( $family_role == 'husband' ): ?>
		<i>Husband, or member info if single male:</i>
		<?php elseif ( $family_role == 'wife' ): ?>
		<i>Wife, or member info if single female:</i>
		<?php endif ?>
	<?php endif ?>
	<div class="person-name">
		<span data-placeholder="First name" data-edit-field="ropc_family_member.first_name">
			<?php echo $first_name ?>
		</span>
		<span data-placeholder="Last name" data-edit-field="ropc_family_member.last_name">
			<?php echo $last_name ?>
		</span>
		<?php if ( $family_role == 'wife' && ( $maiden_name || $can_edit ) ): ?>
		<span class="maiden-name">
			(<span data-placeholder="Maiden name"
				data-edit-field="ropc_family_member.maiden_name"><?php echo $maiden_name ?></span>)
		</span>
		<?php endif ?>
		<?php if ( $can_edit ): ?>
			<span class="delete-person delete-btn fa fa-trash"></span>
		<?php endif ?>
	</div>
	<?php if ( $show_membership ): ?>
		<?php if ( $can_edit ): ?>
			<select class="member-type">
				<?php foreach ( $type_options as $value => $name ): ?>
					<option value="<?php echo $value ?>"
						<?php echo $value == $type ? 'selected="selected"' : ''?>>
						<?php echo $name ?>
					</option>
				<?php endforeach ?>
			</select>
		<?php elseif ( $type && $type != 'regular_attendee' ): ?>
			<div class="member-type">
				<?php echo $type_options[ $type ] ?>
			</div>
		<?php endif ?>
	<?php endif ?>
	<?php if ( $birthday || $can_edit ): ?>
		<div class="person-bd">Birthday:
			<span data-placeholder="Birthday" data-edit-field="ropc_family_member.birthday">
				<?php echo $birthday ?>
			</span>
		</div>
	<?php endif ?>
	<?php if ( $is_parent && ( $occupation || $can_edit ) ): ?>
		<div class="person-occupation">Occupation:
			<span data-placeholder="Occupation" data-edit-field="ropc_family_member.occupation">
				<?php echo $occupation ?>
			</span>
		</div>
	<?php endif ?>
	<?php if ( $is_parent && ( $email || $can_edit ) ): ?>
		<div class="person-email">Email:
			<span data-placeholder="Email" data-edit-field="ropc_family_member.email">
				<?php echo $email ?>
			</span>
		</div>
	<?php endif ?>
	<?php if ( $is_parent && ( $cellphone || $can_edit ) ): ?>
		<div class="person-cell">Cellphone:
			<span data-placeholder="Cellphone" data-edit-field="ropc_family_member.cellphone">
				<?php echo $cellphone ?>
			</span>
		</div>
	<?php endif ?>
</div>
