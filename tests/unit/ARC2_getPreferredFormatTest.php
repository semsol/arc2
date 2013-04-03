<?php

require_once '../ARC2_TestCase.php';

class ARC2_getPreferredFormatTest extends ARC2_TestCase {

	public function testGetPreferredFormat() {
		$_SERVER['HTTP_ACCEPT'] = '';
		$actual = ARC2::getPreferredFormat('xml');
		$this->assertEquals('XML', $actual);
		
		$actual = ARC2::getPreferredFormat('foo');
		$this->assertEquals(null, $actual);
		
		$_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
		$actual = ARC2::getPreferredFormat();
		$this->assertEquals('HTML', $actual);
		
		$_SERVER['HTTP_ACCEPT'] = 'application/rdf+xml,text/html;q=0.9,*/*;q=0.8';
		$actual = ARC2::getPreferredFormat();
		$this->assertEquals('RDFXML', $actual);
	}

}
