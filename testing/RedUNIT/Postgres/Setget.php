<?php
/**
 * RedUNIT_Postgres_Setget
 *
 * @file    RedUNIT/Postgres/Setget.php
 * @desc    Tests whether values are correctly stored.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedUNIT_Postgres_Setget extends RedUNIT_Postgres
{
	public function testNumbers()
	{
		asrt( setget( "-1" ), "-1" );
		asrt( setget( -1 ), "-1" );

		asrt( setget( "-0.25" ), "-0.25" );
		asrt( setget( -0.25 ), "-0.25" );

		asrt( setget( "0.12345678" ), "0.12345678" );
		asrt( setget( 0.12345678 ), "0.12345678" );

		asrt( setget( "-0.12345678" ), "-0.12345678" );
		asrt( setget( -0.12345678 ), "-0.12345678" );

		asrt( setget( "2147483647" ), "2147483647" );
		asrt( setget( 2147483647 ), "2147483647" );

		asrt( setget( -2147483647 ), "-2147483647" );
		asrt( setget( "-2147483647" ), "-2147483647" );

		asrt( setget( "2147483648" ), "2147483648" );
		asrt( setget( "-2147483648" ), "-2147483648" );

		asrt( setget( "199936710040730" ), "199936710040730" );
		asrt( setget( "-199936710040730" ), "-199936710040730" );
	}

	public function testDates()
	{
		asrt( setget( "2010-10-11" ), "2010-10-11" );

		asrt( setget( "2010-10-11 12:10" ), "2010-10-11 12:10" );

		asrt( setget( "2010-10-11 12:10:11" ), "2010-10-11 12:10:11" );

		asrt( setget( "x2010-10-11 12:10:11" ), "x2010-10-11 12:10:11" );
	}

	public function testStrings()
	{
		asrt( setget( "a" ), "a" );

		asrt( setget( "." ), "." );

		asrt( setget( "\"" ), "\"" );

		asrt( setget( "just some text" ), "just some text" );
	}

	public function testBool()
	{
		asrt( setget( true ), "1" );
		asrt( setget( false ), "0" );

		asrt( setget( "true" ), "true" );
		asrt( setget( "false" ), "false" );
	}

	public function testNull()
	{
		asrt( setget( "null" ), "null" );
		asrt( setget( "NULL" ), "NULL" );

		asrt( setget( null ), null );

		asrt( ( setget( 0 ) == 0 ), true );
		asrt( ( setget( 1 ) == 1 ), true );

		asrt( ( setget( true ) == true ), true );
		asrt( ( setget( false ) == false ), true );
	}
}
