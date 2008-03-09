<?php defined('SYSPATH') or die('No direct script access.');

class Valid_Helper_Test {

	public $valid_emails = array
	(
		'l3tt3rsAndNumb3rs@domain.com',
		'has-dash@domain.com',
		'hasApostrophe.o\'leary@domain.org',
		'sixLetterTLD@domain.museum',
		'lettersInDomain@911.com',
		'underscore_inLocal@domain.net',
		'IPInsteadOfDomain@127.0.0.1',
		'IPAndPort@127.0.0.1:25',
		'subdomain@sub.domain.com',
		'local@dash-inDomain.com',
		'dot.inLocal@foo.com',
		'a@singleLetterLocal.org',
		'&*=?^+{}\'~@validCharsInLocal.net',
		'singleLetterDomain@x.org',
	);

	public $invalid_emails = array
	(
		'missingDomain@.com',
		'@missingLocal.org',
		'missingatSign.net',
		'missingDot@com',
		'two@@signs.com',
		'colonButNoPort@127.0.0.1:',
		'someone-else@127.0.0.1.26',
		'.localStartsWithDot@domain.com',
		'localEndsWithDot.@domain.com',
		'two..consecutiveDots@domain.com',
		'domainStartsWithDash@-domain.com',
		'domainEndsWithDash@domain-.com',
		'numbersInTLD@domain.c0m',
		'missingTLD@domain.',
		'! "#$%(),/;<>[]`|@invalidCharsInLocal.org',
		'invalidCharsInDomain@! "#$%(),/;<>_[]`|.org',
		// The two emails below make the email_test fail, but I'm okay with that.
		'TLDDoesntExist@domain.moc', 
		'local@SecondLevelDomainNamesAreInvalidIfTheyAreLongerThan64Charactersss.org',
	);

	public function email_test()
	{
		foreach ($this->valid_emails as $email)
		{
			assert::true(valid::email($email));
		}

		foreach ($this->invalid_emails as $email)
		{
			assert::false(valid::email($email));
		}
	}

	public function numeric_test()
	{
		$num = +0123.45e6; // echo $num; => 123450000
		assert::true(valid::numeric($num));
	}

}
