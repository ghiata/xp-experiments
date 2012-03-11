uses('unittest.TestCase');

// {{{ DemoTest
tests.DemoTest = function(name) {
  {
    unittest.TestCase.call(this, name);
    this.__class = 'tests.DemoTest';
  }
}

tests.DemoTest.prototype= new unittest.TestCase();

tests.DemoTest.prototype.testSucceeds = function() {
  this.assertEquals(1, 1);
}
tests.DemoTest.prototype.testSucceeds['@']= { test : null };

tests.DemoTest.prototype.testFails = function() {
  this.assertEquals(1, 0);
}
tests.DemoTest.prototype.testFails['@']= { test : null };
// }}}
