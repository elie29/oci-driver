<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class CheckTest extends TestCase
{

  public function testTrue(): void
  {
    self::assertTrue(true);
  }
}