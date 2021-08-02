<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Custom404';
$route['404_override'] = 'Custom404';
$route['500_override'] = 'Custom500';
$route['400_override'] = 'Custom400';
$route['403_override'] = 'Custom403';
$route['translate_uri_dashes'] = FALSE;

// API ROUTES

// RESET PASSWORD
$route['v1/syshelper/requestResetPassword'] = 'v1/syshelper/RequestResetPassword';
$route['v1/syshelper/resetPassword'] = 'v1/syshelper/ResetPassword';

// REGISTER

$route['v1/register'] = 'v1/Register';

// VERIFIKASI EMAIL AKUN WAKTU PENDAFTARAN
$route['v1/verify/email'] = 'v1/VerifyEmail';

// OAUTH SERVICE API
$route['v1/oauth/login/request'] = 'v1/oauth/Request';
$route['v1/oauth/token'] = 'v1/oauth/Token';
$route['v1/oauth/refreshToken'] = 'v1/oauth/RefreshToken';
$route['v1/oauth/tokeninfo'] = 'v1/oauth/Tokeninfo';

// LOGOUT

$route['v1/logout'] = 'v1/Logout';

// USER INFO

$route['v1/user/profile'] = 'v1/user/Profile';

// RUMAH

$route['v1/rumah/getCategory'] = 'v1/rumah/GetCategory';
$route['v1/rumah/addRumahCatalog'] = 'v1/rumah/AddRumahCatalog';
$route['v1/rumah/editRumahCatalog'] = 'v1/rumah/EditRumahCatalog';
$route['v1/rumah/deleteRumahCatalog'] = 'v1/rumah/DeleteRumahCatalog';
$route['v1/rumah/myRumahCatalog'] = 'v1/rumah/MyRumahCatalog';

// RUMAH DETAIL
$route['v1/rumah/detail'] = 'v1/rumah/Detail';
$route['v1/rumah/currentDetail'] = 'v1/rumah/CurrentDetail';

// SEARCH RUMAH
$route['v1/search/query'] = 'v1/search/Query';
$route['v1/search/recommendation'] = 'v1/search/Recommendation';
$route['v1/search/quick'] = 'v1/search/Quick';

// ACOUNT SETTINGS
$route['v1/account/edit'] = 'v1/account/Edit';
$route['v1/verify/changeEmail'] = 'v1/ChangeEmailVerify';

// PENGUMUMAN
$route['v1/notification'] = 'v1/Notification';

// BOOKMARK
$route['v1/bookmark/addBookmark'] = 'v1/bookmark/AddBookmark';
$route['v1/bookmark/deleteBookmark'] = 'v1/bookmark/DeleteBookmark';
$route['v1/bookmark/getBookmark'] = 'v1/bookmark/GetBookmark';

// FRONT PAGE API
$route['v1/front/popularKatalog'] = 'v1/front/PopularKatalog';
$route['v1/front/more/popularKatalog'] = 'v1/front/PopularKatalogMore';
$route['v1/front/newKatalog'] = 'v1/front/NewKatalog';
$route['v1/front/more/newKatalog'] = 'v1/front/NewKatalogMore';

// WILAYAH API
$route['v1/wilayah_api/getProvinsi'] = 'v1/wilayah_api/GetProvinsi';
$route['v1/wilayah_api/getKabKota'] = 'v1/wilayah_api/GetKabKota';
$route['v1/wilayah_api/getKecamatan'] = 'v1/wilayah_api/GetKecamatan';
$route['v1/wilayah_api/getDesa'] = 'v1/wilayah_api/GetDesa';

// SERTIFIKAT TYPE
$route['v1/sertifikat/sertifikatType'] = 'v1/sertifikat/SertifikatType';
//

// YOUTUBE LINK EMBEDDER
$route['v1/youtubeEmbedder/embed'] = 'v1/youtubeEmbedder/Embed';
//

// User Preferences Save
$route['v1/preferences/getCatalogLocation'] = 'v1/preferences/GetCatalogLocation';
$route['v1/preferences/setCatalogLocation'] = 'v1/preferences/SetCatalogLocation';

$route['v1/preferences/getCatalogType'] = 'v1/preferences/GetCatalogType';
$route['v1/preferences/setCatalogType'] = 'v1/preferences/SetCatalogType';
//

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/