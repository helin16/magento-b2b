# Change Log

## v0.6.1 (not released yet)

__New Features__
* [#467](https://github.com/formvalidation/formvalidation/issues/467): Add ```dataType```, ```crossDomain```, ```validKey``` options for [remote](http://formvalidation.io/validators/remote/) validator.

It's possible to use remote validator to connect to external validator API, such as [MailGun (#1325)](https://github.com/formvalidation/formvalidation/issues/1315)

* [#940](https://github.com/formvalidation/formvalidation/issues/940): Add ```declarative``` option to support big form
* [#1328](https://github.com/formvalidation/formvalidation/issues/1328), [#1330](https://github.com/formvalidation/formvalidation/pull/1330): Add Netherlands [phone](http://formvalidation.io/validators/phone/) validator, thanks to [@HendrikSwBase](https://github.com/HendrikSwBase)
* [#1347](https://github.com/formvalidation/formvalidation/pull/1347): Add Bulgarian [zip code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@Izopi4a](https://github.com/Izopi4a)
* [#1350](https://github.com/formvalidation/formvalidation/pull/1350): Add Bulgarian [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@Izopi4a](https://github.com/Izopi4a)
* [#1355](https://github.com/formvalidation/formvalidation/pull/1355): Add Polish [zip code](http://formvalidation.io/validators/zipCode/) and [id](http://formvalidation.io/validators/id/) validators, thanks to [@tjagusz](https://github.com/tjagusz)
* [#1357](https://github.com/formvalidation/formvalidation/issues/1357): Support custom framework

__Improvements__
* [#1327](https://github.com/formvalidation/formvalidation/issues/1327): [remote](http://formvalidation.io/validators/remote/) validator fails if Ajax request fails
* [#1427](https://github.com/formvalidation/formvalidation/pull/1427): Update Netherlands [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@DiederikLascaris](https://github.com/DiederikLascaris)
* Add plugin instance to the 3rd parameter of [transformer](http://formvalidation.io/settings/#validator-transformer) callback
* Add Grunt task that runs the jasmine test suites

__Bug Fixes__

This version fixed the ```isValid()``` method which should return ```null``` when there is not validated or being validated field.
It also solves the issues where the submit button is disabled even when the form is valid.

* [#962](https://github.com/formvalidation/formvalidation/issues/962), [#1318](https://github.com/formvalidation/formvalidation/issues/1318): remote validator and ```isValid()``` combined do not work
* [#1160](https://github.com/formvalidation/formvalidation/issues/1160): Submit button is disabled
* [#1171](https://github.com/formvalidation/formvalidation/issues/1171): Submit button being disabled
* [#1220](https://github.com/formvalidation/formvalidation/issues/1220): Can only submit form after changing a value
* [#1221](https://github.com/formvalidation/formvalidation/issues/1221), [#1344](https://github.com/formvalidation/formvalidation/issues/1344): Remote validation trigger ```err.form.fv```
* [#1394](https://github.com/formvalidation/formvalidation/issues/1394): Submit incorrectly enabled even though form has errors

Other issues are fixed in this version:
* [#1107](https://github.com/formvalidation/formvalidation/issues/1107), [#1279](https://github.com/formvalidation/formvalidation/issues/1279), [#1280](https://github.com/formvalidation/formvalidation/pull/1280), [#1419](https://github.com/formvalidation/formvalidation/issues/1419): Show the ```validating``` icon when the field is being validated
* [#1282](https://github.com/formvalidation/formvalidation/issues/1282): Reset checkbox when calling [resetForm()](http://formvalidation.io/api/#reset-form) method
* [#1320](https://github.com/formvalidation/formvalidation/issues/1320): Fix Slovakia [phone number](http://formvalidation.io/validators/phone/) validator
* [#1343](https://github.com/formvalidation/formvalidation/issues/1343), [#1369](https://github.com/formvalidation/formvalidation/issues/1369): Fix the issue where custom validator doesn't provide default message
* [#1379](https://github.com/formvalidation/formvalidation/issues/1379): Don't continue if there is no validators attached to fields
* [#1387](https://github.com/formvalidation/formvalidation/issues/1387): [transformer](http://formvalidation.io/settings/#validator-transformer) option doesn't work with [notEmpty](http://formvalidation.io/validators/notEmpty/) validator
* [#1389](https://github.com/formvalidation/formvalidation/issues/1389): Fix ```isValidContainer()``` and ```validateContainer()``` methods to support fields with the same name

__Document__
* [#673](https://github.com/formvalidation/formvalidation/issues/673): Update [emailAddress](http://formvalidation.io/validators/emailAddress/) validator document
* [#688](https://github.com/formvalidation/formvalidation/issues/688): Add a [notice](http://formvalidation.io/settings/#form-icon) when using FontAwesome icon
* [#973](https://github.com/formvalidation/formvalidation/issues/973), [#1021](https://github.com/formvalidation/formvalidation/issues/1021), [#1346](https://github.com/formvalidation/formvalidation/issues/1346): 3 ways to [improve performance](http://formvalidation.io/validators/remote/#improving-the-performance) when using remote validator
* [#1098](https://github.com/formvalidation/formvalidation/issues/1098), [#1118](https://github.com/formvalidation/formvalidation/issues/1118), [#1325](https://github.com/formvalidation/formvalidation/issues/1325): Add [Playing with Fuel UX Wizard](http://formvalidation.io/examples/fuel-ux-wizard/) example
* [#1109](https://github.com/formvalidation/formvalidation/issues/1109), [#1326](https://github.com/formvalidation/formvalidation/issues/1326): Add [Playing with Typehead](http://formvalidation.io/examples/typeahead/) example
* [#1112](https://github.com/formvalidation/formvalidation/issues/1112): Support thousand separator
* [#1124](https://github.com/formvalidation/formvalidation/issues/1124), [#1329](https://github.com/formvalidation/formvalidation/issues/1329): Fix [CKEditor example](http://formvalidation.io/examples/ckeditor/) on Firefox
* [#1205](https://github.com/formvalidation/formvalidation/issues/1205): Add [Playing with Bootstrap Material Design](http://formvalidation.io/examples/bootstrap-material-design/) example
* [#1308](https://github.com/formvalidation/formvalidation/issues/1308): Update [Showing card icon](http://formvalidation.io/validators/creditCard/#showing-card-icon) example
* [#1313](https://github.com/formvalidation/formvalidation/issues/1313): Add [Adding warning validation state](http://formvalidation.io/examples/adding-warning-validation-state/) example
* [#1333](https://github.com/formvalidation/formvalidation/issues/1333): Update [Enabling the submit button](http://formvalidation.io/examples/enabling-submit-button/) example
* [#1378](https://github.com/formvalidation/formvalidation/issues/1378): Add [Validating multiple inputs as one](http://formvalidation.io/examples/validating-multiple-inputs-one/) example
* [#1388](https://github.com/formvalidation/formvalidation/issues/1388): Add [Field value is changed programmatically](http://formvalidation.io/examples/field-value-changed-programmatically/) example
* [#1390](https://github.com/formvalidation/formvalidation/issues/1390): Fix the [isValid()](http://formvalidation.io/api/#is-valid) method document
* [#1397](https://github.com/formvalidation/formvalidation/issues/1397): Add [Updating validator options](http://formvalidation.io/examples/updating-validator-options/) example
* [#1423](https://github.com/formvalidation/formvalidation/issues/1423): Update the [UIKit icon usage](http://formvalidation.io/settings/#form-icon)
* [formvalidation.io#11](https://github.com/formvalidation/formvalidation.io/pull/11): Fix the issue in [Settings Structure](http://formvalidation.io/settings/#settings-structure) section, thanks to [@DiederikLascaris](https://github.com/DiederikLascaris)
* [support#29](https://github.com/formvalidation/support/issues/29): Add [Playing with jQuery UI Datepicker](http://formvalidation.io/examples/jquery-ui-datepicker/) example
* [support#33](https://github.com/formvalidation/support/issues/33): Add [Playing with Flat UI](http://formvalidation.io/examples/flat-ui/) example

__Language Packages__
* [#1381](https://github.com/formvalidation/formvalidation/pull/1381): Update Slovak language package, thanks to [@PatrikGallik](https://github.com/PatrikGallik)
* [#1400](https://github.com/formvalidation/formvalidation/pull/1400): Update Belgian Dutch language package, thanks to [@jdt](https://github.com/jdt)

## v0.6.0 (2015-01-06)

__New Features__
* [#708](https://github.com/formvalidation/formvalidation/issues/708), [#899](https://github.com/formvalidation/formvalidation/issues/899): Add ```setLocale()``` and ```getLocale()``` methods to support multiple languages
* [#718](https://github.com/formvalidation/formvalidation/issues/718): Add ```validateContainer()``` method
* [#744](https://github.com/formvalidation/formvalidation/issues/744): Add [transformer](http://formvalidation.io/settings/#validator-transformer) option, allowing to hook the value of field before validating
* [#1131](https://github.com/formvalidation/formvalidation/issues/1131): Support add-on
* [#1140](https://github.com/formvalidation/formvalidation/pull/1140): Add UAE [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@s-a-y](https://github.com/s-a-y)
* [#1153](https://github.com/formvalidation/formvalidation/pull/1153): Add EIN validator, thanks to [@joshuachestang](https://github.com/joshuachestang)
* [#1165](https://github.com/formvalidation/formvalidation/pull/1165): Add BIC (ISO 9362) validator, thanks to [@thomaslhotta](https://github.com/thomaslhotta)
* [#1185](https://github.com/formvalidation/formvalidation/pull/1185): Add ```composer.json``` file, thanks to [@rbnvrw](https://github.com/rbnvrw)
* [#1189](https://github.com/formvalidation/formvalidation/issues/1189), [#1194](https://github.com/formvalidation/formvalidation/issues/1194): Add ```err```, ```icon```, ```row``` options
* [#1204](https://github.com/formvalidation/formvalidation/issues/1204): __Support Zurb Foundation framework__
* [#1207](https://github.com/formvalidation/formvalidation/pull/1207): Add Spanish [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@ethernet-zero](https://github.com/ethernet-zero)
* [#1208](https://github.com/formvalidation/formvalidation/pull/1208): Support Spanish [CIF](http://formvalidation.io/validators/id/) validator, thanks to [@ethernet-zero](https://github.com/ethernet-zero)
* [#1210](https://github.com/formvalidation/formvalidation/issues/1210): __Support UI Kit framework__
* [#1211](https://github.com/formvalidation/formvalidation/issues/1211): __Support Semantic UI framework__
* [#1212](https://github.com/formvalidation/formvalidation/issues/1212): __Support Pure framework__
* [#1227](https://github.com/formvalidation/formvalidation/pull/1227), [#1229](https://github.com/formvalidation/formvalidation/pull/1229): Add India [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@waveking](https://github.com/waveking)
* [#1230](https://github.com/formvalidation/formvalidation/pull/1230), [#1231](https://github.com/formvalidation/formvalidation/pull/1231): Add India [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@waveking](https://github.com/waveking)

__Changes__
* [#1167](https://github.com/formvalidation/formvalidation/issues/1167): Remove hexColor validator. Use [color](http://formvalidation.io/validators/color/) validator instead
* [#1272](https://github.com/formvalidation/formvalidation/issues/1272): Change event ```error.x.x``` to ```err.x.x``` to avoid ```window.onerror``` being invoked by jQuery
* Remove tab behavior from base class

__Add-ons__
* [#1116](https://github.com/formvalidation/formvalidation/issues/1116): Showing only one message each time
* [#1126](https://github.com/formvalidation/formvalidation/issues/1126): Required icon
* [#1132](https://github.com/formvalidation/formvalidation/issues/1132): Google reCAPTCHA add-on
* multilingual add-on

__Improvements__
* [#883](https://github.com/formvalidation/formvalidation/pull/883): Look for the field inside form first when using selector, thanks to [@drebrez](https://github.com/drebrez)
* [#908](https://github.com/formvalidation/formvalidation/issues/908), [#1156](https://github.com/formvalidation/formvalidation/pull/1156): Add option to set optional protocol in [uri](http://formvalidation.io/validators/uri/) validator, thanks to [@krecik](https://github.com/krecik)
* [#914](https://github.com/formvalidation/formvalidation/issues/914), [#1035](https://github.com/formvalidation/formvalidation/pull/1035), [#1163](https://github.com/formvalidation/formvalidation/issues/1163): Improve [identical](http://formvalidation.io/validators/identical/) validator, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1037](https://github.com/formvalidation/formvalidation/issues/1037): Show the credit card icon based on its type
* [#1083](https://github.com/formvalidation/formvalidation/issues/1083), [#1092](https://github.com/formvalidation/formvalidation/pull/1092/): Showing tooltip/popover when moving over or clicking the feedback icon (Bootstrap 3.3.0), thanks to [@Arkni](https://github.com/Arkni)
* [#1137](https://github.com/formvalidation/formvalidation/issues/1137): Use ```jQuery``` instead of ```window.jQuery```
* [#1154](https://github.com/formvalidation/formvalidation/issues/1154): Allow to reuse data which is returned by the validator
* [#1177](https://github.com/formvalidation/formvalidation/issues/1177): Don't need to set the [different](http://formvalidation.io/validators/different/) validator for both fields
* [#1186](https://github.com/formvalidation/formvalidation/issues/1186), [#1188](https://github.com/formvalidation/formvalidation/pull/1188): Improve the [CPF](http://formvalidation.io/validators/id/) validator, thanks to [@igorescobar](https://github.com/igorescobar)
* [#1197](https://github.com/formvalidation/formvalidation/pull/1197): Add sample data for [CPF](http://formvalidation.io/validators/id/) validator, thanks to [@dgmike](https://github.com/dgmike)
* [#1207](https://github.com/formvalidation/formvalidation/pull/1207): Improve Spanish [phone](http://formvalidation.io/validators/phone/) validator, thanks to [@ethernet-zero](https://github.com/ethernet-zero)
* [#1218](https://github.com/formvalidation/formvalidation/pull/1218): Improve Slovenian [vat number](http://formvalidation.io/validators/vat/) validator, thanks to [@Glavic](https://github.com/Glavic)
* [#1224](https://github.com/formvalidation/formvalidation/pull/1224): Improve tooltip style when working with Semantic UI form, thanks to [@Arkni](https://github.com/Arkni)
* [#1226](https://github.com/formvalidation/formvalidation/pull/1226): Fix destroying Semantic UI popup, thanks to [@Arkni](https://github.com/Arkni)
* [#1239](https://github.com/formvalidation/formvalidation/pull/1239): Fix typo in UIKit class, thanks to [@Arkni](https://github.com/Arkni)
* [#1252](https://github.com/formvalidation/formvalidation/issues/1252): Validators return true for not supported countries
* [#1255](https://github.com/formvalidation/formvalidation/issues/1255), [#1258](https://github.com/formvalidation/formvalidation/pull/1258): Support to use a Date object as value for ```min``` and ```max``` options, thanks to [@Arkni](https://github.com/Arkni)
* [#1261](https://github.com/formvalidation/formvalidation/issues/1261): Improve [cvv](http://formvalidation.io/validators/cvv/) validator
* [#1268](https://github.com/formvalidation/formvalidation/issues/1268): [uri](http://formvalidation.io/validators/uri/) validator gets slower if more than 25 characters
* The ```isValidContainer()``` method should return ```null``` if the container consists of at least one field which is not validated yet or being validated

__Bug Fixes__
* [#1101](https://github.com/formvalidation/formvalidation/issues/1101): The [cusip](http://formvalidation.io/validators/cusip/) validator doesn't work
* [#1102](https://github.com/formvalidation/formvalidation/issues/1102): Fix the [date](http://formvalidation.io/validators/date/) validator issue where accepts ```2014-11-1 23:``` as valid ```YYYY-MM-DD h:m``` date
* [#1105](https://github.com/formvalidation/formvalidation/issues/1105): The [color](http://formvalidation.io/validators/color/) validator doesn't provide ```html5Attributes``` mapping
* [#1125](https://github.com/formvalidation/formvalidation/pull/1125), [#1136](https://github.com/formvalidation/formvalidation/pull/1136): Update Brazil [ID](http://formvalidation.io/validators/id/) validator to support working with Mask plugin, thanks to [@jonasesteves](https://github.com/jonasesteves)
* [#1243](https://github.com/formvalidation/formvalidation/issues/1243): Fix the icon without label class
* [#1267](https://github.com/formvalidation/formvalidation/issues/1267): [identical](http://formvalidation.io/validators/identical/) validator allows to compare with any input
* [#1274](https://github.com/formvalidation/formvalidation/pull/1274): Fix ```validateContainer()``` to use map value instead of key, thanks to [@jasonblalock](https://github.com/jasonblalock)
* [#1279](https://github.com/formvalidation/formvalidation/issues/1279), [#1280](https://github.com/formvalidation/formvalidation/pull/1280): Show the ```validating``` icon when the field is being validated, thanks to [@tmaly1980](https://github.com/tmaly1980)
* [#1292](https://github.com/formvalidation/formvalidation/issues/1292): Fix bug of US [phone number](http://formvalidation.io/validators/phone/) validator

__Document__
* [#800](https://github.com/formvalidation/formvalidation/issues/800): Add [Using uri and regexp validators](http://formvalidation.io/validators/uri/#using-with-regexp-validator) example
* [#825](https://github.com/formvalidation/formvalidation/issues/825): Add [Bootstrap Datepicker](http://formvalidation.io/examples/bootstrap-datepicker/) example
* [#919](https://github.com/formvalidation/formvalidation/issues/919), [#1114](https://github.com/formvalidation/formvalidation/issues/1114): Add [Google reCAPTCHA](http://formvalidation.io/examples/validating-google-recaptcha/) example
* [#941](https://github.com/formvalidation/formvalidation/issues/941): Add [Clearing field when clicking the icon](http://formvalidation.io/examples/clearing-field-when-clicking-icon/) example
* [#948](https://github.com/formvalidation/formvalidation/issues/948), [#978](https://github.com/formvalidation/formvalidation/issues/978), [#1032](https://github.com/formvalidation/formvalidation/issues/1032), [#1146](https://github.com/formvalidation/formvalidation/issues/1146), [#1162](https://github.com/formvalidation/formvalidation/issues/1162): Add the [Is a@b valid email address](http://formvalidation.io/validators/emailAddress/#is-ab-valid-email-address) section
* [#1034](https://github.com/formvalidation/formvalidation/issues/1034): Add [Only enable the submit button if all fields are valid](http://formvalidation.io/examples/enabling-submit-button/) example
* [#1078](https://github.com/formvalidation/formvalidation/issues/1078), [#1104](https://github.com/formvalidation/formvalidation/issues/1104): Update the [Enabling the submit button all the time](http://formvalidation.io/examples/enabling-submit-button/) example
* [#1106](https://github.com/formvalidation/formvalidation/issues/1106): Add example to the [phone](http://formvalidation.io/validators/phone/) validator
* [#1122](https://github.com/formvalidation/formvalidation/pull/1122): Add third parameter to ```callback``` method of [callback](http://formvalidation.io/validators/callback/) validator, thanks to [@Arkni](https://github.com/Arkni)
* [#1128](https://github.com/formvalidation/formvalidation/issues/1128): Add link to the [Examples](http://formvalidation.io/examples/) from the [homepage](http://formvalidation.io/#2)
* [#1139](https://github.com/formvalidation/formvalidation/pull/1139): Add sample [United Arab Emirates phone numbers](http://formvalidation.io/validators/phone/), thanks to [@s-a-y](https://github.com/s-a-y)
* [#1143](https://github.com/formvalidation/formvalidation/issues/1143), [#1176](https://github.com/formvalidation/formvalidation/issues/1176): Add [Form is submitted twice](http://formvalidation.io/examples/form-submit-twice/) example
* [#1172](https://github.com/formvalidation/formvalidation/issues/1172): Add [Requiring at least one field](http://formvalidation.io/examples/requiring-at-least-one-field/) example
* [#1174](https://github.com/formvalidation/formvalidation/issues/1174): Add [Pickadate](http://formvalidation.io/examples/pickadate/) example
* [#1187](https://github.com/formvalidation/formvalidation/pull/1187): Add sample [Brazil ID (CPF) numbers](http://formvalidation.io/validators/id/), thanks to [@igorescobar](https://github.com/igorescobar)
* [#1233](https://github.com/formvalidation/formvalidation/pull/1233): Add sample [India postal code numbers](http://formvalidation.io/validators/zipCode/), thanks to [@waveking](https://github.com/waveking)

__Language Packages__
* [#1150](https://github.com/formvalidation/formvalidation/pull/1150): Add Catalan language package, thanks to [@ArnauAregall](https://github.com/ArnauAregall)
* [#1216](https://github.com/formvalidation/formvalidation/pull/1216), [#1248](https://github.com/formvalidation/formvalidation/pull/1248): Add Slovak language package, thanks to [@budik21](https://github.com/budik21)
* [#1217](https://github.com/formvalidation/formvalidation/pull/1217), [#1247](https://github.com/formvalidation/formvalidation/pull/1247): Update Czech language package, thanks to [@budik21](https://github.com/budik21)
* [#1225](https://github.com/formvalidation/formvalidation/pull/1225): Add Finnish language package, thanks to [@traone](https://github.com/traone)
* [#1246](https://github.com/formvalidation/formvalidation/pull/1246): Add Hindi language package, thanks to [@gladiatorAsh](https://github.com/gladiatorAsh)
* [#1321](https://github.com/formvalidation/formvalidation/pull/1321): Add Basque language package, thanks to [@xabikip](https://github.com/xabikip)

## v0.5.3 (2014-11-05)

__New Features__
* [#807](https://github.com/formvalidation/formvalidation/issues/807), [#821](https://github.com/formvalidation/formvalidation/pull/821): Add ```min```, ```max``` options for the [date](http://formvalidation.io/validators/date/) validator, thanks to [@Arkni](https://github.com/Arkni)
* [#822](https://github.com/formvalidation/formvalidation/pull/822): Add [color](http://formvalidation.io/validators/color/) validator, thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#844](https://github.com/formvalidation/formvalidation/pull/844), [#874](https://github.com/formvalidation/formvalidation/pull/874): The [stringLength](http://formvalidation.io/validators/stringLength/) validator adds option to evaluate length in UTF-8 bytes, thanks to [@thx2001r](https://github.com/thx2001r)
* [#937](https://github.com/formvalidation/formvalidation/issues/937), [#1001](https://github.com/formvalidation/formvalidation/pull/1001): Add ```minFiles```, ```maxFiles```, ```minTotalSize```, ```maxTotalSize``` options for the [file](http://formvalidation.io/validators/file/) validator, thanks to [@morrizon](https://github.com/morrizon)
* [#960](https://github.com/formvalidation/formvalidation/issues/960), [#1052](https://github.com/formvalidation/formvalidation/issues/1052): Add ```trim``` option for the [stringLength](http://formvalidation.io/validators/stringLength/) validator
* [#1008](https://github.com/formvalidation/formvalidation/pull/1008): Add France [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1010](https://github.com/formvalidation/formvalidation/pull/1010): Add Ireland [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@zmira](https://github.com/zmira)
* [#1018](https://github.com/formvalidation/formvalidation/pull/1018): Add German [phone number](http://formvalidation.io/validators/phone/) and [postal code](http://formvalidation.io/validators/zipCode/) validators, thanks to [@jhadenfeldt](https://github.com/jhadenfeldt)
* [#1022](https://github.com/formvalidation/formvalidation/pull/1022): Add Portugal [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@zmira](https://github.com/zmira)
* [#1033](https://github.com/formvalidation/formvalidation/issues/1033), [#1043](https://github.com/formvalidation/formvalidation/issues/1043), [#1068](https://github.com/formvalidation/formvalidation/issues/1068): Add ```autoFocus``` option
* [#1072](https://github.com/formvalidation/formvalidation/pull/1072): Add Austria and Switzerland [postal code](http://formvalidation.io/validators/zipCode/) validators, thanks to [@thomaslhotta](https://github.com/thomaslhotta)

__Improvements__
* [#823](https://github.com/formvalidation/formvalidation/issues/823): The [hexColor](http://formvalidation.io/validators/color/) validator only accepts 6 hex character values when using HTML 5 ```type='color'``` attribute
* [#864](https://github.com/formvalidation/formvalidation/pull/864): Comma separator handling in [greaterThan](http://formvalidation.io/validators/greaterThan/), [lessThan](http://formvalidation.io/validators/lessThan/) validators, thanks to [@mgibas](https://github.com/mgibas)
* [#999](https://github.com/formvalidation/formvalidation/pull/999), [#1048](https://github.com/formvalidation/formvalidation/issues/1048): Replace ',' with '.' to validate decimal numbers correct, thanks to [@johanronn77](https://github.com/johanronn77)
* [#1002](https://github.com/formvalidation/formvalidation/pull/1002): Put tooltip/popover on bottom if there is not enough space on top, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1015](https://github.com/formvalidation/formvalidation/pull/1015): The [remote](http://formvalidation.io/validators/remote/) validator allows to set ```data``` options via HTML attributes, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1017](https://github.com/formvalidation/formvalidation/pull/1017): Enable validator when setting ```data-bv-validatorname="data-bv-validatorname"```, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1026](https://github.com/formvalidation/formvalidation/issues/1026): Requires jQuery 1.9.1 or higher

__Bug Fixes__
* [#343](https://github.com/formvalidation/formvalidation/issues/343), [#481](https://github.com/formvalidation/formvalidation/issues/481), [#1045](https://github.com/formvalidation/formvalidation/pull/1045): Fix double submit with defered validators, thanks to [@jazzzz](https://github.com/jazzzz)
* [#933](https://github.com/formvalidation/formvalidation/issues/933), [#959](https://github.com/formvalidation/formvalidation/issues/959), [#1047](https://github.com/formvalidation/formvalidation/issues/1047): Tooltip/popover isn't destroyed when the field is valid
* [#991](https://github.com/formvalidation/formvalidation/issues/991): The field is validated only one time when setting ```trigger: 'blur'```, ```container: 'tooltip'```
* [#1014](https://github.com/formvalidation/formvalidation/pull/1014): Fix [isValidField()](http://formvalidation.io/api/#is-valid-field) and [validateField()](http://formvalidation.io/api/#validate-field) methods for fields without validators, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1050](https://github.com/formvalidation/formvalidation/issues/1050): Fix the issue when using multiple fields with same name, the tooltip of the last element is always shown
* [#1055](https://github.com/formvalidation/formvalidation/issues/1055), [#1063](https://github.com/formvalidation/formvalidation/pull/1063): The [error.field.bv](http://formvalidation.io/settings/#event-field) event isn't triggered if verbose is set to false, thanks to [@shineability](https://github.com/shineability)
* [#1057](https://github.com/formvalidation/formvalidation/issues/1057), [#1063](https://github.com/formvalidation/formvalidation/pull/1063): The [verbose](http://formvalidation.io/settings/#field-verbose) option for field doesn't override the form level, thanks to [@shineability](https://github.com/shineability)

__Document__
* [#848](https://github.com/formvalidation/formvalidation/pull/848): Update the [stringLength](http://formvalidation.io/validators/stringLength) document, thanks to [@Relequestual](https://github.com/Relequestual)
* [#885](https://github.com/formvalidation/formvalidation/issues/885): Add a notification about setting [identical](http://formvalidation.io/validators/identical/) validator for both fields
* [#912](https://github.com/formvalidation/formvalidation/issues/912): Add [Using language package](http://formvalidation.io/examples/using-language-package/) example
* [#920](https://github.com/formvalidation/formvalidation/issues/920), [#929](https://github.com/formvalidation/formvalidation/pull/929), [#936](https://github.com/formvalidation/formvalidation/pull/936): Update the [Changing the tooltip, popover's position](http://formvalidation.io/examples/tooltip-popover-position/) example, thanks to [@Arkni](https://github.com/Arkni)
* [#938](https://github.com/formvalidation/formvalidation/issues/938): Add [time](http://formvalidation.io/validators/regexp/#html-5-example) validator example
* [#979](https://github.com/formvalidation/formvalidation/issues/979): Add [Rails usage](http://formvalidation.io/validators/stringLength/#using-with-rails-form) for [stringLength](http://formvalidation.io/validators/stringLength/) validator
* [#1006](https://github.com/formvalidation/formvalidation/pull/1006): Fix the order of [parameters](http://formvalidation.io/settings/#validator-enabled) for [enableFieldValidators()](http://formvalidation.io/api/#enable-field-validators) method, thanks to [@mchrapka](https://github.com/mchrapka)
* [#1009](https://github.com/formvalidation/formvalidation/pull/1009): Fix mixed data/delay in [remote](http://formvalidation.io/validators/remote/) doc, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1019](https://github.com/formvalidation/formvalidation/pull/1019): Updated docs for added German [postal code](http://formvalidation.io/validators/zipCode/) and [phone number](http://formvalidation.io/validators/phone/) validators, thanks to [@jhadenfeldt](https://github.com/jhadenfeldt)
* [#1038](https://github.com/formvalidation/formvalidation/pull/1038): Fix [Changing tooltip, popover's position](http://formvalidation.io/examples/changing-tooltip-position/) example link, thanks to [@Arkni](https://github.com/Arkni)

__Language Packages__
* [#827](https://github.com/formvalidation/formvalidation/pull/827): Update Dutch language package, thanks to [@JvanderHeide](https://github.com/JvanderHeide)
* [#829](https://github.com/formvalidation/formvalidation/pull/829): Update Swedish language package, thanks to [@ulsa](https://github.com/ulsa)
* [#834](https://github.com/formvalidation/formvalidation/pull/834): Update Ukrainian and Russian language packages, thanks to [@oleg-voloshyn](https://github.com/oleg-voloshyn)
* [#835](https://github.com/formvalidation/formvalidation/pull/835): Update Belgium (French) language package, thanks to [@neilime](https://github.com/neilime)
* [#836](https://github.com/formvalidation/formvalidation/pull/836): Update French language package, thanks to [@neilime](https://github.com/neilime)
* [#837](https://github.com/formvalidation/formvalidation/pull/837): Update Bulgarian language package, thanks to [@mraiur](https://github.com/mraiur)
* [#846](https://github.com/formvalidation/formvalidation/pull/846): Update simplified Chinese language package, thanks to [@shamiao](https://github.com/shamiao)
* [#849](https://github.com/formvalidation/formvalidation/pull/849): Update Serbian language package, thanks to [@markocrni](https://github.com/markocrni)
* [#850](https://github.com/formvalidation/formvalidation/issues/850), [#851](https://github.com/formvalidation/formvalidation/pull/851): Update Danish language package, thanks to [@Djarnis](https://github.com/Djarnis)
* [#869](https://github.com/formvalidation/formvalidation/pull/869): Update Polish language package, thanks to [@grzesiek](https://github.com/grzesiek)
* [#870](https://github.com/formvalidation/formvalidation/pull/870): Update Traditional Chinese language package, thanks to [@tureki](https://github.com/tureki)
* [#871](https://github.com/formvalidation/formvalidation/pull/871): Update Czech language package, thanks to [@cuchac](https://github.com/cuchac)
* [#872](https://github.com/formvalidation/formvalidation/pull/872): Update Indonesian language package, thanks to [@egig](https://github.com/egig)
* [#879](https://github.com/formvalidation/formvalidation/pull/879): Update Romanian language package, thanks to [@filipac](https://github.com/filipac)
* [#880](https://github.com/formvalidation/formvalidation/pull/880): Update Belgium (Dutch) language package, thanks to [@dokterpasta](https://github.com/dokterpasta)
* [#881](https://github.com/formvalidation/formvalidation/pull/881): Update Italian language package, thanks to [@maramazza](https://github.com/maramazza)
* [#882](https://github.com/formvalidation/formvalidation/pull/882): Update Spanish language package, thanks to [@vadail](https://github.com/vadail)
* [#891](https://github.com/formvalidation/formvalidation/pull/891): Update Portuguese (Brazil) language package, thanks to [@dgmike](https://github.com/dgmike)
* [#893](https://github.com/formvalidation/formvalidation/pull/893): Fix country name of Dominican Republic, thanks to [@sventech](https://github.com/sventech)
* [#900](https://github.com/formvalidation/formvalidation/pull/900): Update Persian (Farsi) language package, thanks to [@i0](https://github.com/i0)
* [#903](https://github.com/formvalidation/formvalidation/pull/903): Update Hungarian language package, thanks to [@blackfyre](https://github.com/blackfyre)
* [#910](https://github.com/formvalidation/formvalidation/pull/910): Update Greek language package, thanks to [@pRieStaKos](https://github.com/pRieStaKos)
* [#913](https://github.com/formvalidation/formvalidation/pull/913): Update Thai language package, thanks to [@figgaro](https://github.com/figgaro)
* [#915](https://github.com/formvalidation/formvalidation/pull/915): Update Turkish language package, thanks to [@CeRBeR666](https://github.com/CeRBeR666)
* [#961](https://github.com/formvalidation/formvalidation/pull/961): Update Chilean Spanish language package, thanks to [@marceloampuerop6](https://github.com/marceloampuerop6)
* [#967](https://github.com/formvalidation/formvalidation/pull/967): Add Hebrew language package, thanks to [@yakidahan](https://github.com/yakidahan)
* [#974](https://github.com/formvalidation/formvalidation/pull/974): Add Albanian language package, thanks to [@desaretiuss](https://github.com/desaretiuss)
* [#1025](https://github.com/formvalidation/formvalidation/pull/1025): Fix French emailAddress message, thanks to [@jazzzz](https://github.com/jazzzz)
* [#1051](https://github.com/formvalidation/formvalidation/pull/1051): Add Portuguese language package, thanks to [@rtbfreitas](https://github.com/rtbfreitas)

## v0.5.2 (2014-09-25)

__New Features__
* [#480](https://github.com/formvalidation/formvalidation/pull/480): Add ```verbose``` option, thanks to [@mbezhanov](https://github.com/mbezhanov)
* [#542](https://github.com/formvalidation/formvalidation/issues/542), [#666](https://github.com/formvalidation/formvalidation/pull/666): Add blank validator, thanks to [@bermo](https://github.com/bermo)
* [#617](https://github.com/formvalidation/formvalidation/issues/617): Add ```init``` and ```destroy``` methods to validator
* [#724](https://github.com/formvalidation/formvalidation/pull/724): Add Venezuelan VAT number (RIF) validator, thanks to [@paquitodev](https://github.com/paquitodev)
* [#739](https://github.com/formvalidation/formvalidation/pull/739): Add China phone number validator, thanks to [@caijh](https://github.com/caijh)
* [#743](https://github.com/formvalidation/formvalidation/pull/743): Add Venezuela phone number validator, thanks to [@paquitodev](https://github.com/paquitodev)
* [#760](https://github.com/formvalidation/formvalidation/pull/760): Add Romania phone number validator, thanks to [@adrian-dks](https://github.com/adrian-dks)
* [#761](https://github.com/formvalidation/formvalidation/pull/761): Add Romania postal code validator, thanks to [@adrian-dks](https://github.com/adrian-dks)
* [#785](https://github.com/formvalidation/formvalidation/pull/785): Add Denmark phone number validator, thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#787](https://github.com/formvalidation/formvalidation/pull/787): Add Thailand phone number and ID validator, thanks to [@figgaro](https://github.com/figgaro)
* [#793](https://github.com/formvalidation/formvalidation/pull/793), [#798](https://github.com/formvalidation/formvalidation/pull/798): Add Chinese citizen ID validator, thanks to [@shamiao](https://github.com/shamiao)
* [#802](https://github.com/formvalidation/formvalidation/pull/802): Add Russia phone number validator, thanks to [@cylon-v](https://github.com/cylon-v). [#816](https://github.com/formvalidation/formvalidation/pull/816): Improved by [@stepin](https://github.com/stepin)
* [#816](https://github.com/formvalidation/formvalidation/pull/816): Add Russian postal code validator, thanks to [@stepin](https://github.com/stepin)
* [#867](https://github.com/formvalidation/formvalidation/pull/867): Add Czech and Slovakia phone number and postal code validators, thanks to [@cuchac](https://github.com/cuchac)

__Changes__
* [#753](https://github.com/formvalidation/formvalidation/issues/753): Change the default type of [remote](http://formvalidation.io/validators/remote/) validator to GET

__Improvements__
* [#249](https://github.com/formvalidation/formvalidation/pull/249), [#574](https://github.com/formvalidation/formvalidation/issues/574), [#669](https://github.com/formvalidation/formvalidation/issues/669): Add ```delay``` option to the [remote](http://formvalidation.io/validators/remote/) validator, thanks to [@q-state](https://github.com/q-state)
* [#345](https://github.com/formvalidation/formvalidation/issues/345), [#454](https://github.com/formvalidation/formvalidation/pull/454): The [different](http://formvalidation.io/validators/different/) validator allows more than a 2-way comparison, thanks to [@AlaskanShade](https://github.com/AlaskanShade)
* [#557](https://github.com/formvalidation/formvalidation/issues/557), [#569](https://github.com/formvalidation/formvalidation/pull/569): The [container](http://formvalidation.io/settings/#form-container) option can be defined by a callback, thanks to [@mattrick](https://github.com/mattrick)
* [#570](https://github.com/formvalidation/formvalidation/issues/570): Use CSS classes instead of inline styling to fix icons with ```input-group```, thanks to [@dlcrush](https://github.com/dlcrush)
* [#578](https://github.com/formvalidation/formvalidation/issues/578), [#813](https://github.com/formvalidation/formvalidation/pull/813): The [stringLength](http://formvalidation.io/validators/stringLength/) validator supports HTML 5 ```minlength``` attribute, thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#675](https://github.com/formvalidation/formvalidation/pull/675): The [emailAddress](http://formvalidation.io/validators/emailAddress/) validator accepts multiple email addresses, thanks to [@kenny-evitt](https://github.com/kenny-evitt)
* [#716](https://github.com/formvalidation/formvalidation/issues/716), [#765](https://github.com/formvalidation/formvalidation/issues/765): Reuse data returned by [callback](http://formvalidation.io/validators/callback/), [remote](http://formvalidation.io/validators/remote/), custom validators
* [#734](https://github.com/formvalidation/formvalidation/pull/734): The [uri](http://formvalidation.io/validators/uri/) validator adds support for custom protocol, thanks to [@bcamarneiro](https://github.com/bcamarneiro)
* [#737](https://github.com/formvalidation/formvalidation/issues/737): Support VAT number without prefixing by country code
* [#754](https://github.com/formvalidation/formvalidation/issues/754): Support latest Bootstrap when using tooltip/popover to show the message
* [#783](https://github.com/formvalidation/formvalidation/issues/783): Improve behaviour of the [different](http://formvalidation.io/validators/different/) validator
* [#792](https://github.com/formvalidation/formvalidation/pull/792): Add "BootstrapValidator's JavaScript requires jQuery" warning, thanks to [@Arkni](https://github.com/Arkni)
* [#803](https://github.com/formvalidation/formvalidation/pull/803): Add ```minSize``` option for the [file](http://formvalidation.io/validators/file/) validator, thanks to [@Arkni](https://github.com/Arkni)
* [#824](https://github.com/formvalidation/formvalidation/issues/824): Add [phone](http://formvalidation.io/validators/phone/) number validator test suite

__Bug Fixes__
* [#611](https://github.com/formvalidation/formvalidation/issues/611), [#703](https://github.com/formvalidation/formvalidation/issues/703): Tabs get red even form is valid
* [#612](https://github.com/formvalidation/formvalidation/issues/612), [#740](https://github.com/formvalidation/formvalidation/pull/740), [#741](https://github.com/formvalidation/formvalidation/pull/741): Fix the [emailAddress](http://formvalidation.io/validators/emailAddress/) issue which email@server is not valid email address, thanks to [@kromit](https://github.com/kromit)
* [#687](https://github.com/formvalidation/formvalidation/issues/687), [#711](https://github.com/formvalidation/formvalidation/pull/711): Keep disabled validators VALID, thanks to [@talberti](https://github.com/talberti)
* [#725](https://github.com/formvalidation/formvalidation/pull/725): Fix the issue when adding field which does not exist but is already set in "fields" option
* [#732](https://github.com/formvalidation/formvalidation/issues/732): Fix the issue when removing the radio or checkbox field
* [#746](https://github.com/formvalidation/formvalidation/issues/746), [#922](https://github.com/formvalidation/formvalidation/issues/922): The form is still submitted when clicking on submit button which is set ```onclick="return false;"```
* [#758](https://github.com/formvalidation/formvalidation/issues/758): Using [notEmpty](http://formvalidation.io/validators/notEmpty/) validator with ```type="number"```
* [#759](https://github.com/formvalidation/formvalidation/issues/759), [#764](https://github.com/formvalidation/formvalidation/pull/764): The tooltip/popover isn't shown if there is disabled validator.
The tooltip/popover is shown automatically when the field gets the focus, thanks to [@leedorian](https://github.com/leedorian)
* [#797](https://github.com/formvalidation/formvalidation/issues/797), [#799](https://github.com/formvalidation/formvalidation/pull/799): Can't validate ipv4 and ipv6 at the same time. Add ip validator test suite, thanks to [@Arkni](https://github.com/Arkni)
* [#816](https://github.com/formvalidation/formvalidation/pull/816): Fix Russian [VAT](http://formvalidation.io/validators/vat/) number validator, thanks to [@stepin](https://github.com/stepin)
* [#832](https://github.com/formvalidation/formvalidation/issues/832): The form won't be validated if the submit button contains a HTML tag

__Document__
* [#709](https://github.com/formvalidation/formvalidation/issues/709), [#715](https://github.com/formvalidation/formvalidation/pull/715): Add [Bootstrap Select](http://formvalidation.io/examples/bootstrap-select/) and [Select2](http://formvalidation.io/examples/select2/) examples, thanks to [@Arkni](https://github.com/Arkni)
* [#855](https://github.com/formvalidation/formvalidation/issues/855), [#858](https://github.com/formvalidation/formvalidation/pull/858): Add [TinyMCE](http://formvalidation.io/examples/tinymce/) example, thanks to [@Arkni](https://github.com/Arkni)
* [#859](https://github.com/formvalidation/formvalidation/issues/859), [#862](https://github.com/formvalidation/formvalidation/issues/862), [#865](https://github.com/formvalidation/formvalidation/pull/865): Add [Changing tooltip/popover position](http://formvalidation.io/examples/tooltip-popover-position/) example, thanks to [@Arkni](https://github.com/Arkni)

__Language Packages__
* [#706](https://github.com/formvalidation/formvalidation/pull/706): Japanese language package, thanks to [@tsuyoshifujii](https://github.com/tsuyoshifujii)
* [#712](https://github.com/formvalidation/formvalidation/pull/712): Swedish language package, thanks to [@ulsa](https://github.com/ulsa)
* [#727](https://github.com/formvalidation/formvalidation/pull/727): Belgium (French) language package, thanks to [@neilime](https://github.com/neilime)
* [#729](https://github.com/formvalidation/formvalidation/pull/729): Persian (Farsi) language package, thanks to [@i0](https://github.com/i0)
* [#779](https://github.com/formvalidation/formvalidation/pull/779): Romanian language package, thanks to [@filipac](https://github.com/filipac)
* [#787](https://github.com/formvalidation/formvalidation/pull/787): Thai language package, thanks to [@figgaro](https://github.com/figgaro)
* [#788](https://github.com/formvalidation/formvalidation/pull/788): Fully re-translated Simplified Chinese language package, thanks to [@shamiao](https://github.com/shamiao)
* [#795](https://github.com/formvalidation/formvalidation/pull/795): Re-translated traditional Chinese language package, thanks to [@tureki](https://github.com/tureki)
* [#802](https://github.com/formvalidation/formvalidation/pull/802): Russian language package, thanks to [@cylon-v](https://github.com/cylon-v). [#816](https://github.com/formvalidation/formvalidation/pull/816): Improved by [@stepin](https://github.com/stepin)
* [#806](https://github.com/formvalidation/formvalidation/pull/806): Ukrainian language package, thanks to [@oleg-voloshyn](https://github.com/oleg-voloshyn)
* [#840](https://github.com/formvalidation/formvalidation/pull/840): Serbian language package, thanks to [@markocrni](https://github.com/markocrni)
* [#856](https://github.com/formvalidation/formvalidation/pull/856): Norwegian language package, thanks to [@trondulseth](https://github.com/trondulseth)
* [#868](https://github.com/formvalidation/formvalidation/pull/868): Indonesian language package, thanks to [@egig](https://github.com/egig)

## v0.5.1 (2014-08-22)

__New Features__
* [#218](https://github.com/formvalidation/formvalidation/issues/218), [#531](https://github.com/formvalidation/formvalidation/pull/531): Add meid validator, thanks to [@troymccabe](https://github.com/troymccabe)
* [#267](https://github.com/formvalidation/formvalidation/issues/267), [#532](https://github.com/formvalidation/formvalidation/pull/532): Add imo validator, thanks to [@troymccabe](https://github.com/troymccabe)
* [#510](https://github.com/formvalidation/formvalidation/pull/510), [#646](https://github.com/formvalidation/formvalidation/pull/646): Add French [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@dlucazeau](https://github.com/dlucazeau)
* [#536](https://github.com/formvalidation/formvalidation/pull/536): Add Spanish [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@vadail](https://github.com/vadail)
* [#519](https://github.com/formvalidation/formvalidation/pull/519): Add Iceland [VAT](http://formvalidation.io/validators/vat/) number validator, thanks to [@evilchili](https://github.com/evilchili)
* [#620](https://github.com/formvalidation/formvalidation/issues/620), [#621](https://github.com/formvalidation/formvalidation/pull/621): Add Pakistan [phone number](http://formvalidation.io/validators/phone/) validator, thanks to [@abuzer](https://github.com/abuzer)
* [#630](https://github.com/formvalidation/formvalidation/issues/630), [#640](https://github.com/formvalidation/formvalidation/pull/640): Add event name options to avoid ```window.onerror``` being invoked by jQuery, thanks to [@roryprimrose](https://github.com/roryprimrose). Thanks to [@stephengreentree](https://github.com/stephengreentree) for creating the test suite ([#657](https://github.com/formvalidation/formvalidation/pull/657))
* [#637](https://github.com/formvalidation/formvalidation/pull/637): Add South African [VAT](http://formvalidation.io/validators/vat/) number validator, thanks to [@evilchili](https://github.com/evilchili)
* [#638](https://github.com/formvalidation/formvalidation/pull/638), [#647](https://github.com/formvalidation/formvalidation/pull/647): Add Brazilian [phone number](http://formvalidation.io/validators/phone/) and [postal code](http://formvalidation.io/validators/zipCode/) validator, thanks to [@fhferreira](https://github.com/fhferreira)
* [#643](https://github.com/formvalidation/formvalidation/pull/643): Add [zipCode](http://formvalidation.io/validators/zipCode/) and [phone number](http://formvalidation.io/validators/phone/) validators for Morocco, thanks to [@Arkni](https://github.com/Arkni)
* [#650](https://github.com/formvalidation/formvalidation/pull/650): Add Brazilian [VAT](http://formvalidation.io/validators/vat/) number validator, thanks to [@fhferreira](https://github.com/fhferreira)

__Improvements__
* [#502](https://github.com/formvalidation/formvalidation/pull/502): Allowing sites without TLD to pass URI validation, thanks to [@troymccabe](https://github.com/troymccabe)
* [#549](https://github.com/formvalidation/formvalidation/pull/549), [#600](https://github.com/formvalidation/formvalidation/pull/600): Change the CSS/JS path in ```demo/remote.html``` and ```demo/message.html```, thanks to [@leegtang](https://github.com/leegtang), [@Arkni](https://github.com/Arkni)
* [#604](https://github.com/formvalidation/formvalidation/pull/604): Fix the ```demo/date.html``` and ```demo/tab.html``` examples, thanks to [@Arkni](https://github.com/Arkni)
* [#609](https://github.com/formvalidation/formvalidation/pull/609): Add content-type header for ```demo/remote.php```, thanks to [@etorres](https://github.com/etorres)
* [#661](https://github.com/formvalidation/formvalidation/pull/661): Add ```headers``` option to the [remote](http://formvalidation.io/validators/remote/) validator, thanks to [@ryan2049](https://github.com/ryan2049)
* [#664](https://github.com/formvalidation/formvalidation/issues/664): Fix the feedback icon position for Bootstrap 3.2
* [#683](https://github.com/formvalidation/formvalidation/issues/683): Force the format option to be ```YYYY-MM-DD``` when using ```<input type="date" />```
* [#698](https://github.com/formvalidation/formvalidation/issues/698): Ignore type checking if the file type is empty

__Bug Fixes__
* [#284](https://github.com/formvalidation/formvalidation/issues/284), [#294](https://github.com/formvalidation/formvalidation/issues/294), [#441](https://github.com/formvalidation/formvalidation/issues/441), [#516](https://github.com/formvalidation/formvalidation/issues/516), [#580](https://github.com/formvalidation/formvalidation/issues/580): The HTML 5 ```<input type="number" />``` input allows to input non-digits characters
* [#548](https://github.com/formvalidation/formvalidation/issues/548): Fix the issue when using [different](http://formvalidation.io/validators/different/) validator to compare with not existing field
* [#550](https://github.com/formvalidation/formvalidation/issues/550), [#551](https://github.com/formvalidation/formvalidation/pull/551): Cannot validate against both ipv4 and ipv6 at the same time, thanks to [@beeglebug](https://github.com/beeglebug)
* [#588](https://github.com/formvalidation/formvalidation/issues/588): Don't use min, max attributes (greaterThan, lessThan validators) for ```<input type="date" />```
* [#665](https://github.com/formvalidation/formvalidation/issues/665): The [submitButtons](http://formvalidation.io/settings/#form-submit-buttons) option doesn't work correctly
* [#672](https://github.com/formvalidation/formvalidation/issues/672): The [zipCode](http://formvalidation.io/validators/zipCode/) validator throw an exception when passing not supported country code
* [#681](https://github.com/formvalidation/formvalidation/issues/681): Fix the [date](http://formvalidation.io/validators/date/) validator issue where one of date/month/year or hours/minutes/seconds is prefixed by zero
* [#692](https://github.com/formvalidation/formvalidation/issues/692): The [remote](http://formvalidation.io/validators/remote/) validator can't set the type option via HTML attribute
* [#700](https://github.com/formvalidation/formvalidation/issues/700): The [between](http://formvalidation.io/validators/between/), [greaterThan](http://formvalidation.io/validators/greaterThan/), [lessThan](http://formvalidation.io/validators/lessThan/) validators accept param which isn't number

__Language Packages__
* [#400](https://github.com/formvalidation/formvalidation/pull/400): Italian language package, thanks to [@maramazza](https://github.com/maramazza)
* [#503](https://github.com/formvalidation/formvalidation/pull/503): French language package, thanks to [@dlucazeau](https://github.com/dlucazeau)
* [#505](https://github.com/formvalidation/formvalidation/pull/505): Czech language package, thanks to [@AdwinTrave](https://github.com/AdwinTrave)
* [#507](https://github.com/formvalidation/formvalidation/pull/507): Polish language package, thanks to [@grzesiek](https://github.com/grzesiek). [#624](https://github.com/formvalidation/formvalidation/pull/624): Typos fixed by [@lukaszbanasiak](https://github.com/lukaszbanasiak)
* [#517](https://github.com/formvalidation/formvalidation/pull/517): Belgium (Dutch) language package, thanks to [@dokterpasta](https://github.com/dokterpasta)
* [#527](https://github.com/formvalidation/formvalidation/pull/527): Bulgarian language package, thanks to [@mraiur](https://github.com/mraiur)
* [#534](https://github.com/formvalidation/formvalidation/pull/534): Turkish language package, thanks to [@CeRBeR666](https://github.com/CeRBeR666)
* [#536](https://github.com/formvalidation/formvalidation/pull/536): Spanish language package, thanks to [@vadail](https://github.com/vadail)
* [#544](https://github.com/formvalidation/formvalidation/pull/544): Greek language package, thanks to [@pRieStaKos](https://github.com/pRieStaKos)
* [#545](https://github.com/formvalidation/formvalidation/pull/545): Portuguese (Brazil) language package, thanks to [@marcuscarvalho6](https://github.com/marcuscarvalho6)
* [#598](https://github.com/formvalidation/formvalidation/pull/598): Danish language package, thanks to [@Djarnis](https://github.com/Djarnis)
* [#674](https://github.com/formvalidation/formvalidation/pull/674), [#677](https://github.com/formvalidation/formvalidation/pull/677): Dutch language package, thanks to [@jvanderheide](https://github.com/jvanderheide)
* [#679](https://github.com/formvalidation/formvalidation/pull/679): Add Arabic language package, thanks to [@Arkni](https://github.com/Arkni)

## v0.5.0 (2014-07-14)

__New Features__
* [#2](https://github.com/formvalidation/formvalidation/issues/2), [#387](https://github.com/formvalidation/formvalidation/issues/387): Provide the default error messages
* [#93](https://github.com/formvalidation/formvalidation/issues/93), [#385](https://github.com/formvalidation/formvalidation/issues/385): Support translating error messages. Provide the Vietnamese language file
* [#121](https://github.com/formvalidation/formvalidation/issues/121): Add events for form validate successfully or not
* [#125](https://github.com/formvalidation/formvalidation/issues/125): Support dynamic fields
* [#130](https://github.com/formvalidation/formvalidation/pull/130): Add ```addField()``` and ```removeField()``` methods for managing dynamic fields, thanks to [@jcnmulio](https://github.com/jcnmulio)
* [#164](https://github.com/formvalidation/formvalidation/issues/164): Add ```container``` option for indicating the element showing all errors
* [#175](https://github.com/formvalidation/formvalidation/issues/175): Showing errors in tooltip or popover
* [#195](https://github.com/formvalidation/formvalidation/issues/195): Add events for field validation
* [#211](https://github.com/formvalidation/formvalidation/issues/211), [#235](https://github.com/formvalidation/formvalidation/issues/235): Add new method ```getInvalidFields()``` that returns all invalid fields
* [#275](https://github.com/formvalidation/formvalidation/issues/275): Add ```destroy()``` method
* [#282](https://github.com/formvalidation/formvalidation/issues/282), [#347](https://github.com/formvalidation/formvalidation/issues/347): Use error message that is returned from [callback](http://formvalidation.io/validators/callback/), [remote](http://formvalidation.io/validators/remote/) validators
* Add ```status.field.bv``` event which is triggered after updating the field status. It can be used to solve [#300](https://github.com/formvalidation/formvalidation/issues/300), [#301](https://github.com/formvalidation/formvalidation/issues/301)
* [#316](https://github.com/formvalidation/formvalidation/issues/316): Add ```isValidContainer(container)``` method
* [#320](https://github.com/formvalidation/formvalidation/issues/320): Add ```separator``` option to the [date validator](http://formvalidation.io/validators/date/)
* [#323](https://github.com/formvalidation/formvalidation/issues/323): Add ```isValidField(field)``` method
* [#324](https://github.com/formvalidation/formvalidation/issues/324): Add ```success.validator.bv``` and ```error.validator.bv``` events triggered after a validator completes
* [#332](https://github.com/formvalidation/formvalidation/pull/332): Add UK phone number support for the [phone validator](http://formvalidation.io/validators/phone/), thanks to [@aca02djr](https://github.com/aca02djr)
* [#336](https://github.com/formvalidation/formvalidation/issues/336): Add ```$field``` instance to the [callback validator](http://formvalidation.io/validators/callback/)
* [#356](https://github.com/formvalidation/formvalidation/issues/356): Add ```group``` option
* [#374](https://github.com/formvalidation/formvalidation/pull/374): Add Singapore postal code to the [zipCode validator](http://formvalidation.io/validators/zipCode/), thanks to [@thisisclement](https://github.com/thisisclement)
* [#406](https://github.com/formvalidation/formvalidation/issues/406): Add ```revalidateField(field)``` method
* [#433](https://github.com/formvalidation/formvalidation/issues/433): Add ```resetField(field, resetValue)``` method
* [#434](https://github.com/formvalidation/formvalidation/issues/434): Add ```updateMessage(field, validator, message)``` method

__Changes__
* [#42](https://github.com/formvalidation/formvalidation/issues/42): Remove the submit button from ```submitHandler()```. You can use new ```getSubmitButton()``` method to get the clicked submit button
* [#109](https://github.com/formvalidation/formvalidation/issues/109): Remove the ```setLiveMode()``` method
* ```FormValidator.Helper``` renames ```mod_11_10``` to ```mod11And10```, ```mod_37_36``` to ```mod37And36```
* Remove ```submitHandler()``` option. Use ```success.form.bv``` event instead:

_v0.4.5 and earlier versions_
```javascript
$(form).bootstrapValidator({
    submitHandler: function(form, validator, submitButton) {
        ...
    }
});
```

_v0.5.0_
Using ```success.form.bv``` event:

```javascript
$(form)
    .bootstrapValidator(options)
    .on('success.form.bv', function(e) {
        // Prevent form submission
        e.preventDefault();

        var $form        = $(e.target),
            validator    = $form.data('bootstrapValidator'),
            submitButton = validator.getSubmitButton();

        // Do whatever you want here ...
    });
```

__Improvements__
* [#244](https://github.com/formvalidation/formvalidation/pull/244): Only enable the submit buttons if all fields are valid, thanks to [@smeagol74](https://github.com/smeagol74)
* [#262](https://github.com/formvalidation/formvalidation/issues/262): Improve the [```updateStatus()``` method](http://formvalidation.io/api/#update-status). The plugin now doesn't show the errors, feedback icons of given field if there are uncompleted validators
* [#274](https://github.com/formvalidation/formvalidation/pull/274): Fix feedback icons in ```input-group```, thanks to [@tiagofontella](https://github.com/tiagofontella)
* [#287](https://github.com/formvalidation/formvalidation/issues/287), [#291](https://github.com/formvalidation/formvalidation/issues/291): Only send the submit button which is clicked. It's an enhancement for [#238](https://github.com/formvalidation/formvalidation/issues/238)
* [#297](https://github.com/formvalidation/formvalidation/issues/297): Disable feedback icons for particular fields
* [#348](https://github.com/formvalidation/formvalidation/issues/348): The [uri validator](http://formvalidation.io/validators/uri/) now provides an option to support private/local network address
* [#364](https://github.com/formvalidation/formvalidation/issues/364): Clicking the feedback icon also effect to the checkbox, radio fields
* [#366](https://github.com/formvalidation/formvalidation/issues/366): Don't change the enable setting when the new one is the same
* [#371](https://github.com/formvalidation/formvalidation/pull/371): Add H character to the Canadian postcode, thanks to [@jzhang6](https://github.com/jzhang6)
* [#382](https://github.com/formvalidation/formvalidation/issues/382): Add JSHint to Grunt build
* [#388](https://github.com/formvalidation/formvalidation/issues/388): Allow to override the default options. Useful for using multiple forms in the same page
* [#393](https://github.com/formvalidation/formvalidation/pull/393): The [remote validator](http://formvalidation.io/validators/remote/) adds support for dynamic ```url``` and method type (GET/POST), thanks to [@ericnakagawa](https://github.com/ericnakagawa)
* [#416](https://github.com/formvalidation/formvalidation/issues/416), [#448](https://github.com/formvalidation/formvalidation/pull/448): Add ```updateOption()``` method for updating the particular validator option, thanks to [@AlaskanShade](https://github.com/AlaskanShade)
* [#420](https://github.com/formvalidation/formvalidation/issues/420): Enable/disable particular validator
* [#422](https://github.com/formvalidation/formvalidation/issues/422): Exclude particular field by ```excluded``` option or ```data-bv-excluded``` attribute
* [#426](https://github.com/formvalidation/formvalidation/issues/426): Add test suite
* [#430](https://github.com/formvalidation/formvalidation/issues/430): [between](http://formvalidation.io/validators/between/), [greaterThan](http://formvalidation.io/validators/greaterThan/), [lessThan](http://formvalidation.io/validators/lessThan/) add support for comparing to other field, return value of a callback function
* [#431](https://github.com/formvalidation/formvalidation/issues/431): Add built time to the build file
* [#432](https://github.com/formvalidation/formvalidation/issues/432): Define the callback via ```data-bv-callback-callback``` attribute
* [#447](https://github.com/formvalidation/formvalidation/pull/447): [zipCode validator](http://formvalidation.io/validators/zipCode/) allow to set the country code via another field or callback, thanks to [@AlaskanShade](https://github.com/AlaskanShade)
* [#451](https://github.com/formvalidation/formvalidation/pull/451): Validation of numeric fields with decimal steps, thanks to [@Azuka](https://github.com/Azuka)
* [#456](https://github.com/formvalidation/formvalidation/issues/456): Adjust the feedback icon position for ```.input-group``` element
* [#465](https://github.com/formvalidation/formvalidation/issues/465): Support dynamic message

__Bug Fixes__
* [#288](https://github.com/formvalidation/formvalidation/issues/288): Fix [date validator](http://formvalidation.io/validators/date/) issue on IE8
* [#292](https://github.com/formvalidation/formvalidation/pull/292): Fix identical validator issue with not clearing ```has-error``` class, thanks to [@alavers](https://github.com/alavers)
* [#305](https://github.com/formvalidation/formvalidation/pull/305), [#306](https://github.com/formvalidation/formvalidation/pull/306), [#307](https://github.com/formvalidation/formvalidation/pull/307): Fix ```inclusive``` option in the [between](http://formvalidation.io/validators/between/), [greaterThan](http://formvalidation.io/validators/greaterThan/) and [lessThan](http://formvalidation.io/validators/lessThan/) validators, thanks to [@johanronn77](https://github.com/johanronn77)
* [#310](https://github.com/formvalidation/formvalidation/issues/310), [#475](https://github.com/formvalidation/formvalidation/issues/475): The [date validator](http://formvalidation.io/validators/date/) still return valid if the value doesn't contain digits
* [#311](https://github.com/formvalidation/formvalidation/issues/311): file validation extension is case sensitive
* [#312](https://github.com/formvalidation/formvalidation/pull/312): Fix broacast typo in the [uri validator](http://formvalidation.io/validators/uri/), thanks to [@mrpollo](https://github.com/mrpollo)
* [#313](https://github.com/formvalidation/formvalidation/issues/313): Fix the [file validator](http://formvalidation.io/validators/file/) issue on IE 8
* [#314](https://github.com/formvalidation/formvalidation/issues/314): The [creditCard validator](http://formvalidation.io/validators/creditCard/) doesn't work on IE 8
* [#315](https://github.com/formvalidation/formvalidation/issues/315): The [cvv validator](http://formvalidation.io/validators/cvv/) doesn't work on IE 8
* [#325](https://github.com/formvalidation/formvalidation/issues/325): The [```threshold``` option](http://formvalidation.io/settings/#threshold) doesn't work on IE 8
* [#358](https://github.com/formvalidation/formvalidation/issues/358): The [zipCode validator](http://formvalidation.io/validators/zipCode/) doesn't work for Canadian zip code
* [#375](https://github.com/formvalidation/formvalidation/issues/375): Don't submit form when the [callback validator](http://formvalidation.io/validators/callback/) completes and the submit button isn't clicked
* [#377](https://github.com/formvalidation/formvalidation/issues/377): The [id](http://formvalidation.io/validators/id/), [vat](http://formvalidation.io/validators/vat/) validators should return ```false``` if the country code is not supported
* [#389](https://github.com/formvalidation/formvalidation/issues/389): When using multiple forms with HTML attributes on the same page, the plugin options will be the same as the last one
* [#401](https://github.com/formvalidation/formvalidation/issues/401): [stringLength validator](http://formvalidation.io/validators/stringLength/) allows spaces after max length
* [#411](https://github.com/formvalidation/formvalidation/pull/411): Fix the [ean validator](http://formvalidation.io/validators/ean/) when the check digit is zero, thanks to [@manish-in-java](https://github.com/manish-in-java)
* [#417](https://github.com/formvalidation/formvalidation/issues/417): IPv6 validator doesn't work
* [#425](https://github.com/formvalidation/formvalidation/issues/425): Custom trigger event is ignored by field validators
* [#447](https://github.com/formvalidation/formvalidation/pull/447): Skip the ```_isExcluded()``` when initializing the form. This fixes [#269](https://github.com/formvalidation/formvalidation/issues/269), [#273](https://github.com/formvalidation/formvalidation/issues/273). Thanks to [@AlaskanShade](https://github.com/AlaskanShade)
* [#483](https://github.com/formvalidation/formvalidation/issues/483), [#487](https://github.com/formvalidation/formvalidation/pull/487): Added the letters 'W' and 'Z' in the second and third letter list for Canada postal code, thanks to [@jzhang6](https://github.com/jzhang6)
* [#492](https://github.com/formvalidation/formvalidation/issues/492), [#493](https://github.com/formvalidation/formvalidation/pull/493): Fixed Chilean ID (RUT/RUN) finished in 'K' or 'k', thanks to [@marceloampuerop6](https://github.com/marceloampuerop6)

__Document__
* [#259](https://github.com/formvalidation/formvalidation/issues/259): Typo "Support almost Bootstrap forms", thanks to [@lloydde](https://github.com/lloydde)
* [#261](https://github.com/formvalidation/formvalidation/pull/261): English fix to 'amazing contributors' section, thanks to [@lloydde](https://github.com/lloydde)
* [#278](https://github.com/formvalidation/formvalidation/pull/278): Update the [choice validator](http://formvalidation.io/validators/choice/) document, thanks to [@MrC0mm0n](https://github.com/MrC0mm0n)
* [#303](https://github.com/formvalidation/formvalidation/pull/303): Fix typo in [remote validator](http://formvalidation.io/validators/remote/) document, thanks to [@MartinDevillers](https://github.com/MartinDevillers)
* [#334](https://github.com/formvalidation/formvalidation/pull/334): No ID is specified on the form object for registration, thanks to [@jjshoe](https://github.com/jjshoe)
* [#423](https://github.com/formvalidation/formvalidation/pull/423): Add default column to settings table, thanks to [@MartinDevillers](https://github.com/MartinDevillers)
* [#452](https://github.com/formvalidation/formvalidation/pull/452): Update 'United State' to 'United States', thanks to [@mike1e](https://github.com/mike1e)

__Language Packages__
* [#396](https://github.com/formvalidation/formvalidation/pull/396): German language package, thanks to [@logemann](https://github.com/logemann)
* [#474](https://github.com/formvalidation/formvalidation/pull/474): Hungarian language package, thanks to [@blackfyre](https://github.com/blackfyre)
* [#478](https://github.com/formvalidation/formvalidation/pull/478): Simplified and traditional Chinese language package, thanks to [@tureki](https://github.com/tureki)
* [#494](https://github.com/formvalidation/formvalidation/pull/494): Chilean Spanish language package, thanks to [@marceloampuerop6](https://github.com/marceloampuerop6)

## v0.4.5 (2014-05-15)

* Add ```FormValidator.Helper.date``` for validating a date, re-used in [date](http://formvalidation.io/validators/date/), [id](http://formvalidation.io/validators/id/), [vat](http://formvalidation.io/validators/vat/) validators
* [#233](https://github.com/formvalidation/formvalidation/issues/233): Add ```threshold``` option
* [#232](https://github.com/formvalidation/formvalidation/issues/232): Add [id validator](http://formvalidation.io/validators/id/)
* [#242](https://github.com/formvalidation/formvalidation/issues/242): Add ```separator``` option to the [numeric validator](http://formvalidation.io/validators/numeric/)
* [#248](https://github.com/formvalidation/formvalidation/issues/248): Add [isin (International Securities Identification Number) validator](http://formvalidation.io/validators/issn/)
* [#250](https://github.com/formvalidation/formvalidation/issues/250): Add [rtn (Routing transit number) validator](http://formvalidation.io/validators/rtn/)
* [#251](https://github.com/formvalidation/formvalidation/issues/251): Add [cusip (North American Securities) validator](http://formvalidation.io/validators/cusip/)
* [#252](https://github.com/formvalidation/formvalidation/issues/252): Add [sedol (Stock Exchange Daily Official List) validator](http://formvalidation.io/validators/sedol/)
* The [zipCode validator](http://formvalidation.io/validators/zipCode/) adds support for Italian, Dutch postcodes
* [#245](https://github.com/formvalidation/formvalidation/pull/245): The [cvv validator](http://formvalidation.io/validators/cvv/) should support spaces in credit card, thanks to [@evilchili](https://github.com/evilchili)
* Change default ```submitButtons``` to ```[type="submit"]``` to support ```input type="submit"```
* [#226](https://github.com/formvalidation/formvalidation/issues/226): Fix the conflict issue with MooTools
* [#238](https://github.com/formvalidation/formvalidation/issues/238): The submit buttons are not sent
* [#253](https://github.com/formvalidation/formvalidation/issues/253): The [iban validator](http://formvalidation.io/validators/iban/) does not work on IE8
* [#257](https://github.com/formvalidation/formvalidation/issues/257): Plugin method invocation don't work
* Fix the issue that the hidden fields generated by other plugins might not be validated
* When parsing options from HTML attributes, don't add the field which hasn't validators. It improves fixes for [#191](https://github.com/formvalidation/formvalidation/issues/191), [#223](https://github.com/formvalidation/formvalidation/issues/223)

## v0.4.4 (2014-05-05)

* Add ```FormValidator.Helper.mod_11_10``` method that implements modulus 11, 10 (ISO 7064) algorithm. The helper is then reused in validating [German and Croatian VAT](http://formvalidation.io/validators/vat/) numbers
* Add ```FormValidator.Helper.mod_37_36``` method that implements modulus 37, 36 (ISO 7064) algorithm, used in [GRid validator](http://formvalidation.io/validators/grid/)
* [#213](https://github.com/formvalidation/formvalidation/issues/213): Add [EAN (International Article Number) validator](http://formvalidation.io/validators/ean/)
* [#214](https://github.com/formvalidation/formvalidation/issues/214): Add [GRId (Global Release Identifier) validator](http://formvalidation.io/validators/grid/)
* [#215](https://github.com/formvalidation/formvalidation/issues/215): Add [IMEI (International Mobile Station Equipment Identity) validator](http://formvalidation.io/validators/imei/)
* [#216](https://github.com/formvalidation/formvalidation/issues/216): Add [ISMN (International Standard Music Number) validator](http://formvalidation.io/validators/ismn/)
* [#217](https://github.com/formvalidation/formvalidation/issues/217): Add [ISSN (International Standard Serial Number) validator](http://formvalidation.io/validators/issn/)
* [#191](https://github.com/formvalidation/formvalidation/issues/191), [#223](https://github.com/formvalidation/formvalidation/issues/223): Support using both the ```name``` attribute and ```selector``` option for field
* [#206](https://github.com/formvalidation/formvalidation/issues/206): Indicate success/error tab
* [#220](https://github.com/formvalidation/formvalidation/issues/220): Add UK postcode support for the [zipCode validator](http://formvalidation.io/validators/zipCode/)
* [#229](https://github.com/formvalidation/formvalidation/issues/229): The [date validator](http://formvalidation.io/validators/date/) supports seconds
* [#231](https://github.com/formvalidation/formvalidation/issues/231): Wrong prefix of Laser [credit card](http://formvalidation.io/validators/creditCard/) number

## v0.4.3 (2014-04-26)

* Add ```FormValidator.Helper.luhn``` method that implements the Luhn algorithm
* [#77](https://github.com/formvalidation/formvalidation/issues/77): Add [file validator](http://formvalidation.io/validators/file/)
* [#179](https://github.com/formvalidation/formvalidation/issues/179): Add [vat validator](http://formvalidation.io/validators/vat/), support 32 countries
* [#198](https://github.com/formvalidation/formvalidation/pull/198), [#199](https://github.com/formvalidation/formvalidation/pull/199): Add Canadian Postal Code support for the [zipCode validator](http://formvalidation.io/validators/zipCode/), thanks to [@Francismori7](https://github.com/Francismori7)
* [#201](https://github.com/formvalidation/formvalidation/issues/201): The [choice validator](http://formvalidation.io/validators/choice/) supports ```select``` element
* [#202](https://github.com/formvalidation/formvalidation/issues/202): Activate tab containing the first invalid field
* [#205](https://github.com/formvalidation/formvalidation/issues/205): Plugin method invocation
* [#207](https://github.com/formvalidation/formvalidation/issues/207): IE8 error. The field is only validated when its value is changed. It also fixes [#153](https://github.com/formvalidation/formvalidation/issues/153), [#193](https://github.com/formvalidation/formvalidation/issues/193), [#197](https://github.com/formvalidation/formvalidation/issues/197)
* [#209](https://github.com/formvalidation/formvalidation/issues/209): The [```excluded: ':disabled'``` setting](http://formvalidation.io/settings/#excluded) does not work on IE 8, thanks to [@adgrafik](https://github.com/adgrafik)
* [#210](https://github.com/formvalidation/formvalidation/issues/210): The [isbn validator](http://formvalidation.io/validators/isbn/) accepts letters and special characters

## v0.4.2 (2014-04-19)

* [#168](https://github.com/formvalidation/formvalidation/pull/168): Add [siren](http://formvalidation.io/validators/siren/) and [siret](http://formvalidation.io/validators/siret/) validators, thanks to [@jswale](https://github.com/jswale)
* [#177](https://github.com/formvalidation/formvalidation/issues/177): Add [Vehicle Identification Number (VIN) validator](http://formvalidation.io/validators/vin/)
* [#184](https://github.com/formvalidation/formvalidation/issues/184): Add [```excluded``` option](http://formvalidation.io/settings/#excluded)
* [#171](https://github.com/formvalidation/formvalidation/pull/171): The [phone validator](http://formvalidation.io/validators/phone/) now supports +1 country code and area code for US phone number, thanks to [@tomByrer](https://github.com/tomByrer)
* [#173](https://github.com/formvalidation/formvalidation/pull/173): The [remote validator](http://formvalidation.io/validators/remote/) allows to override ```name``` option, thanks to [@jswale](https://github.com/jswale)
* [#178](https://github.com/formvalidation/formvalidation/pull/178): Do not validate fields that ```enabled``` is set to ```false```, thanks to [@henningda](https://github.com/henningda)
* [#182](https://github.com/formvalidation/formvalidation/pull/182): Improve [zipCode validator](http://formvalidation.io/validators/zipCode/), thanks to [@gercheq](https://github.com/gercheq)
* [#169](https://github.com/formvalidation/formvalidation/pull/169): Better to say: ```{validatorname}``` and ```{validatoroption}``` must be lowercase, thanks to [@tomByrer](https://github.com/tomByrer)

## v0.4.1 (2014-04-12)

* [#144](https://github.com/formvalidation/formvalidation/issues/144), [#158](https://github.com/formvalidation/formvalidation/issues/158): Fixed an issue that the custom submit handler is not fired from the second time
* [#106](https://github.com/formvalidation/formvalidation/issues/106): Prevent the [```validate()```](http://formvalidation.io/api/#validate) method from submit the form automatically. So we can call ```validate()``` to validate the form
* [#131](https://github.com/formvalidation/formvalidation/issues/131): Doesn't trigger validation on the first focus
* [#145](https://github.com/formvalidation/formvalidation/issues/145): The row state is now only marked as success if all fields on it are valid
* [#157](https://github.com/formvalidation/formvalidation/issues/157): Added support for element outside of form using the [```selector```](http://formvalidation.io/settings/#fields) option
* [#159](https://github.com/formvalidation/formvalidation/issues/159), [#163](https://github.com/formvalidation/formvalidation/pull/163): User doesn't need to submit the form twice when remote validator complete, thanks to [@jswale](https://github.com/jswale)
* [#162](https://github.com/formvalidation/formvalidation/pull/162): Fix errors in IE 8, thanks to [@adgrafik](https://github.com/adgrafik)
* [#166](https://github.com/formvalidation/formvalidation/issues/166), [#167](https://github.com/formvalidation/formvalidation/pull/167): The [phone validator](http://formvalidation.io/validators/phone/) now also checks the length of US phone number, thanks to [@gercheq](https://github.com/gercheq)

## v0.4.0 (2014-04-03)

* [#14](https://github.com/formvalidation/formvalidation/issues/14), [#57](https://github.com/formvalidation/formvalidation/issues/57): Set validator option by using [HTML 5 attributes](http://formvalidation.io/examples/#attribute)

Form attributes:

```html
<form
    data-bv-message="This value is not valid"
    data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
    data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
    data-bv-feedbackicons-validating="glyphicon glyphicon-refresh"
    >
```

Field attributes:

```html
<input type="text" class="form-control" name="username"
    data-bv-message="The username is not valid"
    data-bv-notempty data-bv-notempty-message="The username is required and cannot be empty"
    data-bv-stringlength="true" data-bv-stringlength-min="6" data-bv-stringlength-max="30" data-bv-stringlength-message="The username must be more than 6 and less than 30 characters long"
    data-bv-different="true" data-bv-different-field="password" data-bv-different-message="The username and password cannot be the same as each other"
    data-bv-remote="true" data-bv-remote-url="remote.php" data-bv-remote-message="The username is not available"
    />
```

* Support [HTML 5 input types](http://formvalidation.io/examples/#html5):

HTML 5 attribute      | Validator
----------------------|----------
```min="..."```       | [greaterThan validator](http://formvalidation.io/validators/greaterThan/)
```max="..."```       | [lessThan validator](http://formvalidation.io/validators/lessThan/)
```maxlength="..."``` | [stringLength validator](http://formvalidation.io/validators/stringLength/)
```pattern="..."```   | [regexp validator](http://formvalidation.io/validators/regexp/)
```required```        | [notEmpty validator](http://formvalidation.io/validators/notEmpty/)
```type="color"```    | [hexColor validator](http://formvalidation.io/validators/color/)
```type="email"```    | [emailAddress validator](http://formvalidation.io/validators/emailAddress/)
```type="range"```    | [between validator](http://formvalidation.io/validators/between/)
```type="url"```      | [uri validator](http://formvalidation.io/validators/uri/)

* [#74](https://github.com/formvalidation/formvalidation/issues/74), [#103](https://github.com/formvalidation/formvalidation/issues/103), [#122](https://github.com/formvalidation/formvalidation/issues/122): Set the custom [trigger event](http://formvalidation.io/settings/#trigger)

It's possible to use ```data-bv-trigger``` attribute:

```html
<form data-bv-trigger="keyup">
    <input type="text" class="form-control" name="firstName" placeholder="First name"
           data-bv-trigger="keyup" />
    ...
    <input type="text" class="form-control" name="lastName" placeholder="First name"
           data-bv-trigger="blur" />
</form>
```

or ```trigger``` option:

```javascript
$(form).bootstrapValidator({
    trigger: 'blur',            // Set for all fields
    fields: {
        firstName: {
            trigger: 'keyup',   // Custom for each field. Can be 'event1 event2 event3'
            validators: {
                ...
            }
        },
        lastName: {
            trigger: 'blur',
            validators: {
                ...
            }
        }
    }
});
```

* [#136](https://github.com/formvalidation/formvalidation/issues/136): Support multiple elements with the [same name](http://formvalidation.io/examples/#fields-with-same-name)

```html
<div class="form-group">
    <input class="form-control" type="text" name="surveyAnswer[]" />
</div>
<div class="form-group">
    <input class="form-control" type="text" name="surveyAnswer[]" />
</div>
<div class="form-group">
    <input class="form-control" type="text" name="surveyAnswer[]" />
</div>
```

* [#109](https://github.com/formvalidation/formvalidation/issues/109): Add [```setLiveMode()``` method](http://formvalidation.io/api/#set-live-mode) to turn on/off the live validating mode
* [#114](https://github.com/formvalidation/formvalidation/issues/114): Add [iban validator](http://formvalidation.io/validators/iban/) for validating IBAN (International Bank Account Number)
* [#116](https://github.com/formvalidation/formvalidation/issues/116): Add [uuid validator](http://formvalidation.io/validators/uuid/), support UUID v3, v4, v5
* [#128](https://github.com/formvalidation/formvalidation/issues/128): Add [numeric validator](http://formvalidation.io/validators/numeric/)
* [#135](https://github.com/formvalidation/formvalidation/issues/135): Add [integer validator](http://formvalidation.io/validators/integer/)
* [#138](https://github.com/formvalidation/formvalidation/issues/138): Add [hex validator](http://formvalidation.io/validators/hex/)
* [#139](https://github.com/formvalidation/formvalidation/issues/139): Add [stringCase validator](http://formvalidation.io/validators/stringCase/) to check a string is lower or upper case
* [#137](https://github.com/formvalidation/formvalidation/issues/137): Register the plugin with [jQuery plugins site](http://plugins.jquery.com/)
* [#133](https://github.com/formvalidation/formvalidation/issues/133): The [regexp validator](http://formvalidation.io/validators/regexp/) allows to pass a string
* [#140](https://github.com/formvalidation/formvalidation/pull/140): Do not validate hidden (```type="hidden"```) and invisible element, thanks to [@easonhan007](https://github.com/easonhan007)
* [```disableSubmitButtons()```](http://formvalidation.io/api/#disable-submit-buttons) is now marked as a public API
* The first parameter of [```updateStatus()``` method](http://formvalidation.io/api/#update-status) now accepts the field name only
* [#126](https://github.com/formvalidation/formvalidation/issues/126): Submit button remains disabled after calling custom ```submitHandler``` and the form is valid
* [#132](https://github.com/formvalidation/formvalidation/issues/132): The ```fields.[fieldName].message``` option is not used when showing the error message

## v0.3.3 (2014-03-27)

* [#50](https://github.com/formvalidation/formvalidation/issues/50): Don't validate disabled element
* [#34](https://github.com/formvalidation/formvalidation/issues/34), [#105](https://github.com/formvalidation/formvalidation/issues/105): Cannot call ```form.submit()``` inside [```submitHandler```](http://formvalidation.io/settings/#submit-handler)
* [#77](https://github.com/formvalidation/formvalidation/issues/77), [#117](https://github.com/formvalidation/formvalidation/issues/117): The [notEmpty validator](http://formvalidation.io/validators/notEmpty/) doesn't work on file input
* [#120](https://github.com/formvalidation/formvalidation/pull/120): Handle case where a field is removed after the bootstrap validation, thanks to [@patmoore](https://github.com/patmoore)

## v0.3.2 (2014-03-21)

* [#56](https://github.com/formvalidation/formvalidation/issues/56): Add [```selector``` option](http://formvalidation.io/settings/#fields) for each field. The field can be defined by CSS validator instead of the ```name``` attribute
* [#107](https://github.com/formvalidation/formvalidation/issues/107): Add [```container``` option](http://formvalidation.io/settings/#fields) for each field to indicate where the error messages are shown
* [#5](https://github.com/formvalidation/formvalidation/issues/5): Add [ip validator](http://formvalidation.io/validators/ip/). Support both IPv4 and IPv6
* [#6](https://github.com/formvalidation/formvalidation/issues/6): Add [isbn validator](http://formvalidation.io/validators/isbn/), support both ISBN 10 and ISBN 13
* [#7](https://github.com/formvalidation/formvalidation/issues/7): Add [step validator](http://formvalidation.io/validators/step/)
* [#95](https://github.com/formvalidation/formvalidation/issues/95): Add [mac validator](http://formvalidation.io/validators/mac/)
* [#96](https://github.com/formvalidation/formvalidation/issues/96): Add [base64 validator](http://formvalidation.io/validators/base64/)
* [#97](https://github.com/formvalidation/formvalidation/issues/97): Add [cvv validator](http://formvalidation.io/validators/cvv/)
* [#99](https://github.com/formvalidation/formvalidation/issues/99), [#100](https://github.com/formvalidation/formvalidation/pull/100): Add [phone validator](http://formvalidation.io/validators/phone/). Support US phone number only, thanks to [@gercheq](https://github.com/gercheq)
* [#112](https://github.com/formvalidation/formvalidation/issues/112): [creditCard validator](http://formvalidation.io/validators/creditCard/) now validates both IIN ranges and length

## v0.3.1 (2014-03-17)

* [#4](https://github.com/formvalidation/formvalidation/issues/4): Add [date validator](http://formvalidation.io/validators/date/)
* [#72](https://github.com/formvalidation/formvalidation/issues/72), [#79](https://github.com/formvalidation/formvalidation/issues/79): Improve [```updateStatus()``` method](http://formvalidation.io/api/#update-status) to make the plugin play well with another
* [#80](https://github.com/formvalidation/formvalidation/issues/80): Add [```enabled``` option](http://formvalidation.io/settings/#fields) and [```enableFieldValidators()``` method](http://formvalidation.io/api/#enable-field-validators) to enable/disable all validators to given field
* [#90](https://github.com/formvalidation/formvalidation/pull/90): Add ```bower.json``` file, thanks to [@ikanedo](https://github.com/ikanedo)
* [#3](https://github.com/formvalidation/formvalidation/issues/3), [#92](https://github.com/formvalidation/formvalidation/issues/92): Support more form controls on the same row
* Remove the ```columns``` option. Now the plugin works normally no matter how many columns the form uses
* [#102](https://github.com/formvalidation/formvalidation/issues/102): The [```resetForm``` method](http://formvalidation.io/api/#reset-form) now only resets fields with validator rules
* [#82](https://github.com/formvalidation/formvalidation/issues/82), [#84](https://github.com/formvalidation/formvalidation/issues/84): The error messages aren't shown if the form field doesn't have label
* [#89](https://github.com/formvalidation/formvalidation/issues/89): [```submitHandler```](http://formvalidation.io/settings/#submit-handler) or default submission isn't called after [remote validation](http://formvalidation.io/validators/remote/) completes

## v0.3.0 (2014-03-10)

* [#44](https://github.com/formvalidation/formvalidation/issues/44): Rewrite entirely using Deferred
* [#26](https://github.com/formvalidation/formvalidation/issues/26), [#27](https://github.com/formvalidation/formvalidation/issues/27), [#67](https://github.com/formvalidation/formvalidation/pull/67): Add [choice validator](http://formvalidation.io/validators/choice/), thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#31](https://github.com/formvalidation/formvalidation/issues/31): The [remote validator](http://formvalidation.io/validators/remote/) supports dynamic data
* [#36](https://github.com/formvalidation/formvalidation/issues/36), [#58](https://github.com/formvalidation/formvalidation/issues/58): Add method to [validate form](http://formvalidation.io/api/#validate) manually
* [#41](https://github.com/formvalidation/formvalidation/issues/41): Disable submit button on successful form submit
* [#42](https://github.com/formvalidation/formvalidation/issues/42): Add submit button to [```submitHandler()```](http://formvalidation.io/settings/#submit-handler) parameter
* [#48](https://github.com/formvalidation/formvalidation/issues/48): Add optional [feedback icons](http://formvalidation.io/settings/#feedback-icons)
* [#64](https://github.com/formvalidation/formvalidation/pull/64): Support [Danish zip code](http://formvalidation.io/validators/zipCode/), thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#65](https://github.com/formvalidation/formvalidation/pull/65): Support [Sweden zip code](http://formvalidation.io/validators/zipCode/), thanks to [@emilchristensen](https://github.com/emilchristensen)
* [#70](https://github.com/formvalidation/formvalidation/issues/70): Support custom grid columns
* [#71](https://github.com/formvalidation/formvalidation/issues/71): Show all errors
* [#76](https://github.com/formvalidation/formvalidation/issues/76): Add [```resetForm()``` method](http://formvalidation.io/api/#reset-form)
* [#50](https://github.com/formvalidation/formvalidation/issues/50): Don't validate disabled element
* [#51](https://github.com/formvalidation/formvalidation/issues/51): Submit after submit doesn't work
* [#53](https://github.com/formvalidation/formvalidation/issues/53), [#54](https://github.com/formvalidation/formvalidation/pull/54): Fix [notEmpty validator](http://formvalidation.io/validators/notEmpty/) for radios and checkboxes, thanks to [@kristian-puccio](https://github.com/kristian-puccio)
* [#55](https://github.com/formvalidation/formvalidation/issues/55): The plugin doesn't validate other fields if the [remote validator](http://formvalidation.io/validators/remote/) returns ```true```
* [#62](https://github.com/formvalidation/formvalidation/pull/62): The [callback validator](http://formvalidation.io/validators/callback/) passes wrong parameter, thanks to [@iplus](https://github.com/iplus)
* [#59](https://github.com/formvalidation/formvalidation/pull/59): Add example for Rail field convention, thanks to [@narutosanjiv](https://github.com/narutosanjiv)
* [#60](https://github.com/formvalidation/formvalidation/pull/60): Update the installation guide, thanks to [@vaz](https://github.com/vaz)
* [#73](https://github.com/formvalidation/formvalidation/issues/73): Describe which version should be [included](http://formvalidation.io/getting-started/#including-library) in the Usage section

## v0.2.2 (2014-01-07)

* [#15](https://github.com/formvalidation/formvalidation/issues/15): Focus to the first invalid element
* [#31](https://github.com/formvalidation/formvalidation/issues/31): [remote validator](http://formvalidation.io/validators/remote/): Allow to set additional data to remote URL
* [#32](https://github.com/formvalidation/formvalidation/issues/32), [#43](https://github.com/formvalidation/formvalidation/issues/43), [#47](https://github.com/formvalidation/formvalidation/issues/47): Only validate not empty field
* [#39](https://github.com/formvalidation/formvalidation/issues/39): Validate existing fields only
* [#34](https://github.com/formvalidation/formvalidation/issues/34): Avoid from calling form submit recursively
* [#40](https://github.com/formvalidation/formvalidation/issues/40): Fix the issue when the form label doesn't have class

## v0.2.1 (2013-11-08)

* [#29](https://github.com/formvalidation/formvalidation/issues/29): Upgrade Bootstrap to v3.0.2
* [#30](https://github.com/formvalidation/formvalidation/issues/30): Hide the error block containers before validating

## v0.2.0 (2013-10-21)

* [#24](https://github.com/formvalidation/formvalidation/issues/24): Add [```live``` option](http://formvalidation.io/settings/#live)
* [#20](https://github.com/formvalidation/formvalidation/issues/20): Add custom submit handler using [```submitHandler``` option](http://formvalidation.io/settings/#submit-handler)
* [#9](https://github.com/formvalidation/formvalidation/issues/9): Add [creditCard validator](http://formvalidation.io/validators/creditCard/)
* [#18](https://github.com/formvalidation/formvalidation/issues/18): Add [different validator](http://formvalidation.io/validators/different/)
* [#21](https://github.com/formvalidation/formvalidation/issues/21): Add [callback validator](http://formvalidation.io/validators/callback/)
* [#22](https://github.com/formvalidation/formvalidation/issues/22): Support form that labels are placed in extra small (```col-xs-```), small (```col-sm-```), medium (```col-md-```) elements
* [#25](https://github.com/formvalidation/formvalidation/issues/25): The [regexp validator](http://formvalidation.io/validators/regexp/) does not work

## v0.1.1 (2013-10-17)

* Added [```submitButtons``` option](http://formvalidation.io/settings/#submit-buttons)
* [#16](https://github.com/formvalidation/formvalidation/issues/16): Added disabling client side validation in HTML 5
* [#17](https://github.com/formvalidation/formvalidation/issues/17): Added support for default Bootstrap form without labels
* [#19](https://github.com/formvalidation/formvalidation/issues/19): Added support for select box validator

## v0.1.0 (2013-10-14)

* First release
* Provide various validators:
    - [between validator](http://formvalidation.io/validators/between/)
    - [digits validator](http://formvalidation.io/validators/digits/)
    - [emailAddress validator](http://formvalidation.io/validators/emailAddress/)
    - [greaterThan validator](http://formvalidation.io/validators/greaterThan/)
    - [hexColor validator](http://formvalidation.io/validators/color/)
    - [identical validator](http://formvalidation.io/validators/identical/)
    - [lessThan validator](http://formvalidation.io/validators/lessThan/)
    - [notEmpty validator](http://formvalidation.io/validators/notEmpty/)
    - [regexp validator](http://formvalidation.io/validators/regexp/)
    - [remote validator](http://formvalidation.io/validators/remote/)
    - [stringLength validator](http://formvalidation.io/validators/stringLength/)
    - [uri validator](http://formvalidation.io/validators/uri/)
    - [zipCode validator](http://formvalidation.io/validators/zipCode/)