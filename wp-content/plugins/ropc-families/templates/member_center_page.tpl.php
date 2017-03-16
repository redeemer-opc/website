<?php echo ropc_families_display_navbar( $data ) ?>
<div id="main-content">
	<?php if ( $type == 'families' ): ?>
		<div class="all-families">
			<?php foreach ( $data[ 'families' ] as $family ): ?>
				<?php echo ropc_families_display_family( $family, $data[ 'in_edit_mode' ] ); ?>
			<?php endforeach ?>
		</div>
		<div class="mobile-show"></div>
		<?php if ( $data[ 'in_edit_mode' ] ): ?>
		<div class="add-family-btn-container">
			<div class="container">
				<button class="button add-family">Add family</button>
			</div>
		</div>
		<div class="hidden family-template">
			<?php echo ropc_families_display_family( [], TRUE ); ?>
		</div>
		<?php endif ?>
		<div class="container return-to-top">
			<a class="btn" href="#member_center_nav"><span class="fa fa-arrow-up"></span> Return to top</a>
		</div>
	<?php elseif ( $type == "splash" ): ?>
		<div class="member-center-splash">
			<div class="container">
				<h2>Member Center</h2>
				Using the options above, you can:
				<ul>
					<li>Navigate and search through the directory</li>
					<li>View birthdays and anniversaries by month</li>
				</ul>
			</div>
		</div>
	<?php elseif ( $type == "birthday" ): ?>
		<div class="birthdays">
			<div class="container">
				<h2><?php echo $data[ 'bd_month' ] ?> Birthdays</h2>
				<table class="dates-table">
					<?php foreach ( $data[ 'by_day' ] as $day => $people ): ?>
					<tr>
						<td class="date-cell"><?php echo $day ?></td>
						<td><?php echo $people ?></td>
					</tr>
					<?php endforeach ?>
				</table>
			</div>
		</div>
		<?php elseif ( $type == "anniversary" ): ?>
		<div class="anniversaries">
			<div class="container">
				<h2><?php echo $data[ 'anniv_month' ] ?> Anniversaries</h2>
				<table class="dates-table">
					<?php foreach ( $data[ 'by_day' ] as $day => $people ): ?>
					<tr>
						<td class="date-cell"><?php echo $day ?></td>
						<td><?php echo $people ?></td>
					</tr>
					<?php endforeach ?>
				</table>
			</div>
		</div>
	<?php endif ?>
</div>
<?php if ( $data[ 'alternate_view_url' ] ): ?>
	<div class="switch-view">
		<a class="button" href="<?php echo $data[ 'alternate_view_url' ] ?>">
		<?php if ( $data[ 'in_edit_mode' ] ): ?>
				Preview this page
		<?php else: ?>
				Edit this page
		<?php endif ?>
		</a>
	</div>
<?php endif ?>
