<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) {
	die('No direct script access allowed');
}
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//  Copyright (C) 2006 Magnus Ullberg (magnus@ullberg.us)
//  Portions Copyright (C) 2010 Mikael Carlsson (mickecamino@gmail.com)
//	Copyright 2013 Schmooze Com Inc.
//  Copyright 2018 Sangoma Technologies, Inc

include __DIR__ . '/functions.migrated.php';

//not sure how to make this BMO
function blacklist_hookGet_config($engine) {
	global $ext;
	switch ($engine) {
		case "asterisk":
			// Code from modules/core/functions.inc.php core_get_config inbound routes
			$didlist = core_did_list();
			if (is_array($didlist)) {
				foreach ($didlist as $item) {

					$exten  = trim((string) $item['extension']);
					$cidnum = trim((string) $item['cidnum']);

					if ($cidnum != '' && $exten == '') {
						$exten  = 's';
						$pricid = ($item['pricid']) ? true : false;
					}
					else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
						$pricid = true;
					}
					else {
						$pricid = false;
					}
					$context = ($pricid) ? "ext-did-0001" : "ext-did-0002";

					if (function_exists("empty_freepbx")) {
						$exten = empty_freepbx($exten) ? "s" : $exten;
					}
					else {
						$exten = (empty($exten) ? "s" : $exten);
					}

					$exten = $exten . (empty($cidnum) ? "" : "/" . $cidnum); //if a CID num is defined, add it
					$ext->splice($context, $exten, 'did', new ext_set('returnhere', '1'));
					$ext->splice($context, $exten, 'did', new ext_gosub('1', 's', 'app-blacklist-check'));
					$ext->splice($context, $exten, 'callerid', new ext_gotoif('${LEN(${BLDEST})}', '${BLDEST}'));
				}
			} // else no DID's defined. Not even a catchall.
			break;
	}
}