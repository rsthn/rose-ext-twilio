# Twilio Adapter for Rose

This extension adds expression functions to [Rose](https://github.com/rsthn/rose-core) to send SMS using Twilio.

<br/>

# Installation

```sh
composer require rsthn/rose-ext-twilio
```

<br/>

## Configuration Section: `Twilio`

The configuration section should have the default settings that the extension will use when using the `twilio::send` function. If you want to change these runtime, use `twilio::config` first.

|Field|Type|Description|Default|
|----|----|-----------|-------|
|sid|`string`|SID of Twilio API|Required
|token|`string`|Access Token|Required
|from|`string`|Phone number used as "send from".|Required

<br/>

## Expression Functions

### `twilio::send` targetPhoneNumber:string message:string

Sends an SMS to the specified phone number.

Returns: `{ error:string, errorCode:string, sid:string, price:number }`

Example:

```clojure
(twilio::send "+11115558888" "Hello World")
```

### `twilio::config` settings:object

Changes one or more configuration settings. The `settings` object should have one or more fields just as the configuration section.

Returns: `boolean`

Example:

```clojure
(twilio::set (& "sid" "SID_VALUE"))
```
