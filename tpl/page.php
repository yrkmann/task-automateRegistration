<!DOCTYPE html>
<html lang="ru_RU" dir="ltr">
<head>
	<meta charset="UTF-8" />
	<title>Register me!</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="css/main.css" />
</head>
<body>
	<div class="layout">
		<div class="container" role="main">
			<div class="row">
				<?php if (App::$alerts) { ?>
				<div class="col-xs-12">
					<div class="app-messages">
						<?php foreach (App::alerts() as $e) { ?>
						<div class="app-error alert alert-warning">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<?php echo esc_html($e['message']); ?>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
				<div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
					<div class="row">
						<div class="col-xs-12">
							<h1>Register me!</h1>
							<?php if ($messages) { ?>
							<div class="form-messages">
								<?php foreach ($messages as $e) { ?>
								<div class="form-message alert alert-<?php echo esc_html($e['type']); ?>"><?php echo esc_html($e['message']); ?></div>
								<?php } ?>
							</div>
							<?php } ?>
							<form id="register" class="form" action="" method="post">
								<div class="form-group">
									<label for="input_service_form_url">Service Form URL</label>
									<input id="input_service_form_url" class="form-control" type="text" disabled="disabled" value="<?php echo esc_html(APP_SERVICE_FORM_URL); ?>" />
									<span class="help-block"><a href="<?php echo esc_url(APP_SERVICE_FORM_URL); ?>" target="_blank">Open URL</a></span>
								</div>
								<div class="form-group">
									<label for="input_service_user">Register Email</label>
									<input id="input_service_user" class="form-control" type="email" name="service_user" required="required" value="<?php echo (!empty($input['service_user']) ? esc_html($input['service_user']) : null); ?>" />
								</div>
								<div class="form-group">
									<label for="input_service_pass">Register Password</label>
									<input id="input_service_pass" class="form-control" type="text" name="service_pass" required="required" value="<?php echo (!empty($input['service_pass']) ? esc_html($input['service_pass']) : null); ?>" />
								</div>
								<div class="form-group">
									<label for="input_imap_pass">Email Password for IMAP access</label>
									<input id="input_imap_pass" class="form-control" type="password" name="imap_pass" required="required" value="<?php echo (!empty($input['imap_pass']) ? esc_html($input['imap_pass']) : null); ?>" />
									<span class="help-block">If 2-step authentication is enabled this must be special application password</span>
								</div>
								<div class="form-group">
									<button class="btn btn-block btn-primary btn-lg" type="submit" name="action" value="register">Register & Activate</button>
								</div>
								<button class="btn btn-info" type="submit" name="action" value="create">Only register</button>
								<button class="btn btn-info" type="submit" name="action" value="activate">Only activate</button>
							</form>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-5 col-sm-offset-1 col-md-6 col-md-offset-1 col-lg-5 col-lg-offset-1">
					<h1>Activity log</h1>
					<div class="logs">
					<?php if (!App::logs()) { ?>
						<p>Empty.</p>
					<?php } else foreach (App::logs() as $log) { ?>
						<p>[<?php echo esc_html(date('H:i:s', $log['time'])); ?>] <?php echo esc_html($log['message']); ?></p>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>