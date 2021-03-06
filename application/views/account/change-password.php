<?=Form::open('account/change-password', 'method="post" class="tabbed')?>
<div>
	<label for="password">Current Password</label>
	<input type="password" name="password" id="password" />
	<?=form_error('password')?>
</div>

<div>
	<label for="new_password">New Password</label>
	<input type="password" name="new_password" id="new_password" />
	<?=form_error('new_password')?>
</div>

<div>
	<label for="password_confirm">Confirm Password</label>
	<input type="password" name="password_confirm" id="password_confirm" />
</div>

<div id="controls">
	<?=HTML::link('account', Lang::line('general.cancel'), 'class="negative button"')?>
	<button type="submit" name="change_password" id="change_password" class="button"><?=Lang::line('general.change_password')?></button>
</div>
<?=Form::close()?>
