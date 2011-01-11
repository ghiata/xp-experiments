uses('lang.reflect.Field', 'lang.reflect.Method');

// {{{ XPClass
function XPClass(name) {
  {
    this.__class = 'lang.XPClass';
    this.name = name;
  }
}

XPClass.forName = function(name) {
  uses(name);
  return new XPClass(name);
}

XPClass.prototype= new Object();

XPClass.prototype.toString = function() {
  return this.getClassName() + '<' + this.name + '>';
}

XPClass.prototype.getName = function() {
  return this.name;
}

XPClass.prototype.newInstance = function() {
  return new ($xp[this.name])();
}

XPClass.prototype.getMethod = function(name) {
  if (typeof($xp[this.name][name]) !== 'function') {
    throw new IllegalArgumentException('No such method ' + this.name + '::' + name);
  }
  return new Method(this, name);
}

XPClass.prototype.getMethods = function() {
  var methods = new Array();
  var gather = function(self, object, parent, modifiers) {
    for (var member in object) {
      if ((parent || object.hasOwnProperty(member)) && typeof(object[member]) === 'function') {
        methods.push(new Method(self, member, modifiers));
      }
    }
  };

  gather(this, $xp[this.name], false, Modifiers.STATIC);
  gather(this, $xp[this.name].prototype, true, 0);
  return methods;
}

XPClass.prototype.getField = function(name) {
  if (typeof($xp[this.name][name]) === 'function') {
    throw new IllegalArgumentException('No such field ' + this.name + '::' + name);
  }
  return new Field(this, name);
}

XPClass.prototype.getFields = function() {
  var fields = new Array();
  var gather = function(self, object, parent, modifiers) {
    for (var member in object) {
      if ((parent || object.hasOwnProperty(member)) && typeof(object[member]) !== 'function') {
        fields.push(new Field(self, member, modifiers));
      }
    }
  };

  gather(this, $xp[this.name], false, Modifiers.STATIC);
  gather(this, $xp[this.name].prototype, true, 0);
  return fields;
}
// }}}