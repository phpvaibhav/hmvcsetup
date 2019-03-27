<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);
define('JSV0', 'Javascript:void(0)');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/

/* Application specific constants */

//keys (session, third party API keys like google, stripe etc)

/* Firebase API key for notifications */
define('NOTIFICATION_KEY','AAAA5xxGbHM:APA91bGyNWSNz6H9wqrS8SJcBZF6gCqk_7LrD2sfSxQbxtVnt8rTt1dTrDVWO90L-BRtsguLx9ldLzIk08yvTXO0Pc22haLLVOWLA-vTYQBi8g0hxUIc-99zZVuJES-mzY-0FwGY7iej');

/* session keys */
define('USER_SESS_KEY', 'app_user_sess'); 
define('ADMIN_USER_SESS_KEY', 'app_admin_user_sess');
/* session keys */

//stripe key
define('STRIPE_SECRET_KEY', 'sk_test_SBQwdtKJ1q8KNJD0nyOSvwgS'); 
define('STRIPE_PUBLISH_KEY', 'pk_test_uko2LRwvTrsLpvhMrbbwIHTj');

//DB tables
define('ADMIN', 'admin');
define('ORDER_RECYCLE', 'order_recycle');
define('USERS', 'users');
define('ORDER_DETAIL', 'order_detail');
define('ORDER_PRODUCT', 'order_product');
define('ORDER', 'order');
define('DOCUMENT', 'document');
define('WATER', 'water');
define('BOTTLE', 'bottle');
define('RECYCLE_BOTTLE', 'recycle_bottle');
define('PRODUCT', 'product');
define('ADDRESS', 'address');
define('NOTIFICATION', 'notification');
define('DELIVERY', 'delivery');
define('BILL_PAYMENT', 'payment');
define('OFFICE_CHARGES', 'office_charges');

//error messages

defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

// define('DEFAULT_NO_IMG', 'noimagefound.jpg');
define('THEME_BUTTON', 'btn btn-primary');
define('THEME', ''); // skin-1, skin-2, skin-
define('EDIT_ICON', '<i class="fa fa-pencil-square-o" aria-hidden="true"  style="color:#A82E14;"></i>');
define('DELETE_ICON', '<i class="fa fa-trash-o" aria-hidden="true" style="color:#A82E14;"></i>');
define('ACTIVE_ICON', '<i class="fa fa-check" aria-hidden="true" style="color:#A82E14;"></i>');
define('INACTIVE_ICON', '<i class="fa fa-times" aria-hidden="true" style="color:#A82E14;"></i>');
define('VIEW_ICON', '<i class="fa fa-eye" aria-hidden="true"></i>');

define('DEFAULT_USER','backend_asset/custom/images/user-place.png');
define('MAP_USER','backend_asset/custom/images/new_map_icon.png');

//Title, Site name, Copyright etc
define('SITE_NAME','Alka Silver Lake'); //your project name
define('COPYRIGHT','COPYRIGHT © '.date("Y",strtotime("-1 year")).'-'.date("Y").' All right reserved by Alka Silver Lake.');
define('SITE_TITLE','Alka Silver Lake');



// define('DEFAULT_IMAGE','uploads/user_avatar/user_placeholder.png');

//common messages
define('UNKNOWN_ERR', 'Something went wrong. Please try again');

//uploads path
define('USER_AVATAR_PATH', 'uploads/profile/'); //user avatar
define('USER_DEFAULT_AVATAR', 'uploads/user_avatar/user_placeholder.png'); //user placeholder image
define('WATER_DEFAULT_AVATAR', 'uploads/user_avatar/images.jpeg'); //user placeholder image
define('BOTTLE_DEFAULT_AVATAR', 'uploads/user_avatar/bottle.png'); //user bottle image
define('PRODUCT_DEFAULT_AVATAR', 'uploads/user_avatar/bottle.png'); //user product image
define('WATER_IMAGE_PATH', 'uploads/water/'); //user placeholder image
define('BOTTLE_IMAGE_PATH', 'uploads/bottle/'); //user placeholder image
define('RECYCLE_BOTTLE_IMAGE_PATH', 'uploads/recycle_bottle/'); //user placeholder image
define('PRODUCT_IMAGE_PATH', 'uploads/product/'); //user placeholder image
define('TC_PDF','uploads/document/');


/* APIS Status*/
define('FAIL','fail');
define('SUCCESS','success');
define('OK',200);
define('SERVER_ERROR',400);
