<div class="tier-pricing">
	<table class="table"  data-metal="<?php echo $_data['metal']; ?>" data-id="<?php echo $_data['id']; ?>">
		<thead>
			<tr>
				<th>Qty</th>
				<th>Each (excl. VAT)</th>

				<th>Each (inc. VAT)</th>

				<th>Starts from (inc. VAT)</th>
			</tr>
		</thead>
		<tbody>
			<tr data-min="1" data-max="1" data-amount="0" class="high-light">
				<td>1</td>
				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>

				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>

				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>
			</tr>
			<?php foreach($_discounts as $di_){ ?>
			<tr data-min="<?php echo $di_->min; ?>" data-max="<?php echo $di_->max; ?>" data-amount="<?php echo $di_->amount; ?>">
				<td>
					<?php echo $di_->min; ?> -
					<?php echo $di_->max; ?>
				</td>
				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>

				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>

				<td>
					<span>
						<?php echo get_imgs(); ?>
					</span>
				</td>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<?php /*?><td colspan="4">
					For larger quantities please call us on
					<strong>
						<?php echo get_option('glp_contact_more_qty'); ?>
					</strong><br>to discuss todays rates.
				</td><?php */?>
			</tr>
		</tfoot>
	</table>
</div>