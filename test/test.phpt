<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.07.17
 * Time: 14:46
 */
namespace Niklas\Test;
use Niklas\DAO;
use Tester\Assert;
use Tester\Environment;

require "../vendor/autoload.php";

Environment::setup();
$dao = new DAO("127.0.0.1", "dbuser", "dbpass", "NiklasDAOTest");
$dao->query("SELECT * FROM TestTable WHERE id= ?", ["2"])->one($test = new TestTable());
print_r($test);
Assert::equal("2", $test->column1);