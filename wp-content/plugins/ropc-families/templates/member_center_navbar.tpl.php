<div class="member-center-nav">
	<div class="container">
		<?php foreach ( $divisions_processed as $url_part => $info ): ?>
			<a class="<?php echo $info[ 'class' ] ?>" href="?page=<?php echo $url_part ?>">
				<?php echo $info[ 'text' ] ?>
			</a>
		<?php endforeach ?>
		<span class="search-container <?php echo $search_safe ? 'active' : '' ?>">
			<span class="fa fa-search search-icon"></span>
			<input type="search" class="search-input" value="<?php echo $search_safe ?>"
				placeholder="Search by last name"/>
		</span>
		<span class="birthdays-container <?php echo $data[ 'bd_month' ] ? 'active' : '' ?>">
			<span class="fa fa-birthday-cake cake-icon"></span>
			Birthdays
			<select name="birthday_month">
				<option value=""></option>
				<?php foreach ( $data[ 'months' ] as $num => $name ): ?>
				<option <?php echo $data[ 'bd_month_n' ] == $num ? 'selected="selected"' : '' ?>
					value="<?php echo $num ?>"><?php echo $name ?></option>
				<?php endforeach ?>
			</select>
		</span>
		<span class="anniversaries-container <?php echo $data[ 'anniv_month' ] ? 'active' : '' ?>">
			<span class="fa fa-heart cake-icon"></span>
			Anniversaries 
			<select name="anniversary_month">
				<option value=""></option>
				<?php foreach ( $data[ 'months' ] as $num => $name ): ?>
				<option <?php echo $data[ 'anniv_month_n' ] == $num ? 'selected="selected"' : '' ?>
					value="<?php echo $num ?>"><?php echo $name ?></option>
				<?php endforeach ?>
			</select>
		</span>

	</div>
</div>
