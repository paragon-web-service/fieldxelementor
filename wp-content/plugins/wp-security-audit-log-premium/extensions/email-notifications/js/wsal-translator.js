var WsalTranslator = {
// Validation messages
	errorsTitle: "<?php _e('Please correct the following errors:', 'wp-security-audit-log');?>",
	titleMissing: "<?php _e('Title is missing.', 'wp-security-audit-log');?>",
	titleNotValid: "<?php _e('Title is not valid.', 'wp-security-audit-log');?>",
	emailMissing: "<?php _e('Email or Username is missing.', 'wp-security-audit-log');?>",
	emailNotValid: "<?php _e('Email or Username is not valid.', 'wp-security-audit-log');?>",
	phoneMissing: "<?php _e('Mobile number is missing.', 'wp-security-audit-log');?>",
	phoneNotValid: "<?php _e('Mobile number is not valid.', 'wp-security-audit-log');?>",
	inputRequired: '<?php _e("A trigger\'s condition must not be longer than 50 characters.", "wp-security-audit-log-sample");?>',
	inputAtLeastOne: "<?php _e('Please add at least one trigger.', 'wp-security-audit-log');?>",
	formNotValid: "<?php _e('The form is not valid. Please refresh the page and try again.', 'wp-security-audit-log');?>",
	titleLengthError: "<?php _e('Title cannot be longer than {0} characters.', 'wp-security-audit-log');?>",
	emailLengthError: "<?php _e('Email or Username cannot be longer than {0} characters.', 'wp-security-audit-log');?>",
	triggerNotValid: "<?php _e('Condition is not valid', 'wp-security-audit-log');?>",

	alertCodeNotValid: "<?php _e('Event Code is not valid', 'wp-security-audit-log');?>",
	dateNotValid: "<?php _e('Date is not valid', 'wp-security-audit-log');?>",
	timeNotValid: "<?php _e('Time is not valid', 'wp-security-audit-log');?>",
	usernameNotValid: "<?php _e('Username is not valid', 'wp-security-audit-log');?>",
	usernameNotFound: "<?php _e('Username does not exist', 'wp-security-audit-log');?>",
	userRoleNotValid: "<?php _e('User role is not valid', 'wp-security-audit-log');?>",
	userRoleNotFound: "<?php _e('User role does not exist', 'wp-security-audit-log');?>",
	sourceIpNotValid: "<?php _e('Source IP is not valid', 'wp-security-audit-log');?>",
	postIdNotValid: "<?php _e('PostID is not valid', 'wp-security-audit-log');?>",
	pageIdNotValid: "<?php _e('Page ID is not valid', 'wp-security-audit-log');?>",
	customPostIdNotValid: "<?php _e('Custom Post ID is not valid', 'wp-security-audit-log');?>",
	postIdNotFound: "<?php _e('The specified Post ID does not exist', 'wp-security-audit-log');?>",
	pageIdNotFound: "<?php _e('The specified Page ID does not exist', 'wp-security-audit-log');?>",
	siteIdNotValid: "<?php _e('The specified Site ID is not valid', 'wp-security-audit-log');?>",
	customPostIdNotFound: "<?php _e('The specified Custom Post ID does not exist', 'wp-security-audit-log');?>",

	isMissing : "<?php _e('is missing', 'wp-security-audit-log');?>",

// Form fields
	emailText : "<?php _e('Email Address(es) or WordPress Users:', 'wp-security-audit-log');?>",
	phoneText : "<?php _e('Mobile number(s) for SMS notifications:', 'wp-security-audit-log');?>",
	deleteButtonText : "<?php _e('Delete', 'wp-security-audit-log');?>",
	saveNotifButtonText : "<?php _e('Save Notification', 'wp-security-audit-log');?>",
	addNotifButtonText : "<?php _e('Add Notification', 'wp-security-audit-log');?>",
	enabledText : "<?php _e('Enabled', 'wp-security-audit-log');?>",
	disabledText : "<?php _e('Disabled', 'wp-security-audit-log');?>",

// Groups
	'groupOptions' : "<?php _e('Grouping', 'wp-security-audit-log');?>",
	'groupAbove' : "<?php _e('Group with above trigger', 'wp-security-audit-log');?>",
	'groupBelow' : "<?php _e('Group with below trigger', 'wp-security-audit-log');?>",
	'ungroup' : "<?php _e('Remove from group', 'wp-security-audit-log');?>",
	'moveUp' : "<?php _e('Move trigger up', 'wp-security-audit-log');?>",
	'moveDown' : "<?php _e('Move trigger down', 'wp-security-audit-log');?>"
};
