<h2>Omise settings</h2>
<h3>Cards</h3>
<div id="omise_card_panel">
	<table>
		<tr>
			<th>Name</th>
			<th>Number</th>
			<th>Created date</th>
			<th>Action</th>
		</tr>
		<tbody>
<?php
if(isset($viewData ["existingCards"]->data)){
	foreach ( $viewData ["existingCards"]->data as $card ) {
		$nonce = wp_create_nonce ( "omise_delete_card_" . $card->id );
		echo "<tr><td>{$card->name}</td><td>XXXX XXXX XXXX {$card->last_digits}</td>";
		$created_date = date_i18n( get_option( 'date_format' ), strtotime($card->created));
		echo "<td>{$created_date}</td>";
		echo "<td><button class='button delete_card' data-card-id='{$card->id}' data-delete-card-nonce='{$nonce}'>Delete</button></td></tr>";
	}
}

?>
		</tbody>
	</table>
	<h4>Add new card</h4>
	<form name="omise_cc_form" id="omise_cc_form">
	<?php wp_nonce_field('omise_add_card','omise_add_card_nonce'); ?>
	<fieldset>
	<?php
	require_once ('omise-cc-form.php');
	?>
	<div class="clear"></div>
		</fieldset>
	</form>
	<button id="omise_add_new_card" class="button">Save card</button>
</div>