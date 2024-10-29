<?php

function autorank_admin_notice_nodb() {
	echo '<div class="error">';
	printf( __( 'In %1$s, there is a line of code that says %2$s. Please change it to %3$s in order to enable this settings panel.', 'autorank' ),
		'<code><big>' . esc_html( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'autorank.php' ) . '</big></code>',
		'<code><big>\'use_db\' => <strong>false</strong>,</big></code>',
		'<code><big>\'use_db\' => <strong>true</strong>,</big></code>' );
	echo '</div>';
}

function autorank_admin_notice_updated() {
	echo '<div class="updated"><p>';
	_e( 'Settings saved.', 'autorank' );
	echo '</p></div>';
}

function autorank_admin() {
	if ( !empty( $_POST ) ) {
		autorank_admin_parse();
	}

	$autorank = autorank_get_settings();

	$options = autorank_admin_get_options( $autorank );

	if ( !$autorank['use_db'] ) {
		add_action( 'autorank_admin_notices', 'autorank_admin_notice_nodb' );
		delete_option( 'autorank' );
	} ?>

<div class="wrap">

	<div class="wrap">

		<?php screen_icon(); ?>

		<h2><?php _e( 'AutoRank Settings', 'autorank' ) ?></h2>
		<?php do_action( 'autorank_admin_notices' ); ?>

		<form action="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>" method="post">

<?php if ( bbp_has_forums() ) { while ( bbp_forums() ) { bbp_the_forum();
	$options['post_modifier_forum[' . bbp_get_forum_id() . ']'] = array(
		'title' => sprintf( __( '"%s" forum multiplier', 'autorank' ), bbp_get_forum_title() ),
		'value' => isset( $autorank['post_modifier_forum'][bbp_get_forum_id()] ) ? $autorank['post_modifier_forum'][bbp_get_forum_id()] : 1,
		'class' => 'short'
	);
} } ?>

<table class="form-table"><tbody><?php

foreach ( $options as $name => $option ) {
	if ( !isset( $option['type'] ) )
		$option['type'] = 'text';

	switch ( $option['type'] ) {
		case 'text': ?>

<tr valign="top"><th scope="row"><?php echo $option['title']; ?></th><td>
			<input name="<?php echo $name; ?>" type="text" id="<?php echo $name; ?>" type="<?php echo $option['type']; ?>" value="<?php echo $option['value']; ?>" class="<?php echo $option['type']; ?> <?php echo $option['class']; ?>" />
</td></tr>
<?php		break;
		case 'select': ?>

<tr valign="top"><th scope="row"><?php echo $option['title']; ?></th><td>
			<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
<?php foreach ( $option['options'] as $val => $value ) { ?>
				<option value="<?php echo $val; if ( $val == $option['value'] ) echo '" selected="selected'; ?>"><?php echo $value; ?></option>
<?php } ?>
			</select>
</td></tr>
<?php		break;

		default:
			var_dump( $name, $option );
	}
}

?></tbody></table>

	<fieldset>
		<legend><?php _e( 'Ranks', 'autorank' ); ?></legend>

		<div>
		<table id="ranks" class="widefat">
			<thead>
			<tr>
				<th><?php _e( 'Title', 'autorank' ); ?></th>
				<th><?php _e( 'Color', 'autorank' ); ?></th>
				<th><?php _e( 'Required Score', 'autorank' ); ?></th>
				<th><?php _e( 'Estimated Posts Required', 'autorank' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th><?php _e( 'Title', 'autorank' ); ?></th>
				<th><?php _e( 'Color', 'autorank' ); ?></th>
				<th><?php _e( 'Required Score', 'autorank' ); ?></th>
				<th><?php _e( 'Estimated Posts Required', 'autorank' ); ?></th>
			</tr>
			</tfoot>

			<tbody>
<?php $average_post_score = autorank_get_average_post_score();

foreach ( $autorank['ranks'] as $score => $rank ) { ?>
			<tr>
				<td><input type="text" class="text long" name="autorank_rank_titles[]" value="<?php if ( is_array( $rank ) ) echo esc_attr( $rank[0] ); else echo esc_attr( $rank ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><input type="text" class="text short" name="autorank_rank_colors[]"<?php if ( is_array( $rank ) ) echo ' style="color: ' . esc_attr( $rank[1] ) . ';"'; ?> value="<?php if ( is_array( $rank ) ) echo esc_attr( $rank[1] ); ?>" placeholder="<?php esc_attr_e( 'Default', 'autorank' ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><input type="text" class="text short" name="autorank_rank_scores[]" value="<?php echo round( $score, 6 ); ?>"<?php if ( !$autorank['use_db'] ) echo ' disabled="disabled"'; ?> /></td>
				<td><?php echo $average_post_score ? ceil( round( $score, 6 ) / $average_post_score ) : '?'; ?></td>
			</tr>
<?php } ?>
<?php if ( $autorank['use_db'] ) { ?>
			<tr>
				<td><input type="text" class="text long" name="autorank_rank_titles[]" value="" /></td>
				<td><input type="text" class="text short" name="autorank_rank_colors[]" value="" placeholder="<?php esc_attr_e( 'Default', 'autorank' ); ?>" /></td>
				<td><input type="text" class="text short" name="autorank_rank_scores[]" value="" /></td>
				<td>?</td>
			</tr>
<?php } ?>
			</tbody>
		</table>
<?php if ( $autorank['use_db'] ) { ?>
		<script type="text/javascript">//<![CDATA[
jQuery(function($) {
	var newRow = $('<tr/>').html('<?php echo addslashes( '<td><input type="text" class="text long" name="autorank_rank_titles[]"/><td><input type="text" class="text short" name="autorank_rank_colors[]" value="" placeholder="' . __( 'Default', 'autorank' ) . '"/></td><td><input type="text" class="text short" name="autorank_rank_scores[]" value=""/></td><td>?</td>' ); ?>'),
		avgScore = <?php echo $average_post_score; ?>;

	$('#ranks tbody').live('input', 'changed', function f(e){
		var unusedRows = $('#ranks tbody tr').filter(function(){
					if ($(this).find('input[name="autorank_rank_titles\[\]"]').val().length)
						return false;
					if ($(this).find('input[name="autorank_rank_colors\[\]"]').val().length)
						return false;
					if ($(this).find('input[name="autorank_rank_scores\[\]"]').val().length)
						return false;
					return true;
				}),
			pos = e.target.selectionStart;

		if (e.target.name == 'autorank_rank_scores[]') {
			if (e.target.value != e.target.value.replace(/[^0-9e\.]/, '')) {
				setTimeout(function(){
					var pos = e.target.value.substring(0, e.target.selectionStart).replace(/[^0-9\.]+/g, '').length;
					e.target.value = e.target.value.replace(/[^0-9\.]+/g, '');
					setTimeout(function(){
						e.target.selectionStart = e.target.selectionEnd = pos;

						f(e); // IRON
					}, 0);
				}, 0);
				return;
			}

			var estPosts = (e.target.value || NaN) / avgScore;
			if (String(estPosts).indexOf('e') != -1)
				$(e.target).parent().parent().children(':last').text('<?php echo addslashes( __( 'A lot', 'autorank' ) ); ?>');
			else
				$(e.target).parent().parent().children(':last').text(isNaN(estPosts) ? '?' : Math.ceil(estPosts));
		}
		if (e.target.name == 'autorank_rank_colors[]') {
			e.target.setAttribute('style', 'color: ' + e.target.value);
		}

		switch (unusedRows.length) {
			case 0:
				$('#ranks tbody').append(newRow.clone());
			case 1:
				break;
			default:
				unusedRows.each(function(){
					if (!$(this).has(e.target).length)
						$(this).remove();
				});
		}

		$.each($('#ranks tbody tr').get().sort(function(_a, _b){
			var a = +($(_a).find('input[name="autorank_rank_scores\[\]"]').val() || NaN),
				b = +($(_b).find('input[name="autorank_rank_scores\[\]"]').val() || NaN);

			if (isNaN(a)) {
				if (isNaN(b))
					return 0;
				return 1;
			}
			if (isNaN(b))
				return -1;

			return a - b;
		}), function(){
			$(this).appendTo($('#ranks tbody'));
		});

		e.target.focus();
		e.target.selectionStart = e.target.selectionEnd = pos = pos;
	});
});
		//]]></script>
<?php } ?>
		</div>
	</fieldset>

			<p class="submit">
				<?php wp_nonce_field( 'autorank-update' ); ?>
				<input type="hidden" name="action" value="update" />
				<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'autorank' ); ?>" />
			</p>
		</form>
	</div>

<?php }

function autorank_admin_menu_add() {
	add_options_page( __( 'AutoRank', 'autorank' ), __( 'AutoRank', 'autorank' ), 'manage_options', 'autorank', 'autorank_admin' );
}
add_action( 'admin_menu', 'autorank_admin_menu_add' );

function autorank_admin_parse() {
	if ( !empty( $_POST ) ) {
		if ( $_POST['action'] == 'update' ) {
			check_admin_referer( 'autorank-update' );

			$autorank = autorank_get_settings();

			if ( !$autorank['use_db'] )
				return;

			foreach ( array( 'show_score', 'show_stats', 'show_rank', 'rank_before_name', 'show_rank_page' ) as $option ) {
				if ( isset( $_POST[$option] ) ) {
					$autorank[$option] = !!$_POST[$option];
				}
			}

			foreach ( array( 'post_default_score', 'post_modifier_first', 'post_modifier_word', 'post_modifier_char' ) as $option ) {
				if ( isset( $_POST[$option] ) ) {
					$autorank[$option] = (double) $_POST[$option];
				}
			}

			foreach ( array( 'text_score', 'text_reqscore' ) as $option ) {
				if ( isset( $_POST[$option] ) ) {
					$autorank[$option] = $_POST[$option];
				}
			}

			if ( isset( $_POST['post_modifier_forum'] ) ) {
				$autorank['post_modifier_forum'] = array();
				foreach ( $_POST['post_modifier_forum'] as $id => $multiplier ) {
					if ( is_numeric( $multiplier ) && ( (double) $multiplier ) != 1 && bbp_get_forum_id( $id ) )
						$autorank['post_modifier_forum'][$id] = (double) $multiplier;
				}
			}

			if ( isset( $_POST['autorank_rank_titles'] ) && isset( $_POST['autorank_rank_colors'] ) && isset( $_POST['autorank_rank_scores'] ) ) {
				$autorank['ranks'] = array();

				for ( $i = 0; $i < count( $_POST['autorank_rank_titles'] ); $i++ ) {
					if ( trim( $_POST['autorank_rank_titles'][$i] ) == '' ) {
						continue;
					}

					if ( trim( $_POST['autorank_rank_colors'][$i] ) == '' ) {
						$autorank['ranks'][(double) $_POST['autorank_rank_scores'][$i]] = trim( $_POST['autorank_rank_titles'][$i] );
					} else {
						$autorank['ranks'][(double) $_POST['autorank_rank_scores'][$i]] = array(
							trim( $_POST['autorank_rank_titles'][$i] ),
							trim( $_POST['autorank_rank_colors'][$i] ) );
					}
				}
			}

			$GLOBALS['autorank'] = $autorank;

			update_option( 'autorank', $autorank );

			autorank_recount();

			add_action( 'autorank_admin_notices', 'autorank_admin_notice_updated' );
		}
	}
}

function autorank_admin_get_options( $autorank ) {
	return array(
		'show_score' => array(
			'title'   => __( 'Show scores next to posts', 'autorank' ),
			'type'    => 'select',
			'value'   => $autorank['show_score'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_stats' => array(
			'title'   => __( 'Show scores on the statistics page template', 'autorank' ),
			'type'    => 'select',
			'value'   => $autorank['show_stats'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_rank' => array(
			'title' => __( 'Show ranks next to posts', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['show_rank'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'rank_before_name' => array(
			'title' => __( 'Put ranks before names instead of below them', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['rank_before_name'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'show_rank_page' => array(
			'title' => __( 'Show users the list of ranks', 'autorank' ),
			'type'  => 'select',
			'value' => $autorank['show_rank_page'],
			'options' => array(
				true  => __( 'Yes', 'autorank' ),
				false => __( 'No', 'autorank' )
			)
		),

		'post_default_score' => array(
			'title' => __( 'Base score', 'autorank' ),
			'value' => $autorank['post_default_score'],
			'class' => 'short'
		),

		'post_modifier_first' => array(
			'title' => __( 'New topic bonus', 'autorank' ),
			'value' => $autorank['post_modifier_first'],
			'class' => 'short'
		),

		'post_modifier_word' => array(
			'title' => __( 'Word bonus', 'autorank' ),
			'value' => $autorank['post_modifier_word'],
			'class' => 'short'
		),

		'post_modifier_char' => array(
			'title' => __( 'Letter bonus', 'autorank' ),
			'value' => $autorank['post_modifier_char'],
			'class' => 'short'
		),

		'text_score' => array(
			'title' => __( '"Score:" text', 'autorank' ),
			'value' => $autorank['text_score'],
			'note'  => __( 'Shown next to a user\'s score.', 'autorank' ),
			'class' => 'long'
		),

		'text_reqscore' => array(
			'title' => __( '"Required score:" text', 'autorank' ),
			'value' => $autorank['text_reqscore'],
			'note'  => __( 'Shown when a user\'s rank is hovered over.', 'autorank' ),
			'class' => 'long'
		),
	);
}
