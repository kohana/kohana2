<?php defined('SYSPATH') or die('No direct script access.');

// Phone prefix and format
$locale['phone_prefix'] = '1';
$locale['phone_format'] = '3-3-4';

// State names
$locale['states'] = array
(
	'AL' => 'Alabama',
	'AK' => 'Alaska',
	'AZ' => 'Arizona',
	'AR' => 'Arkansas',
	'CA' => 'California',
	'CO' => 'Colorado',
	'CT' => 'Connecticut',
	'DE' => 'Delaware',
	'DC' => 'District of Columbia',
	'FL' => 'Florida',
	'GA' => 'Georgia',
	'GU' => 'Guam',
	'HI' => 'Hawaii',
	'ID' => 'Idaho',
	'IL' => 'Illinois',
	'IN' => 'Indiana',
	'IA' => 'Iowa',
	'KS' => 'Kansas',
	'KY' => 'Kentucky',
	'LA' => 'Louisiana',
	'ME' => 'Maine',
	'MD' => 'Maryland',
	'MA' => 'Massachusetts',
	'MI' => 'Michigan',
	'MN' => 'Minnesota',
	'MS' => 'Mississippi',
	'MO' => 'Missouri',
	'MT' => 'Montana',
	'NE' => 'Nebraska',
	'NV' => 'Nevada',
	'NH' => 'New Hampshire',
	'NJ' => 'New Jersey',
	'NM' => 'New Mexico',
	'NY' => 'New York',
	'NC' => 'North Carolina',
	'ND' => 'North Dakota',
	'OH' => 'Ohio',
	'OK' => 'Oklahoma',
	'OR' => 'Oregon',
	'PA' => 'Pennsylvania',
	'RI' => 'Rhode Island',
	'SC' => 'South Carolina',
	'SD' => 'South Dakota',
	'TN' => 'Tennessee',
	'TX' => 'Texas',
	'UT' => 'Utah',
	'VT' => 'Vermont',
	'VA' => 'Virginia',
	'WA' => 'Washington',
	'WV' => 'West Virginia',
	'WI' => 'Wisconsin',
	'WY' => 'Wyoming',
);

// States with territories
$locale['all_states'] = array_merge($locale['states'], array
(
	'AS' => 'American Samoa',
	'FM' => 'Federated States of Micronesia',
	'MH' => 'Marshall Islands',
	'MP' => 'Northern Mariana Islands',
	'PW' => 'Palau',
	'PR' => 'Puerto Rico',
	'VI' => 'Virgin Islands',
));
// Re-sort the list
ksort($locale['all_states']);

// Month names
$locale['months'] = array
(
	1  => array('short' => 'Jan', 'long' => 'January'),
	2  => array('short' => 'Feb', 'long' => 'February'),
	3  => array('short' => 'Mar', 'long' => 'March'),
	4  => array('short' => 'Apr', 'long' => 'April'),
	5  => array('short' => 'May', 'long' => 'May'),
	6  => array('short' => 'Jun', 'long' => 'June'),
	7  => array('short' => 'Jul', 'long' => 'July'),
	8  => array('short' => 'Aug', 'long' => 'August'),
	9  => array('short' => 'Sep', 'long' => 'September'),
	10 => array('short' => 'Oct', 'long' => 'October'),
	11 => array('short' => 'Nov', 'long' => 'November'),
	12 => array('short' => 'Dec', 'long' => 'December'),
);

// Day names
$locale['days'] = array
(
	0 => array('short' => 'Sun', 'long' => 'Sunday'),
	1 => array('short' => 'Mon', 'long' => 'Monday'),
	2 => array('short' => 'Tue', 'long' => 'Tuesday'),
	3 => array('short' => 'Wed', 'long' => 'Wednesday'),
	4 => array('short' => 'Thu', 'long' => 'Thursday'),
	5 => array('short' => 'Fri', 'long' => 'Friday'),
	6 => array('short' => 'Sat', 'long' => 'Saturday'),
);