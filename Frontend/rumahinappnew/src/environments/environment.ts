// This file can be replaced during build by using the `fileReplacements` array.
// `ng build --prod` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.

export const environment = {
  production: false
};

export const darkModeEnvironment = {
  'isDarkModeEnabled': false,
  'displayMode': 'light'
}

export const baseAuthData = {
  'basicToken': 'N2Q5NmY2OTc5MDMxOWNmNmM1ZmViMjU4NDllYjQ0ODU6MGFhYmZkYjEwN2EwYTBjYmI0YTVlYTk3MjQyOTZjZGM=',
  'appReqId': '828c4916459d94a5ee573ba6a85ae70cb8319bd1f5d1597d6fb9010f5a06927b',
  'appVersion': '1.0.0',
  'appPlatform': 'Android',
  'clientId': 'f76174e254c673fb2212656cc452622e',
  'clientSecret': 'evPfz292xSNjmc3nSESysJ6LJXD7fBv3FmdrDeBPZtLDYhRBkPDwJ59W8nVVMahT',
  'xApiKey': '46766932-c65e-4b11-924a-131811b935aa'
}

export const mimeData = {
  'urlEncoded': 'application/x-www-form-urlencoded',
  'json': 'application/json; charset=UTF-8'
}

export const baseUrlData = {
  'apiV1': 'https://api.netspeed.my.id/rumahinapi/v1/'
}

export const baseUrlDataPython = {
  'apiV1': 'https://api-py.netspeed.my.id/v1/'
}

export const tokenData = {
  'accessToken': localStorage.getItem("bearerAccessToken"),
  'refreshToken': localStorage.getItem("refreshToken"),
  'ssoToken': localStorage.getItem("ssoToken")
}

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/dist/zone-error';  // Included with Angular CLI.
