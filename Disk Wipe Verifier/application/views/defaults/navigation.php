<div id="navigation" class="span-24 last">
<?php
	if ($this->session->userdata('logged_in')) {
		echo anchor(array('users','logout'), 'Log Out', 'class="button right"');
	} else {
		echo "Navigation";
	}
?>
</div>