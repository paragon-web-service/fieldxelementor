var wsalModel = wsalModelWp || {
	'info': {
		'title': '',
		'email': '',
		'phone': '',
		'emailLabel': WsalTranslator.emailText,
		'phoneLabel': WsalTranslator.phoneText
	},
	'errors': {
		'titleMissing': '',
		'titleInvalid': '',
		'emailMissing': '',
		'emailInvalid': '',
		'phoneMissing': '',
		'phoneInvalid': '',
		'triggersMissing': '',
		'triggers': {}
	},
	'buttons': {
		'deleteButton': WsalTranslator.deleteButtonText,
		'saveNotifButton': WsalTranslator.saveNotifButtonText,
		'addNotifButton': WsalTranslator.addNotifButtonText
	},
	'triggers': [],
	'default': {
		'select1': {
			'data': [ 'AND', 'OR' ],
			'selected': 0
		},
		'select2': {
			'data': [ 'EVENT ID', 'DATE', 'TIME', 'USERNAME', 'USER ROLE', 'SOURCE IP', 'POST ID', 'PAGE ID', 'CUSTOM POST ID', 'SITE DOMAIN', 'POST TYPE', 'POST STATUS', 'OBJECT', 'TYPE', 'CUSTOM USER FIELD' ],
			'selected': 0
		},
		'select3': {
			'data': [ 'IS EQUAL', 'CONTAINS', 'IS AFTER', 'IS BEFORE', 'IS NOT' ],
			'selected': 0
		},
		'select4': {
			'data': [ 'DRAFT', 'FUTURE', 'PENDING', 'PRIVATE', 'PUBLISHED' ],
			'selected': 0
		},
		'select5': {
			'data': WsalPostTypes.post_types.split( ', ' ),
			'selected': 0
		},
		'select6': {
			'data': WsalUserRoles.user_roles.split( ', ' ),
			'selected': 0
		},
		'select7': {
			'data': WsalObjects.objects.split( ', ' ),
			'selected': 0
		},
		'select8': {
			'data': WsalTypes.type.split( ', ' ),
			'selected': 0
		},
		'input1': '',
		'deleteButton': WsalTranslator.deleteButtonText
	},
	'viewState': []
};
