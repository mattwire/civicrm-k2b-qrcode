# k2b-qrcode

![Screenshot](/images/screenshot.png)

This implements a hook for net.ourpowerbase.qrcodecheckin that generates qrcode tokens to be used for a bus ticket.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.6+
* CiviCRM 5.3.1+
* net.ourpowerbase.qrcodecheckin (https://github.com/progressivetech/net.ourpowerbase.qrcodecheckin).

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl k2b-qrcode@https://github.com/FIXME/k2b-qrcode/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/k2b-qrcode.git
cv en k2b_qrcode
```

## Usage

Include the qrcodecheckin tokens in an email.
  
#### The qrcode in the email will contain:
* Their contact ID
* Bus Pickup Time (custom field)
* Their Bus Pickup Point (custom_field)

#### Email contains:
* Their name
* New Custom Field:Bus Pickup Time
* Their Bus Pickup Point
