// {{{ Say
function Say() {
  {
    this.__class = 'Say';
  }
}

Say.main = function(args) {
  new Say().hello(args[0]);
}

Say.prototype= new Object();

Say.prototype.greeting = 'Hello';

Say.prototype.hello= function(name) {
  Console.writeLine(this.greeting, ' ', name);
}
// }}}