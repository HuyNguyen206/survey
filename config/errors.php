<?php
 
return [
 
	'error_400' => [
		'id' => 'bad_request',
		'status' => '400'
	],
	
	'error_406' => [
		'id' => 'invalid_data',
		'status' => '406'
	],
	
	'error_500' => [
		'id' => 'bad_connect',
		'status' => '500'
	],
	
	'error_missing' => [
		'id' => 'missing_header',
		'status' => '406'
	],
	
	'error_empty' => [
		'id' => 'empty_header',
		'status' => '406'
	],
	
	'error_missingPost' => [
		'id' => 'missing_post_content',
		'status' => '406'
	],
	
	'error_emptyPost' => [
		'id' => 'empty_post_content',
		'status' => '406'
	],
    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */
 
    'bad_request' => [
        'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
    ],
 
    'forbidden' => [
        'title'  => 'The request was a valid request, but the server is refusing to respond to it.',
        'detail' => 'Your request was valid, but you are not authorised to perform that action.'
    ],
 
    'not_found' => [
        'title'  => 'The requested resource could not be found but may be available again in the future. Subsequent requests by the client are permissible.',
        'detail' => 'The resource you were looking for was not found.'
    ],
 
    'precondition_failed' => [
        'title'  => 'The server does not meet one of the preconditions that the requester put on the request.',
        'detail' => 'Your request did not satisfy the required preconditions.'
    ],
 
	'method_not_allowed' => [
        'title'  => 'The server cannot or will not process the request.',
        'detail' => 'Your request did not satisfy the required method.'
    ],
	
	'bad_connect' => [
		'title' => 'The server cannot or will not process the request due to something that is happen in server.', 
		'detail' => 'Server error. Please try again.'
	],
	
	'missing_header' => [
		'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
	],
	
	'missing_post_content' => [
		'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
	],
	
	'empty_header' => [
		'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
	],
	
	'empty_post_content' => [
		'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
	],
	
	'invalid_data' => [
		'title'  => 'The server cannot or will not process the request due to something that is perceived to be a client error.',
        'detail' => 'Your request had an error. Please try again.'
	],
      'session_expire' => [
        'msg'  => 'Bạn chưa đăng nhập vào hệ thống hoặc phiên đăng nhập đã kết thúc. Vui lòng đăng nhập để thực hiện khảo sát.',
        'detail' => 'Your request had an error. Please try again.'
    ],
];
